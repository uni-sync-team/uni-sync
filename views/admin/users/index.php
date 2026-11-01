<?php use App\Core\View; ?>
<h1>Utilisateurs</h1>

<div class="bar">
  <form action="/admin/utilisateurs" method="get" role="search">
    <label class="sr" for="q">Rechercher</label>
    <input id="q" name="q" type="search" value="<?= View::e($q) ?>" placeholder="Login ou email">
    <button class="btn light" type="submit">Rechercher</button>
  </form>
  <a class="btn" href="/admin/utilisateurs/nouveau">Nouvel utilisateur</a>
</div>

<?php if (!$users): ?>
<p>Aucun utilisateur trouvé.</p>
<?php else: ?>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Login</th><th>Email</th><th>Rôle</th><th>Statut</th><th></th></tr></thead>
  <tbody>
<?php foreach ($users as $u): ?>
    <tr>
      <td><?= View::e($u['login']) ?><?= (int) $u['idUser'] === $me ? ' <small>(vous)</small>' : '' ?></td>
      <td><?= View::e($u['email']) ?></td>
      <td><?= View::e($u['roles'] ?? '—') ?></td>
      <td><span class="badge<?= $u['actif'] ? '' : ' off' ?>"><?= $u['actif'] ? 'Actif' : 'Désactivé' ?></span></td>
      <td><a href="/admin/utilisateurs/<?= (int) $u['idUser'] ?>/modifier">Modifier</a></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>
