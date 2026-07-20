<?php

/**
 * Configurações do OS Manager (marca/logo + modelos de checklist).
 */

include('../../../inc/includes.php');

Session::checkRight('plugin_osmanager', UPDATE);

if (isset($_POST['save'])) {
    plugin_osmanager_config_set('company', $_POST['company'] ?? '');
    plugin_osmanager_config_set('logo_path', $_POST['logo_path'] ?? '');
    Html::redirect($_SERVER['REQUEST_URI']);
}

if (isset($_GET['seed'])) {
    PluginOsmanagerChecklistTemplate::seedDefaults();
    Html::redirect($_SERVER['REQUEST_URI']);
}

Html::header('OS Manager - Configurações', $_SERVER['PHP_SELF'], 'config', 'plugins');

$company = plugin_osmanager_config_get('company', '');
$logo = plugin_osmanager_config_get('logo_path', '');
?>

<div class="center">
<h2>Configurações do OS Manager</h2>
<form method="post" action="">
<table class="tab_cadre_fixe">
    <tr><th colspan="2">Marca</th></tr>
    <tr><td>Empresa</td><td><input type="text" name="company" size="50" value="<?= htmlspecialchars($company) ?>"></td></tr>
    <tr><td>Caminho do Logo (PNG)</td><td><input type="text" name="logo_path" size="60" value="<?= htmlspecialchars($logo) ?>"></td></tr>
    <tr><td colspan="2" class="center"><input type="submit" name="save" value="Salvar" class="submit"></td></tr>
</table>
<?php Html::closeForm(); ?>

<p>
    <a class="vsubmit" href="?seed=1">Criar modelos de checklist padrão (Notebook, Desktop, Servidor, Impressora)</a>
</p>
</div>

<?php
Html::footer();
