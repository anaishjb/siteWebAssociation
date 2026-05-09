<?php
require_once 'api/config.php';
require_once 'includes/helpers.php';

$titrePage       = 'Faire un don';
$cssFile         = 'style.css';
$metaDescription = 'Soutenez LDS Association par un don et contribuez à nos actions solidaires';
$navActive       = 'don';

require_once 'includes/header.php';
?>

<main id="contenu-principal">
  <section class="section">
    <div class="container">

      <h1>Soutenir LDS Association</h1>

      <p class="don-intro">
        LDS Association est une association loi 1901 à but non lucratif. Votre soutien nous permet
        d'organiser des événements sportifs et de financer des actions solidaires concrètes
        pour les familles et personnes en difficulté à Tremblay-en-France et dans les communes voisines.
      </p>

      <h2 id="titre-comment">Comment nous soutenir ?</h2>

      <div class="don-options" aria-labelledby="titre-comment">

        <article class="don-option">
          <h2>Don en espèces</h2>
          <p>
            Vous pouvez remettre votre don directement à nos bénévoles lors de nos événements
            ou à notre siège associatif. Un reçu vous est remis sur demande.
          </p>
          <p>
            <strong>Adresse :</strong><br />
            5 rue Nicolas Copernic<br />
            93290 Tremblay-en-France
          </p>
        </article>

        <article class="don-option">
          <h2>Don lors d'un événement</h2>
          <p>
            Chacun de nos événements sportifs est une occasion de contribuer directement.
            Les inscriptions et les collectes réalisées sur place sont intégralement reversées
            à nos actions solidaires.
          </p>
          <a href="evenements.php" class="btn-primary btn-option">
            Voir nos événements
          </a>
        </article>

        <article class="don-option">
          <h2>Don matériel</h2>
          <p>
            Vêtements, produits d'hygiène, fournitures scolaires, denrées alimentaires non périssables…
            Vos dons en nature sont les bienvenus. Contactez-nous pour organiser une remise.
          </p>
          <a href="contact.php" class="btn-primary btn-option">
            Nous contacter
          </a>
        </article>

      </div>

      <section aria-labelledby="titre-utilisation">
        <h2 id="titre-utilisation">À quoi sert votre don ?</h2>
        <ul class="don-liste">
          <li>Financement des maraudes mensuelles (repas, vêtements, hygiène)</li>
          <li>Organisation du Shopping Solidaire semestriel</li>
          <li>Fournitures scolaires pour les enfants en difficulté</li>
          <li>Frais d'organisation des événements sportifs</li>
          <li>Développement de nouveaux projets solidaires</li>
        </ul>
      </section>

      <div class="don-cta">
        <h2>Une question sur votre don ?</h2>
        <p>
          Notre équipe est disponible pour vous répondre et vous expliquer comment
          votre contribution sera utilisée.
        </p>
        <a href="contact.php" class="btn-primary">Nous écrire</a>
      </div>

    </div>
  </section>
</main>

<?php require_once 'includes/footer.php'; ?>
