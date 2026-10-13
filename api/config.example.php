<?php
// Copier ce fichier en config.php et renseigner les valeurs.
// config.php est ignoré par Git : ne jamais committer de clé.
return [
    'anthropic_api_key' => 'sk-ant-xxxxxxxx',
    'anthropic_model'   => 'claude-sonnet-5-5',
    'db_dsn'            => 'mysql:host=127.0.0.1;dbname=mention;charset=utf8mb4',
    'db_user'           => 'mention',
    'db_pass'           => 'change-me',
    'rate_limit_per_hour' => 10,
];
