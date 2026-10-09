<?php
require_once __DIR__ . '/common.php';
page_header('Instalação');

$tables = [
    "CREATE TABLE IF NOT EXISTS osmanager_orders (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS osmanager_checklist (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        orders_id INT UNSIGNED NOT NULL,
        label VARCHAR(255) NOT NULL,
        checked TINYINT NOT NULL DEFAULT 0,
        KEY (orders_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

$ok = true;
foreach ($tables as $sql) {
    if (!$DB->query($sql)) {
        echo "<div class='card' style='color:#c0392b'>Erro: " . h($DB->error) . "</div>";
        $ok = false;
    }
}

if ($ok) {
    echo "<div class='card'>
      <h3>Tabelas criadas com sucesso.</h3>
      <p>Apague ou proteja este arquivo <code>install.php</code> e acesse o <a href='index.php'>painel</a>.</p>
    </div>";
}

page_footer();
