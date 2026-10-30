(function () {
  var input = document.getElementById('mdp');
  var btn = document.getElementById('reveal');

  if (!input || !btn) {
    return;
  }

  // Le bouton est caché dans le HTML : sans JavaScript, il ne sert à rien.
  btn.hidden = false;

  btn.addEventListener('click', function () {
    var show = input.type === 'password';

    input.type = show ? 'text' : 'password';
    btn.textContent = show ? 'Masquer' : 'Afficher';
    btn.setAttribute('aria-pressed', show);
    btn.setAttribute('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
  });
})();
