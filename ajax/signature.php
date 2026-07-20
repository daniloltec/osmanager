<?php

/**
 * Recebe a assinatura (PNG do canvas) e salva.
 */

include('../../../inc/includes.php');

Session::checkRight('plugin_osmanager', UPDATE);

$orders_id = (int)($_POST['orders_id'] ?? 0);
$type = $_POST['type'] ?? '';
$data = $_POST['data'] ?? '';

if (!$orders_id || !in_array($type, ['technician', 'client']) || !$data) {
    http_response_code(400);
    exit('invalid');
}

$ok = PluginOsmanagerSignature::saveFromData($orders_id, $type, $data);
echo $ok ? 'ok' : 'fail';
