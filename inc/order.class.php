<?php

/**
 * Ordem de Serviço (cabeçalho) vinculada a um Ticket do GLPI.
 */
class PluginOsmanagerOrder extends CommonDBTM
{
    public static $rightname = 'plugin_osmanager';

    const STATUS_DRAFT = 1;
    const STATUS_PROGRESS = 2;
    const STATUS_DONE = 3;

    public static function getTypeName($nb = 0)
    {
        return _n('Ordem de Serviço', 'Ordens de Serviço', $nb);
    }

    public static function getTable($class = null)
    {
        return 'glpi_plugin_osmanager_orders';
    }

    /**
     * Retorna (ou cria) a OS de um Ticket.
     */
    public static function getOrCreateForTicket($tickets_id)
    {
        $obj = new self();
        if ($obj->getFromDBByCrit(['tickets_id' => $tickets_id, 'is_deleted' => 0])) {
            return $obj;
        }

        $ticket = new Ticket();
        if (!$ticket->getFromDB($tickets_id)) {
            return false;
        }

        $templates_id = PluginOsmanagerChecklistTemplate::getDefaultTemplateId();
        $id = $obj->add([
            'tickets_id'  => $tickets_id,
            'entities_id' => $ticket->fields['entities_id'],
            'templates_id' => $templates_id,
            'status'      => self::STATUS_DRAFT,
        ]);

        $obj->getFromDB($id);
        return $obj;
    }

    public function getStatusLabel()
    {
        $map = [
            self::STATUS_DRAFT    => 'Rascunho',
            self::STATUS_PROGRESS => 'Em andamento',
            self::STATUS_DONE     => 'Concluída',
        ];
        return $map[$this->fields['status']] ?? '—';
    }
}
