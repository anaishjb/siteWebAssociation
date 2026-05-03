let currentFontSize = 16;

const htmlElement = document.documentElement;

/* Bouton accessibilité */
const accessBtn = document.getElementById("accessibility-btn");
const accessPanel = document.getElementById("accessibility-panel");

/* Zoom */
const increaseButton = document.getElementById("increase-font");
const decreaseButton = document.getElementById("decrease-font");
const resetButton = document.getElementById("reset-font");

/* Espacement */
const spacingRange = document.getElementById("spacing-range");
const resetSpacing = document.getElementById("reset-spacing");

/* Police */
const fontChoice = document.getElementById("font-choice");

/* Autres */
const backToTopButton = document.getElementById("back-to-top");
const menuToggle = document.getElementById("menu-toggle");
const mainMenu = document.getElementById("main-menu");


/* Ouvrir / fermer panneau accessibilité */
if (accessBtn && accessPanel) {
  accessBtn.addEventListener("click", () => {
    const isOpen = accessPanel.hidden === false;

    accessPanel.hidden = isOpen;
    accessBtn.setAttribute("aria-expanded", isOpen ? "false" : "true");
  });
}


/* Zoom */
if (increaseButton) {
  increaseButton.addEventListener("click", () => {
    if (currentFontSize < 22) {
      currentFontSize++;
      htmlElement.style.setProperty("--base-font-size", currentFontSize + "px");
    }
  });
}

if (decreaseButton) {
  decreaseButton.addEventListener("click", () => {
    if (currentFontSize > 12) {
      currentFontSize--;
      htmlElement.style.setProperty("--base-font-size", currentFontSize + "px");
    }
  });
}

if (resetButton) {
  resetButton.addEventListener("click", () => {
    currentFontSize = 16;
    htmlElement.style.setProperty("--base-font-size", "16px");
  });
}


/* Espacement (slider) */
if (spacingRange) {
  spacingRange.addEventListener("input", () => {
    htmlElement.style.setProperty("--letter-spacing", spacingRange.value + "em");
  });
}

if (resetSpacing) {
  resetSpacing.addEventListener("click", () => {
    spacingRange.value = 0;
    htmlElement.style.setProperty("--letter-spacing", "0em");
  });
}


/* Police */
if (fontChoice) {
  fontChoice.addEventListener("change", () => {
    document.body.classList.remove(
      "font-arial",
      "font-verdana",
      "font-tahoma",
      "font-georgia"
    );

    document.body.classList.add("font-" + fontChoice.value.toLowerCase());
  });
}


/* Bouton remonter en haut */
if (backToTopButton) {
  backToTopButton.addEventListener("click", () => {
    window.scrollTo({
      top: 0,
      behavior: "smooth"
    });
  });
}


/* Menu responsive */
if (menuToggle && mainMenu) {
  menuToggle.addEventListener("click", () => {
    const isOpen = mainMenu.classList.toggle("open");
    menuToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
  });
}

const submenuButton = document.querySelector(".submenu-toggle");
const submenu = document.querySelector("#menu-association");

if (submenuButton && submenu) {
  submenuButton.addEventListener("click", () => {
    const isOpen = submenuButton.getAttribute("aria-expanded") === "true";

    submenuButton.setAttribute("aria-expanded", String(!isOpen));
    submenu.hidden = isOpen;
  });
}