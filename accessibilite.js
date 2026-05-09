// Panneau accessibilité, menu mobile et bouton haut de page.
// Chargé par toutes les pages via includes/footer.php et les pages HTML statiques.

// Signale que JS est disponible → active le comportement hamburger en CSS
document.documentElement.classList.add('js');

let taillePoliceCourante = 16;

const elementHtml        = document.documentElement;
const boutonAccess       = document.getElementById('accessibility-btn');
const panneauAccess      = document.getElementById('accessibility-panel');
const boutonAugmenter    = document.getElementById('increase-font');
const boutonDiminuer     = document.getElementById('decrease-font');
const boutonResetPolice  = document.getElementById('reset-font');
const choixPolice        = document.getElementById('font-choice');
const boutonHaut         = document.getElementById('back-to-top');
const boutonMenu         = document.getElementById('menu-toggle');
const menuPrincipal      = document.getElementById('main-menu');
const sliderEspacement   = document.getElementById('spacing-range');
const boutonResetEspace  = document.getElementById('reset-spacing');

// Ouvrir / fermer le panneau accessibilité
if (boutonAccess && panneauAccess) {
  boutonAccess.addEventListener('click', () => {
    const estOuvert = !panneauAccess.hidden;
    panneauAccess.hidden = estOuvert;
    boutonAccess.setAttribute('aria-expanded', estOuvert ? 'false' : 'true');
  });
}

// Agrandir la police
if (boutonAugmenter) {
  boutonAugmenter.addEventListener('click', () => {
    if (taillePoliceCourante < 22) {
      taillePoliceCourante++;
      elementHtml.style.setProperty('--base-font-size', taillePoliceCourante + 'px');
    }
  });
}

// Réduire la police
if (boutonDiminuer) {
  boutonDiminuer.addEventListener('click', () => {
    if (taillePoliceCourante > 12) {
      taillePoliceCourante--;
      elementHtml.style.setProperty('--base-font-size', taillePoliceCourante + 'px');
    }
  });
}

// Remettre la taille par défaut
if (boutonResetPolice) {
  boutonResetPolice.addEventListener('click', () => {
    taillePoliceCourante = 16;
    elementHtml.style.setProperty('--base-font-size', '16px');
  });
}

// Espacement des caractères
if (sliderEspacement) {
  sliderEspacement.addEventListener('input', () => {
    elementHtml.style.setProperty('--letter-spacing', sliderEspacement.value + 'em');
  });
}

if (boutonResetEspace) {
  boutonResetEspace.addEventListener('click', () => {
    sliderEspacement.value = 0;
    elementHtml.style.setProperty('--letter-spacing', '0em');
  });
}

// Choix de la police
if (choixPolice) {
  choixPolice.addEventListener('change', () => {
    document.body.classList.remove('font-arial', 'font-verdana', 'font-tahoma', 'font-georgia');
    document.body.classList.add('font-' + choixPolice.value.toLowerCase());
  });
}

// Menu hamburger (mobile)
if (boutonMenu && menuPrincipal) {
  boutonMenu.addEventListener('click', () => {
    const estOuvert = menuPrincipal.classList.toggle('open');
    boutonMenu.setAttribute('aria-expanded', estOuvert ? 'true' : 'false');
  });
}
