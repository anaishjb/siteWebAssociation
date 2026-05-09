<?php
session_start();
require_once '../api/config.php';
require_once '../includes/helpers.php';

// Déjà connecté → tableau de bord
if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erreur      = '';
$identifiant = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $erreur = 'Requête invalide. Rechargez la page.';
    } else {

        $identifiant = trim($_POST['identifiant'] ?? '');
        $motDePasse  = $_POST['mot_de_passe'] ?? '';

        if ($identifiant === '' || $motDePasse === '') {
            $erreur = 'Veuillez remplir tous les champs.';
        } else {
            try {
                $bdd  = connecterBDD();
                $stmt = $bdd->prepare('SELECT id, mot_de_passe_hash FROM admin_utilisateurs WHERE identifiant = :id LIMIT 1');
                $stmt->execute([':id' => $identifiant]);
                $admin = $stmt->fetch();

                if ($admin && password_verify($motDePasse, $admin['mot_de_passe_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['admin_id']          = $admin['id'];
                    $_SESSION['admin_identifiant'] = $identifiant;
                    header('Location: index.php');
                    exit;
                } else {
                    // Délai volontaire pour ralentir les attaques par force brute
                    usleep(300000);
                    $erreur = 'Identifiant ou mot de passe incorrect.';
                }
            } catch (PDOException $e) {
                $erreur = 'Erreur de connexion à la base de données. Réessayez.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Connexion — Admin LDS</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<a href="#contenu-principal" class="skip-link">Aller au contenu principal</a>

<main id="contenu-principal">
  <div class="login-wrapper">
    <div class="login-card">

      <h1>Espace administrateur</h1>
      <p>Connectez-vous pour accéder au tableau de bord.</p>

      <?php if ($erreur): ?>
      <div role="alert" class="alerte alerte-erreur"><?= h($erreur) ?></div>
      <?php endif; ?>

      <form method="post" action="login.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>" />

        <div class="form-group">
          <label class="form-label" for="identifiant">Identifiant</label>
          <input
            type="text"
            class="form-control"
            id="identifiant"
            name="identifiant"
            value="<?= h($identifiant) ?>"
            autocomplete="username"
            required
            aria-required="true"
            <?= $erreur ? 'aria-invalid="true"' : '' ?>
          />
        </div>

        <div class="form-group">
          <label class="form-label" for="mot_de_passe">Mot de passe</label>
          <input
            type="password"
            class="form-control"
            id="mot_de_passe"
            name="mot_de_passe"
            autocomplete="current-password"
            required
            aria-required="true"
            <?= $erreur ? 'aria-invalid="true"' : '' ?>
          />
        </div>

        <button type="submit" class="btn-submit">Se connecter</button>
      </form>

      <p class="lien-site-public">
        <a href="../index.php">← Retour au site</a>
      </p>

    </div>
  </div>
</main>

</body>
</html>
