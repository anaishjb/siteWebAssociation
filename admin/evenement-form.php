<?php
require_once 'auth.php';
require_once '../api/config.php';
require_once '../includes/helpers.php';

$id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$modeEdit = $id > 0;

$titrePage = $modeEdit ? 'Modifier un événement' : 'Ajouter un événement';
$navActive  = 'evenements';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erreurs = [];
$valeurs = [
    'titre'       => '',
    'description' => '',
    'date_event'  => '',
    'lieu'        => '',
    'image_src'   => '',
    'image_alt'   => '',
    'statut'      => 'prochain',
];

try {
    $bdd = connecterBDD();

    if ($modeEdit) {
        $stmt = $bdd->prepare('SELECT * FROM evenements WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $existant = $stmt->fetch();
        if (!$existant) {
            header('Location: evenements.php');
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

        $valeurs['titre']       = trim($_POST['titre']       ?? '');
        $valeurs['description'] = trim($_POST['description'] ?? '');
        $valeurs['date_event']  = trim($_POST['date_event']  ?? '');
        $valeurs['lieu']        = trim($_POST['lieu']        ?? '');
        $valeurs['image_alt']   = trim($_POST['image_alt']   ?? '');
        $valeurs['statut']      = ($_POST['statut'] ?? '') === 'passe' ? 'passe' : 'prochain';

        if ($valeurs['titre'] === '') {
            $erreurs['titre'] = 'Le titre est obligatoire.';
        }
        if ($valeurs['description'] === '') {
            $erreurs['description'] = 'La description est obligatoire.';
        }
        if ($valeurs['date_event'] === '') {
            $erreurs['date_event'] = 'La date est obligatoire.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $valeurs['date_event'])) {
            $erreurs['date_event'] = 'Format de date invalide.';
        }

        // Gestion de l'image uploadée
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
                $nomFichier = uniqid('ev_', true) . '.' . $ext;
                $dossier    = dirname(__DIR__) . '/uploads/evenements/';

                if (!is_dir($dossier)) {
                    mkdir($dossier, 0755, true);
                }

                if (move_uploaded_file($fichier['tmp_name'], $dossier . $nomFichier)) {
                    if ($modeEdit && str_starts_with((string)$valeurs['image_src'], 'uploads/')) {
                        $ancienChemin = dirname(__DIR__) . '/' . $valeurs['image_src'];
                        if (file_exists($ancienChemin)) {
                            unlink($ancienChemin);
                        }
                    }
                    $nouvelleImageSrc = 'uploads/evenements/' . $nomFichier;
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
                $params = [
                    ':titre'       => $valeurs['titre'],
                    ':description' => $valeurs['description'],
                    ':date_event'  => $valeurs['date_event'],
                    ':lieu'        => $valeurs['lieu'] !== '' ? $valeurs['lieu'] : null,
                    ':image_src'   => $nouvelleImageSrc !== '' ? $nouvelleImageSrc : null,
                    ':image_alt'   => $valeurs['image_alt'] !== '' ? $valeurs['image_alt'] : null,
                    ':statut'      => $valeurs['statut'],
                ];

                if ($modeEdit) {
                    $stmt = $bdd->prepare(
                        'UPDATE evenements
                         SET titre=:titre, description=:description, date_event=:date_event,
                             lieu=:lieu, image_src=:image_src, image_alt=:image_alt, statut=:statut
                         WHERE id=:id'
                    );
                    $params[':id'] = $id;
                } else {
                    $stmt = $bdd->prepare(
                        'INSERT INTO evenements (titre, description, date_event, lieu, image_src, image_alt, statut)
                         VALUES (:titre, :description, :date_event, :lieu, :image_src, :image_alt, :statut)'
                    );
                }

                $stmt->execute($params);
                header('Location: evenements.php?succes=1');
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
    <li><a href="evenements.php">Événements</a></li>
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
        action="evenement-form.php<?= $modeEdit ? '?id=' . $id : '' ?>"
        enctype="multipart/form-data"
        novalidate
        aria-label="Formulaire événement">

    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>" />

    <p class="mention-obligatoire">Les champs marqués d'un <span class="obligatoire" aria-hidden="true">*</span><span class="sr-only">astérisque</span> sont obligatoires.</p>

    <div class="form-group">
      <label class="form-label" for="titre">Titre <span class="obligatoire" aria-hidden="true">*</span></label>
      <input
        type="text"
        class="form-control"
        id="titre"
        name="titre"
        value="<?= h($valeurs['titre']) ?>"
        required
        aria-required="true"
        maxlength="255"
        <?= isset($erreurs['titre']) ? 'aria-invalid="true" aria-describedby="err-titre"' : '' ?>
      />
      <?php if (isset($erreurs['titre'])): ?>
      <span class="form-error" id="err-titre" role="alert"><?= h($erreurs['titre']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label class="form-label" for="description">Description <span class="obligatoire" aria-hidden="true">*</span></label>
      <textarea
        class="form-control"
        id="description"
        name="description"
        rows="5"
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
        <label class="form-label" for="date_event">Date <span class="obligatoire" aria-hidden="true">*</span></label>
        <input
          type="date"
          class="form-control"
          id="date_event"
          name="date_event"
          value="<?= h($valeurs['date_event']) ?>"
          required
          aria-required="true"
          <?= isset($erreurs['date_event']) ? 'aria-invalid="true" aria-describedby="err-date"' : '' ?>
        />
        <?php if (isset($erreurs['date_event'])): ?>
        <span class="form-error" id="err-date" role="alert"><?= h($erreurs['date_event']) ?></span>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label class="form-label" for="statut">Statut <span class="obligatoire" aria-hidden="true">*</span></label>
        <select class="form-control" id="statut" name="statut" required>
          <option value="prochain" <?= $valeurs['statut'] === 'prochain' ? 'selected' : '' ?>>À venir</option>
          <option value="passe"    <?= $valeurs['statut'] === 'passe'    ? 'selected' : '' ?>>Passé</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="lieu">Lieu</label>
      <input
        type="text"
        class="form-control"
        id="lieu"
        name="lieu"
        value="<?= h($valeurs['lieu'] ?? '') ?>"
        maxlength="255"
      />
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

      <input
        type="file"
        class="form-control"
        id="image"
        name="image"
        accept="image/jpeg,image/png,image/webp,image/gif"
        aria-describedby="hint-image<?= isset($erreurs['image']) ? ' err-image' : '' ?>"
        <?= isset($erreurs['image']) ? 'aria-invalid="true"' : '' ?>
      />
      <?php if (isset($erreurs['image'])): ?>
      <span class="form-error" id="err-image" role="alert"><?= h($erreurs['image']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label class="form-label" for="image_alt">Texte alternatif de l'image <span class="obligatoire" aria-hidden="true">*</span></label>
      <span class="form-hint" id="hint-alt">Décrit l'image pour les personnes malvoyantes. Obligatoire si une image est ajoutée.</span>
      <input
        type="text"
        class="form-control"
        id="image_alt"
        name="image_alt"
        value="<?= h($valeurs['image_alt'] ?? '') ?>"
        maxlength="255"
        aria-describedby="hint-alt<?= isset($erreurs['image_alt']) ? ' err-image-alt' : '' ?>"
        <?= isset($erreurs['image_alt']) ? 'aria-invalid="true"' : '' ?>
      />
      <?php if (isset($erreurs['image_alt'])): ?>
      <span class="form-error" id="err-image-alt" role="alert"><?= h($erreurs['image_alt']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn-admin">
        <?= $modeEdit ? 'Enregistrer les modifications' : 'Ajouter l\'événement' ?>
      </button>
      <a href="evenements.php" class="btn-admin btn-retour">Annuler</a>
    </div>

  </form>
</div>

<?php require_once 'footer.php'; ?>
