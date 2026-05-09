<?php
require_once 'api/config.php';
require_once 'includes/helpers.php';

$titrePage       = 'Accueil';
$cssFile         = 'style.css';
$metaDescription = 'LDS Association — sport et solidarité à Tremblay-en-France';
$navActive       = 'accueil';

try {
    $bdd = connecterBDD();

    $hero         = $bdd->query('SELECT * FROM hero LIMIT 1')->fetch();
    $toutesCartes = $bdd->query('SELECT * FROM cartes_accueil ORDER BY section, ordre ASC')->fetchAll();

    $poles      = array_values(array_filter($toutesCartes, fn($c) => $c['section'] === 'poles'));
    $evenements = array_values(array_filter($toutesCartes, fn($c) => $c['section'] === 'evenements'));
    $actions    = array_values(array_filter($toutesCartes, fn($c) => $c['section'] === 'actions'));

    $erreur = false;
} catch (PDOException $e) {
    $erreur = true;
}

require_once 'includes/header.php';
?>

<main id="contenu-principal">

<?php if ($erreur): ?>
  <div role="alert" class="erreur-chargement container" style="margin: 2rem auto">
    <p>Le contenu n'a pas pu être chargé. Veuillez réessayer.</p>
  </div>

<?php else: ?>

  <?php if ($hero): ?>
  <section class="hero section">
    <div class="container hero-grid">
      <div class="hero-image">
        <img src="<?= h($hero['image_src']) ?>" alt="<?= h($hero['image_alt']) ?>" />
      </div>
      <div class="hero-content">
        <h1><?= h($hero['titre']) ?></h1>
        <p><?= h($hero['texte']) ?></p>
        <a href="<?= h($hero['bouton_href']) ?>" class="btn-primary">
          <?= h($hero['bouton_texte']) ?>
        </a>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="section" aria-labelledby="titre-poles">
    <div class="container">
      <h2 id="titre-poles">Nos pôles</h2>
      <div class="cards-grid">
        <?php foreach ($poles as $pole): ?>
        <article class="card">
          <h3><?= h($pole['titre']) ?></h3>
          <p><?= h($pole['texte']) ?></p>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section section-evenements" aria-labelledby="titre-evenements">
    <div class="container">
      <h2 id="titre-evenements">Événements</h2>
      <div class="cards-grid">
        <?php foreach ($evenements as $ev): ?>
        <a href="<?= h($ev['lien_href']) ?>" class="card-link">
          <article class="card">
            <h3><?= h($ev['titre']) ?></h3>
            <p><?= h($ev['texte']) ?></p>
          </article>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section" aria-labelledby="titre-actions">
    <div class="container">
      <h2 id="titre-actions">Actions solidaires</h2>
      <div class="cards-grid">
        <?php foreach ($actions as $action): ?>
        <article class="card">
          <h3><?= h($action['titre']) ?></h3>
          <p><?= h($action['texte']) ?></p>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

<?php endif; ?>

</main>

<?php require_once 'includes/footer.php'; ?>
