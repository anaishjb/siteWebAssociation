<?php
header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

try {
    $bdd = connecterBDD();

    // La section héros est une ligne unique
    $hero = $bdd->query('SELECT * FROM hero LIMIT 1')->fetch();

    // On récupère toutes les cartes et on les sépare par section
    $toutesLesCartes = $bdd
        ->query('SELECT * FROM cartes_accueil ORDER BY section, ordre ASC')
        ->fetchAll();

    $poles      = [];
    $evenements = [];
    $actions    = [];

    foreach ($toutesLesCartes as $carte) {
        if ($carte['section'] === 'poles') {
            $poles[] = $carte;
        } elseif ($carte['section'] === 'evenements') {
            $evenements[] = $carte;
        } elseif ($carte['section'] === 'actions') {
            $actions[] = $carte;
        }
    }

    echo json_encode([
        'hero'             => $hero,
        'poles'            => $poles,
        'evenements'       => $evenements,
        'actionsSolidaires'=> $actions,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erreur' => 'Impossible de charger les données.']);
}
