<?php
header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

try {
    $bdd = connecterBDD();

    $introTextes = $bdd->query('SELECT texte FROM poles_intro_textes ORDER BY ordre ASC')->fetchAll();
    $poles       = $bdd->query('SELECT * FROM poles ORDER BY ordre ASC')->fetchAll();

    // On renvoie juste les textes (pas les id inutiles au front)
    $textesPourLeFront = array_column($introTextes, 'texte');

    echo json_encode([
        'intro' => $textesPourLeFront,
        'liste' => $poles,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erreur' => 'Impossible de charger les données.']);
}
