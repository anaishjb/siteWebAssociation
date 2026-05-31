<?php
require_once 'auth.php';
require_once '../api/config.php';
require_once '../includes/helpers.php';

$titrePage = 'Gestion des missions';
$navActive  = 'missions';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$alerte = '';
$erreur = false;

try {
    $bdd = connecterBDD();
} catch (PDOException $e) {
    $erreur = true;
}

// Suppression objectif ou besoin
if (!$erreur && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $alerte = '<div role="alert" class="alerte alerte-erreur">Requête invalide.</div>';
    } else {
        $id   = (int)($_POST['id']   ?? 0);
        $type = $_POST['type'] ?? '';

        if ($id > 0 && in_array($type, ['objectif', 'besoin'], true)) {
            $table = $type === 'objectif' ? 'missions_objectifs' : 'missions_besoins';
            $bdd->prepare("DELETE FROM {$table} WHERE id = :id")->execute([':id' => $id]);
            $alerte = '<div role="status" class="alerte alerte-succes">Élément supprimé.</div>';
        }
    }
}

if (isset($_GET['succes'])) {
    $alerte = '<div role="status" class="alerte alerte-succes">Modifications enregistrées.</div>';
}

$intro     = [];
$objectifs = [];
$besoins   = [];
$reve      = [];

if (!$erreur) {
    $intro     = $bdd->query('SELECT * FROM missions_intro LIMIT 1')->fetch();
    $objectifs = $bdd->query('SELECT * FROM missions_objectifs ORDER BY ordre ASC')->fetchAll();
    $besoins   = $bdd->query('SELECT * FROM missions_besoins ORDER BY ordre ASC')->fetchAll();
    $reve      = $bdd->query('SELECT * FROM missions_reve LIMIT 1')->fetch();
}

require_once 'header.php';
?>

<h1>Gestion des missions</h1>

<?= $alerte ?>

<?php if ($erreur): ?>
<div role="alert" class="alerte alerte-erreur">Erreur de connexion à la base de données.</div>
<?php else: ?>

<!-- Intro -->
<section class="admin-section">
  <div class="admin-section-header">
    <h2>Introduction</h2>
    <a href="mission-intro-form.php" class="btn-admin">Modifier</a>
  </div>
  <?php if ($intro): ?>
  <p><strong>Titre :</strong> <?= h($intro['titre']) ?></p>
  <p><?= h(mb_substr($intro['texte1'], 0, 120)) ?>…</p>
  <?else: ?>
  <p>Aucune introduction enregistrée.</p>
  <?php endif; ?>
</section>

<!-- Objectifs -->
<section class="admin-section">
  <div class="admin-section-header">
    <h2>Ce que nous voulons accomplir</h2>
    <a href="mission-item-form.php?type=objectif" class="btn-admin">+ Ajouter</a>
  </div>

  <?php if (empty($objectifs)): ?>
  <p>Aucun objectif pour l'instant.</p>
  <?php else: ?>
  <div class="admin-table-wrapper">
    <table class="admin-table" aria-label="Liste des objectifs">
      <thead>
        <tr>
          <th scope="col">Ordre</th>
          <th scope="col">Titre</th>
          <th scope="col"><span class="sr-only">Actions</span></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($objectifs as $obj): ?>
        <tr>
          <td><?= (int)$obj['ordre'] ?></td>
          <td><?= h($obj['titre']) ?></td>
          <td class="actions-cel">
            <a href="mission-item-form.php?type=objectif&id=<?= (int)$obj['id'] ?>" class="btn-admin">Modifier</a>
            <form method="post" action="missions.php"
                  onsubmit="return confirm('Supprimer cet objectif ?')">
              <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>" />
              <input type="hidden" name="id"   value="<?= (int)$obj['id'] ?>" />
              <input type="hidden" name="type" value="objectif" />
              <button type="submit" class="btn-admin btn-danger">Supprimer</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</section>

<!-- Besoins -->
<section class="admin-section">
  <div class="admin-section-header">
    <h2>Ce dont nous avons besoin</h2>
    <a href="mission-item-form.php?type=besoin" class="btn-admin">+ Ajouter</a>
  </div>

  <?php if (empty($besoins)): ?>
  <p>Aucun besoin pour l'instant.</p>
  <?php else: ?>
  <div class="admin-table-wrapper">
    <table class="admin-table" aria-label="Liste des besoins">
      <thead>
        <tr>
          <th scope="col">Ordre</th>
          <th scope="col">Titre</th>
          <th scope="col"><span class="sr-only">Actions</span></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($besoins as $besoin): ?>
        <tr>
          <td><?= (int)$besoin['ordre'] ?></td>
          <td><?= h($besoin['titre']) ?></td>
          <td class="actions-cel">
            <a href="mission-item-form.php?type=besoin&id=<?= (int)$besoin['id'] ?>" class="btn-admin">Modifier</a>
            <form method="post" action="missions.php"
                  onsubmit="return confirm('Supprimer ce besoin ?')">
              <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>" />
              <input type="hidden" name="id"   value="<?= (int)$besoin['id'] ?>" />
              <input type="hidden" name="type" value="besoin" />
              <button type="submit" class="btn-admin btn-danger">Supprimer</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</section>

<!-- Rêve -->
<section class="admin-section">
  <div class="admin-section-header">
    <h2>Notre rêve</h2>
    <a href="mission-reve-form.php" class="btn-admin">Modifier</a>
  </div>
  <?php if ($reve): ?>
  <p><strong>Titre :</strong> <?= h($reve['titre']) ?></p>
  <p><?= h(mb_substr($reve['texte1'], 0, 120)) ?>…</p>
  <?php else: ?>
  <p>Aucune section rêve enregistrée.</p>
  <?php endif; ?>
</section>

<?php endif; ?>

<?php require_once 'footer.php'; ?>
