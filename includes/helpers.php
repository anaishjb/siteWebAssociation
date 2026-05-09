<?php
// Fonctions utilitaires partagées par toutes les pages PHP

// Échappe le texte pour l'afficher en HTML sans risque
function h(string $texte): string
{
    return htmlspecialchars($texte, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Formate une date MySQL (YYYY-MM-DD) en français : "21 juin 2025"
function formaterDateFr(string $dateSQL): string
{
    $mois = [
        1  => 'janvier',   2  => 'février',   3  => 'mars',
        4  => 'avril',     5  => 'mai',        6  => 'juin',
        7  => 'juillet',   8  => 'août',       9  => 'septembre',
        10 => 'octobre',   11 => 'novembre',   12 => 'décembre',
    ];

    [$annee, $moisNum, $jour] = explode('-', $dateSQL);
    return (int)$jour . ' ' . $mois[(int)$moisNum] . ' ' . $annee;
}
