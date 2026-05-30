<?php
require_once 'auth.php';
require_once '../api/config.php';
require_once '../includes/helpers.php';

$id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$modeEdit = $id > 0;

$titrePage = $modeEdit ? 'Modifier un partenaire' : 'Ajouter un partenaire';
$navActive  = 'partenaires';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erreurs = [];
$valeurs = [
    'nom'              => '',
    'description'      => '',
    'type_partenariat' => '',
    'lien_site'        => '',
    'logo_src'         => '',
    'logo_alt'         => '',
    'ordre'            => 0,
];

try {
    $bdd = connecterBDD();

    if ($modeEdit) {
        $stmt = $bdd->prepare('SELECT * FROM partenaires WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $existant = $stmt->fetch();
        if (!$existant) {
            header('Location: partenaires.php');
            exit;
        }
        $valeurs = $existant;
    }

} catch (PDOException $e) {
    $erreurs['global'] = 'Erreur de base de données.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $erreurs['global'] = 'Requête invalide. Rechargez la page.';
    } else {

        $valeurs['nom']              = trim($_POST['nom']              ?? '');
        $valeurs['description']      = trim($_POST['description']      ?? '');
        $valeurs['type_partenariat'] = trim($_POST['type_partenariat'] ?? '');
        $valeurs['lien_site']        = trim($_POST['lien_site']        ?? '');
        $valeurs['logo_alt']         = trim($_POST['logo_alt']         ?? '');
        $valeurs['ordre']            = (int)($_POST['ordre']           ?? 0);

        if ($valeurs['nom'] === '') {
            $erreurs['nom'] = 'Le nom est obligatoire.';
        }
        if ($valeurs['description'] === '') {
            $erreurs['description'] = 'La description est obligatoire.';
        }
        if ($valeurs['type_partenariat'] === '') {
            $erreurs['type_partenariat'] = 'Le type de partenariat est obligatoire.';
        }
        if ($valeurs['lien_site'] !== '' && !filter_var($valeurs['lien_site'], FILTER_VALIDATE_URL)) {
            $erreurs['lien_site'] = 'L\'URL du site n\'est pas valide.';
        }

        // Gestion du logo
        $nouveauLogoSrc = $valeurs['logo_src'];

        if (!empty($_FILES['logo']['name'])) {
            $fichier   = $_FILES['logo'];
            $typesOk   = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];
            $maxTaille = 2 * 1024 * 1024;

            if ($fichier['error'] !== UPLOAD_ERR_OK) {
                $erreurs['logo'] = 'Erreur lors du téléversement.';
            } elseif (!in_array($fichier['type'], $typesOk, true)) {
                $erreurs['logo'] = 'Format accepté : JPG, PNG, WEBP, GIF, SVG.';
            } elseif ($fichier['size'] > $maxTaille) {
                $erreurs['logo'] = 'Le logo ne doit pas dépasser 2 Mo.';
            } else {
                $ext        = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
                $nomFichier = uniqid('logo_', true) . '.' . $ext;
                $dossier    = dirname(__DIR__) . '/uploads/partenaires/';

                if (!is_dir($dossier)) {
                    mkdir($dossier, 0755, true);
                }

                if (move_uploaded_file($fichier['tmp_name'], $dossier . $nomFichier)) {
                    if ($modeEdit && str_starts_with((string)$valeurs['logo_src'], 'uploads/')) {
                        $ancienChemin = dirname(__DIR__) . '/' . $valeurs['logo_src'];
                        if (file_exists($ancienChemin)) {
                            unlink($ancienChemin);
                        }
                    }
                    $nouveauLogoSrc = 'uploads/partenaires/' . $nomFichier;
                } else {
                    $erreurs['logo'] = 'Impossible de sauvegarder le logo.';
                }
            }

            if (empty($erreurs['logo']) && $valeurs['logo_alt'] === '') {
                $erreurs['logo_alt'] = 'Le texte alternatif est obligatoire quand un logo est ajouté.';
            }
        }

        if (empty($erreurs)) {
            try {
                $params = [
                    ':nom'              => $valeurs['nom'],
                    ':description'      => $valeurs['description'],
                    ':type_partenariat' => $valeurs['type_partenariat'],
                    ':lien_site'        => $valeurs['lien_site'] !== '' ? $valeurs['lien_site'] : null,
                    ':logo_src'         => $nouveauLogoSrc !== '' ? $nouveauLogoSrc : null,
                    ':logo_alt'         => $valeurs['logo_alt'] !== '' ? $valeurs['logo_alt'] : null,
                    ':ordre'            => $valeurs['ordre'],
                ];

                if ($modeEdit) {
                    $stmt = $bdd->prepare(
                        'UPDATE partenaires
                         SET nom=:nom, description=:description, type_partenariat=:type_partenariat,
                             lien_site=:lien_site, logo_src=:logo_src, logo_alt=:logo_alt, ordre=:ordre
                         WHERE id=:id'
                    );
                    $params[':id'] = $id;
                } else {
                    $stmt = $bdd->prepare(
                        'INSERT INTO partenaires (nom, description, type_partenariat, lien_site, logo_src, logo_alt, ordre)
                         VALUES (:nom, :description, :type_partenariat, :lien_site, :logo_src, :logo_alt, :ordre)'
                    );
                }

                $stmt->execute($params);
                header('Location: partenaires.php?succes=1');
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
    <li><a href="partenaires.php">Partenaires</a></li>
    <li aria-hidden="true"> › </li>
    <li aria-current="page"><?= $modeEdit ? 'Modifier' : 'Ajouter' ?></li>
  </ol>
</nav>

<h1><?= $titrePage ?></h1>

<?php if (!empty($erreurs['global'])): ?>
<div role="alert" class="alerte alerte-erreur"><?= h($erreurs['global']) ?></div>
<?php endif; ?>

<div class="form-ev-card">
  <form method="post"
        action="partenaire-form.php<?= $modeEdit ? '?id=' . $id : '' ?>"
        enctype="multipart/form-data"
        novalidate
        aria-label="Formulaire partenaire">

    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>" />

    <p class="mention-obligatoire">Les champs marqués d'un <span class="obligatoire" aria-hidden="true">*</span><span class="sr-only">astérisque</span> sont obligatoires.</p>

    <div class="form-group">
      <label class="form-label" for="nom">Nom <span class="obligatoire" aria-hidden="true">*</span></label>
      <input
        type="text"
        class="form-control"
        id="nom"
        name="nom"
        value="<?= h($valeurs['nom']) ?>"
        required
        aria-required="true"
        maxlength="255"
        <?= isset($erreurs['nom']) ? 'aria-invalid="true" aria-describedby="err-nom"' : '' ?>
      />
      <?php if (isset($erreurs['nom'])): ?>
      <span class="form-error" id="err-nom" role="alert"><?= h($erreurs['nom']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label class="form-label" for="description">Description <span class="obligatoire" aria-hidden="true">*</span></label>
      <textarea
        class="form-control"
        id="description"
        name="description"
        rows="4"
        required
        aria-required="true"
        <?= isset($erreurs['description']) ? 'aria-invalid="true" aria-describedby="err-description"' : '' ?>
      ><?= h($valeurs['description']) ?></textarea>
      <?php if (isset($erreurs['description'])): ?>
      <span class="form-error" id="err-description" role="alert"><?= h($erreurs['description']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label class="form-label" for="type_partenariat">Type de partenariat <span class="obligatoire" aria-hidden="true">*</span></label>
        <input
          type="text"
          class="form-control"
          id="type_partenariat"
          name="type_partenariat"
          value="<?= h($valeurs['type_partenariat']) ?>"
          required
          aria-required="true"
          maxlength="100"
          placeholder="ex : Institutionnel, Associatif…"
          <?= isset($erreurs['type_partenariat']) ? 'aria-invalid="true" aria-describedby="err-type"' : '' ?>
        />
        <?php if (isset($erreurs['type_partenariat'])): ?>
        <span class="form-error" id="err-type" role="alert"><?= h($erreurs['type_partenariat']) ?></span>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label class="form-label" for="ordre">Ordre d'affichage</label>
        <span class="form-hint" id="hint-ordre">0 = affiché en premier.</span>
        <input
          type="number"
          class="form-control"
          id="ordre"
          name="ordre"
          value="<?= (int)$valeurs['ordre'] ?>"
          min="0"
          aria-describedby="hint-ordre"
        />
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="lien_site">Site web</label>
      <input
        type="url"
        class="form-control"
        id="lien_site"
        name="lien_site"
        value="<?= h($valeurs['lien_site'] ?? '') ?>"
        maxlength="255"
        placeholder="https://..."
        <?= isset($erreurs['lien_site']) ? 'aria-invalid="true" aria-describedby="err-lien"' : '' ?>
      />
      <?php if (isset($erreurs['lien_site'])): ?>
      <span class="form-error" id="err-lien" role="alert"><?= h($erreurs['lien_site']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label class="form-label" for="logo">Logo</label>
      <span class="form-hint" id="hint-logo">Formats acceptés : JPG, PNG, WEBP, GIF, SVG — 2 Mo max.</span>

      <?php if (!empty($valeurs['logo_src'])): ?>
      <div class="apercu-image">
        <img src="../<?= h($valeurs['logo_src']) ?>" alt="<?= h($valeurs['logo_alt'] ?? '') ?>" />
        <p class="apercu-legende">Logo actuel — choisir un nouveau fichier pour le remplacer.</p>
      </div>
      <?php endif; ?>

      <input
        type="file"
        class="form-control"
        id="logo"
        name="logo"
        accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml"
        aria-describedby="hint-logo<?= isset($erreurs['logo']) ? ' err-logo' : '' ?>"
        <?= isset($erreurs['logo']) ? 'aria-invalid="true"' : '' ?>
      />
      <?php if (isset($erreurs['logo'])): ?>
      <span class="form-error" id="err-logo" role="alert"><?= h($erreurs['logo']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label class="form-label" for="logo_alt">Texte alternatif du logo <span class="obligatoire" aria-hidden="true">*</span></label>
      <span class="form-hint" id="hint-alt">Obligatoire si un logo est ajouté.</span>
      <input
        type="text"
        class="form-control"
        id="logo_alt"
        name="logo_alt"
        value="<?= h($valeurs['logo_alt'] ?? '') ?>"
        maxlength="255"
        aria-describedby="hint-alt<?= isset($erreurs['logo_alt']) ? ' err-logo-alt' : '' ?>"
        <?= isset($erreurs['logo_alt']) ? 'aria-invalid="true"' : '' ?>
      />
      <?php if (isset($erreurs['logo_alt'])): ?>
      <span class="form-error" id="err-logo-alt" role="alert"><?= h($erreurs['logo_alt']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn-admin">
        <?= $modeEdit ? 'Enregistrer les modifications' : 'Ajouter le partenaire' ?>
      </button>
      <a href="partenaires.php" class="btn-admin btn-retour">Annuler</a>
    </div>

  </form>
</div>

<?php require_once 'footer.php'; ?>
