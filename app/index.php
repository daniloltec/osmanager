<?php
require_once __DIR__ . '/common.php';
page_header('Ordens de Serviço');

$search = trim($_GET['q'] ?? '');
$where = "t.is_deleted = 0";
if ($search !== '') {
    $search = $DB->real_escape_string($search);
    $where .= " AND (t.id = '" . intval($search) . "' OR t.name LIKE '%$search%')";
}

$sql = "SELECT t.id, t.name, t.date, t.status, e.name AS entity,
    (SELECT GROUP_CONCAT(u.name SEPARATOR ', ') FROM glpi_tickets_users tu JOIN glpi_users u ON u.id = tu.users_id WHERE tu.tickets_id = t.id AND tu.type = 1) AS requester
    FROM glpi_tickets t
    LEFT JOIN glpi_entities e ON e.id = t.entities_id
    WHERE $where
    ORDER BY t.date DESC
    LIMIT 100";
$res = $DB->query($sql);
?>
<div class="card no-print">
  <form method="get">
    <label for="q">Buscar chamado (número ou texto):</label>
    <input type="text" id="q" name="q" value="<?= h($search) ?>" placeholder="Ex: 1234">
    <p>
      <button class="btn">Buscar</button>
      <a class="btn" href="index.php">Limpar</a>
    </p>
  </form>
</div>

<div class="card">
<table>
  <tr><th>#</th><th>Chamado</th><th>Solicitante</th><th>Status GLPI</th><th>OS</th></tr>
  <?php while ($t = $res->fetch_assoc()):
      $oid = $DB->query("SELECT id, status FROM osmanager_orders WHERE tickets_id = " . intval($t['id']))->fetch_assoc();
  ?>
  <tr>
    <td><?= h($t['id']) ?></td>
    <td><?= h($t['name']) ?><br><span class="muted"><?= h($t['entity']) ?></span></td>
    <td><?= h($t['requester']) ?></td>
    <td><?= h(glpi_ticket_status_label($t['status'])) ?></td>
    <td>
      <?php if ($oid): ?>
        <a class="btn green" href="os.php?id=<?= $oid['id'] ?>">Ver OS</a>
        <a class="btn" href="print.php?id=<?= $oid['id'] ?>">PDF</a>
      <?php else: ?>
        <a class="btn" href="os.php?tickets_id=<?= $t['id'] ?>">Criar OS</a>
      <?php endif; ?>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
</div>
<?php page_footer(); ?>
