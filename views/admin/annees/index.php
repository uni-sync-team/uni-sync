<?php use App\Core\View; ?>
<h1>Années universitaires</h1>

<div class="bar">
  <span></span>
  <a class="btn" href="/admin/annees/nouveau">Nouvelle année</a>
</div>

<?php if (!$annees): ?>
<p>Aucune année universitaire trouvée.</p>
<?php else: ?>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Libellé</th><th>Début</th><th>Fin</th><th></th></tr></thead>
  <tbody>
<?php foreach ($annees as $a): ?>
    <tr>
      <td><?= View::e($a['libelle']) ?></td>
      <td><?= View::e(date('d/m/Y', strtotime($a['dateDebut']))) ?></td>
      <td><?= View::e(date('d/m/Y', strtotime($a['dateFin']))) ?></td>
      <td><a href="/admin/annees/<?= (int) $a['idAnnee'] ?>/modifier">Modifier</a></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>
