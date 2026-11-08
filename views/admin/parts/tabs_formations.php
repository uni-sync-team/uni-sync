<?php

// Needs: $tab = 'formations' | 'filieres'
$tab = $tab ?? 'formations';

?>
<nav class="tabs" aria-label="Formations et filières">
  <a href="/admin/formations"<?= $tab === 'formations' ? ' aria-current="page"' : '' ?>>Formations</a>
  <a href="/admin/filieres"<?= $tab === 'filieres' ? ' aria-current="page"' : '' ?>>Filières</a>
</nav>
