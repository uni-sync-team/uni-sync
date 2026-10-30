<?php

use App\Core\Csrf;
use App\Core\View;

$title   = 'Connexion – UniSync';
$styles  = ['/css/login.css'];
$scripts = ['/js/login.js'];

require __DIR__ . "/parts/header.php";

?>

<main class="auth" id="principal">

  <div class="auth-brand">
    <a class="logo" href="/"><i aria-hidden="true"></i>UniSync</a>

    <div>
      <p class="auth-lead">Notes, réclamations et résultats, au même endroit.</p>
      <ul class="auth-roles">
        <li><strong>Étudiant</strong><span>Consulte ses notes et dépose une réclamation.</span></li>
        <li><strong>Enseignant</strong><span>Saisit les notes de ses modules.</span></li>
        <li><strong>Administration</strong><span>Gère les départements, les filières et les comptes.</span></li>
      </ul>
    </div>
  </div>

  <div class="auth-panel">
    <div class="box">
      <h1>Connexion</h1>
      <p class="intro">Votre espace s’ouvre selon votre rôle.</p>

<?php if (!empty($error)): ?>
      <p class="alert" role="alert"><?= View::e($error) ?></p>
<?php endif; ?>

      <form action="/login" method="post">
        <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">

        <label for="login">Login</label>
        <div class="field">
          <input id="login" name="login" type="text" autocomplete="username" required autofocus>
        </div>

        <label for="mdp">Mot de passe</label>
        <div class="field">
          <input id="mdp" name="motDePasse" type="password" autocomplete="current-password" required>
          <button class="reveal" id="reveal" type="button" aria-pressed="false" aria-label="Afficher le mot de passe" hidden>Afficher</button>
        </div>

        <button class="btn" type="submit">Se connecter</button>
      </form>

      <p class="aide">Compte oublié ? Contactez l’administrateur du département.</p>
      <p class="aide"><a href="/">Retour à l’accueil</a></p>
    </div>
  </div>

</main>
</body>
</html>
