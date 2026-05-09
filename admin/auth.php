<?php
// Inclure ce fichier en tête de chaque page protégée de l'admin.
// Redirige vers login.php si l'administrateur n'est pas connecté.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
