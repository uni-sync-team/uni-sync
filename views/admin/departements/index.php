<?php use App\Core\View; ?>
<h1>Départements</h1>

<div class="bar">
  <form action="/admin/departements" method="get" role="search">
    <label class="sr" for="q">Rechercher</label>
    <input id="q" name="q" type="search" value="<?= View::e($q) ?>" placeholder="Nom du département">
    <button class="btn light" type="submit">Rechercher</button>
  </form>
  <a class="btn" href="/admin/departements/nouveau">Nouveau département</a>
</div>

<?php if (!$departements): ?>
<p>Aucun département trouvé.</p>
<?php else: ?>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Nom</th><th>Description</th><th></th></tr></thead>
  <tbody>
<?php foreach ($departements as $d): ?>
    <tr>
      <td><?= View::e($d['nom']) ?></td>
      <td><?= View::e($d['description'] ?: '—') ?></td>
      <td><a href="/admin/departements/<?= (int) $d['idDepartement'] ?>/modifier">Modifier</a></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>
