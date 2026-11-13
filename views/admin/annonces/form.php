<?php

use App\Core\Csrf;
use App\Core\View;

$editing = $annonce !== null;
$action  = $editing ? '/admin/annonces/' . (int) $annonce['idPublication'] : '/admin/annonces';
$err     = fn(string $f) => isset($errors[$f]) ? '<p class="err" role="alert">' . View::e($errors[$f]) . '</p>' : '';
$val     = fn(string $f) => View::e((string) ($data[$f] ?? ''));

?>
<h1><?= View::e($pageTitle) ?></h1>

<div class="panel wide">
<?php if (!empty($errors['general'])): ?>
  <p class="flash error" role="alert"><?= View::e($errors['general']) ?></p>
<?php endif; ?>

  <form action="<?= View::e($action) ?>" method="post" novalidate>
    <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">

    <fieldset>
      <legend>Annonce</legend>
      <div class="form-grid">
        <div class="field full">
          <label for="titre">Titre</label>
          <input id="titre" name="titre" type="text" maxlength="150" value="<?= $val('titre') ?>" autocomplete="off" required autofocus>
          <?= $err('titre') ?>
        </div>

        <div class="field full">
          <label for="contenu">Contenu <span class="muted">(<?= (int) $maxContent ?> caractères maximum)</span></label>
          <textarea id="contenu" name="contenu" rows="7" maxlength="<?= (int) $maxContent ?>" required><?= $val('contenu') ?></textarea>
          <?= $err('contenu') ?>
        </div>

        <div class="field">
          <label for="datePublication">Date de publication</label>
          <input id="datePublication" name="datePublication" type="date" value="<?= $val('datePublication') ?>" required>
          <?= $err('datePublication') ?>
        </div>

        <div class="field">
          <label for="dateExpiration">Date d’expiration <span class="muted">(optionnelle)</span></label>
          <input id="dateExpiration" name="dateExpiration" type="date" value="<?= $val('dateExpiration') ?>">
          <?= $err('dateExpiration') ?>
        </div>

        <div class="field full">
          <label class="check"><input type="checkbox" name="actif" value="1"<?= !empty($data['actif']) ? ' checked' : '' ?>> Annonce active (visible sur la page d’accueil à partir de sa date de publication)</label>
        </div>
      </div>
    </fieldset>

    <div class="actions">
      <button class="btn" type="submit"><?= $editing ? 'Enregistrer' : 'Publier l’annonce' ?></button>
      <a class="btn light" href="/admin/annonces">Annuler</a>
    </div>
  </form>
</div>
