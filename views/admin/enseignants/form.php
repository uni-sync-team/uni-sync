<?php

use App\Core\Csrf;
use App\Core\View;

$editing = $enseignant !== null;
$action  = $editing ? '/admin/enseignants/' . (int) $data['idEnseignant'] : '/admin/enseignants';
$err     = fn(string $f) => isset($errors[$f]) ? '<p class="err" role="alert">' . View::e($errors[$f]) . '</p>' : '';
$val     = fn(string $f) => View::e($data[$f] ?? '');

?>
<h1><?= View::e($pageTitle) ?></h1>

<div class="panel wide">
  <?php if (!empty($errors['general'])): ?>
    <p class="flash error" role="alert"><?= View::e($errors['general']) ?></p>
  <?php endif; ?>

  <form action="<?= View::e($action) ?>" method="post" novalidate>
    <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">

    <?php if (!$editing): ?>
      <fieldset>
        <legend>Compte</legend>
        <div class="form-grid">
          <div class="field">
            <label for="login">Login</label>
            <input id="login" name="login" type="text" value="<?= $val('login') ?>" autocomplete="off" required autofocus>
            <?= $err('login') ?>
          </div>

          <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= $val('email') ?>" autocomplete="off" required>
            <?= $err('email') ?>
          </div>

          <div class="field full">
            <label for="password">Mot de passe</label>
            <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
            <?= $err('password') ?>
          </div>
        </div>
      </fieldset>
    <?php endif; ?>

    <fieldset>
      <legend>Profil enseignant</legend>
      <div class="form-grid">
        <div class="field">
          <label for="prenom">Prénom</label>
          <input id="prenom" name="prenom" type="text" maxlength="100" value="<?= $val('prenom') ?>"
            autocomplete="given-name" required>
          <?= $err('prenom') ?>
        </div>

        <div class="field">
          <label for="nom">Nom</label>
          <input id="nom" name="nom" type="text" maxlength="100" value="<?= $val('nom') ?>"
            autocomplete="family-name" required>
          <?= $err('nom') ?>
        </div>

        <div class="field">
          <label for="idDepartement">Département</label>
          <select id="idDepartement" name="idDepartement" required>
            <option value="">— choisir —</option>
            <?php foreach ($departements as $dep): ?>
              <option value="<?= (int) $dep['idDepartement'] ?>" <?= (int) ($data['idDepartement'] ?? 0) === (int) $dep['idDepartement'] ? ' selected' : '' ?>>
                <?= View::e($dep['nom']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?= $err('idDepartement') ?>
        </div>

        <div class="field">
          <label for="grade">Grade <span class="muted">(optionnel)</span></label>
          <select id="grade" name="grade">
            <option value="">— aucun —</option>
            <?php foreach (\App\Models\Enseignant::GRADES as $g): ?>
              <option value="<?= View::e($g) ?>" <?= ($data['grade'] ?? '') === $g ? ' selected' : '' ?>><?= View::e($g) ?></option>
            <?php endforeach; ?>
          </select>
          <?= $err('grade') ?>
        </div>

      </div>
    </fieldset>

    <?php if ($editing): ?>
      <label class="check">
        <input type="checkbox" name="actif" value="1" <?= !empty($data['actif']) ? ' checked' : '' ?>> Compte actif
      </label>
    <?php endif; ?>

    <div class="actions">
      <button class="btn" type="submit"><?= $editing ? 'Enregistrer' : 'Ajouter l\'enseignant' ?></button>
      <a class="btn light" href="/admin/enseignants">Annuler</a>
    </div>
  </form>
</div><?php
