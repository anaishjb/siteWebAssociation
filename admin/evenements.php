<?php
require_once 'auth.php';
require_once '../api/config.php';
require_once '../includes/helpers.php';

$titrePage = 'Gestion des événements';
$navActive  = 'evenements';

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
            $stmt = $bdd->prepare('SELECT image_src FROM evenements WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $ev = $stmt->fetch();

            $bdd->prepare('DELETE FROM evenements WHERE id = :id')->execute([':id' => $id]);

            if ($ev && $ev['image_src'] && str_starts_with($ev['image_src'], 'uploads/')) {
                $cheminImage = dirname(__DIR__) . '/' . $ev['image_src'];
                if (file_exists($cheminImage)) {
                    unlink($cheminImage);
                }
            }

            $alerte = '<div role="alert" class="alerte alerte-succes">Événement supprimé.</div>';
        }
    }
}

if (isset($_GET['succes'])) {
    $alerte = '<div role="alert" class="alerte alerte-succes">Événement enregistré avec succès.</div>';
}

$evenements = [];
if (!$erreur) {
    $evenements = $bdd->query(
        'SELECT * FROM evenements ORDER BY date_event DESC'
    )->fetchAll();
}

require_once 'header.php';
?>

<h1>Gestion des événements</h1>

<?= $alerte ?>

<?php if ($erreur): ?>
<div role="alert" class="alerte alerte-erreur">Erreur de connexion à la base de données.</div>

<?php else: ?>

<p><a href="evenement-form.php" class="btn-admin">+ Ajouter un événement</a></p>

<?php if (empty($evenements)): ?>
<p>Aucun événement pour l'instant.</p>

<?php else: ?>

<div class="admin-table-wrapper">
  <table class="admin-table" aria-label="Liste des événements">
    <thead>
      <tr>
        <th scope="col">Titre</th>
        <th scope="col">Date</th>
        <th scope="col">Lieu</th>
        <th scope="col">Statut</th>
        <th scope="col"><span class="sr-only">Actions</span></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($evenements as $ev): ?>
      <tr>
        <td><?= h($ev['titre']) ?></td>
        <td><?= h(date('d/m/Y', strtotime($ev['date_event']))) ?></td>
        <td><?= $ev['lieu'] ? h($ev['lieu']) : '—' ?></td>
        <td>
          <span class="badge-statut badge-<?= h($ev['statut']) ?>">
            <?= $ev['statut'] === 'prochain' ? 'À venir' : 'Passé' ?>
          </span>
        </td>
        <td class="actions-cel">
          <a href="evenement-form.php?id=<?= (int)$ev['id'] ?>" class="btn-admin">Modifier</a>
          <form method="post" action="evenements.php"
                onsubmit="return confirm('Supprimer cet événement définitivement ?')">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>" />
            <input type="hidden" name="id" value="<?= (int)$ev['id'] ?>" />
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
