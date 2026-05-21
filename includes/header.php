<?php
// Variables attendues depuis la page appelante :
// $titrePage       — titre affiché dans l'onglet du navigateur
// $cssFile         — chemin vers la feuille de style (ex. "style.css")
// $metaDescription — description pour les moteurs de recherche
// $navActive       — identifiant du lien actif : 'accueil' | 'qui' | 'evenements' | 'actions' | 'contact' | 'don'
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?= h($metaDescription) ?>" />
  <title><?= h($titrePage) ?> - LDS Association</title>
  <link rel="stylesheet" href="<?= h($cssFile) ?>?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . '/siteWebAssolds/' . $cssFile) ?>" />
</head>

<body id="top">

<a href="#contenu-principal" class="skip-link">Aller au contenu principal</a>

<header class="site-header">
  <div class="header-wrapper">
    <div class="logo-container">
      <a href="index.php" aria-label="Retour à l'accueil">
        <img src="images/Logolds.jpg" alt="Logo de LDS Association" class="logo" />
      </a>
    </div>

    <div class="nav-container">
      <nav class="main-nav" aria-label="Navigation principale">
        <button class="menu-toggle" id="menu-toggle"
          aria-expanded="false" aria-controls="main-menu"
          aria-label="Ouvrir le menu">☰</button>

        <ul class="nav-list" id="main-menu">
          <li>
            <a href="index.php" <?= $navActive === 'accueil' ? 'class="active" aria-current="page"' : '' ?>>
              Accueil
            </a>
          </li>
          <li>
            <a href="QuiSommesNs.html" <?= $navActive === 'qui' ? 'class="active" aria-current="page"' : '' ?>>
              Qui sommes nous
            </a>
          </li>
          <li class="has-submenu">
            <?php
              $sousEvenements = ['evenements-passes', 'prochain-evenement'];
              $evenementsActif = $navActive === 'evenements';
              $sousPageActive  = in_array($navActive, $sousEvenements);
            ?>
            <a href="evenements.php"
               <?= $evenementsActif ? 'class="active" aria-current="page"' : ($sousPageActive ? 'class="active"' : '') ?>>
              Événements
            </a>
            <ul class="submenu">
              <li>
                <a href="evenements-passes.php" <?= $navActive === 'evenements-passes' ? 'aria-current="page"' : '' ?>>
                  Événements passés
                </a>
              </li>
              <li>
                <a href="prochain-evenement.php" <?= $navActive === 'prochain-evenement' ? 'aria-current="page"' : '' ?>>
                  Prochain événement
                </a>
              </li>
            </ul>
          </li>
          <li>
            <a href="actions_solidaires.html" <?= $navActive === 'actions' ? 'class="active" aria-current="page"' : '' ?>>
              Actions solidaires
            </a>
          </li>
          <li>
            <a href="contact.php" <?= $navActive === 'contact' ? 'class="active" aria-current="page"' : '' ?>>
              Contact
            </a>
          </li>
          <li>
            <a href="don.php" <?= $navActive === 'don' ? 'class="active" aria-current="page"' : '' ?>>
              Don
            </a>
          </li>
        </ul>
      </nav>

      <div class="accessibility">
        <button type="button" id="accessibility-btn" class="accessibility-btn"
          aria-expanded="false" aria-controls="accessibility-panel">
          Accessibilité
        </button>

        <div id="accessibility-panel" class="accessibility-panel" hidden>
          <button type="button" id="increase-font">Agrandir A+</button>
          <button type="button" id="reset-font">Taille normale A</button>
          <button type="button" id="decrease-font">Réduire A-</button>

          <label for="spacing-range">Espacement des caractères</label>
          <input type="range" id="spacing-range" min="0" max="0.2" step="0.01" value="0" />
          <button type="button" id="reset-spacing">Réinitialiser espacement</button>

          <label for="font-choice">Police</label>
          <select id="font-choice">
            <option value="Arial">Arial</option>
            <option value="Verdana">Verdana</option>
            <option value="Tahoma">Tahoma</option>
            <option value="Georgia">Georgia</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</header>
