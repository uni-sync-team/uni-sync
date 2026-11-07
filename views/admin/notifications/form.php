<?php

use App\Core\Csrf;
use App\Core\View;

$err = fn(string $f) => isset($errors[$f]) ? '<p class="err" role="alert">' . View::e($errors[$f]) . '</p>' : '';

?>
<h1>Nouvelle notification</h1>

<div class="panel">
<?php if (!empty($errors['general'])): ?>
  <p class="flash error" role="alert"><?= View::e($errors['general']) ?></p>
<?php endif; ?>
  <form action="/admin/notifications" method="post">
    <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">

    <label for="cible">Destinataire</label>
    <select id="cible" name="cible" required>
      <option value="utilisateur"<?= $data['cible'] === 'utilisateur' ? ' selected' : '' ?>>Un utilisateur</option>
      <option value="role"<?= $data['cible'] === 'role' ? ' selected' : '' ?>>Tous les comptes actifs d’un rôle</option>
      <option value="tous"<?= $data['cible'] === 'tous' ? ' selected' : '' ?>>Tous les comptes actifs</option>
    </select>
    <?= $err('cible') ?>

    <label for="login">Login (si « Un utilisateur »)</label>
    <input id="login" name="login" type="text" value="<?= View::e($data['login']) ?>" autocomplete="off">
    <?= $err('login') ?>

    <label for="role">Rôle (si « rôle »)</label>
    <select id="role" name="role">
      <option value="">— choisir —</option>
<?php foreach ($roles as $r): ?>
      <option value="<?= View::e($r['nom']) ?>"<?= $data['role'] === $r['nom'] ? ' selected' : '' ?>><?= View::e($r['nom']) ?> – <?= View::e($r['description'] ?? '') ?></option>
<?php endforeach; ?>
    </select>
    <?= $err('role') ?>

    <label for="message">Message (<?= (int) $maxLength ?> caractères maximum)</label>
    <textarea id="message" name="message" rows="5" maxlength="<?= (int) $maxLength ?>" required><?= View::e($data['message']) ?></textarea>
    <?= $err('message') ?>

    <button class="btn" type="submit">Envoyer</button>
    <a class="btn light" href="/admin/notifications">Annuler</a>
  </form>
</div>
