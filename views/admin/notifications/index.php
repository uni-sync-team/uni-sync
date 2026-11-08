<?php

use App\Core\Csrf;
use App\Core\View;

?>
<h1>Notifications</h1>

<div class="bar">
  <form class="filters" action="/admin/notifications" method="get" role="search">
    <div><label for="q">Rechercher</label><input id="q" name="q" type="search" value="<?= View::e($filters['q']) ?>" placeholder="Login ou message"></div>
    <div><label for="statut">Statut</label>
      <select id="statut" name="statut">
        <option value="">Tous</option>
        <option value="non_lu"<?= $filters['statut'] === 'non_lu' ? ' selected' : '' ?>>Non lues</option>
        <option value="lu"<?= $filters['statut'] === 'lu' ? ' selected' : '' ?>>Lues</option>
      </select></div>
    <button class="btn light" type="submit">Filtrer</button>
  </form>
  <a class="btn" href="/admin/notifications/nouveau">Nouvelle notification</a>
</div>

<p class="hint"><?= (int) $total ?> notification(s).</p>

<?php if (!$rows): ?>
<p>Aucune notification trouvée.</p>
<?php else: ?>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Date</th><th>Destinataire</th><th>Message</th><th>Statut</th><th></th></tr></thead>
  <tbody>
<?php foreach ($rows as $n): ?>
    <tr>
      <td><?= View::e(date('d/m/Y H:i', strtotime($n['dateCreation']))) ?></td>
      <td><?= View::e($n['login']) ?></td>
      <td><?= View::e(mb_strimwidth($n['message'], 0, 120, '…')) ?></td>
      <td><span class="badge<?= $n['lu'] ? '' : ' warn' ?>"><?= $n['lu'] ? 'Lue' : 'Non lue' ?></span></td>
      <td>
        <form class="inline" action="/admin/notifications/<?= (int) $n['idNotification'] ?>/supprimer" method="post" onsubmit="return confirm('Supprimer cette notification ?')">
          <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">
          <button class="btn light" type="submit">Supprimer</button>
        </form>
      </td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>

<?php require __DIR__ . '/../parts/pagination.php'; ?>
