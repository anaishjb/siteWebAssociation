<?php
require_once 'auth.php';
require_once '../api/config.php';
require_once '../includes/helpers.php';

$titrePage = 'Gestion de l\'accueil';
$navActive  = 'accueil';

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

// Suppression d'une carte
if (!$erreur && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $alerte = '<div role="alert" class="alerte alerte-erreur">Requête invalide.</div>';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $bdd->prepare('DELETE FROM cartes_accueil WHERE id = :id')->execute([':id' => $id]);
            $alerte = '<div role="alert" class="alerte alerte-succes">Carte supprimée.</div>';
        }
    }
}

if (isset($_GET['succes'])) {
    $alerte = '<div role="alert" class="alerte alerte-succes">Modifications enregistrées.</div>';
}

$hero   = [];
$cartes = [];

if (!$erreur) {
    $hero   = $bdd->query('SELECT * FROM hero LIMIT 1')->fetch();
    $cartes = $bdd->query('SELECT * FROM cartes_accueil ORDER BY section, ordre ASC')->fetchAll();
}

$sections = [
    'poles'       => 'Nos pôles',
    'evenements'  => 'Événements',
    'actions'     => 'Actions solidaires',
];

require_once 'header.php';
?>

<h1>Gestion de l'accueil</h1>

<?= $alerte ?>

<?php if ($erreur): ?>
<div role="alert" class="alerte alerte-erreur">Erreur de connexion à la base de données.</div>
<?php else: ?>

<!-- Hero -->
<section class="admin-section">
  <div class="admin-section-header">
    <h2>Bandeau principal (hero)</h2>
    <a href="accueil-hero-form.php" class="btn-admin">Modifier</a>
  </div>
  <?php if ($hero): ?>
  <p><strong>Titre :</strong> <?= h($hero['titre']) ?></p>
  <p><strong>Bouton :</strong> <?= h($hero['bouton_texte']) ?> → <?= h($hero['bouton_href']) ?></p>
  <?php else: ?>
  <p>Aucun hero enregistré.</p>
  <?php endif; ?>
</section>

<!-- Cartes par section -->
<?php foreach ($sections as $cle => $libelle): ?>
<?php $cartesFiltrees = array_values(array_filter($cartes, fn($c) => $c['section'] === $cle)); ?>
<section class="admin-section">
  <div class="admin-section-header">
    <h2>Cartes — <?= h($libelle) ?></h2>
    <a href="accueil-carte-form.php?section=<?= h($cle) ?>" class="btn-admin">+ Ajouter</a>
  </div>

  <?php if (empty($cartesFiltrees)): ?>
  <p>Aucune carte pour cette section.</p>
  <?php else: ?>
  <div class="admin-table-wrapper">
    <table class="admin-table" aria-label="Cartes — <?= h($libelle) ?>">
      <thead>
        <tr>
          <th scope="col">Ordre</th>
          <th scope="col">Titre</th>
          <th scope="col">Lien</th>
          <th scope="col"><span class="sr-only">Actions</span></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cartesFiltrees as $carte): ?>
        <tr>
          <td><?= (int)$carte['ordre'] ?></td>
          <td><?= h($carte['titre']) ?></td>
          <td><?= $carte['lien_href'] ? h($carte['lien_href']) : '—' ?></td>
          <td class="actions-cel">
            <a href="accueil-carte-form.php?id=<?= (int)$carte['id'] ?>" class="btn-admin">Modifier</a>
            <form method="post" action="accueil.php"
                  onsubmit="return confirm('Supprimer cette carte ?')">
              <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>" />
              <input type="hidden" name="id" value="<?= (int)$carte['id'] ?>" />
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
<?php endforeach; ?>

<?php endif; ?>

<?php require_once 'footer.php'; ?>
