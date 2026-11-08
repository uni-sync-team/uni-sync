<?php use App\Core\View; use App\Models\Etudiant; ?>
<h1>Etudiants</h1>

<div class="bar">
  <form action="/admin/etudiants" method="get" role="search">
    <label class="sr" for="q">Rechercher</label>
    <input id="q" name="q" type="search" value="<?= View::e($q) ?>" placeholder="Nom ou Prenom">
    <button class="btn light" type="submit">Rechercher</button>
  </form>
  <a class="btn" href="/admin/etudiants/nouveau">Nouvel Etudiant</a>
</div>

<?php if (!$etudiants): ?>
<p>Aucun utilisateur trouvé.</p>
<?php else: ?>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Nom</th><th>Prenom</th><th>CNE</th><th>Date de Naissance</th><th>Telephone</th><th>Status</th><th></th></tr></thead>
  <tbody>
<?php foreach ($etudiants as $u): ?>
    <tr>
      <td><?= View::e($u['nom']) ?><?= (int) $u['idEtudiant'] === $me ? ' <small>(vous)</small>' : '' ?></td>
      <td><?= View::e($u['prenom']) ?></td>
      <td><?= View::e($u['cne'] ?? '—') ?></td>
      <td><?= $u['dateNaissance'] ?: 'Unknown' ?></span></td>
      <td><?= View::e($u['telephone'] ?: '—') ?></td>
      <td><span class="badge<?= Etudiant::isActive($u['idEtudiant']) ? '' : ' off' ?>"><?=  Etudiant::isActive($u['idEtudiant'])? 'Actif' : 'Désactivé' ?></span></td>
      <td><a href="/admin/etudiants/<?= (int) $u['idEtudiant'] ?>/modifier">Modifier</a></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>
