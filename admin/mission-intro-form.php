<?php
require_once 'auth.php';
require_once '../api/config.php';
require_once '../includes/helpers.php';

$titrePage = 'Modifier l\'introduction — Missions';
$navActive  = 'missions';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erreurs = [];
$valeurs = ['image_src' => '', 'image_alt' => '', 'titre' => '', 'texte1' => '', 'texte2' => ''];

try {
    $bdd     = connecterBDD();
    $existant = $bdd->query('SELECT * FROM missions_intro LIMIT 1')->fetch();
    if ($existant) {
        $valeurs = $existant;
    }
} catch (PDOException $e) {
    $erreurs['global'] = 'Erreur de base de données.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $erreurs['global'] = 'Requête invalide. Rechargez la page.';
    } else {
        $valeurs['titre']     = trim($_POST['titre']     ?? '');
        $valeurs['texte1']    = trim($_POST['texte1']    ?? '');
        $valeurs['texte2']    = trim($_POST['texte2']    ?? '');
        $valeurs['image_alt'] = trim($_POST['image_alt'] ?? '');

        if ($valeurs['titre']  === '') $erreurs['titre']  = 'Le titre est obligatoire.';
        if ($valeurs['texte1'] === '') $erreurs['texte1'] = 'Le premier paragraphe est obligatoire.';
        if ($valeurs['texte2'] === '') $erreurs['texte2'] = 'Le second paragraphe est obligatoire.';

        $nouvelleImageSrc = $valeurs['image_src'];

        if (!empty($_FILES['image']['name'])) {
            $fichier   = $_FILES['image'];
            $typesOk   = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $maxTaille = 5 * 1024 * 1024;

            if ($fichier['error'] !== UPLOAD_ERR_OK) {
                $erreurs['image'] = 'Erreur lors du téléversement.';
            } elseif (!in_array($fichier['type'], $typesOk, true)) {
                $erreurs['image'] = 'Format accepté : JPG, PNG, WEBP, GIF.';
            } elseif ($fichier['size'] > $maxTaille) {
                $erreurs['image'] = 'L\'image ne doit pas dépasser 5 Mo.';
            } else {
                $ext        = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
                $nomFichier = uniqid('mission_intro_', true) . '.' . $ext;
                $dossier    = dirname(__DIR__) . '/uploads/missions/';
                if (!is_dir($dossier)) mkdir($dossier, 0755, true);

                if (move_uploaded_file($fichier['tmp_name'], $dossier . $nomFichier)) {
                    if (str_starts_with((string)$valeurs['image_src'], 'uploads/')) {
                        $ancien = dirname(__DIR__) . '/' . $valeurs['image_src'];
                        if (file_exists($ancien)) unlink($ancien);
                    }
                    $nouvelleImageSrc = 'uploads/missions/' . $nomFichier;
                } else {
                    $erreurs['image'] = 'Impossible de sauvegarder l\'image.';
                }
            }

            if (empty($erreurs['image']) && $valeurs['image_alt'] === '') {
                $erreurs['image_alt'] = 'Le texte alternatif est obligatoire quand une image est ajoutée.';
            }
        }

        if (empty($erreurs)) {
            try {
                if ($existant) {
                    $bdd->prepare(
                        'UPDATE missions_intro SET titre=:titre, texte1=:texte1, texte2=:texte2,
                         image_src=:image_src, image_alt=:image_alt WHERE id=:id'
                    )->execute([
                        ':titre'     => $valeurs['titre'],
                        ':texte1'    => $valeurs['texte1'],
                        ':texte2'    => $valeurs['texte2'],
                        ':image_src' => $nouvelleImageSrc,
                        ':image_alt' => $valeurs['image_alt'],
                        ':id'        => $existant['id'],
                    ]);
                } else {
                    $bdd->prepare(
                        'INSERT INTO missions_intro (titre, texte1, texte2, image_src, image_alt)
                         VALUES (:titre, :texte1, :texte2, :image_src, :image_alt)'
                    )->execute([
                        ':titre'     => $valeurs['titre'],
                        ':texte1'    => $valeurs['texte1'],
                        ':texte2'    => $valeurs['texte2'],
                        ':image_src' => $nouvelleImageSrc,
                        ':image_alt' => $valeurs['image_alt'],
                    ]);
                }
                header('Location: missions.php?succes=1');
                exit;
            } catch (PDOException $e) {
                $erreurs['global'] = 'Erreur lors de la sauvegarde.';
            }
        }
    }
}

require_once 'header.php';
?>

<nav class="breadcrumb-admin" aria-label="Fil d'Ariane">
  <ol>
    <li><a href="missions.php">Missions</a></li>
    <li aria-hidden="true"> › </li>
    <li aria-current="page">Modifier l'introduction</li>
  </ol>
</nav>

<h1>Modifier l'introduction</h1>

<?php if (!empty($erreurs['global'])): ?>
<div role="alert" class="alerte alerte-erreur"><?= h($erreurs['global']) ?></div>
<?php endif; ?>

<div class="form-ev-card">
  <form method="post" action="mission-intro-form.php" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>" />

    <div class="form-group">
      <label class="form-label" for="titre">Titre <span class="obligatoire" aria-hidden="true">*</span></label>
      <input type="text" class="form-control" id="titre" name="titre"
        value="<?= h($valeurs['titre']) ?>" required aria-required="true" maxlength="255"
        <?= isset($erreurs['titre']) ? 'aria-invalid="true" aria-describedby="err-titre"' : '' ?> />
      <?php if (isset($erreurs['titre'])): ?>
      <span class="form-error" id="err-titre" role="alert"><?= h($erreurs['titre']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label class="form-label" for="texte1">Paragraphe 1 <span class="obligatoire" aria-hidden="true">*</span></label>
      <textarea class="form-control" id="texte1" name="texte1" rows="4" required aria-required="true"
        <?= isset($erreurs['texte1']) ? 'aria-invalid="true" aria-describedby="err-texte1"' : '' ?>
      ><?= h($valeurs['texte1']) ?></textarea>
      <?php if (isset($erreurs['texte1'])): ?>
      <span class="form-error" id="err-texte1" role="alert"><?= h($erreurs['texte1']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label class="form-label" for="texte2">Paragraphe 2 <span class="obligatoire" aria-hidden="true">*</span></label>
      <textarea class="form-control" id="texte2" name="texte2" rows="4" required aria-required="true"
        <?= isset($erreurs['texte2']) ? 'aria-invalid="true" aria-describedby="err-texte2"' : '' ?>
      ><?= h($valeurs['texte2']) ?></textarea>
      <?php if (isset($erreurs['texte2'])): ?>
      <span class="form-error" id="err-texte2" role="alert"><?= h($erreurs['texte2']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label class="form-label" for="image">Image</label>
      <span class="form-hint" id="hint-image">Formats acceptés : JPG, PNG, WEBP, GIF — 5 Mo max.</span>
      <?php if (!empty($valeurs['image_src'])): ?>
      <div class="apercu-image">
        <img src="../<?= h($valeurs['image_src']) ?>" alt="<?= h($valeurs['image_alt'] ?? '') ?>" />
        <p class="apercu-legende">Image actuelle — choisir un nouveau fichier pour la remplacer.</p>
      </div>
      <?php endif; ?>
      <input type="file" class="form-control" id="image" name="image"
        accept="image/jpeg,image/png,image/webp,image/gif"
        aria-describedby="hint-image<?= isset($erreurs['image']) ? ' err-image' : '' ?>"
        <?= isset($erreurs['image']) ? 'aria-invalid="true"' : '' ?> />
      <?php if (isset($erreurs['image'])): ?>
      <span class="form-error" id="err-image" role="alert"><?= h($erreurs['image']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label class="form-label" for="image_alt">Texte alternatif de l'image</label>
      <span class="form-hint" id="hint-alt">Obligatoire si une image est ajoutée.</span>
      <input type="text" class="form-control" id="image_alt" name="image_alt"
        value="<?= h($valeurs['image_alt'] ?? '') ?>" maxlength="255"
        aria-describedby="hint-alt<?= isset($erreurs['image_alt']) ? ' err-image-alt' : '' ?>"
        <?= isset($erreurs['image_alt']) ? 'aria-invalid="true"' : '' ?> />
      <?php if (isset($erreurs['image_alt'])): ?>
      <span class="form-error" id="err-image-alt" role="alert"><?= h($erreurs['image_alt']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn-admin">Enregistrer</button>
      <a href="missions.php" class="btn-admin btn-retour">Annuler</a>
    </div>
  </form>
</div>

<?php require_once 'footer.php'; ?>
