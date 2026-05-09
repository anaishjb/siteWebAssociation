<?php
require_once 'api/config.php';
require_once 'includes/helpers.php';

$titrePage       = 'Événements';
$cssFile         = 'evenements.css';
$metaDescription = 'Découvrez les événements sportifs de LDS Association — prochains événements et archives';
$navActive       = 'evenements';

try {
    $bdd = connecterBDD();

    $prochainEv = $bdd->query(
        "SELECT titre, date_event FROM evenements WHERE statut = 'prochain' ORDER BY date_event ASC LIMIT 1"
    )->fetch();

    $nbEvenementsPassés = (int) $bdd->query(
        "SELECT COUNT(*) FROM evenements WHERE statut = 'passe'"
    )->fetchColumn();

    $erreur = false;
} catch (PDOException $e) {
    $erreur = true;
}

require_once 'includes/header.php';
?>

<main id="contenu-principal">

  <section class="section intro-section">
    <div class="container">
      <h1>Nos événements</h1>
      <p>
        LDS Association organise des événements sportifs ouverts à tous pour rassembler
        les habitants autour du sport et de la solidarité. Les fonds collectés financent
        directement nos actions solidaires.
      </p>
    </div>
  </section>

  <section class="section" aria-labelledby="titre-rubriques">
    <div class="container">
      <h2 id="titre-rubriques">Choisissez une rubrique</h2>

      <div class="grille-rubriques">

        <a href="prochain-evenement.php" class="rubrique-card" aria-label="Voir le prochain événement">
          <div class="rubrique-card-corps">
            <h3>Prochain événement</h3>
            <?php if (!$erreur && $prochainEv): ?>
              <p>
                <strong><?= h($prochainEv['titre']) ?></strong><br />
                <time datetime="<?= h($prochainEv['date_event']) ?>">
                  le <?= formaterDateFr($prochainEv['date_event']) ?>
                </time>
              </p>
            <?php else: ?>
              <p>Découvrez notre prochain rendez-vous sportif.</p>
            <?php endif; ?>
            <span class="rubrique-lien" aria-hidden="true">Voir →</span>
          </div>
        </a>

        <a href="evenements-passes.php" class="rubrique-card" aria-label="Voir les événements passés">
          <div class="rubrique-card-corps">
            <h3>Événements passés</h3>
            <?php if (!$erreur): ?>
              <p>
                <?= $nbEvenementsPassés > 0
                  ? $nbEvenementsPassés . ' événement' . ($nbEvenementsPassés > 1 ? 's' : '') . ' archivé' . ($nbEvenementsPassés > 1 ? 's' : '')
                  : 'Retrouvez l\'historique de nos événements.' ?>
              </p>
            <?php else: ?>
              <p>Retrouvez l'historique de nos événements passés.</p>
            <?php endif; ?>
            <span class="rubrique-lien" aria-hidden="true">Voir →</span>
          </div>
        </a>

      </div>
    </div>
  </section>

</main>

<?php require_once 'includes/footer.php'; ?>
