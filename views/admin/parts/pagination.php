<?php

use App\Core\View;

// Needs: $basePath, $query (active filters), $page, $pages
$link = fn(int $p) => View::e($basePath . '?' . http_build_query($query + ['p' => $p]));

?>
<?php if ($pages > 1): ?>
<nav class="pager" aria-label="Pagination">
<?php if ($page > 1): ?><a href="<?= $link($page - 1) ?>">‹ Précédent</a><?php endif; ?>
  <span>Page <?= (int) $page ?> sur <?= (int) $pages ?></span>
<?php if ($page < $pages): ?><a href="<?= $link($page + 1) ?>">Suivant ›</a><?php endif; ?>
</nav>
<?php endif; ?>
