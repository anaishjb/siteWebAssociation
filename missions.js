let currentFontSize = 16;

const htmlElement = document.documentElement;

const accessBtn = document.getElementById("accessibility-btn");
const accessPanel = document.getElementById("accessibility-panel");

const increaseButton = document.getElementById("increase-font");
const decreaseButton = document.getElementById("decrease-font");
const resetButton = document.getElementById("reset-font");

const spacingRange = document.getElementById("spacing-range");
const resetSpacing = document.getElementById("reset-spacing");

const fontChoice = document.getElementById("font-choice");

const backToTopButton = document.getElementById("back-to-top");
const menuToggle = document.getElementById("menu-toggle");
const mainMenu = document.getElementById("main-menu");

accessBtn.addEventListener("click", () => {
  const isOpen = accessPanel.hidden === false;
  accessPanel.hidden = isOpen;
  accessBtn.setAttribute("aria-expanded", isOpen ? "false" : "true");
});

increaseButton.addEventListener("click", () => {
  if (currentFontSize < 22) {
    currentFontSize++;
    htmlElement.style.setProperty("--base-font-size", currentFontSize + "px");
  }
});

decreaseButton.addEventListener("click", () => {
  if (currentFontSize > 12) {
    currentFontSize--;
    htmlElement.style.setProperty("--base-font-size", currentFontSize + "px");
  }
});

resetButton.addEventListener("click", () => {
  currentFontSize = 16;
  htmlElement.style.setProperty("--base-font-size", "16px");
});

spacingRange.addEventListener("input", () => {
  htmlElement.style.setProperty("--letter-spacing", spacingRange.value + "em");
});

resetSpacing.addEventListener("click", () => {
  spacingRange.value = 0;
  htmlElement.style.setProperty("--letter-spacing", "0em");
});

fontChoice.addEventListener("change", () => {
  document.body.classList.remove(
    "font-arial",
    "font-verdana",
    "font-tahoma",
    "font-georgia"
  );

  document.body.classList.add("font-" + fontChoice.value.toLowerCase());
});

backToTopButton.addEventListener("click", () => {
  window.scrollTo({
    top: 0,
    behavior: "smooth"
  });
});

menuToggle.addEventListener("click", () => {
  const isOpen = mainMenu.classList.toggle("open");
  menuToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
});