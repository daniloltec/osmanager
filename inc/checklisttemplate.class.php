<?php

/**
 * Modelo de checklist (ex.: Notebook, Desktop, Servidor).
 */
class PluginOsmanagerChecklistTemplate extends CommonDBTM
{
    public static $rightname = 'plugin_osmanager';

    public static function getTypeName($nb = 0)
    {
        return _n('Modelo de Checklist', 'Modelos de Checklist', $nb);
    }

    public static function getTable($class = null)
    {
        return 'glpi_plugin_osmanager_templates';
    }

    /**
     * Retorna o ID do primeiro modelo (fallback).
     */
    public static function getDefaultTemplateId()
    {
        $obj = new self();
        $rows = $obj->find([], 'id ASC', 1);
        if (count($rows)) {
            return array_key_first($rows);
        }
        return 0;
    }

    /**
     * Cria os modelos padrão (Notebook, Desktop, Servidor, ...).
     */
    public static function seedDefaults()
    {
        $defaults = [
            'Notebook' => ['Liga', 'Não Liga', 'Tela OK', 'Tela quebrada', 'Teclado',
                'Touchpad', 'Wi-Fi', 'Bluetooth', 'Webcam', 'Microfone', 'SSD', 'HD',
                'Memória', 'Cooler', 'Bateria', 'Fonte', 'Carregador', 'Windows Inicializa',
                'Office', 'Drivers', 'Atualizações', 'Backup realizado'],
            'Desktop' => ['Liga', 'Não Liga', 'Fonte', 'Placa-mãe', 'Memória', 'HD',
                'SSD', 'Vídeo', 'Cooler', 'Windows Inicializa', 'Office', 'Drivers',
                'Atualizações', 'Backup realizado'],
            'Servidor' => ['Liga', 'Não Liga', 'RAID', 'Memória', 'Discos', 'Rede',
                'Fontes redundantes', 'Backup', 'Sistema Operacional', 'Virtualização'],
            'Impressora' => ['Liga', 'Não Liga', 'Papel', 'Cartucho/Toner', 'Rede',
                'USB', 'Driver', 'Calibração'],
        ];

        foreach ($defaults as $name => $items) {
            $tpl = new self();
            $tid = $tpl->add(['name' => $name, 'entities_id' => 0]);
            foreach ($items as $i => $label) {
                $item = new PluginOsmanagerChecklistTemplateItem();
                $item->add(['templates_id' => $tid, 'label' => $label, 'position' => $i]);
            }
        }
    }
}
