<?php
require_once 'api/config.php';
require_once 'includes/helpers.php';

$titrePage       = 'Nos partenaires';
$cssFile         = 'style.css';
$metaDescription = 'Les partenaires de LDS Association qui soutiennent nos actions solidaires';
$navActive       = 'actions';

try {
    $bdd        = connecterBDD();
    $partenaires = $bdd->query('SELECT * FROM partenaires ORDER BY ordre ASC, id ASC')->fetchAll();
    $erreur     = false;
} catch (PDOException $e) {
    $erreur = true;
}

require_once 'includes/header.php';
?>

<nav class="breadcrumb" aria-label="Fil d'Ariane">
  <ol>
    <li><a href="index.php">Accueil</a></li>
    <li><a href="actions_solidaires.html">Actions solidaires</a></li>
    <li aria-current="page">Nos partenaires</li>
  </ol>
</nav>

<main id="contenu-principal">
  <section class="section">
    <div class="container">

      <h1>Nos partenaires</h1>

      <p class="texte-intro">
        LDS Association s'appuie sur le soutien de partenaires institutionnels et associatifs
        qui partagent nos valeurs. Grâce à eux, nous pouvons organiser nos événements et
        concrétiser nos actions solidaires.
      </p>

      <?php if ($erreur): ?>
      <div role="alert" class="alerte alerte-erreur">
        Le contenu n'a pas pu être chargé. Veuillez réessayer.
      </div>

      <?php elseif (empty($partenaires)): ?>
      <p class="partenaires-vide">
        Aucun partenaire n'est enregistré pour le moment. Revenez bientôt !
      </p>

      <?php else: ?>
      <div class="partenaires-liste">

        <?php foreach ($partenaires as $p): ?>
        <article class="partenaire-card" aria-labelledby="partenaire-<?= (int)$p['id'] ?>">

          <?php if ($p['logo_src']): ?>
          <img
            class="partenaire-logo"
            src="<?= h($p['logo_src']) ?>"
            alt="<?= h($p['logo_alt'] ?: $p['nom']) ?>"
          />
          <?php endif; ?>

          <h2 id="partenaire-<?= (int)$p['id'] ?>"><?= h($p['nom']) ?></h2>
          <span class="partenaire-badge"><?= h($p['type_partenariat']) ?></span>
          <p><?= h($p['description']) ?></p>

          <?php if ($p['lien_site']): ?>
          <a
            href="<?= h($p['lien_site']) ?>"
            class="btn-lien"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Visiter le site de <?= h($p['nom']) ?> (nouvelle fenêtre)"
          >
            Visiter le site →
          </a>
          <?php endif; ?>

        </article>
        <?php endforeach; ?>

      </div>
      <?php endif; ?>

      <section class="section-secondaire" aria-labelledby="titre-devenir">
        <h2 id="titre-devenir">Devenir partenaire</h2>
        <p>
          Vous êtes une entreprise, une institution ou une association et souhaitez soutenir
          nos actions ? Contactez-nous pour en savoir plus sur les modalités de partenariat.
        </p>
        <a href="contact.php" class="btn-primary">Nous contacter</a>
      </section>

    </div>
  </section>
</main>

<?php require_once 'includes/footer.php'; ?>
