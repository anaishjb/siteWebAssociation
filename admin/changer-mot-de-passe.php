<?php
require_once 'auth.php';
require_once '../api/config.php';
require_once '../includes/helpers.php';

$titrePage = 'Changer le mot de passe';
$navActive  = 'changer-mdp';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$succes  = false;
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $erreurs['global'] = 'Requête invalide. Rechargez la page.';
    } else {

        $actuel    = $_POST['mot_de_passe_actuel']    ?? '';
        $nouveau   = $_POST['nouveau_mot_de_passe']   ?? '';
        $confirmer = $_POST['confirmer_mot_de_passe'] ?? '';

        if ($actuel === '') {
            $erreurs['actuel'] = 'Le mot de passe actuel est obligatoire.';
        }

        if ($nouveau === '') {
            $erreurs['nouveau'] = 'Le nouveau mot de passe est obligatoire.';
        } elseif (mb_strlen($nouveau) < 8) {
            $erreurs['nouveau'] = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
        }

        if ($confirmer === '') {
            $erreurs['confirmer'] = 'La confirmation est obligatoire.';
        } elseif ($nouveau !== $confirmer) {
            $erreurs['confirmer'] = 'Les deux mots de passe ne correspondent pas.';
        }

        if (empty($erreurs)) {
            try {
                $bdd  = connecterBDD();
                $stmt = $bdd->prepare('SELECT mot_de_passe_hash FROM admin_utilisateurs WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => $_SESSION['admin_id']]);
                $admin = $stmt->fetch();

                if (!$admin || !password_verify($actuel, $admin['mot_de_passe_hash'])) {
                    usleep(300000);
                    $erreurs['actuel'] = 'Mot de passe actuel incorrect.';
                } else {
                    $nouveauHash = password_hash($nouveau, PASSWORD_BCRYPT);

                    $stmt = $bdd->prepare('UPDATE admin_utilisateurs SET mot_de_passe_hash = :hash WHERE id = :id');
                    $stmt->execute([
                        ':hash' => $nouveauHash,
                        ':id'   => $_SESSION['admin_id'],
                    ]);

                    $succes = true;
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }

            } catch (PDOException $e) {
                $erreurs['global'] = 'Erreur de base de données. Réessayez.';
            }
        }
    }
}

require_once 'header.php';
?>

<h1>Changer le mot de passe</h1>

<?php if (!empty($erreurs['global'])): ?>
<div role="alert" class="alerte alerte-erreur"><?= h($erreurs['global']) ?></div>
<?php endif; ?>

<?php if ($succes): ?>
<div role="status" class="alerte alerte-succes">Mot de passe mis à jour avec succès.</div>
<?php endif; ?>

<div class="changer-mdp-card">

  <form method="post" action="changer-mot-de-passe.php" novalidate aria-label="Formulaire de changement de mot de passe">
    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>" />

    <div class="form-group">
      <label class="form-label" for="mot_de_passe_actuel">Mot de passe actuel</label>
      <input
        type="password"
        class="form-control"
        id="mot_de_passe_actuel"
        name="mot_de_passe_actuel"
        autocomplete="current-password"
        required
        aria-required="true"
        <?= isset($erreurs['actuel']) ? 'aria-invalid="true" aria-describedby="err-actuel"' : '' ?>
      />
      <?php if (isset($erreurs['actuel'])): ?>
      <span class="form-error" id="err-actuel" role="alert"><?= h($erreurs['actuel']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label class="form-label" for="nouveau_mot_de_passe">Nouveau mot de passe</label>
      <span class="form-hint" id="hint-nouveau">Minimum 8 caractères.</span>
      <input
        type="password"
        class="form-control"
        id="nouveau_mot_de_passe"
        name="nouveau_mot_de_passe"
        autocomplete="new-password"
        required
        aria-required="true"
        aria-describedby="hint-nouveau<?= isset($erreurs['nouveau']) ? ' err-nouveau' : '' ?>"
        <?= isset($erreurs['nouveau']) ? 'aria-invalid="true"' : '' ?>
      />
      <?php if (isset($erreurs['nouveau'])): ?>
      <span class="form-error" id="err-nouveau" role="alert"><?= h($erreurs['nouveau']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label class="form-label" for="confirmer_mot_de_passe">Confirmer le nouveau mot de passe</label>
      <input
        type="password"
        class="form-control"
        id="confirmer_mot_de_passe"
        name="confirmer_mot_de_passe"
        autocomplete="new-password"
        required
        aria-required="true"
        <?= isset($erreurs['confirmer']) ? 'aria-invalid="true" aria-describedby="err-confirmer"' : '' ?>
      />
      <?php if (isset($erreurs['confirmer'])): ?>
      <span class="form-error" id="err-confirmer" role="alert"><?= h($erreurs['confirmer']) ?></span>
      <?php endif; ?>
    </div>

    <button type="submit" class="btn-submit">Mettre à jour le mot de passe</button>

  </form>

</div>

<?php require_once 'footer.php'; ?>
