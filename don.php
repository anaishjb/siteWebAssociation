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

        

        

        <article class="don-option don-option-paypal">
          <h3>Don en ligne — PayPal</h3>
          <p>
            Vous pouvez effectuer un don sécurisé en ligne via PayPal.
            
          </p>
          <a href="https://www.paypal.com/paypalme/ldsassoc"
             class="btn-primary btn-option btn-paypal"
             target="_blank"
             rel="noopener noreferrer"
             aria-label="Faire un don via PayPal (nouvelle fenêtre)">
            Faire un don via PayPal
          </a>
        </article>

        <article class="don-option">
          <h3>Don matériel</h3>
          <p>
            Vêtements, produits d'hygiène, fournitures scolaires, denrées alimentaires non périssables…
            Vos dons sont les bienvenus. Contactez-nous pour organiser une remise.
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
