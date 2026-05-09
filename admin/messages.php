<?php
require_once 'auth.php';
require_once '../api/config.php';
require_once '../includes/helpers.php';

$titrePage = 'Messages contact';
$navActive  = 'messages';

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

// Actions POST : marquer comme lu ou supprimer
if (!$erreur && $_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $alerte = '<div role="alert" class="alerte alerte-erreur">Requête invalide.</div>';
    } else {
        $action = $_POST['action'] ?? '';
        $id     = (int) ($_POST['id'] ?? 0);

        if ($action === 'marquer_lu' && $id > 0) {
            $bdd->prepare('UPDATE contact_messages SET lu = 1 WHERE id = :id')->execute([':id' => $id]);
            $alerte = '<div role="alert" class="alerte alerte-succes">Message marqué comme lu.</div>';
        } elseif ($action === 'supprimer' && $id > 0) {
            $bdd->prepare('DELETE FROM contact_messages WHERE id = :id')->execute([':id' => $id]);
            if (isset($_GET['id'])) {
                header('Location: messages.php?supprime=1');
                exit;
            }
            $alerte = '<div role="alert" class="alerte alerte-succes">Message supprimé.</div>';
        }
    }
}

// Affichage d'un message précis
$messageDetail = null;
if (!$erreur && isset($_GET['id'])) {
    $idDetail = (int) $_GET['id'];
    $stmt     = $bdd->prepare('SELECT * FROM contact_messages WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $idDetail]);
    $messageDetail = $stmt->fetch();

    // Marquer automatiquement comme lu à l'ouverture
    if ($messageDetail && !$messageDetail['lu']) {
        $bdd->prepare('UPDATE contact_messages SET lu = 1 WHERE id = :id')->execute([':id' => $idDetail]);
        $messageDetail['lu'] = 1;
    }
}

// Liste complète
$messages = [];
if (!$erreur) {
    $messages = $bdd->query(
        'SELECT id, nom, prenom, email, sujet, date_envoi, lu
         FROM contact_messages
         ORDER BY date_envoi DESC'
    )->fetchAll();
}

if (isset($_GET['supprime'])) {
    $alerte = '<div role="alert" class="alerte alerte-succes">Message supprimé avec succès.</div>';
}

require_once 'header.php';
?>

<h1>Messages de contact</h1>

<?= $alerte ?>

<?php if ($erreur): ?>
<div role="alert" class="alerte alerte-erreur">Erreur de connexion à la base de données.</div>

<?php elseif ($messageDetail): ?>

<nav class="breadcrumb-admin" aria-label="Fil d'Ariane">
  <ol>
    <li><a href="messages.php">Messages</a></li>
    <li aria-hidden="true"> › </li>
    <li aria-current="page">Message #<?= (int)$messageDetail['id'] ?></li>
  </ol>
</nav>

<article class="message-detail" aria-labelledby="titre-message">
  <h2 id="titre-message" class="message-titre"><?= h($messageDetail['sujet']) ?></h2>

  <dl class="message-meta">
    <dt>De :</dt>
    <dd><?= h($messageDetail['prenom'] . ' ' . $messageDetail['nom']) ?></dd>
    <dt>E-mail :</dt>
    <dd><a href="mailto:<?= h($messageDetail['email']) ?>"><?= h($messageDetail['email']) ?></a></dd>
    <dt>Reçu le :</dt>
    <dd><?= h(date('d/m/Y à H:i', strtotime($messageDetail['date_envoi']))) ?></dd>
    <dt>Statut :</dt>
    <dd><?= $messageDetail['lu'] ? 'Lu' : '<strong>Non lu</strong>' ?></dd>
  </dl>

  <div class="message-body" aria-label="Contenu du message"><?= h($messageDetail['message']) ?></div>

  <div class="actions-message">
    <a href="mailto:<?= h($messageDetail['email']) ?>" class="btn-admin">
      Répondre par e-mail
    </a>

    <?php if (!$messageDetail['lu']): ?>
    <form method="post" action="messages.php?id=<?= (int)$messageDetail['id'] ?>">
      <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>" />
      <input type="hidden" name="action" value="marquer_lu" />
      <input type="hidden" name="id" value="<?= (int)$messageDetail['id'] ?>" />
      <button type="submit" class="btn-admin">Marquer comme lu</button>
    </form>
    <?php endif; ?>

    <form method="post" action="messages.php?id=<?= (int)$messageDetail['id'] ?>"
          onsubmit="return confirm('Supprimer ce message définitivement ?')">
      <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>" />
      <input type="hidden" name="action" value="supprimer" />
      <input type="hidden" name="id" value="<?= (int)$messageDetail['id'] ?>" />
      <button type="submit" class="btn-admin btn-danger">Supprimer</button>
    </form>

    <a href="messages.php" class="btn-admin btn-retour">← Retour à la liste</a>
  </div>
</article>

<?php else: ?>

<?php if (empty($messages)): ?>
<p>Aucun message reçu pour l'instant.</p>
<?php else: ?>

<div class="admin-table-wrapper">
  <table class="admin-table" aria-label="Liste des messages de contact">
    <thead>
      <tr>
        <th scope="col">#</th>
        <th scope="col">Expéditeur</th>
        <th scope="col">E-mail</th>
        <th scope="col">Sujet</th>
        <th scope="col">Date</th>
        <th scope="col">Statut</th>
        <th scope="col"><span class="sr-only">Actions</span></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($messages as $m): ?>
      <tr class="<?= !$m['lu'] ? 'ligne-non-lue' : '' ?>">
        <td><?= (int)$m['id'] ?></td>
        <td><?= h($m['prenom'] . ' ' . $m['nom']) ?></td>
        <td><?= h($m['email']) ?></td>
        <td><?= h($m['sujet']) ?></td>
        <td><?= h(date('d/m/Y H:i', strtotime($m['date_envoi']))) ?></td>
        <td>
          <?php if (!$m['lu']): ?>
            <span class="badge-non-lu" aria-label="Non lu">Non lu</span>
          <?php else: ?>
            Lu
          <?php endif; ?>
        </td>
        <td>
          <a href="messages.php?id=<?= (int)$m['id'] ?>" class="btn-admin">
            Voir<span class="sr-only"> le message de <?= h($m['prenom'] . ' ' . $m['nom']) ?></span>
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php endif; ?>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
