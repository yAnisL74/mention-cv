<?php
declare(strict_types=1);

/**
 * POST /api/analyze.php
 * Corps : {"cv": "texte du CV", "offer": "texte de l'offre (optionnel)"}
 * Réponse 200 : {"id": 42, "result": {...analyse + CV réécrit...}}
 */
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/prompt.php';
require __DIR__ . '/anthropic.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    json_error(405, 'method_not_allowed');
}

$body = read_json_body();
$cv = trim((string) ($body['cv'] ?? ''));
$offer = trim((string) ($body['offer'] ?? ''));

if (mb_strlen(preg_replace('/\s+/', '', $cv)) < 80) {
    json_error(422, 'cv_too_short');
}

$pdo = db($config);
$client = client_hash();

// Limitation de débit : N analyses par heure et par client.
$stmt = $pdo->prepare(
    'SELECT COUNT(*) FROM analyses WHERE client_hash = ? AND created_at > NOW() - INTERVAL 1 HOUR'
);
$stmt->execute([$client]);
if ((int) $stmt->fetchColumn() >= (int) $config['rate_limit_per_hour']) {
    json_error(429, 'rate_limited_api');
}

try {
    $result = ask_claude_json($config, build_prompt($cv, $offer));
} catch (RuntimeException $e) {
    json_error(502, $e->getMessage());
}

// On ne stocke que les scores, jamais le contenu du CV (données personnelles).
$scores = [];
foreach ($result['criteres'] ?? [] as $c) {
    if (isset($c['nom'], $c['note'])) {
        $scores[(string) $c['nom']] = (float) $c['note'];
    }
}

$stmt = $pdo->prepare(
    'INSERT INTO analyses (client_hash, note, note_apres, criteres, with_offer)
     VALUES (?, ?, ?, ?, ?)'
);
$stmt->execute([
    $client,
    (float) ($result['note'] ?? 0),
    (float) ($result['note_apres'] ?? 0),
    json_encode($scores, JSON_UNESCAPED_UNICODE),
    $offer !== '' ? 1 : 0,
]);

json_response(200, ['id' => (int) $pdo->lastInsertId(), 'result' => $result]);
