<?php use App\Core\View; ?>
<h1>Formations et filières</h1>

<?php $tab = 'filieres'; require __DIR__ . '/../parts/tabs_formations.php'; ?>

<div class="bar">
  <span></span>
  <a class="btn" href="/admin/filieres/nouveau">Nouvelle filière</a>
</div>

<?php if (!$filieres): ?>
<p>Aucune filière trouvée.</p>
<?php else: ?>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Filière</th><th>Département</th><th>Description</th><th></th></tr></thead>
  <tbody>
<?php foreach ($filieres as $f): ?>
    <tr>
      <td><?= View::e($f['nom']) ?></td>
      <td><?= View::e($f['departement']) ?></td>
      <td><?= View::e($f['description'] ?: '—') ?></td>
      <td>
        <a href="/admin/formations?filiere=<?= (int) $f['idFiliere'] ?>">Formations</a> ·
        <a href="/admin/filieres/<?= (int) $f['idFiliere'] ?>/modifier">Modifier</a>
      </td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>
