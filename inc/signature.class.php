<?php

/**
 * Assinatura (técnico / cliente) da OS.
 */
class PluginOsmanagerSignature extends CommonDBTM
{
    public static $rightname = 'plugin_osmanager';

    public static function getTable($class = null)
    {
        return 'glpi_plugin_osmanager_signatures';
    }

    public static function getForOrder($orders_id)
    {
        $obj = new self();
        $rows = $obj->find(['orders_id' => $orders_id]);
        $out = [];
        foreach ($rows as $r) {
            $out[$r['type']] = $r;
        }
        return $out;
    }

    /**
     * Salva o PNG vindo do canvas e registra no banco.
     */
    public static function saveFromData($orders_id, $type, $dataUrl)
    {
        if (!preg_match('/^data:image\/png;base64,(.+)$/', $dataUrl, $m)) {
            return false;
        }
        $base = defined('GLPI_PLUGIN_DOC_DIR')
            ? GLPI_PLUGIN_DOC_DIR
            : GLPI_DATA_DIR . '/plugins';
        $dir = $base . '/osmanager/signatures';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = $orders_id . '_' . $type . '_' . time() . '.png';
        file_put_contents($dir . '/' . $filename, base64_decode($m[1]));

        // Remove assinatura anterior do mesmo tipo.
        foreach (self::getForOrder($orders_id) as $existing) {
            if ($existing['type'] === $type) {
                @unlink($dir . '/' . $existing['filename']);
                (new self())->delete(['id' => $existing['id']]);
            }
        }

        $obj = new self();
        return $obj->add([
            'orders_id' => $orders_id,
            'type'      => $type,
            'filename'  => $filename,
        ]);
    }
}
