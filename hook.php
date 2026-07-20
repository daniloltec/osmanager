<?php

/**
 * Hooks do plugin OS Manager.
 */

/**
 * Injeta o botão "Ordem de Serviço" no formulário do Ticket.
 */
function plugin_osmanager_post_show_item($params = [])
{
    if (!isset($params['item']) || !($params['item'] instanceof Ticket)) {
        return;
    }

    if (!Session::haveRight('plugin_osmanager', READ)) {
        return;
    }

    $ticketId = $params['item']->getID();
    if (!$ticketId) {
        return;
    }

    global $CFG_GLPI;
    $url = $CFG_GLPI['root_doc'] . '/plugins/osmanager/front/order.php?tickets_id=' . $ticketId;
    echo <<<HTML
    <div style="margin:10px 0;">
        <a class="vsubmit" style="text-decoration:none;padding:8px 14px;border-radius:6px;"
           href="{$url}">
            <i class="fas fa-clipboard-list"></i> Ordem de Serviço
        </a>
    </div>
HTML;
}

/**
 * Lê configuração do plugin.
 */
function plugin_osmanager_config_get($name, $default = '')
{
    global $DB;
    $result = $DB->queryOrDie(
        "SELECT `value` FROM `glpi_plugin_osmanager_configs` WHERE `name` = '$name' LIMIT 1"
    );
    if ($row = $DB->fetchAssoc($result)) {
        return $row['value'];
    }
    return $default;
}

/**
 * Grava configuração do plugin.
 */
function plugin_osmanager_config_set($name, $value)
{
    global $DB;
    $value = $DB->escape($value);
    $DB->queryOrDie(
        "INSERT INTO `glpi_plugin_osmanager_configs` (`name`, `value`)
         VALUES ('$name', '$value')
         ON DUPLICATE KEY UPDATE `value` = '$value'"
    );
}
