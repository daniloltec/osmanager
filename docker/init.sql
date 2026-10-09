-- Cria apenas as tabelas do OS Manager.
-- Seguro rodar em cima de um banco GLPI real (não toca nas tabelas do GLPI).
CREATE DATABASE IF NOT EXISTS glpi;
USE glpi;

CREATE TABLE IF NOT EXISTS osmanager_orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tickets_id INT UNSIGNED NOT NULL,
    entities_id INT UNSIGNED NOT NULL DEFAULT 0,
    status TINYINT NOT NULL DEFAULT 1,
    diagnosis TEXT,
    requester VARCHAR(255),
    technician VARCHAR(255),
    client_name VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY (tickets_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS osmanager_checklist (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    orders_id INT UNSIGNED NOT NULL,
    label VARCHAR(255) NOT NULL,
    checked TINYINT NOT NULL DEFAULT 0,
    KEY (orders_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
