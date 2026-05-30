<?php
require_once 'auth.php';
require_once '../api/config.php';
require_once '../includes/helpers.php';

$type = ($_GET['type'] ?? $_POST['type'] ?? '') === 'besoin' ? 'besoin' : 'objectif';
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$modeEdit = $id > 0;

$table      = $type === 'objectif' ? 'missions_objectifs' : 'missions_besoins';
$labelType  = $type === 'objectif' ? 'objectif' : 'besoin';
$titrePage  = $modeEdit ? "Modifier un {$labelType}" : "Ajouter un {$labelType}";
$navActive  = 'missions';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erreurs = [];
$valeurs = ['titre' => '', 'texte' => '', 'ordre' => 0];

try {
    $bdd = connecterBDD();

    if ($modeEdit) {
        $stmt = $bdd->prepare("SELECT * FROM {$table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $existant = $stmt->fetch();
        if (!$existant) {
            header('Location: missions.php');
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
        $valeurs['titre'] = trim($_POST['titre'] ?? '');
        $valeurs['texte'] = trim($_POST['texte'] ?? '');
        $valeurs['ordre'] = (int)($_POST['ordre'] ?? 0);

        if ($valeurs['titre'] === '') $erreurs['titre'] = 'Le titre est obligatoire.';
        if ($valeurs['texte'] === '') $erreurs['texte'] = 'Le texte est obligatoire.';

        if (empty($erreurs)) {
            try {
                if ($modeEdit) {
                    $bdd->prepare("UPDATE {$table} SET titre=:titre, texte=:texte, ordre=:ordre WHERE id=:id")
                        ->execute([
                            ':titre' => $valeurs['titre'],
                            ':texte' => $valeurs['texte'],
                            ':ordre' => $valeurs['ordre'],
                            ':id'    => $id,
                        ]);
                } else {
                    $bdd->prepare("INSERT INTO {$table} (titre, texte, ordre) VALUES (:titre, :texte, :ordre)")
                        ->execute([
                            ':titre' => $valeurs['titre'],
                            ':texte' => $valeurs['texte'],
                            ':ordre' => $valeurs['ordre'],
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
    <li aria-current="page"><?= $modeEdit ? 'Modifier' : 'Ajouter' ?></li>
  </ol>
</nav>

<h1><?= h($titrePage) ?></h1>

<?php if (!empty($erreurs['global'])): ?>
<div role="alert" class="alerte alerte-erreur"><?= h($erreurs['global']) ?></div>
<?php endif; ?>

<div class="form-ev-card">
  <form method="post"
        action="mission-item-form.php?type=<?= h($type) ?><?= $modeEdit ? '&id=' . $id : '' ?>"
        novalidate>
    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>" />
    <input type="hidden" name="type" value="<?= h($type) ?>" />

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

    <div class="form-group">
      <label class="form-label" for="ordre">Ordre d'affichage</label>
      <span class="form-hint" id="hint-ordre">0 = affiché en premier.</span>
      <input type="number" class="form-control" id="ordre" name="ordre"
        value="<?= (int)$valeurs['ordre'] ?>" min="0" aria-describedby="hint-ordre" />
    </div>

    <div class="form-actions">
      <button type="submit" class="btn-admin">
        <?= $modeEdit ? 'Enregistrer les modifications' : 'Ajouter' ?>
      </button>
      <a href="missions.php" class="btn-admin btn-retour">Annuler</a>
    </div>
  </form>
</div>

<?php require_once 'footer.php'; ?>
