<?php

/**
 * Resposta de checklist de uma OS específica.
 */
class PluginOsmanagerOrderChecklist extends CommonDBTM
{
    public static $rightname = 'plugin_osmanager';

    public static function getTable($class = null)
    {
        return 'glpi_plugin_osmanager_order_checklist';
    }

    /**
     * Garante que existam linhas para todos os itens do modelo da OS.
     */
    public static function ensureForOrder($orders_id, $templates_id)
    {
        $existing = (new self())->find(['orders_id' => $orders_id]);
        if (count($existing) > 0) {
            return;
        }
        foreach (PluginOsmanagerChecklistTemplateItem::getItems($templates_id) as $item) {
            (new self())->add([
                'orders_id'          => $orders_id,
                'template_items_id'  => $item['id'],
                'label'              => $item['label'],
                'checked'            => 0,
            ]);
        }
    }

    /**
     * Retorna as respostas de uma OS.
     */
    public static function getForOrder($orders_id)
    {
        $obj = new self();
        return $obj->find(['orders_id' => $orders_id], 'id ASC');
    }
}
