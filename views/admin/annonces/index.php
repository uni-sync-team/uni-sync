<?php

use App\Core\Csrf;
use App\Core\View;
use App\Models\Publication;

$classes = ['Active' => 'ok', 'Programmée' => 'warn', 'Expirée' => 'off', 'Désactivée' => 'off'];

?>
<h1>Annonces</h1>

<div class="bar">
  <form class="filters" action="/admin/annonces" method="get" role="search">
    <div><label for="q">Rechercher</label><input id="q" name="q" type="search" value="<?= View::e($q) ?>" placeholder="Titre"></div>
    <button class="btn light" type="submit">Rechercher</button>
  </form>
  <a class="btn" href="/admin/annonces/nouveau">Nouvelle annonce</a>
</div>

<p class="hint"><?= (int) $total ?> annonce(s). Seules les annonces actives sont visibles sur la page d’accueil.</p>

<?php if (!$rows): ?>
<p>Aucune annonce trouvée.</p>
<?php else: ?>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Titre</th><th>Publication</th><th>Expiration</th><th>Statut</th><th>Auteur</th><th></th></tr></thead>
  <tbody>
<?php foreach ($rows as $a): $statut = Publication::statut($a); ?>
    <tr>
      <td><?= View::e($a['titre']) ?></td>
      <td><?= View::e(date('d/m/Y', strtotime($a['datePublication']))) ?></td>
      <td><?= $a['dateExpiration'] !== null ? View::e(date('d/m/Y', strtotime($a['dateExpiration']))) : '—' ?></td>
      <td><span class="badge <?= View::e($classes[$statut]) ?>"><?= View::e($statut) ?></span></td>
      <td><?= View::e($a['prenom'] . ' ' . $a['nom']) ?></td>
      <td>
        <div class="row-actions">
          <a href="/admin/annonces/<?= (int) $a['idPublication'] ?>/modifier">Modifier</a>
          <form class="inline" action="/admin/annonces/<?= (int) $a['idPublication'] ?>/supprimer" method="post" onsubmit="return confirm('Supprimer cette annonce ?')">
            <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">
            <button class="btn light" type="submit">Supprimer</button>
          </form>
        </div>
      </td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>

<?php require __DIR__ . '/../parts/pagination.php'; ?>
