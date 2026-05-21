<?php
require_once 'auth.php';
require_once '../api/config.php';
require_once '../includes/helpers.php';

$titrePage = 'Gestion des partenaires';
$navActive  = 'partenaires';

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

// Suppression
if (!$erreur && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $alerte = '<div role="alert" class="alerte alerte-erreur">Requête invalide.</div>';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $bdd->prepare('SELECT logo_src FROM partenaires WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $p = $stmt->fetch();

            $bdd->prepare('DELETE FROM partenaires WHERE id = :id')->execute([':id' => $id]);

            if ($p && $p['logo_src'] && str_starts_with($p['logo_src'], 'uploads/')) {
                $cheminLogo = dirname(__DIR__) . '/' . $p['logo_src'];
                if (file_exists($cheminLogo)) {
                    unlink($cheminLogo);
                }
            }

            $alerte = '<div role="alert" class="alerte alerte-succes">Partenaire supprimé.</div>';
        }
    }
}

if (isset($_GET['succes'])) {
    $alerte = '<div role="alert" class="alerte alerte-succes">Partenaire enregistré avec succès.</div>';
}

$partenaires = [];
if (!$erreur) {
    $partenaires = $bdd->query('SELECT * FROM partenaires ORDER BY ordre ASC, id ASC')->fetchAll();
}

require_once 'header.php';
?>

<h1>Gestion des partenaires</h1>

<?= $alerte ?>

<?php if ($erreur): ?>
<div role="alert" class="alerte alerte-erreur">Erreur de connexion à la base de données.</div>

<?php else: ?>

<p><a href="partenaire-form.php" class="btn-admin">+ Ajouter un partenaire</a></p>

<?php if (empty($partenaires)): ?>
<p>Aucun partenaire pour l'instant.</p>

<?php else: ?>

<div class="admin-table-wrapper">
  <table class="admin-table" aria-label="Liste des partenaires">
    <thead>
      <tr>
        <th scope="col">Nom</th>
        <th scope="col">Type</th>
        <th scope="col">Site web</th>
        <th scope="col">Ordre</th>
        <th scope="col"><span class="sr-only">Actions</span></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($partenaires as $p): ?>
      <tr>
        <td>
          <?php if ($p['logo_src']): ?>
          <img src="../<?= h($p['logo_src']) ?>" alt="<?= h($p['logo_alt'] ?: $p['nom']) ?>" class="logo-miniature" />
          <?php endif; ?>
          <?= h($p['nom']) ?>
        </td>
        <td><?= h($p['type_partenariat']) ?></td>
        <td>
          <?php if ($p['lien_site']): ?>
          <a href="<?= h($p['lien_site']) ?>" target="_blank" rel="noopener noreferrer">
            <?= h($p['lien_site']) ?>
          </a>
          <?php else: ?>
          —
          <?php endif; ?>
        </td>
        <td><?= (int)$p['ordre'] ?></td>
        <td class="actions-cel">
          <a href="partenaire-form.php?id=<?= (int)$p['id'] ?>" class="btn-admin">Modifier</a>
          <form method="post" action="partenaires.php"
                onsubmit="return confirm('Supprimer ce partenaire définitivement ?')">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>" />
            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>" />
            <button type="submit" class="btn-admin btn-danger">Supprimer</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php endif; ?>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
