<?php

/**
 * Gera o PDF profissional da Ordem de Serviço usando o TCPDF do próprio GLPI.
 */

include('../../../inc/includes.php');

Session::checkRight('plugin_osmanager', READ);

$orders_id = (int)($_GET['orders_id'] ?? 0);
$order = new PluginOsmanagerOrder();
if (!$order->getFromDB($orders_id)) {
    Html::displayErrorAndDie('OS inválida', true);
}
$ticket = new Ticket();
$ticket->getFromDB($order->fields['tickets_id']);

require_once(GLPI_ROOT . '/vendor/tecnickcom/tcpdf/tcpdf.php');

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
$pdf->SetCreator('OS Manager');
$pdf->SetAuthor(plugin_osmanager_config_get('company', 'Empresa'));
$pdf->SetTitle('Ordem de Serviço #' . $order->getID());
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);

$company = plugin_osmanager_config_get('company', 'Empresa');
$logo = plugin_osmanager_config_get('logo_path', '');

// Cabeçalho.
if ($logo && file_exists($logo)) {
    $pdf->Image($logo, 12, 10, 40);
}
$pdf->SetXY(120, 12);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 8, 'ORDEM DE SERVIÇO', 0, 1, 'R');
$pdf->SetFont('helvetica', '', 10);
$pdf->SetX(120);
$pdf->Cell(0, 6, 'OS #' . $order->getID() . '  |  Ticket #' . $ticket->fields['id'], 0, 1, 'R');
$pdf->Ln(4);

$entity = new Entity(); $entity->getFromDB($ticket->fields['entities_id']);

$category = new ITILCategory(); $category->getFromDB($ticket->fields['itilcategories_id']);

// Técnico responsável.
$assign = '';
$user = new User();
$tu = new Ticket_User();
foreach ($tu->find(['tickets_id' => $ticket->fields['id'], 'type' => Ticket_User::ASSIGN]) as $link) {
    if ($user->getFromDB($link['users_id'])) {
        $assign = $user->getFriendlyName();
        break;
    }
}
$requester = $user->getFromDB($ticket->fields['users_id_recipient'])
    ? $user->getFriendlyName() : '';

function row($pdf, $l, $v) {
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(45, 6, $l, 1, 0);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(0, 6, ' ' . $v, 1, 1);
}

$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, 'Dados do Chamado', 0, 1);
row($pdf, 'Empresa', $entity->fields['name'] ?? '');
row($pdf, 'Solicitante', $requester);
row($pdf, 'Técnico', $assign);
row($pdf, 'Abertura', $ticket->fields['date']);
row($pdf, 'Prioridade', Ticket::getPriorityName($ticket->fields['priority']));
row($pdf, 'Categoria', $category->fields['name'] ?? '');
$pdf->Ln(2);

// Checklist.
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, 'Checklist', 0, 1);
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(10, 6, '#', 1, 0, 'C');
$pdf->Cell(120, 6, 'Item', 1, 0);
$pdf->Cell(25, 6, 'Status', 1, 0, 'C');
$pdf->Cell(0, 6, 'Obs', 1, 1);
foreach (PluginOsmanagerOrderChecklist::getForOrder($order->getID()) as $row) {
    $pdf->Cell(10, 6, $row['id'], 1, 0, 'C');
    $pdf->Cell(120, 6, ' ' . $row['label'], 1, 0);
    $pdf->Cell(25, 6, $row['checked'] ? 'OK' : '---', 1, 0, 'C');
    $pdf->Cell(0, 6, ' ' . ($row['note'] ?? ''), 1, 1);
}
$pdf->Ln(2);

// Diagnóstico.
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, 'Diagnóstico', 0, 1);
$pdf->SetFont('helvetica', '', 9);
$pdf->MultiCell(0, 5, $order->fields['diagnosis'] ?? '', 1);
$pdf->Ln(4);

// Assinaturas.
$signatures = PluginOsmanagerSignature::getForOrder($order->getID());
$base = defined('GLPI_PLUGIN_DOC_DIR') ? GLPI_PLUGIN_DOC_DIR : GLPI_DATA_DIR . '/plugins';
$dir = $base . '/osmanager/signatures';
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, 'Assinaturas', 0, 1);
$pdf->SetFont('helvetica', '', 9);
$y = $pdf->GetY();
if (isset($signatures['technician']) && file_exists($dir . '/' . $signatures['technician']['filename'])) {
    $pdf->Image($dir . '/' . $signatures['technician']['filename'], 15, $y, 70);
}
if (isset($signatures['client']) && file_exists($dir . '/' . $signatures['client']['filename'])) {
    $pdf->Image($dir . '/' . $signatures['client']['filename'], 120, $y, 70);
}
$pdf->Ln(30);
$pdf->Cell(90, 6, 'Técnico responsável', 'T', 0, 'C');
$pdf->Cell(0, 6, 'Cliente / Solicitante', 'T', 1, 'C');

// Rodapé.
$pdf->Ln(6);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 5, $company . ' — Gerado em ' . date('d/m/Y H:i') . ' via OS Manager', 0, 1, 'C');

$pdf->Output('OS_' . $order->getID() . '.pdf', 'I');
