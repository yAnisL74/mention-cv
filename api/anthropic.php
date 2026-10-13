<?php
declare(strict_types=1);

/** Appelle l'API Messages d'Anthropic et renvoie l'objet JSON produit par le modèle. */
function ask_claude_json(array $config, string $prompt): array
{
    $payload = json_encode([
        'model'      => $config['anthropic_model'],
        'max_tokens' => 4000,
        'messages'   => [['role' => 'user', 'content' => $prompt]],
    ], JSON_UNESCAPED_UNICODE);

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 90,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-api-key: ' . $config['anthropic_api_key'],
            'anthropic-version: 2023-06-01',
        ],
        CURLOPT_POSTFIELDS     => $payload,
    ]);
    $raw = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false || $status !== 200) {
        throw new RuntimeException('ai_unavailable');
    }

    $response = json_decode($raw, true);
    $text = '';
    foreach ($response['content'] ?? [] as $block) {
        if (($block['type'] ?? '') === 'text') {
            $text .= $block['text'];
        }
    }

    return extract_json($text);
}

/** Isole le premier objet JSON de la réponse (le modèle peut ajouter des ```json). */
function extract_json(string $text): array
{
    $start = strpos($text, '{');
    $end = strrpos($text, '}');
    if ($start === false || $end === false || $end <= $start) {
        throw new RuntimeException('bad_shape');
    }
    $data = json_decode(substr($text, $start, $end - $start + 1), true);
    if (!is_array($data) || !isset($data['cv']) || !is_array($data['cv'])) {
        throw new RuntimeException('bad_shape');
    }
    return $data;
}
