<?php use App\Core\View; ?>
<h1>Enseignants</h1>

<div class="bar">
  <form action="/admin/enseignants" method="get" role="search">
    <label class="sr" for="q">Rechercher</label>
    <input id="q" name="q" type="search" value="<?= View::e($q) ?>" placeholder="Nom ou Prénom">
    <button class="btn light" type="submit">Rechercher</button>
  </form>
  <a class="btn" href="/admin/enseignants/nouveau">Nouvel Enseignant</a>
</div>

<?php if (!$enseignants): ?>
<p>Aucun enseignant trouvé.</p>
<?php else: ?>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Nom</th><th>Prénom</th><th>Département</th><th>Grade</th><th>Email</th><th>Statut</th><th></th></tr></thead>
  <tbody>
<?php foreach ($enseignants as $e): ?>
    <tr>
      <td><?= View::e($e['nom']) ?></td>
      <td><?= View::e($e['prenom']) ?></td>
      <td><?= View::e($e['departement']) ?></td>
      <td><?= View::e($e['grade'] ?: '—') ?></td>
      <td><?= View::e($e['email']) ?></td>
      <td><span class="badge<?= $e['actif'] ? '' : ' off' ?>"><?= $e['actif'] ? 'Actif' : 'Désactivé' ?></span></td>
      <td><a href="/admin/enseignants/<?= (int) $e['idEnseignant'] ?>/modifier">Modifier</a></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>
