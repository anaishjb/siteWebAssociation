<?php
require_once 'auth.php';
require_once '../api/config.php';
require_once '../includes/helpers.php';

$titrePage = 'Tableau de bord';
$navActive  = 'tableau-bord';

try {
    $bdd = connecterBDD();

    $nbMessages    = (int) $bdd->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();
    $nbNonLus      = (int) $bdd->query('SELECT COUNT(*) FROM contact_messages WHERE lu = 0')->fetchColumn();
    $nbEvenements  = (int) $bdd->query('SELECT COUNT(*) FROM evenements')->fetchColumn();
    $nbPartenaires = (int) $bdd->query('SELECT COUNT(*) FROM partenaires')->fetchColumn();

    $derniersMessages = $bdd->query(
        'SELECT id, nom, prenom, sujet, date_envoi, lu
         FROM contact_messages
         ORDER BY date_envoi DESC
         LIMIT 5'
    )->fetchAll();

    $erreur = false;
} catch (PDOException $e) {
    $erreur = true;
}

require_once 'header.php';
?>

<h1>Tableau de bord</h1>

<?php if ($erreur): ?>
<div role="alert" class="alerte alerte-erreur">Erreur de connexion à la base de données.</div>
<?php else: ?>

<section aria-labelledby="titre-stats">
  <h2 id="titre-stats" class="sr-only">Statistiques</h2>
  <div class="stats-grid">
    <div class="stat-card">
      <span class="stat-number"><?= $nbMessages ?></span>
      <span class="stat-label">Messages reçus</span>
    </div>
    <div class="stat-card">
      <span class="stat-number"><?= $nbNonLus ?></span>
      <span class="stat-label">Non lus</span>
    </div>
    <div class="stat-card">
      <span class="stat-number"><?= $nbEvenements ?></span>
      <span class="stat-label">Événements</span>
    </div>
    <div class="stat-card">
      <span class="stat-number"><?= $nbPartenaires ?></span>
      <span class="stat-label">Partenaires</span>
    </div>
  </div>
</section>

<section aria-labelledby="titre-derniers">
  <h2 id="titre-derniers" class="titre-section">
    Derniers messages reçus
    <?php if ($nbNonLus > 0): ?>
    <span class="badge-non-lu" aria-label="<?= $nbNonLus ?> non lus"><?= $nbNonLus ?></span>
    <?php endif; ?>
  </h2>

  <?php if (empty($derniersMessages)): ?>
  <p>Aucun message pour l'instant.</p>
  <?php else: ?>

  <div class="admin-table-wrapper">
    <table class="admin-table" aria-label="Derniers messages de contact">
      <thead>
        <tr>
          <th scope="col">Expéditeur</th>
          <th scope="col">Sujet</th>
          <th scope="col">Date</th>
          <th scope="col">Statut</th>
          <th scope="col"><span class="sr-only">Actions</span></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($derniersMessages as $m): ?>
        <tr class="<?= !$m['lu'] ? 'ligne-non-lue' : '' ?>">
          <td><?= h($m['prenom'] . ' ' . $m['nom']) ?></td>
          <td><?= h($m['sujet']) ?></td>
          <td><?= h(date('d/m/Y H:i', strtotime($m['date_envoi']))) ?></td>
          <td><?= $m['lu'] ? 'Lu' : '<strong>Non lu</strong>' ?></td>
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

  <p class="lien-suite">
    <a href="messages.php" class="btn-admin">Voir tous les messages</a>
  </p>
  <?php endif; ?>
</section>

<?php endif; ?>

<?php require_once 'footer.php'; ?>
