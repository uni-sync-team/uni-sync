<?php use App\Core\View; ?>
<h1>Formations et filières</h1>

<?php $tab = 'formations'; require __DIR__ . '/../parts/tabs_formations.php'; ?>

<div class="bar">
  <form action="/admin/formations" method="get">
    <label class="sr" for="filiere">Filière</label>
    <select id="filiere" name="filiere">
      <option value="">Toutes les filières</option>
<?php foreach ($filieres as $f): ?>
      <option value="<?= (int) $f['idFiliere'] ?>"<?= $idFiliere === (int) $f['idFiliere'] ? ' selected' : '' ?>><?= View::e($f['nom']) ?></option>
<?php endforeach; ?>
    </select>
    <button class="btn light" type="submit">Filtrer</button>
  </form>
  <a class="btn" href="/admin/formations/nouveau">Nouvelle formation</a>
</div>

<?php if (!$formations): ?>
<p>Aucune formation trouvée.</p>
<?php else: ?>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Intitulé</th><th>Filière</th><th>Niveau</th><th>Durée</th><th></th></tr></thead>
  <tbody>
<?php foreach ($formations as $f): ?>
    <tr>
      <td><?= View::e($f['intitule']) ?></td>
      <td><?= View::e($f['filiere']) ?></td>
      <td><?= View::e($f['niveau'] ?: '—') ?></td>
      <td><?= $f['duree'] !== null ? (int) $f['duree'] . ' an' . ((int) $f['duree'] > 1 ? 's' : '') : '—' ?></td>
      <td><a href="/admin/formations/<?= (int) $f['idFormation'] ?>/modifier">Modifier</a></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>
