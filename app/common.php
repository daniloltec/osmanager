<?php
require_once __DIR__ . '/config.php';

function h($str)
{
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}

function os_status_label($s)
{
    $map = [1 => 'Rascunho', 2 => 'Em andamento', 3 => 'Concluída'];
    return $map[(int) $s] ?? '—';
}

function glpi_ticket_status_label($s)
{
    $map = [
        1 => 'Novo',
        2 => 'Em atendimento (pendente)',
        3 => 'Em atendimento',
        4 => 'Pendente',
        5 => 'Resolvido',
        6 => 'Fechado',
    ];
    return $map[(int) $s] ?? '—';
}

function page_header($title = 'OS Manager')
{
    ?><!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($title) ?></title>
<style>
  body { font-family: Arial, Helvetica, sans-serif; margin: 0; background: #f4f6f8; color: #222; }
  header { background: #2c3e50; color: #fff; padding: 12px 20px; }
  header a { color: #fff; text-decoration: none; font-weight: bold; }
  .wrap { max-width: 1000px; margin: 20px auto; padding: 0 16px; }
  .card { background: #fff; border: 1px solid #e1e4e8; border-radius: 8px; padding: 16px; margin-bottom: 16px; }
  table { border-collapse: collapse; width: 100%; background: #fff; }
  th, td { border: 1px solid #e1e4e8; padding: 8px 10px; text-align: left; font-size: 14px; }
  th { background: #f0f2f5; }
  input, select, textarea { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccd0d5; border-radius: 4px; }
  label { font-weight: bold; display: block; margin: 10px 0 4px; }
  .btn { display: inline-block; padding: 8px 14px; background: #2980b9; color: #fff; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-size: 14px; }
  .btn.green { background: #27ae60; }
  .btn.red { background: #c0392b; }
  .muted { color: #777; }
  .row { display: flex; gap: 12px; }
  .row > div { flex: 1; }
  @media print { header, .no-print { display: none !important; } body { background: #fff; } .card { border: none; } }
</style>
</head>
<body>
<header><a href="index.php">OS Manager — Controle Interno</a></header>
<div class="wrap">
<?php
}

function page_footer()
{
    ?></div></body></html><?php
}
