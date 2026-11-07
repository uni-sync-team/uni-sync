<?php use App\Core\View; ?>
<h1>Historique des actions</h1>

<form class="filters" action="/admin/historique" method="get">
  <div><label for="login">Utilisateur</label><input id="login" name="login" type="search" value="<?= View::e($filters['login']) ?>" placeholder="Login"></div>
  <div><label for="q">Action</label><input id="q" name="q" type="search" value="<?= View::e($filters['q']) ?>" placeholder="Texte de l’action"></div>
  <div><label for="du">Du</label><input id="du" name="du" type="date" value="<?= View::e($filters['du']) ?>"></div>
  <div><label for="au">Au</label><input id="au" name="au" type="date" value="<?= View::e($filters['au']) ?>"></div>
  <button class="btn" type="submit">Filtrer</button>
  <a class="btn light" href="/admin/historique">Réinitialiser</a>
</form>

<p class="hint"><?= (int) $total ?> action(s) enregistrée(s). Le journal est en lecture seule.</p>

<?php if (!$rows): ?>
<p>Aucune action ne correspond à ces critères.</p>
<?php else: ?>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Date</th><th>Utilisateur</th><th>Action</th></tr></thead>
  <tbody>
<?php foreach ($rows as $r): ?>
    <tr>
      <td><?= View::e(date('d/m/Y H:i', strtotime($r['dateAction']))) ?></td>
      <td><?= View::e($r['login']) ?></td>
      <td><?= View::e($r['action']) ?></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>

<?php require __DIR__ . '/../parts/pagination.php'; ?>
