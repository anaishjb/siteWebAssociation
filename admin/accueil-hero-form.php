<?php
require_once 'auth.php';
require_once '../api/config.php';
require_once '../includes/helpers.php';

$titrePage = 'Modifier le bandeau principal';
$navActive  = 'accueil';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erreurs = [];
$valeurs = ['image_src' => '', 'image_alt' => '', 'titre' => '', 'texte' => '', 'bouton_href' => '', 'bouton_texte' => ''];

try {
    $bdd      = connecterBDD();
    $existant = $bdd->query('SELECT * FROM hero LIMIT 1')->fetch();
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
        $valeurs['titre']        = trim($_POST['titre']        ?? '');
        $valeurs['texte']        = trim($_POST['texte']        ?? '');
        $valeurs['bouton_href']  = trim($_POST['bouton_href']  ?? '');
        $valeurs['bouton_texte'] = trim($_POST['bouton_texte'] ?? '');
        $valeurs['image_alt']    = trim($_POST['image_alt']    ?? '');

        if ($valeurs['titre']        === '') $erreurs['titre']        = 'Le titre est obligatoire.';
        if ($valeurs['texte']        === '') $erreurs['texte']        = 'Le texte est obligatoire.';
        if ($valeurs['bouton_href']  === '') $erreurs['bouton_href']  = 'Le lien du bouton est obligatoire.';
        if ($valeurs['bouton_texte'] === '') $erreurs['bouton_texte'] = 'Le texte du bouton est obligatoire.';

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
                $nomFichier = uniqid('hero_', true) . '.' . $ext;
                $dossier    = dirname(__DIR__) . '/uploads/accueil/';
                if (!is_dir($dossier)) mkdir($dossier, 0755, true);

                if (move_uploaded_file($fichier['tmp_name'], $dossier . $nomFichier)) {
                    if (str_starts_with((string)$valeurs['image_src'], 'uploads/')) {
                        $ancien = dirname(__DIR__) . '/' . $valeurs['image_src'];
                        if (file_exists($ancien)) unlink($ancien);
                    }
                    $nouvelleImageSrc = 'uploads/accueil/' . $nomFichier;
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
                        'UPDATE hero SET titre=:titre, texte=:texte, bouton_href=:bouton_href,
                         bouton_texte=:bouton_texte, image_src=:image_src, image_alt=:image_alt
                         WHERE id=:id'
                    )->execute([
                        ':titre'        => $valeurs['titre'],
                        ':texte'        => $valeurs['texte'],
                        ':bouton_href'  => $valeurs['bouton_href'],
                        ':bouton_texte' => $valeurs['bouton_texte'],
                        ':image_src'    => $nouvelleImageSrc,
                        ':image_alt'    => $valeurs['image_alt'],
                        ':id'           => $existant['id'],
                    ]);
                } else {
                    $bdd->prepare(
                        'INSERT INTO hero (titre, texte, bouton_href, bouton_texte, image_src, image_alt)
                         VALUES (:titre, :texte, :bouton_href, :bouton_texte, :image_src, :image_alt)'
                    )->execute([
                        ':titre'        => $valeurs['titre'],
                        ':texte'        => $valeurs['texte'],
                        ':bouton_href'  => $valeurs['bouton_href'],
                        ':bouton_texte' => $valeurs['bouton_texte'],
                        ':image_src'    => $nouvelleImageSrc,
                        ':image_alt'    => $valeurs['image_alt'],
                    ]);
                }
                header('Location: accueil.php?succes=1');
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
    <li><a href="accueil.php">Accueil</a></li>
    <li aria-hidden="true"> › </li>
    <li aria-current="page">Bandeau principal</li>
  </ol>
</nav>

<h1>Modifier le bandeau principal</h1>

<?php if (!empty($erreurs['global'])): ?>
<div role="alert" class="alerte alerte-erreur"><?= h($erreurs['global']) ?></div>
<?php endif; ?>

<div class="form-ev-card">
  <form method="post" action="accueil-hero-form.php" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>" />

    <p class="mention-obligatoire">Les champs marqués d'un <span class="obligatoire" aria-hidden="true">*</span><span class="sr-only">astérisque</span> sont obligatoires.</p>

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
      <label class="form-label" for="texte">Texte <span class="obligatoire" aria-hidden="true">*</span></label>
      <textarea class="form-control" id="texte" name="texte" rows="5" required aria-required="true"
        <?= isset($erreurs['texte']) ? 'aria-invalid="true" aria-describedby="err-texte"' : '' ?>
      ><?= h($valeurs['texte']) ?></textarea>
      <?php if (isset($erreurs['texte'])): ?>
      <span class="form-error" id="err-texte" role="alert"><?= h($erreurs['texte']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label class="form-label" for="bouton_texte">Texte du bouton <span class="obligatoire" aria-hidden="true">*</span></label>
        <input type="text" class="form-control" id="bouton_texte" name="bouton_texte"
          value="<?= h($valeurs['bouton_texte']) ?>" required aria-required="true" maxlength="100"
          <?= isset($erreurs['bouton_texte']) ? 'aria-invalid="true" aria-describedby="err-bouton-texte"' : '' ?> />
        <?php if (isset($erreurs['bouton_texte'])): ?>
        <span class="form-error" id="err-bouton-texte" role="alert"><?= h($erreurs['bouton_texte']) ?></span>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label class="form-label" for="bouton_href">Lien du bouton <span class="obligatoire" aria-hidden="true">*</span></label>
        <input type="text" class="form-control" id="bouton_href" name="bouton_href"
          value="<?= h($valeurs['bouton_href']) ?>" required aria-required="true" maxlength="255"
          placeholder="evenements.php"
          <?= isset($erreurs['bouton_href']) ? 'aria-invalid="true" aria-describedby="err-bouton-href"' : '' ?> />
        <?php if (isset($erreurs['bouton_href'])): ?>
        <span class="form-error" id="err-bouton-href" role="alert"><?= h($erreurs['bouton_href']) ?></span>
        <?php endif; ?>
      </div>
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
      <label class="form-label" for="image_alt">Texte alternatif de l'image <span class="obligatoire" aria-hidden="true">*</span></label>
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
      <a href="accueil.php" class="btn-admin btn-retour">Annuler</a>
    </div>
  </form>
</div>

<?php require_once 'footer.php'; ?>
