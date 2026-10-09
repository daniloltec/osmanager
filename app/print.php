<?php
require_once __DIR__ . '/common.php';
page_header('Impressão OS');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order = $DB->query("SELECT * FROM osmanager_orders WHERE id = $id")->fetch_assoc();
if (!$order) {
    echo "<div class='card'>OS não encontrada.</div>";
    page_footer();
    exit;
}

$tickets_id = (int) $order['tickets_id'];
$ticket = $DB->query("SELECT t.id, t.name, t.date, e.name AS entity FROM glpi_tickets t LEFT JOIN glpi_entities e ON e.id = t.entities_id WHERE t.id = $tickets_id")->fetch_assoc();
$cr = $DB->query("SELECT * FROM osmanager_checklist WHERE orders_id = $id ORDER BY id");
?>
<div class="card no-print">
  <button class="btn" onclick="window.print()">Imprimir / Salvar PDF</button>
  <a class="btn" href="os.php?id=<?= $id ?>">Editar</a>
  <a class="btn" href="index.php">Voltar</a>
</div>

<div class="card" style="max-width:800px;margin:auto">
  <h2 style="text-align:center;margin-top:0">ORDEM DE SERVIÇO #<?= h($order['id']) ?></h2>
  <table style="border:none">
    <tr><td><b>Chamado GLPI:</b> #<?= h($ticket['id'] ?? $tickets_id) ?></td><td><b>Data:</b> <?= h($order['date_creation']) ?></td></tr>
    <tr><td><b>Título:</b> <?= h($ticket['name'] ?? '') ?></td><td><b>Entidade:</b> <?= h($ticket['entity'] ?? '') ?></td></tr>
    <tr><td><b>Solicitante:</b> <?= h($order['requester']) ?></td><td><b>Status:</b> <?= h(os_status_label($order['status'])) ?></td></tr>
  </table>

  <h3>Diagnóstico / Serviço</h3>
  <p><?= nl2br(h($order['diagnosis'])) ?: '<span class="muted">—</span>' ?></p>

  <h3>Checklist</h3>
  <table>
    <tr><th style="width:40px">OK</th><th>Item</th></tr>
    <?php while ($c = $cr->fetch_assoc()): ?>
    <tr><td><?= $c['checked'] ? '☑' : '☐' ?></td><td><?= h($c['label']) ?></td></tr>
    <?php endwhile; ?>
  </table>

  <div class="row" style="margin-top:40px">
    <div>_______________________________<br>Técnico: <?= h($order['technician']) ?></div>
    <div>_______________________________<br>Cliente: <?= h($order['client_name']) ?></div>
  </div>
</div>
<?php page_footer(); ?>
