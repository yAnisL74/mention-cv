<?php
declare(strict_types=1);

/**
 * GET /api/stats.php
 * Statistiques anonymes : nombre d'analyses, note moyenne avant/après, moyenne par critère.
 */
require __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    json_error(405, 'method_not_allowed');
}

$pdo = db($config);

$totals = $pdo->query(
    'SELECT COUNT(*) AS total,
            ROUND(AVG(note), 1) AS note_moyenne,
            ROUND(AVG(note_apres), 1) AS note_apres_moyenne,
            ROUND(AVG(note_apres - note), 1) AS gain_moyen
     FROM analyses'
)->fetch();

$byCriterion = [];
$rows = $pdo->query('SELECT criteres FROM analyses ORDER BY id DESC LIMIT 500')->fetchAll();
foreach ($rows as $row) {
    foreach (json_decode($row['criteres'], true) ?: [] as $name => $score) {
        $byCriterion[$name][] = (float) $score;
    }
}
$criteres = [];
foreach ($byCriterion as $name => $scores) {
    $criteres[] = ['nom' => $name, 'moyenne' => round(array_sum($scores) / count($scores), 1)];
}

json_response(200, [
    'total'              => (int) $totals['total'],
    'note_moyenne'       => $totals['note_moyenne'] !== null ? (float) $totals['note_moyenne'] : null,
    'note_apres_moyenne' => $totals['note_apres_moyenne'] !== null ? (float) $totals['note_apres_moyenne'] : null,
    'gain_moyen'         => $totals['gain_moyen'] !== null ? (float) $totals['gain_moyen'] : null,
    'criteres'           => $criteres,
]);
