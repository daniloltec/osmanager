<?php

/**
 * OS Manager - Plugin GLPI 11
 *
 * Gera Ordem de Serviço a partir de chamados existentes, com checklist,
 * diagnóstico, assinaturas e exportação PDF profissional.
 *
 * Tudo roda dentro do próprio GLPI (sem app externa, sem DB separado).
 */

define('PLUGIN_OSMANAGER_VERSION', '1.0.0');
define('PLUGIN_OSMANAGER_MIN_GLPI', '11.0');
define('PLUGIN_OSMANAGER_MAX_GLPI', '11.99');

function plugin_version_osmanager()
{
    return [
        'name'         => 'OS Manager',
        'version'      => PLUGIN_OSMANAGER_VERSION,
        'author'       => 'OsManager',
        'license'      => 'GPLv2+',
        'homepage'     => '',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_OSMANAGER_MIN_GLPI,
                'max' => PLUGIN_OSMANAGER_MAX_GLPI,
            ],
        ],
    ];
}

function plugin_init_osmanager()
{
    global $PLUGIN_HOOKS;

    // Botão "Ordem de Serviço" no Ticket.
    $PLUGIN_HOOKS['post_show_item']['osmanager'] = 'plugin_osmanager_post_show_item';

    // Direitos.
    $PLUGIN_HOOKS['config_page']['osmanager'] = 'front/config.php';
}

function plugin_osmanager_install()
{
    global $DB;

    $tables = [
        'glpi_plugin_osmanager_orders' => "
            CREATE TABLE IF NOT EXISTS `glpi_plugin_osmanager_orders` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tickets_id` INT UNSIGNED NOT NULL,
                `entities_id` INT UNSIGNED NOT NULL DEFAULT 0,
                `templates_id` INT UNSIGNED NOT NULL DEFAULT 0,
                `status` INT NOT NULL DEFAULT 1,
                `diagnosis` TEXT NULL,
                `is_deleted` TINYINT NOT NULL DEFAULT 0,
                `date_creation` TIMESTAMP NULL DEFAULT NULL,
                `date_mod` TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `tickets_id` (`tickets_id`),
                KEY `entities_id` (`entities_id`),
                KEY `status` (`status`),
                KEY `is_deleted` (`is_deleted`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'glpi_plugin_osmanager_templates' => "
            CREATE TABLE IF NOT EXISTS `glpi_plugin_osmanager_templates` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `is_recursive` TINYINT NOT NULL DEFAULT 0,
                `entities_id` INT UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `entities_id` (`entities_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'glpi_plugin_osmanager_template_items' => "
            CREATE TABLE IF NOT EXISTS `glpi_plugin_osmanager_template_items` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `templates_id` INT UNSIGNED NOT NULL,
                `label` VARCHAR(255) NOT NULL,
                `position` INT NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `templates_id` (`templates_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'glpi_plugin_osmanager_order_checklist' => "
            CREATE TABLE IF NOT EXISTS `glpi_plugin_osmanager_order_checklist` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `orders_id` INT UNSIGNED NOT NULL,
                `template_items_id` INT UNSIGNED NOT NULL,
                `label` VARCHAR(255) NOT NULL,
                `checked` TINYINT NOT NULL DEFAULT 0,
                `note` TEXT NULL,
                PRIMARY KEY (`id`),
                KEY `orders_id` (`orders_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'glpi_plugin_osmanager_signatures' => "
            CREATE TABLE IF NOT EXISTS `glpi_plugin_osmanager_signatures` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `orders_id` INT UNSIGNED NOT NULL,
                `type` VARCHAR(32) NOT NULL,
                `filename` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `orders_id` (`orders_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'glpi_plugin_osmanager_configs' => "
            CREATE TABLE IF NOT EXISTS `glpi_plugin_osmanager_configs` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `value` TEXT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `name` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ];

    foreach ($tables as $sql) {
        $DB->queryOrDie($sql, $DB->error());
    }

    return true;
}

function plugin_osmanager_uninstall()
{
    global $DB;

    $tables = [
        'glpi_plugin_osmanager_orders',
        'glpi_plugin_osmanager_templates',
        'glpi_plugin_osmanager_template_items',
        'glpi_plugin_osmanager_order_checklist',
        'glpi_plugin_osmanager_signatures',
        'glpi_plugin_osmanager_configs',
    ];
    foreach ($tables as $t) {
        $DB->queryOrDie("DROP TABLE IF EXISTS `$t`", $DB->error());
    }

    return true;
}

function plugin_osmanager_check_prerequisites()
{
    if (version_compare(GLPI_VERSION, PLUGIN_OSMANAGER_MIN_GLPI, '<')) {
        echo "OS Manager requer GLPI >= " . PLUGIN_OSMANAGER_MIN_GLPI;
        return false;
    }
    return true;
}

function plugin_osmanager_check_config()
{
    return true;
}
