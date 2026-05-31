<?php
session_start();
require_once 'api/config.php';
require_once 'includes/helpers.php';

$titrePage       = 'Contact';
$cssFile         = 'style.css';
$metaDescription = 'Contactez LDS Association — posez vos questions ou rejoignez nos bénévoles';
$navActive       = 'contact';

// Génération du token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$succes  = false;
$erreurs = [];
$valeurs = ['nom' => '', 'prenom' => '', 'email' => '', 'sujet' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Vérification CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $erreurs['global'] = 'Requête invalide. Veuillez recharger la page et réessayer.';
    } else {

        // Récupération et nettoyage des valeurs
        $valeurs['nom']     = trim($_POST['nom']     ?? '');
        $valeurs['prenom']  = trim($_POST['prenom']  ?? '');
        $valeurs['email']   = trim($_POST['email']   ?? '');
        $valeurs['sujet']   = trim($_POST['sujet']   ?? '');
        $valeurs['message'] = trim($_POST['message'] ?? '');

        // Validations
        if ($valeurs['nom'] === '') {
            $erreurs['nom'] = 'Le nom est obligatoire.';
        } elseif (mb_strlen($valeurs['nom']) > 100) {
            $erreurs['nom'] = 'Le nom ne doit pas dépasser 100 caractères.';
        }

        if ($valeurs['prenom'] === '') {
            $erreurs['prenom'] = 'Le prénom est obligatoire.';
        } elseif (mb_strlen($valeurs['prenom']) > 100) {
            $erreurs['prenom'] = 'Le prénom ne doit pas dépasser 100 caractères.';
        }

        if ($valeurs['email'] === '') {
            $erreurs['email'] = 'L\'adresse e-mail est obligatoire.';
        } elseif (!filter_var($valeurs['email'], FILTER_VALIDATE_EMAIL)) {
            $erreurs['email'] = 'L\'adresse e-mail n\'est pas valide.';
        } elseif (mb_strlen($valeurs['email']) > 255) {
            $erreurs['email'] = 'L\'adresse e-mail ne doit pas dépasser 255 caractères.';
        }

        if ($valeurs['sujet'] === '') {
            $erreurs['sujet'] = 'Le sujet est obligatoire.';
        } elseif (mb_strlen($valeurs['sujet']) > 255) {
            $erreurs['sujet'] = 'Le sujet ne doit pas dépasser 255 caractères.';
        }

        if ($valeurs['message'] === '') {
            $erreurs['message'] = 'Le message est obligatoire.';
        } elseif (mb_strlen($valeurs['message']) < 10) {
            $erreurs['message'] = 'Le message doit contenir au moins 10 caractères.';
        }

        // Insertion si pas d'erreurs
        if (empty($erreurs)) {
            try {
                $bdd  = connecterBDD();
                $stmt = $bdd->prepare(
                    'INSERT INTO contact_messages (nom, prenom, email, sujet, message)
                     VALUES (:nom, :prenom, :email, :sujet, :message)'
                );
                $stmt->execute([
                    ':nom'     => $valeurs['nom'],
                    ':prenom'  => $valeurs['prenom'],
                    ':email'   => $valeurs['email'],
                    ':sujet'   => $valeurs['sujet'],
                    ':message' => $valeurs['message'],
                ]);

                $succes  = true;
                $valeurs = ['nom' => '', 'prenom' => '', 'email' => '', 'sujet' => '', 'message' => ''];
                // Renouvellement du token après envoi réussi
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            } catch (PDOException $e) {
                $erreurs['global'] = 'Une erreur s\'est produite lors de l\'envoi. Veuillez réessayer.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<main id="contenu-principal">
  <section class="section">
    <div class="container">

      <h1>Nous contacter</h1>

      <p class="texte-intro">
        Vous avez une question, souhaitez rejoindre notre équipe de bénévoles ou proposer un partenariat ?
        Remplissez ce formulaire, nous vous répondrons dans les plus brefs délais.
      </p>

      <?php if (!empty($erreurs['global'])): ?>
      <div role="alert" class="alerte alerte-erreur" aria-live="assertive">
        <?= h($erreurs['global']) ?>
      </div>
      <?php endif; ?>

      <?php if ($succes): ?>
      <div role="status" class="alerte alerte-succes" aria-live="polite" aria-atomic="true">
        Votre message a bien été envoyé. Merci de nous avoir contactés !
      </div>
      <?php endif; ?>

      <?php if (!$succes): ?>
      <p class="mention-obligatoire">
        Les champs marqués d'un <span class="obligatoire" aria-hidden="true">*</span>
        <span class="sr-only">astérisque</span> sont obligatoires.
      </p>

      <form method="post" action="contact.php#form-contact" id="form-contact"
            novalidate aria-label="Formulaire de contact"
            class="formulaire-contact">

        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>" />

        <div class="form-group">
          <label class="form-label" for="nom">
            Nom <span class="obligatoire" aria-hidden="true">*</span>
          </label>
          <input
            type="text"
            class="form-control"
            id="nom"
            name="nom"
            value="<?= h($valeurs['nom']) ?>"
            autocomplete="family-name"
            required
            aria-required="true"
            maxlength="100"
            <?= isset($erreurs['nom']) ? 'aria-invalid="true" aria-describedby="err-nom"' : '' ?>
          />
          <?php if (isset($erreurs['nom'])): ?>
          <span class="form-error" id="err-nom" role="alert"><?= h($erreurs['nom']) ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="prenom">
            Prénom <span class="obligatoire" aria-hidden="true">*</span>
          </label>
          <input
            type="text"
            class="form-control"
            id="prenom"
            name="prenom"
            value="<?= h($valeurs['prenom']) ?>"
            autocomplete="given-name"
            required
            aria-required="true"
            maxlength="100"
            <?= isset($erreurs['prenom']) ? 'aria-invalid="true" aria-describedby="err-prenom"' : '' ?>
          />
          <?php if (isset($erreurs['prenom'])): ?>
          <span class="form-error" id="err-prenom" role="alert"><?= h($erreurs['prenom']) ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="email">
            Adresse e-mail <span class="obligatoire" aria-hidden="true">*</span>
          </label>
          <input
            type="email"
            class="form-control"
            id="email"
            name="email"
            value="<?= h($valeurs['email']) ?>"
            autocomplete="email"
            required
            aria-required="true"
            maxlength="255"
            <?= isset($erreurs['email']) ? 'aria-invalid="true" aria-describedby="err-email"' : '' ?>
          />
          <?php if (isset($erreurs['email'])): ?>
          <span class="form-error" id="err-email" role="alert"><?= h($erreurs['email']) ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="sujet">
            Sujet <span class="obligatoire" aria-hidden="true">*</span>
          </label>
          <input
            type="text"
            class="form-control"
            id="sujet"
            name="sujet"
            value="<?= h($valeurs['sujet']) ?>"
            required
            aria-required="true"
            maxlength="255"
            <?= isset($erreurs['sujet']) ? 'aria-invalid="true" aria-describedby="err-sujet"' : '' ?>
          />
          <?php if (isset($erreurs['sujet'])): ?>
          <span class="form-error" id="err-sujet" role="alert"><?= h($erreurs['sujet']) ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="message">
            Message <span class="obligatoire" aria-hidden="true">*</span>
          </label>
          <span class="form-hint" id="hint-message">Minimum 10 caractères.</span>
          <textarea
            class="form-control"
            id="message"
            name="message"
            required
            aria-required="true"
            aria-describedby="hint-message<?= isset($erreurs['message']) ? ' err-message' : '' ?>"
            <?= isset($erreurs['message']) ? 'aria-invalid="true"' : '' ?>
          ><?= h($valeurs['message']) ?></textarea>
          <?php if (isset($erreurs['message'])): ?>
          <span class="form-error" id="err-message" role="alert"><?= h($erreurs['message']) ?></span>
          <?php endif; ?>
        </div>

        <button type="submit" class="btn-submit">Envoyer le message</button>

      </form>
      <?php endif; ?>

      

    </div>
  </section>
</main>

<?php require_once 'includes/footer.php'; ?>
