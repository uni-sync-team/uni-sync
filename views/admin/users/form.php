<?php

use App\Core\Csrf;
use App\Core\View;

$editing = $user !== null;
$action  = $editing ? '/admin/utilisateurs/' . (int) $user['idUser'] : '/admin/utilisateurs';
$err     = fn(string $f) => isset($errors[$f]) ? '<p class="err" role="alert">' . View::e($errors[$f]) . '</p>' : '';

?>
<h1><?= View::e($pageTitle) ?></h1>

<div class="panel">
<?php if (!empty($errors['general'])): ?>
  <p class="flash error" role="alert"><?= View::e($errors['general']) ?></p>
<?php endif; ?>
  <form action="<?= View::e($action) ?>" method="post">
    <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">

    <label for="login">Login</label>
    <input id="login" name="login" type="text" value="<?= View::e($editing ? $user['login'] : $data['login']) ?>" <?= $editing ? 'readonly' : 'required autofocus' ?>>
    <?= $err('login') ?>

    <label for="email">Email</label>
    <input id="email" name="email" type="email" value="<?= View::e($data['email']) ?>" required>
    <?= $err('email') ?>

    <label for="password">Mot de passe<?= $editing ? ' (laisser vide pour ne pas changer)' : '' ?></label>
    <input id="password" name="password" type="password" autocomplete="new-password" <?= $editing ? '' : 'required' ?>>
    <?= $err('password') ?>

    <label for="role">Rôle</label>
    <select id="role" name="role" required<?= $isSelf ? ' disabled' : '' ?>>
      <option value="">— choisir —</option>
<?php foreach ($roles as $r): ?>
      <option value="<?= View::e($r['nom']) ?>"<?= $data['role'] === $r['nom'] ? ' selected' : '' ?>><?= View::e($r['nom']) ?> – <?= View::e($r['description'] ?? '') ?></option>
<?php endforeach; ?>
    </select>
    <?php if ($isSelf): ?><input type="hidden" name="role" value="<?= View::e($data['role']) ?>"><?php endif; ?>
    <?= $err('role') ?>

<?php if ($editing): ?>
    <label class="check"><input type="checkbox" name="actif" value="1"<?= $data['actif'] ? ' checked' : '' ?><?= $isSelf ? ' disabled' : '' ?>> Compte actif</label>
<?php if ($isSelf): ?>
    <p class="hint">Vous ne pouvez pas désactiver votre propre compte ni changer votre rôle.</p>
<?php endif; ?>
<?php else: ?>
    <p class="hint">Le profil étudiant ou enseignant se crée dans la section correspondante.</p>
<?php endif; ?>

    <button class="btn" type="submit"><?= $editing ? 'Enregistrer' : 'Créer le compte' ?></button>
    <a class="btn light" href="/admin/utilisateurs">Annuler</a>
  </form>
</div>
