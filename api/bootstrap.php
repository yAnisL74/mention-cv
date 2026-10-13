<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    json_error(500, 'not_configured');
}
$config = require $configFile;

function json_response(int $status, array $data): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error(int $status, string $code): never
{
    json_response($status, ['error' => $code]);
}

function db(array $config): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO($config['db_dsn'], $config['db_user'], $config['db_pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function read_json_body(): array
{
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        json_error(400, 'invalid_json');
    }
    return $data;
}

/** Empreinte de l'IP (jamais l'IP en clair) pour la limitation de débit. */
function client_hash(): string
{
    return hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . date('Y-m-d'));
}
