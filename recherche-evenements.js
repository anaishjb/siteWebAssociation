// Moteur de recherche avec tolérance aux fautes d'orthographe (Levenshtein)

function normaliser(str) {
  return str
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .toLowerCase();
}

function levenshtein(a, b) {
  const matrix = [];
  for (let i = 0; i <= b.length; i++) matrix[i] = [i];
  for (let j = 0; j <= a.length; j++) matrix[0][j] = j;
  for (let i = 1; i <= b.length; i++) {
    for (let j = 1; j <= a.length; j++) {
      if (b[i - 1] === a[j - 1]) {
        matrix[i][j] = matrix[i - 1][j - 1];
      } else {
        matrix[i][j] = Math.min(
          matrix[i - 1][j - 1] + 1,
          matrix[i][j - 1]     + 1,
          matrix[i - 1][j]     + 1
        );
      }
    }
  }
  return matrix[b.length][a.length];
}

function correspond(terme, texte) {
  const t = normaliser(terme);
  const x = normaliser(texte);

  // Correspondance exacte
  if (x.includes(t)) return true;

  // Fuzzy mot par mot (seulement pour les mots de plus de 2 caractères)
  const motsCherches = t.split(/\s+/).filter(function (m) { return m.length > 2; });
  const motsTexte    = x.split(/\s+/);

  return motsCherches.every(function (motCherche) {
    const tolerance = motCherche.length <= 4 ? 1 : 2;
    return motsTexte.some(function (motTexte) {
      return levenshtein(motCherche, motTexte) <= tolerance;
    });
  });
}

function initRecherche(inputId, grilleId, compteurId, carteSelecteur) {
  const input    = document.getElementById(inputId);
  const grille   = document.getElementById(grilleId);
  const compteur = document.getElementById(compteurId);

  if (!input || !grille) return;

  const selecteur = carteSelecteur || '.carte-evenement';
  const cartes    = Array.from(grille.querySelectorAll(selecteur));
  const total  = cartes.length;
  compteur.textContent = total + ' événement' + (total > 1 ? 's' : '');

  input.addEventListener('input', function () {
    const terme  = this.value.trim();
    let visibles = 0;

    cartes.forEach(function (carte) {
      const texte   = carte.dataset.recherche || '';
      const visible = terme === '' || correspond(terme, texte);
      carte.style.display = visible ? '' : 'none';
      if (visible) visibles++;
    });

    compteur.textContent =
      visibles + ' événement' + (visibles > 1 ? 's' : '') +
      (terme !== '' ? ' trouvé' + (visibles > 1 ? 's' : '') : '');
  });
}
