<?php
require_once 'api/config.php';
require_once 'includes/helpers.php';

$titrePage       = 'Nos pôles';
$cssFile         = 'poles.css';
$metaDescription = 'Présentation des pôles de LDS Association';
$navActive       = 'qui';

try {
    $bdd = connecterBDD();

    $introTextes = $bdd->query('SELECT texte FROM poles_intro_textes ORDER BY ordre ASC')->fetchAll();
    $poles       = $bdd->query('SELECT * FROM poles ORDER BY ordre ASC')->fetchAll();

    $erreur = false;
} catch (PDOException $e) {
    $erreur = true;
}

require_once 'includes/header.php';
?>

<nav class="breadcrumb" aria-label="Fil d'Ariane">
  <ol>
    <li><a href="index.php">Accueil</a></li>
    <li><a href="QuiSommesNs.html">Qui sommes nous</a></li>
    <li aria-current="page">Nos pôles</li>
  </ol>
</nav>

<main id="contenu-principal">

<?php if ($erreur): ?>
  <div role="alert" class="erreur-chargement container" style="margin: 2rem auto">
    <p>Le contenu n'a pas pu être chargé. Veuillez réessayer.</p>
  </div>

<?php else: ?>

  <section class="section intro-section">
    <div class="container">
      <h1 class="sr-only">Nos pôles</h1>
      <div class="intro-box">
        <?php foreach ($introTextes as $paragraphe): ?>
        <p><?= h($paragraphe['texte']) ?></p>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php foreach ($poles as $index => $pole): ?>
  <section class="section pole-section" aria-labelledby="titre-pole-<?= $index + 1 ?>">
    <div class="container pole-grid">
      <div class="pole-image">
        <img src="<?= h($pole['image_src']) ?>" alt="<?= h($pole['image_alt']) ?>" />
      </div>
      <div class="pole-text">
        <h2 id="titre-pole-<?= $index + 1 ?>"><?= h($pole['titre']) ?></h2>
        <p><?= h($pole['texte1']) ?></p>
        <p><?= h($pole['texte2']) ?></p>
      </div>
    </div>
  </section>
  <?php endforeach; ?>

<?php endif; ?>

</main>

<?php require_once 'includes/footer.php'; ?>
