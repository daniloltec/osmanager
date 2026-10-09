-- Dados de DEMO (somente para testar localmente SEM um GLPI real).
-- REMOVA a montagem deste arquivo ao apontar o app para um banco GLPI real,
-- caso contrário ele injeta chamados falsos no seu GLPI.
USE glpi;

CREATE TABLE IF NOT EXISTS glpi_entities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    entities_id INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS glpi_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    realname VARCHAR(255),
    firstname VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS glpi_tickets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status INT NOT NULL DEFAULT 1,
    entities_id INT UNSIGNED NOT NULL DEFAULT 0,
    is_deleted TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS glpi_tickets_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tickets_id INT UNSIGNED NOT NULL,
    users_id INT UNSIGNED NOT NULL,
    type INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO glpi_entities (id, name) VALUES (1, 'Matriz'), (2, 'Filial SP')
    ON DUPLICATE KEY UPDATE name = VALUES(name);
INSERT INTO glpi_users (id, name, realname, firstname) VALUES
    (1, 'joao.silva', 'Silva', 'João'),
    (2, 'maria.souza', 'Souza', 'Maria')
    ON DUPLICATE KEY UPDATE name = VALUES(name);
INSERT INTO glpi_tickets (id, name, status, entities_id) VALUES
    (1001, 'Computador não liga', 3, 1),
    (1002, 'Impressora com erro de toner', 1, 1),
    (1003, 'Rede lenta no setor financeiro', 5, 2)
    ON DUPLICATE KEY UPDATE name = VALUES(name);
INSERT INTO glpi_tickets_users (tickets_id, users_id, type) VALUES
    (1001, 1, 1),
    (1002, 2, 1),
    (1003, 1, 1)
    ON DUPLICATE KEY UPDATE type = VALUES(type);
