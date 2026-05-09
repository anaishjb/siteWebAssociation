<?php
header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

// Formate une date MySQL (YYYY-MM-DD) en français : "21 juin 2025"
function formaterDate(string $dateSQL): string
{
    $mois = [
        1 => 'janvier', 2 => 'février',  3 => 'mars',    4 => 'avril',
        5 => 'mai',     6 => 'juin',     7 => 'juillet', 8 => 'août',
        9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre'
    ];
    [$annee, $moisNum, $jour] = explode('-', $dateSQL);
    return (int)$jour . ' ' . $mois[(int)$moisNum] . ' ' . $annee;
}

try {
    $bdd = connecterBDD();

    $statut = $_GET['statut'] ?? null;

    if ($statut === 'prochain') {
        $evt = $bdd
            ->query("SELECT * FROM evenements WHERE statut = 'prochain' ORDER BY date_event ASC LIMIT 1")
            ->fetch();

        if ($evt) {
            $evt['date_formatee'] = formaterDate($evt['date_event']);
        }

        echo json_encode(['evenement' => $evt]);

    } elseif ($statut === 'passe') {
        $evts = $bdd
            ->query("SELECT * FROM evenements WHERE statut = 'passe' ORDER BY date_event DESC")
            ->fetchAll();

        foreach ($evts as &$evt) {
            $evt['date_formatee'] = formaterDate($evt['date_event']);
        }

        echo json_encode(['evenements' => $evts]);

    } else {
        // Page d'aperçu : prochain événement + 3 derniers passés
        $prochain = $bdd
            ->query("SELECT * FROM evenements WHERE statut = 'prochain' ORDER BY date_event ASC LIMIT 1")
            ->fetch();

        $passes = $bdd
            ->query("SELECT * FROM evenements WHERE statut = 'passe' ORDER BY date_event DESC LIMIT 3")
            ->fetchAll();

        if ($prochain) {
            $prochain['date_formatee'] = formaterDate($prochain['date_event']);
        }

        foreach ($passes as &$evt) {
            $evt['date_formatee'] = formaterDate($evt['date_event']);
        }

        echo json_encode(['prochain' => $prochain, 'passes' => $passes]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erreur' => 'Impossible de charger les données.']);
}
