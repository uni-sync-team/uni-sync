<?php

use App\Core\Csrf;
use App\Core\View;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Espace <?= View::e($label) ?> – UniSync</title>
<link rel="stylesheet" href="/css/style.css">
</head>
<body>
<main class="auth">
  <div class="box">
    <h1>Espace <?= View::e($label) ?></h1>
    <form action="/logout" method="post">
      <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">
      <p class="aide">you are connected, this page will be replaced by the dashboard.</p>
      <button class="btn" type="submit">disconnect</button>
    </form>
  </div>
</main>
</body>
</html>
