<?php
require_once 'auth.php';
require_once '../api/config.php';
require_once '../includes/helpers.php';

$id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$modeEdit = $id > 0;

$sectionsValides = ['poles', 'evenements', 'actions'];
$sectionParam    = $_GET['section'] ?? '';
$titrePage       = $modeEdit ? 'Modifier une carte' : 'Ajouter une carte';
$navActive       = 'accueil';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erreurs = [];
$valeurs = [
    'section'   => in_array($sectionParam, $sectionsValides) ? $sectionParam : 'poles',
    'titre'     => '',
    'texte'     => '',
    'lien_href' => '',
    'ordre'     => 0,
];

try {
    $bdd = connecterBDD();

    if ($modeEdit) {
        $stmt = $bdd->prepare('SELECT * FROM cartes_accueil WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $existant = $stmt->fetch();
        if (!$existant) {
            header('Location: accueil.php');
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
        $valeurs['section']   = in_array($_POST['section'] ?? '', $sectionsValides) ? $_POST['section'] : 'poles';
        $valeurs['titre']     = trim($_POST['titre']     ?? '');
        $valeurs['texte']     = trim($_POST['texte']     ?? '');
        $valeurs['lien_href'] = trim($_POST['lien_href'] ?? '');
        $valeurs['ordre']     = (int)($_POST['ordre']    ?? 0);

        if ($valeurs['titre'] === '') $erreurs['titre'] = 'Le titre est obligatoire.';
        if ($valeurs['texte'] === '') $erreurs['texte'] = 'Le texte est obligatoire.';

        if (empty($erreurs)) {
            try {
                $params = [
                    ':section'   => $valeurs['section'],
                    ':titre'     => $valeurs['titre'],
                    ':texte'     => $valeurs['texte'],
                    ':lien_href' => $valeurs['lien_href'] !== '' ? $valeurs['lien_href'] : null,
                    ':ordre'     => $valeurs['ordre'],
                ];

                if ($modeEdit) {
                    $bdd->prepare(
                        'UPDATE cartes_accueil SET section=:section, titre=:titre, texte=:texte,
                         lien_href=:lien_href, ordre=:ordre WHERE id=:id'
                    )->execute(array_merge($params, [':id' => $id]));
                } else {
                    $bdd->prepare(
                        'INSERT INTO cartes_accueil (section, titre, texte, lien_href, ordre)
                         VALUES (:section, :titre, :texte, :lien_href, :ordre)'
                    )->execute($params);
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
    <li aria-current="page"><?= $modeEdit ? 'Modifier une carte' : 'Ajouter une carte' ?></li>
  </ol>
</nav>

<h1><?= h($titrePage) ?></h1>

<?php if (!empty($erreurs['global'])): ?>
<div role="alert" class="alerte alerte-erreur"><?= h($erreurs['global']) ?></div>
<?php endif; ?>

<div class="form-ev-card">
  <form method="post"
        action="accueil-carte-form.php<?= $modeEdit ? '?id=' . $id : '' ?>"
        novalidate>
    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>" />

    <p class="mention-obligatoire">Les champs marqués d'un <span class="obligatoire" aria-hidden="true">*</span><span class="sr-only">astérisque</span> sont obligatoires.</p>

    <div class="form-group">
      <label class="form-label" for="section">Section <span class="obligatoire" aria-hidden="true">*</span></label>
      <select class="form-control" id="section" name="section" required>
        <option value="poles"      <?= $valeurs['section'] === 'poles'      ? 'selected' : '' ?>>Nos pôles</option>
        <option value="evenements" <?= $valeurs['section'] === 'evenements' ? 'selected' : '' ?>>Événements</option>
        <option value="actions"    <?= $valeurs['section'] === 'actions'    ? 'selected' : '' ?>>Actions solidaires</option>
      </select>
    </div>

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
      <textarea class="form-control" id="texte" name="texte" rows="4" required aria-required="true"
        <?= isset($erreurs['texte']) ? 'aria-invalid="true" aria-describedby="err-texte"' : '' ?>
      ><?= h($valeurs['texte']) ?></textarea>
      <?php if (isset($erreurs['texte'])): ?>
      <span class="form-error" id="err-texte" role="alert"><?= h($erreurs['texte']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label class="form-label" for="lien_href">Lien (optionnel)</label>
        <input type="text" class="form-control" id="lien_href" name="lien_href"
          value="<?= h($valeurs['lien_href'] ?? '') ?>" maxlength="255"
          placeholder="evenements.php" />
      </div>

      <div class="form-group">
        <label class="form-label" for="ordre">Ordre d'affichage</label>
        <span class="form-hint" id="hint-ordre">0 = affiché en premier.</span>
        <input type="number" class="form-control" id="ordre" name="ordre"
          value="<?= (int)$valeurs['ordre'] ?>" min="0" aria-describedby="hint-ordre" />
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn-admin">
        <?= $modeEdit ? 'Enregistrer les modifications' : 'Ajouter la carte' ?>
      </button>
      <a href="accueil.php" class="btn-admin btn-retour">Annuler</a>
    </div>
  </form>
</div>

<?php require_once 'footer.php'; ?>
