<?php

use App\Core\Csrf;
use App\Core\View;

$editing = $user !== null;
$action  = $editing ? '/admin/etudiants/' . (int) $data['idEtudiant'] : '/admin/etudiants';
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

    <?php if (!$editing):  ?>
    <fieldset>
      <legend>Compte</legend>
      <div class="form-grid">
        <div class="field">
          <label for="login">Login</label>
          <input id="login" name="login" type="text" value="<?= $val('login') ?>" autocomplete="off"
                 <?= $editing ? 'readonly' : 'required autofocus' ?>>
          <?= $err('login') ?>
        </div>

        <div class="field">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" value="<?= $val('email') ?>" autocomplete="off" required>
          <?= $err('email') ?>
        </div>

        <div class="field full">
          <label for="password">Mot de passe<?= $editing ? ' <span class="muted">(laisser vide pour ne pas changer)</span>' : '' ?></label>
          <input id="password" name="password" type="password" minlength="8"
                 autocomplete="new-password" <?= $editing ? '' : 'required' ?>>
          <?= $err('password') ?>
        </div>
      </div>
    </fieldset>
    <?php endif; ?>

    <fieldset>
      <legend>Profil étudiant</legend>
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
          <label for="cne">CNE</label>
          <input id="cne" name="cne" type="text" maxlength="20" value="<?= $val('cne') ?>"
                 style="text-transform:uppercase" autocomplete="off" required>
          <?= $err('cne') ?>
        </div>

        <div class="field">
          <label for="dateNaissance">Date de naissance</label>
          <input id="dateNaissance" name="dateNaissance" type="date" max="<?= date('Y-m-d') ?>"
                 value="<?= $val('dateNaissance') ?>" autocomplete="bday">
          <?= $err('dateNaissance') ?>
        </div>

        <div class="field full">
          <label for="telephone">Téléphone</label>
          <input id="telephone" name="telephone" type="tel" maxlength="20" inputmode="tel"
                 placeholder="0612345678 ou +212612345678" value="<?= $val('telephone') ?>" autocomplete="tel">
          <?= $err('telephone') ?>
        </div>
      </div>
    </fieldset>

<?php if ($editing): ?>
    <label class="check">
      <input type="checkbox" name="actif" value="1"<?= !empty($data['actif']) ? ' checked' : '' ?>> Compte actif
    </label>
<?php endif; ?>

    <div class="actions">
      <button class="btn" type="submit"><?= $editing ? 'Enregistrer' : 'Ajouter l\'étudiant' ?></button>
      <a class="btn light" href="/admin/etudiants">Annuler</a>
    </div>
  </form>
</div>
