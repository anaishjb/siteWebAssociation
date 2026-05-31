<?php
require_once 'api/config.php';
require_once 'includes/helpers.php';

$titrePage       = 'Prochains événements';
$cssFile         = 'evenements.css';
$metaDescription = 'Prochains événements sportifs de LDS Association';
$navActive       = 'prochain-evenement';

try {
    $bdd = connecterBDD();

    $evenements = $bdd->query(
        "SELECT * FROM evenements WHERE statut = 'prochain' ORDER BY date_event ASC"
    )->fetchAll();

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
    <li aria-current="page">À venir</li>
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
      <h1>Prochains événements</h1>
      <p>Découvrez les événements sportifs que nous organisons prochainement.</p>
      <p><a href="evenements.php">← Retour aux événements</a></p>
    </div>
  </section>

  <section class="section" aria-labelledby="titre-prochains">
    <div class="container">
      <h2 id="titre-prochains">Liste des prochains événements</h2>

      <?php if (empty($evenements)): ?>
        <p class="aucun-evenement">Aucun événement à venir pour le moment. Revenez bientôt !</p>
        <p><a href="evenements-passes.php">Voir les événements passés</a></p>

      <?php else: ?>

        <div class="recherche-wrapper">
          <label for="recherche-prochain" class="sr-only">Rechercher un événement</label>
          <input
            type="search"
            id="recherche-prochain"
            class="recherche-input"
            placeholder="Rechercher par titre, lieu, description…"
            aria-controls="grille-prochains"
            aria-label="Rechercher un prochain événement"
          />
          <p class="recherche-compteur" id="compteur-prochains" aria-live="polite" aria-atomic="true"></p>
        </div>

        <div class="grille-evenements" id="grille-prochains">
          <?php foreach ($evenements as $ev): ?>
          <article
            class="carte-evenement"
            data-recherche="<?= h(mb_strtolower($ev['titre'] . ' ' . $ev['lieu'] . ' ' . $ev['description'])) ?>"
          >
            <?php if ($ev['image_src']): ?>
            <img
              class="carte-evenement-image"
              src="<?= h($ev['image_src']) ?>"
              alt="<?= h($ev['image_alt']) ?>"
            />
            <?php endif; ?>
            <div class="carte-evenement-corps">
              <h3><?= h($ev['titre']) ?></h3>
              <div class="detail-meta">
                <span class="badge-date">
                  <time datetime="<?= h($ev['date_event']) ?>">
                    <?= formaterDateFr($ev['date_event']) ?>
                  </time>
                </span>
                <?php if ($ev['lieu']): ?>
                <span class="badge-lieu">Lieu : <?= h($ev['lieu']) ?></span>
                <?php endif; ?>
              </div>
              <p><?= h($ev['description']) ?></p>
            </div>
          </article>
          <?php endforeach; ?>
        </div>

      <?php endif; ?>
    </div>
  </section>

<?php endif; ?>

</main>

<script src="recherche-evenements.js"></script>
<script>
  initRecherche('recherche-prochain', 'grille-prochains', 'compteur-prochains');
</script>

<?php require_once 'includes/footer.php'; ?>
