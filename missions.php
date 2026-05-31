<?php
require_once 'api/config.php';
require_once 'includes/helpers.php';

$titrePage       = 'Nos missions';
$cssFile         = 'missions.css';
$metaDescription = 'Nos missions solidaires — LDS Association';
$navActive       = 'missions';

try {
    $bdd = connecterBDD();

    $intro     = $bdd->query('SELECT * FROM missions_intro LIMIT 1')->fetch();
    $objectifs = $bdd->query('SELECT * FROM missions_objectifs ORDER BY ordre ASC')->fetchAll();
    $besoins   = $bdd->query('SELECT * FROM missions_besoins ORDER BY ordre ASC')->fetchAll();
    $reve      = $bdd->query('SELECT * FROM missions_reve LIMIT 1')->fetch();

    $erreur = false;
} catch (PDOException $e) {
    $erreur = true;
}

require_once 'includes/header.php';
?>

<nav class="breadcrumb" aria-label="Fil d'Ariane">
  <ol>
    <li><a href="index.php">Accueil</a></li>
    <li><a href="actions_solidaires.html">Actions solidaires</a></li>
    <li aria-current="page">Missions</li>
  </ol>
</nav>

<main id="contenu-principal">

<?php if ($erreur): ?>
  <div role="alert" class="erreur-chargement container" style="margin: 2rem auto">
    <p>Le contenu n'a pas pu être chargé. Veuillez réessayer.</p>
  </div>

<?php else: ?>

  <?php if ($intro): ?>
  <section class="section intro">
    <div class="container intro-grid">
      <div class="intro-image">
        <img src="<?= h($intro['image_src']) ?>" alt="<?= h($intro['image_alt']) ?>" />
      </div>
      <div class="intro-text">
        <h1><?= h($intro['titre']) ?></h1>
        <p><?= h($intro['texte1']) ?></p>
        <p><?= h($intro['texte2']) ?></p>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="section" aria-labelledby="titre-objectifs">
    <div class="container">
      <h2 id="titre-objectifs">Ce que nous voulons accomplir</h2>
      <div class="cards-grid">
        <?php foreach ($objectifs as $obj): ?>
        <article class="card">
          <h3><?= h($obj['titre']) ?></h3>
          <p><?= h($obj['texte']) ?></p>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section besoin" aria-labelledby="titre-besoin">
    <div class="container small-container">
      <h2 id="titre-besoin">Ce dont nous avons besoin pour aller plus loin</h2>
      <?php foreach ($besoins as $besoin): ?>
      <article class="wide-card">
        <h3><?= h($besoin['titre']) ?></h3>
        <p><?= h($besoin['texte']) ?></p>
      </article>
      <?php endforeach; ?>
    </div>
  </section>

  <?php if ($reve): ?>
  <section class="section reve" aria-labelledby="titre-reve">
    <div class="container reve-grid">
      <div>
        <h2 id="titre-reve"><?= h($reve['titre']) ?></h2>
        <p><?= h($reve['texte1']) ?></p>
        <p><?= h($reve['texte2']) ?></p>
      </div>
      <div class="reve-image">
        <img src="<?= h($reve['image_src']) ?>" alt="<?= h($reve['image_alt']) ?>" />
      </div>
    </div>
  </section>
  <?php endif; ?>

<?php endif; ?>

</main>

<?php require_once 'includes/footer.php'; ?>
