<?php

/**
 * Tela principal da Ordem de Serviço.
 */

include('../../../inc/includes.php');

Session::checkRight('plugin_osmanager', READ);

$tickets_id = (int)($_GET['tickets_id'] ?? 0);
if (!$tickets_id) {
    Html::displayErrorAndDie('Ticket inválido', true);
}

$order = PluginOsmanagerOrder::getOrCreateForTicket($tickets_id);
if (!$order) {
    Html::displayErrorAndDie('Não foi possível carregar o Ticket', true);
}

PluginOsmanagerOrderChecklist::ensureForOrder($order->getID(), $order->fields['templates_id']);

// Salvar alterações.
if (isset($_POST['save'])) {
    $order->update([
        'id'        => $order->getID(),
        'status'    => (int)$_POST['status'],
        'diagnosis' => $_POST['diagnosis'] ?? '',
    ]);

    foreach (($_POST['check'] ?? []) as $cid => $val) {
        $ck = new PluginOsmanagerOrderChecklist();
        $ck->update([
            'id'      => (int)$cid,
            'checked' => 1,
            'note'    => $_POST['note'][$cid] ?? '',
        ]);
    }
    // Itens não marcados: garantir unchecked.
    foreach (PluginOsmanagerOrderChecklist::getForOrder($order->getID()) as $row) {
        if (!isset($_POST['check'][$row['id']])) {
            (new PluginOsmanagerOrderChecklist())->update([
                'id' => $row['id'], 'checked' => 0, 'note' => $_POST['note'][$row['id']] ?? ''
            ]);
        }
    }
    Html::redirect($_SERVER['REQUEST_URI']);
}

$ticket = new Ticket();
$ticket->getFromDB($tickets_id);

Html::header('Ordem de Serviço #' . $ticket->fields['id'], $_SERVER['PHP_SELF'], 'plugins', 'osmanager');

// ---- Dados do Ticket ----
$entity = new Entity();
$entity->getFromDB($ticket->fields['entities_id']);
$category = new ITILCategory();
$category->getFromDB($ticket->fields['itilcategories_id']);
$location = new Location();
$location->getFromDB($ticket->fields['locations_id']);

// Solicitante.
$requester = '';
$user = new User();
$tu = new Ticket_User();
foreach ($tu->find(['tickets_id' => $tickets_id, 'type' => Ticket_User::REQUESTER]) as $link) {
    if ($user->getFromDB($link['users_id'])) {
        $requester = $user->getFriendlyName();
        break;
    }
}
// Técnico responsável.
$assign = '';
foreach ($tu->find(['tickets_id' => $tickets_id, 'type' => Ticket_User::ASSIGN]) as $link) {
    if ($user->getFromDB($link['users_id'])) {
        $assign = $user->getFriendlyName();
        break;
    }
}
$phone = $user->fields['phone'] ?? '';
$email = $user->fields['email'] ?? '';

// Ativo associado (equipamento).
$equip = [];
$it = new Item_Ticket();
foreach ($it->find(['tickets_id' => $tickets_id]) as $link) {
    $itemtype = $link['itemtype'];
    if (class_exists($itemtype)) {
        $obj = new $itemtype();
        if ($obj->getFromDB($link['items_id'])) {
            $equip = [
                'type'  => $itemtype::getTypeName(1),
                'name'  => $obj->fields['name'] ?? '',
                'serial' => $obj->fields['serial'] ?? '',
                'otherserial' => $obj->fields['otherserial'] ?? '',
            ];
            break;
        }
    }
}

$signatures = PluginOsmanagerSignature::getForOrder($order->getID());
?>

<style>
.osm-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:8px 24px; }
.osm-grid div { font-size:13px; }
.osm-grid b { color:#555; }
.osm-card { background:#fff; border:1px solid #e3e3e3; border-radius:8px; padding:16px; margin-bottom:16px; }
.osm-canvas { border:1px solid #ccc; border-radius:6px; width:100%; height:140px; touch-action:none; }
</style>

<div class="osm-card">
    <h2>Ordem de Serviço #<?= $order->getID() ?> &mdash; Ticket #<?= $ticket->fields['id'] ?></h2>
    <div class="osm-grid">
        <div><b>Status OS:</b> <?= $order->getStatusLabel() ?></div>
        <div><b>Status Ticket:</b> <?= Ticket::getStatusName($ticket->fields['status']) ?></div>
        <div><b>Prioridade:</b> <?= Ticket::getPriorityName($ticket->fields['priority']) ?></div>
        <div><b>Urgência:</b> <?= Ticket::getUrgencyName($ticket->fields['urgency']) ?></div>
        <div><b>Impacto:</b> <?= Ticket::getImpactName($ticket->fields['impact']) ?></div>
        <div><b>Empresa:</b> <?= $entity->fields['name'] ?? '' ?></div>
        <div><b>Solicitante:</b> <?= $requester ?></div>
        <div><b>Técnico:</b> <?= $assign ?></div>
        <div><b>Telefone:</b> <?= $phone ?></div>
        <div><b>Email:</b> <?= $email ?></div>
        <div><b>Categoria:</b> <?= $category->fields['name'] ?? '' ?></div>
        <div><b>Localização:</b> <?= $location->fields['name'] ?? '' ?></div>
        <div><b>Abertura:</b> <?= $ticket->fields['date'] ?></div>
        <div><b>Atendimento:</b> <?= $ticket->fields['solvedate'] ?? '—' ?></div>
        <div><b>Equipamento:</b> <?= $equip['type'] ?? '—' ?> <?= $equip['name'] ?? '' ?></div>
        <div><b>Nº Série:</b> <?= $equip['serial'] ?? '—' ?></div>
        <div><b>Patrimônio:</b> <?= $equip['otherserial'] ?? '—' ?></div>
    </div>
</div>

<form method="post" action="">
<div class="osm-card">
    <h3>Checklist</h3>
    <table class="tab_cadre_fixe">
        <tr><th>#</th><th>Item</th><th>OK</th><th>Observação</th></tr>
        <?php foreach (PluginOsmanagerOrderChecklist::getForOrder($order->getID()) as $row): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['label']) ?></td>
            <td style="text-align:center;">
                <input type="checkbox" name="check[<?= $row['id'] ?>]" value="1"
                    <?= $row['checked'] ? 'checked' : '' ?>>
            </td>
            <td><input type="text" name="note[<?= $row['id'] ?>]" size="40"
                value="<?= htmlspecialchars($row['note'] ?? '') ?>"></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="osm-card">
    <h3>Diagnóstico</h3>
    <textarea name="diagnosis" rows="6" style="width:100%;"><?= htmlspecialchars($order->fields['diagnosis'] ?? '') ?></textarea>
</div>

<div class="osm-card">
    <h3>Assinaturas</h3>
    <div class="osm-grid">
        <div>
            <b>Técnico</b><br>
            <canvas class="osm-canvas" id="sigTech"></canvas>
            <button type="button" onclick="clearCanvas('sigTech')">Limpar</button>
        </div>
        <div>
            <b>Cliente</b><br>
            <canvas class="osm-canvas" id="sigClient"></canvas>
            <button type="button" onclick="clearCanvas('sigClient')">Limpar</button>
        </div>
    </div>
</div>

<div class="osm-card">
    <b>Status OS:</b>
    <select name="status">
        <option value="1" <?= $order->fields['status']==1?'selected':'' ?>>Rascunho</option>
        <option value="2" <?= $order->fields['status']==2?'selected':'' ?>>Em andamento</option>
        <option value="3" <?= $order->fields['status']==3?'selected':'' ?>>Concluída</option>
    </select>
    <button type="submit" name="save" class="submit">Salvar</button>
    <a class="vsubmit" href="pdf.php?orders_id=<?= $order->getID() ?>" target="_blank">Gerar PDF</a>
</div>
</form>

<script>
function clearCanvas(id){ const c=document.getElementById(id); c.getContext('2d').clearRect(0,0,c.width,c.height); }
// Desenho básico no canvas.
document.querySelectorAll('.osm-canvas').forEach(c=>{
    const ctx=c.getContext('2d'); let drawing=false;
    c.addEventListener('mousedown',()=>drawing=true);
    c.addEventListener('mouseup',()=>drawing=false);
    c.addEventListener('mousemove',e=>{ if(!drawing)return; const r=c.getBoundingClientRect();
        ctx.lineTo(e.clientX-r.left,e.clientY-r.top); ctx.stroke(); });
});
// Ao salvar, envia assinaturas via AJAX.
document.querySelector('form').addEventListener('submit',e=>{
    ['sigTech','sigClient'].forEach((id,i)=>{
        const c=document.getElementById(id);
        const type=i===0?'technician':'client';
        if(c.toDataURL){ fetch('ajax/signature.php',{method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'orders_id=<?= $order->getID() ?>&type='+type+'&data='+encodeURIComponent(c.toDataURL())}); }
    });
});
</script>

<?php
Html::footer();
