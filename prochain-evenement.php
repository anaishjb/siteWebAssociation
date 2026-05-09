<?php
require_once 'api/config.php';
require_once 'includes/helpers.php';

$titrePage       = 'Prochain événement';
$cssFile         = 'evenements.css';
$metaDescription = 'Prochain événement sportif de LDS Association';
$navActive       = 'prochain-evenement';

try {
    $bdd = connecterBDD();

    $evenement = $bdd->query(
        "SELECT * FROM evenements WHERE statut = 'prochain' ORDER BY date_event ASC LIMIT 1"
    )->fetch();

    $erreur = false;
} catch (PDOException $e) {
    $erreur = true;
}

require_once 'includes/header.php';
?>

<nav class="breadcrumb" aria-label="Fil d'Ariane">
  <ol>
    <li><a href="index.php">Accueil</a></li>
    <li><a href="evenements.php">Événements</a></li>
    <li aria-current="page">Prochain événement</li>
  </ol>
</nav>

<main id="contenu-principal">

<?php if ($erreur): ?>
  <div role="alert" class="erreur-chargement container" style="margin: 2rem auto">
    <p>Le contenu n'a pas pu être chargé. Veuillez réessayer.</p>
  </div>

<?php elseif (!$evenement): ?>
  <section class="section">
    <div class="container">
      <h1>Prochain événement</h1>
      <p class="aucun-evenement">Aucun événement à venir pour le moment. Revenez bientôt !</p>
      <p><a href="evenements-passes.php">Voir les événements passés</a></p>
    </div>
  </section>

<?php else: ?>

  <section class="section" aria-labelledby="titre-prochain-evenement">
    <div class="container">
      <h1 id="titre-prochain-evenement"><?= h($evenement['titre']) ?></h1>

      <div class="detail-evenement">
        <div>
          <div class="detail-meta">
            <span class="badge-date">
              <time datetime="<?= h($evenement['date_event']) ?>">
                <?= formaterDateFr($evenement['date_event']) ?>
              </time>
            </span>
            <?php if ($evenement['lieu']): ?>
            <span class="badge-lieu">Lieu : <?= h($evenement['lieu']) ?></span>
            <?php endif; ?>
          </div>
          <p><?= h($evenement['description']) ?></p>
          <p><a href="evenements.php">← Retour aux événements</a></p>
        </div>

        <?php if ($evenement['image_src']): ?>
        <div>
          <img
            class="detail-evenement-image"
            src="<?= h($evenement['image_src']) ?>"
            alt="<?= h($evenement['image_alt']) ?>"
          />
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

<?php endif; ?>

</main>

<?php require_once 'includes/footer.php'; ?>
