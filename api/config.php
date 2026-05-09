<?php
// Paramètres de connexion à la base de données.
// Modifiez ces valeurs selon votre configuration XAMPP / hébergement.

define('DB_HOTE',        'localhost');
define('DB_NOM',         'p27_anais');
define('DB_UTILISATEUR', 'root');  // XAMPP local : root / Handiman : p27_anais
define('DB_MOT_PASSE',   '');      // XAMPP local : vide / Handiman : ton mot de passe
define('DB_CHARSET', 'utf8mb4');

function connecterBDD(): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOTE,
        DB_NOM,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    return new PDO($dsn, DB_UTILISATEUR, DB_MOT_PASSE, $options);
}
