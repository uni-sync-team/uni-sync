<?php use App\Core\View; ?>
<h1>Séances</h1>

<form class="filter-row" action="/admin/seances" method="get">
  <div>
    <label for="module">Module</label>
    <select id="module" name="module">
      <option value="">Tous</option>
<?php foreach ($modules as $m): ?>
      <option value="<?= (int) $m['idModule'] ?>"<?= $filters['idModule'] === (int) $m['idModule'] ? ' selected' : '' ?>><?= View::e($m['code'] . ' – ' . $m['intitule']) ?></option>
<?php endforeach; ?>
    </select>
  </div>
  <div>
    <label for="enseignant">Enseignant</label>
    <select id="enseignant" name="enseignant">
      <option value="">Tous</option>
<?php foreach ($enseignants as $e): ?>
      <option value="<?= (int) $e['idEnseignant'] ?>"<?= $filters['idEnseignant'] === (int) $e['idEnseignant'] ? ' selected' : '' ?>><?= View::e($e['nom'] . ' ' . $e['prenom']) ?></option>
<?php endforeach; ?>
    </select>
  </div>
  <div>
    <label for="salle">Salle</label>
    <select id="salle" name="salle">
      <option value="">Toutes</option>
<?php foreach ($salles as $s): ?>
      <option value="<?= (int) $s['idSalle'] ?>"<?= $filters['idSalle'] === (int) $s['idSalle'] ? ' selected' : '' ?>><?= View::e($s['nom']) ?></option>
<?php endforeach; ?>
    </select>
  </div>
  <div><label for="du">Du</label><input id="du" name="du" type="date" value="<?= View::e($filters['du']) ?>"></div>
  <div><label for="au">Au</label><input id="au" name="au" type="date" value="<?= View::e($filters['au']) ?>"></div>
  <button class="btn" type="submit">Filtrer</button>
  <a class="btn light" href="/admin/seances">Réinitialiser</a>
</form>

<div class="bar">
  <span class="hint"><?= (int) $total ?> séance(s).</span>
  <a class="btn" href="/admin/seances/nouveau">Nouvelle séance</a>
</div>

<?php if (!$seances): ?>
<p>Aucune séance ne correspond à ces critères.</p>
<?php else: ?>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Date</th><th>Horaire</th><th>Module</th><th>Type</th><th>Enseignant</th><th>Salle</th><th></th></tr></thead>
  <tbody>
<?php foreach ($seances as $s): ?>
    <tr>
      <td><?= View::e(date('d/m/Y', strtotime($s['dateDebut']))) ?></td>
      <td><?= View::e(date('H:i', strtotime($s['dateDebut'])) . ' – ' . date('H:i', strtotime($s['dateFin']))) ?></td>
      <td><?= View::e($s['moduleCode'] . ' – ' . $s['module']) ?></td>
      <td><?= View::e($s['typeSeance'] ?: '—') ?></td>
      <td><?= View::e($s['enseignantNom'] . ' ' . $s['enseignantPrenom']) ?></td>
      <td><?= View::e($s['salle']) ?></td>
      <td>
        <a href="/admin/seances/<?= (int) $s['idSeance'] ?>/modifier">Modifier</a> ·
        <form class="inline-form" action="/admin/seances/<?= (int) $s['idSeance'] ?>/supprimer" method="post" onsubmit="return confirm('Supprimer cette séance ?');">
          <input type="hidden" name="csrf" value="<?= View::e(\App\Core\Csrf::token()) ?>">
          <button class="linklike" type="submit">Supprimer</button>
        </form>
      </td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>

<?php require __DIR__ . '/../parts/pagination.php'; ?>
