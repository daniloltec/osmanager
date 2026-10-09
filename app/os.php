<?php
require_once __DIR__ . '/common.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tickets_id = isset($_GET['tickets_id']) ? intval($_GET['tickets_id']) : 0;

function load_ticket($DB, $tid)
{
    return $DB->query(
        "SELECT t.id, t.name, t.entities_id, e.name AS entity,
            (SELECT GROUP_CONCAT(u.name SEPARATOR ', ') FROM glpi_tickets_users tu JOIN glpi_users u ON u.id = tu.users_id WHERE tu.tickets_id = t.id AND tu.type = 1) AS requester
         FROM glpi_tickets t
         LEFT JOIN glpi_entities e ON e.id = t.entities_id
         WHERE t.id = $tid AND t.is_deleted = 0"
    )->fetch_assoc();
}

$order = null;
if ($id) {
    $order = $DB->query("SELECT * FROM osmanager_orders WHERE id = $id")->fetch_assoc();
    if ($order) {
        $tickets_id = (int) $order['tickets_id'];
    }
}

$ticket = null;
if ($tickets_id) {
    $ticket = load_ticket($DB, $tickets_id);
}

// salvar (deve ocorrer ANTES de qualquer saída HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diagnosis   = $DB->real_escape_string($_POST['diagnosis'] ?? '');
    $status      = intval($_POST['status'] ?? 1);
    $technician  = $DB->real_escape_string($_POST['technician'] ?? '');
    $client_name = $DB->real_escape_string($_POST['client_name'] ?? '');
    $entities_id = intval($_POST['entities_id'] ?? 0);
    $requester   = $DB->real_escape_string($_POST['requester'] ?? '');

    if ($order) {
        $DB->query("UPDATE osmanager_orders SET status=$status, diagnosis='$diagnosis', technician='$technician', client_name='$client_name', requester='$requester' WHERE id=" . intval($order['id']));
        $oid = $order['id'];
    } else {
        $DB->query("INSERT INTO osmanager_orders (tickets_id, entities_id, status, diagnosis, requester, technician, client_name) VALUES ($tickets_id, $entities_id, $status, '$diagnosis', '$requester', '$technician', '$client_name')");
        $oid = $DB->insert_id;
    }

    $DB->query("DELETE FROM osmanager_checklist WHERE orders_id = $oid");
    if (!empty($_POST['check_label'])) {
        foreach ($_POST['check_label'] as $i => $lab) {
            $lab = $DB->real_escape_string(trim($lab));
            if ($lab === '') {
                continue;
            }
            $checked = (!empty($_POST['check_checked'][$i])) ? 1 : 0;
            $DB->query("INSERT INTO osmanager_checklist (orders_id, label, checked) VALUES ($oid, '$lab', $checked)");
        }
    }

    header("Location: os.php?id=$oid");
    exit;
}

if (!$ticket && !$order) {
    page_header('Ordem de Serviço');
    echo "<div class='card'>Chamado não encontrado.</div>";
    page_footer();
    exit;
}

$status      = $order['status'] ?? 1;
$diagnosis   = $order['diagnosis'] ?? '';
$technician  = $order['technician'] ?? '';
$client_name = $order['client_name'] ?? '';
$requester   = $order['requester'] ?? ($ticket['requester'] ?? '');
$entities_id = $order['entities_id'] ?? ($ticket['entities_id'] ?? 0);

$checklist = [];
if ($order) {
    $cr = $DB->query("SELECT * FROM osmanager_checklist WHERE orders_id = " . intval($order['id']) . " ORDER BY id");
    while ($c = $cr->fetch_assoc()) {
        $checklist[] = $c;
    }
}

page_header('Ordem de Serviço');
?>
<div class="card">
  <h3>Chamado GLPI #<?= h($ticket['id'] ?? $tickets_id) ?> — <?= h($ticket['name'] ?? '') ?></h3>
  <p class="muted">Entidade: <?= h($ticket['entity'] ?? '') ?> | Solicitante: <?= h($requester) ?></p>
</div>

<form method="post" class="card">
  <input type="hidden" name="entities_id" value="<?= h($entities_id) ?>">
  <input type="hidden" name="requester" value="<?= h($requester) ?>">

  <label>Diagnóstico / Descrição do serviço</label>
  <textarea name="diagnosis" rows="5"><?= h($diagnosis) ?></textarea>

  <div class="row">
    <div>
      <label>Status</label>
      <select name="status">
        <option value="1" <?= $status == 1 ? 'selected' : '' ?>>Rascunho</option>
        <option value="2" <?= $status == 2 ? 'selected' : '' ?>>Em andamento</option>
        <option value="3" <?= $status == 3 ? 'selected' : '' ?>>Concluída</option>
      </select>
    </div>
    <div>
      <label>Técnico responsável</label>
      <input name="technician" value="<?= h($technician) ?>">
    </div>
    <div>
      <label>Cliente (nome)</label>
      <input name="client_name" value="<?= h($client_name) ?>">
    </div>
  </div>

  <label>Checklist</label>
  <div id="checklist">
    <?php foreach ($checklist as $c): ?>
      <div class="row" style="margin-bottom:6px">
        <div style="flex:0 0 40px"><input type="checkbox" name="check_checked[]" <?= $c['checked'] ? 'checked' : '' ?> style="width:auto"></div>
        <div><input type="text" name="check_label[]" value="<?= h($c['label']) ?>"></div>
      </div>
    <?php endforeach; ?>
  </div>
  <button type="button" class="btn" onclick="addCheck()">+ Adicionar item</button>

  <p>
    <button class="btn green" type="submit">Salvar</button>
    <?php if ($order): ?><a class="btn" href="print.php?id=<?= $order['id'] ?>">Imprimir / PDF</a><?php endif; ?>
    <a class="btn" href="index.php">Voltar</a>
  </p>
</form>

<script>
function addCheck() {
  const d = document.createElement('div');
  d.className = 'row';
  d.style.marginBottom = '6px';
  d.innerHTML = '<div style="flex:0 0 40px"><input type="checkbox" name="check_checked[]" style="width:auto"></div>' +
                '<div><input type="text" name="check_label[]" placeholder="Item do checklist"></div>';
  document.getElementById('checklist').appendChild(d);
}
</script>
<?php page_footer(); ?>
