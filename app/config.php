<?php
/**
 * Configuração do OS Manager (versão standalone, fora do GLPI).
 *
 * Ordem de prioridade para as credenciais do banco:
 *   1. Variáveis de ambiente (OS_DB_HOST / OS_DB_NAME / OS_DB_USER / OS_DB_PASS) — Docker
 *   2. Arquivo de config do GLPI (GLPI_ROOT/config/config_db.php)
 *   3. Fallback manual abaixo
 */

// >>> Em produção (VPS), aponte para a pasta do GLPI <<<
define('GLPI_ROOT', '/var/www/glpi');

function osmanager_get_glpi_db_config($glpiRoot)
{
    $candidates = [
        $glpiRoot . '/config/config_db.php',
        $glpiRoot . '/inc/config.php',
    ];
    foreach ($candidates as $file) {
        if (!is_readable($file)) {
            continue;
        }
        $content = file_get_contents($file);
        $get = function ($key) use ($content) {
            if (preg_match("/define\(\s*['\"]" . preg_quote($key, '/') . "['\"]\s*,\s*['\"](.*?)['\"]\s*\)/s", $content, $m)) {
                return $m[1];
            }
            return null;
        };
        $host = $get('DB_HOST');
        $name = $get('DB_NAME');
        $user = $get('DB_USER');
        $pass = $get('DB_PASSWORD');
        if ($host !== null && $name !== null && $user !== null) {
            return ['host' => $host, 'name' => $name, 'user' => $user, 'pass' => $pass ?? ''];
        }
    }
    return null;
}

$envHost = getenv('OS_DB_HOST');
$envName = getenv('OS_DB_NAME');
$envUser = getenv('OS_DB_USER');
$envPass = getenv('OS_DB_PASS');

if ($envHost !== false && $envName !== false && $envUser !== false) {
    define('DB_HOST', $envHost);
    define('DB_NAME', $envName);
    define('DB_USER', $envUser);
    define('DB_PASS', $envPass !== false ? $envPass : '');
} else {
    $__cfg = osmanager_get_glpi_db_config(GLPI_ROOT);
    if ($__cfg === null) {
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'glpi');
        define('DB_USER', 'glpi');
        define('DB_PASS', '');
    } else {
        define('DB_HOST', $__cfg['host']);
        define('DB_NAME', $__cfg['name']);
        define('DB_USER', $__cfg['user']);
        define('DB_PASS', $__cfg['pass']);
    }
}

$DB = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($DB->connect_error) {
    die('Falha na conexão com o banco: ' . $DB->connect_error);
}
$DB->set_charset('utf8mb4');
