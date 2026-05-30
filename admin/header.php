<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />
  <title><?= htmlspecialchars($titrePage ?? 'Admin', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?> — Admin LDS</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body id="top">

<a href="#contenu-principal" class="skip-link">Aller au contenu principal</a>

<header class="admin-topbar">
  <span class="topbar-title">LDS Admin</span>
  <nav aria-label="Navigation administrateur">
    <a href="../index.php"><span aria-hidden="true">←</span> Site public</a>
    <a href="logout.php">Déconnexion <span class="topbar-user">(<?= htmlspecialchars($_SESSION['admin_identifiant'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>)</span></a>
  </nav>
</header>

<div class="admin-body">

  <nav class="admin-sidebar" aria-label="Menu administrateur">
    <ul>
      <li><a href="index.php" <?= ($navActive ?? '') === 'tableau-bord' ? 'class="active" aria-current="page"' : '' ?>>Tableau de bord</a></li>
      <li><a href="accueil.php" <?= ($navActive ?? '') === 'accueil' ? 'class="active" aria-current="page"' : '' ?>>Page d'accueil</a></li>
      <li><a href="evenements.php" <?= ($navActive ?? '') === 'evenements' ? 'class="active" aria-current="page"' : '' ?>>Événements</a></li>
      <li><a href="partenaires.php" <?= ($navActive ?? '') === 'partenaires' ? 'class="active" aria-current="page"' : '' ?>>Partenaires</a></li>
      <li><a href="missions.php" <?= ($navActive ?? '') === 'missions' ? 'class="active" aria-current="page"' : '' ?>>Missions</a></li>
      <li><a href="messages.php" <?= ($navActive ?? '') === 'messages' ? 'class="active" aria-current="page"' : '' ?>>Messages contact</a></li>
      <li><a href="changer-mot-de-passe.php" <?= ($navActive ?? '') === 'changer-mdp' ? 'class="active" aria-current="page"' : '' ?>>Changer le mot de passe</a></li>
    </ul>
  </nav>

  <main id="contenu-principal" class="admin-main">
