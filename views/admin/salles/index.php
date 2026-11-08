<?php use App\Core\View; ?>
<h1>Salles</h1>

<div class="bar">
  <span></span>
  <a class="btn" href="/admin/salles/nouveau">Nouvelle salle</a>
</div>

<?php if (!$salles): ?>
<p>Aucune salle trouvée.</p>
<?php else: ?>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Nom</th><th>Capacité</th><th>Type</th><th></th></tr></thead>
  <tbody>
<?php foreach ($salles as $s): ?>
    <tr>
      <td><?= View::e($s['nom']) ?></td>
      <td><?= (int) $s['capacite'] ?></td>
      <td><?= View::e($s['typeSalle'] ?: '—') ?></td>
      <td><a href="/admin/salles/<?= (int) $s['idSalle'] ?>/modifier">Modifier</a></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>
