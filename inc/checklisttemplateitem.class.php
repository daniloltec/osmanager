<?php

/**
 * Item de um modelo de checklist.
 */
class PluginOsmanagerChecklistTemplateItem extends CommonDBTM
{
    public static $rightname = 'plugin_osmanager';

    public static function getTable($class = null)
    {
        return 'glpi_plugin_osmanager_template_items';
    }

    public static function getTypeName($nb = 0)
    {
        return _n('Item de Checklist', 'Itens de Checklist', $nb);
    }

    /**
     * Retorna os itens de um modelo ordenados.
     */
    public static function getItems($templates_id)
    {
        $obj = new self();
        return $obj->find(['templates_id' => $templates_id], 'position ASC');
    }
}
