<?php

use App\Core\Csrf;
use App\Core\View;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Connexion – UniSync</title>
<link rel="stylesheet" href="/css/style.css">
</head>
<body>
<main class="auth">
  <div class="box">
    <h1>Connexion</h1>
<?php if (!empty($error)): ?>
    <p class="alert" role="alert"><?= View::e($error) ?></p>
<?php endif; ?>
    <form action="/login" method="post">
      <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">
      <label for="login">Login</label>
      <input id="login" name="login" type="text" autocomplete="username" required autofocus>
      <label for="mdp">Mot de passe</label>
      <input id="mdp" name="motDePasse" type="password" autocomplete="current-password" required>
      <button class="btn" type="submit">Se connecter</button>
      <p class="aide"><a href="/">Retour à l’accueil</a></p>
    </form>
  </div>
</main>
</body>
</html>
