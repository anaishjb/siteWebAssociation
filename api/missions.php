<?php
header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

try {
    $bdd = connecterBDD();

    $intro      = $bdd->query('SELECT * FROM missions_intro LIMIT 1')->fetch();
    $objectifs  = $bdd->query('SELECT * FROM missions_objectifs ORDER BY ordre ASC')->fetchAll();
    $besoins    = $bdd->query('SELECT * FROM missions_besoins  ORDER BY ordre ASC')->fetchAll();
    $reve       = $bdd->query('SELECT * FROM missions_reve    LIMIT 1')->fetch();

    echo json_encode([
        'intro'     => $intro,
        'objectifs' => $objectifs,
        'besoins'   => $besoins,
        'reve'      => $reve,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erreur' => 'Impossible de charger les données.']);
}
