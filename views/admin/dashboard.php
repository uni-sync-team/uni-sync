<?php use App\Core\View; ?>
<h1>Tableau de bord</h1>

<div class="stats">
<?php foreach ([
  'utilisateurs' => ['Utilisateurs', '/admin/utilisateurs'],
  'etudiants'    => ['Étudiants', null],
  'enseignants'  => ['Enseignants', null],
  'demandes'     => ['Demandes en attente', null],
  'reclamations' => ['Réclamations en attente', null],
  'annonces'     => ['Annonces actives', null],
] as $key => [$label, $href]): ?>
  <div class="stat">
    <strong><?= (int) $counters[$key] ?></strong>
    <span><?= $href ? '<a href="' . View::e($href) . '">' . View::e($label) . '</a>' : View::e($label) ?></span>
  </div>
<?php endforeach; ?>
</div>

<h2>Dernières actions</h2>
<?php if (!$actions): ?>
<p>Aucune action enregistrée pour le moment.</p>
<?php else: ?>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Date</th><th>Utilisateur</th><th>Action</th></tr></thead>
  <tbody>
<?php foreach ($actions as $a): ?>
    <tr><td><?= View::e($a['dateAction']) ?></td><td><?= View::e($a['login']) ?></td><td><?= View::e($a['action']) ?></td></tr>
<?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>
