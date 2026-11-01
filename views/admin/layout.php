<?php

use App\Core\Csrf;
use App\Core\View;

$title  = ($pageTitle ?? 'Administration') . ' – UniSync';
$styles = ['/css/admin.css'];
require __DIR__ . '/../parts/header.php';

?>
<div class="admin">
  <aside class="side">
    <a class="logo" href="/"><i aria-hidden="true"></i>UniSync</a>
    <nav aria-label="Administration">
      <ul>
<?php foreach ($sections as [$key, $label, $href]): ?>
        <li><?php if ($href): ?><a href="<?= View::e($href) ?>"<?= $key === ($active ?? '') ? ' aria-current="page"' : '' ?>><?= View::e($label) ?></a><?php else: ?><span class="soon"><?= View::e($label) ?> <small>bientôt</small></span><?php endif; ?></li>
<?php endforeach; ?>
      </ul>
    </nav>
    <form action="/logout" method="post">
      <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">
      <button class="btn" type="submit">Se déconnecter</button>
    </form>
  </aside>
  <main id="principal" class="main">
<?php if (!empty($flash)): ?>
    <p class="flash <?= View::e($flash['type']) ?>" role="status"><?= View::e($flash['message']) ?></p>
<?php endif; ?>
<?php require __DIR__ . '/../' . $content . '.php'; ?>
  </main>
</div>
</body>
</html>
