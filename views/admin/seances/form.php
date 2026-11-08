<?php

use App\Core\Csrf;
use App\Core\View;
use App\Models\Seance;

$editing = $seance !== null;
$action  = $editing ? '/admin/seances/' . (int) $data['idSeance'] : '/admin/seances';
$err     = fn(string $f) => isset($errors[$f]) ? '<p class="err" role="alert">' . View::e($errors[$f]) . '</p>' : '';
$val     = fn(string $f) => View::e((string) ($data[$f] ?? ''));
$sel     = fn(string $f, $v) => (string) ($data[$f] ?? '') === (string) $v ? ' selected' : '';

?>
<h1><?= View::e($pageTitle) ?></h1>

<div class="panel wide">
<?php if (!empty($errors['general'])): ?>
  <p class="flash error" role="alert"><?= View::e($errors['general']) ?></p>
<?php endif; ?>

  <form action="<?= View::e($action) ?>" method="post" novalidate>
    <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">

    <fieldset>
      <legend>Séance</legend>
      <div class="form-grid">
        <div class="field full">
          <label for="idModule">Module</label>
          <select id="idModule" name="idModule" required autofocus>
            <option value="">— choisir —</option>
<?php foreach ($modules as $m): ?>
            <option value="<?= (int) $m['idModule'] ?>"<?= $sel('idModule', $m['idModule']) ?>><?= View::e($m['code'] . ' – ' . $m['intitule'] . ' (' . $m['formation'] . ', ' . $m['semestre'] . ')') ?></option>
<?php endforeach; ?>
          </select>
          <?= $err('idModule') ?>
        </div>

        <div class="field">
          <label for="idEnseignant">Enseignant</label>
          <select id="idEnseignant" name="idEnseignant" required>
            <option value="">— choisir —</option>
<?php foreach ($enseignants as $e): ?>
            <option value="<?= (int) $e['idEnseignant'] ?>"<?= $sel('idEnseignant', $e['idEnseignant']) ?>><?= View::e($e['nom'] . ' ' . $e['prenom']) ?></option>
<?php endforeach; ?>
          </select>
          <?= $err('idEnseignant') ?>
        </div>

        <div class="field">
          <label for="idSalle">Salle</label>
          <select id="idSalle" name="idSalle" required>
            <option value="">— choisir —</option>
<?php foreach ($salles as $s): ?>
            <option value="<?= (int) $s['idSalle'] ?>"<?= $sel('idSalle', $s['idSalle']) ?>><?= View::e($s['nom'] . ' (' . (int) $s['capacite'] . ' places)') ?></option>
<?php endforeach; ?>
          </select>
          <?= $err('idSalle') ?>
        </div>

        <div class="field">
          <label for="dateDebut">Début</label>
          <input id="dateDebut" name="dateDebut" type="datetime-local" value="<?= $val('dateDebut') ?>" required>
          <?= $err('dateDebut') ?>
        </div>

        <div class="field">
          <label for="dateFin">Fin</label>
          <input id="dateFin" name="dateFin" type="datetime-local" value="<?= $val('dateFin') ?>" required>
          <?= $err('dateFin') ?>
        </div>

        <div class="field full">
          <label for="typeSeance">Type <span class="muted">(optionnel)</span></label>
          <select id="typeSeance" name="typeSeance">
            <option value="">— aucun —</option>
<?php foreach (Seance::TYPES as $t): ?>
            <option value="<?= View::e($t) ?>"<?= ($data['typeSeance'] ?? '') === $t ? ' selected' : '' ?>><?= View::e($t) ?></option>
<?php endforeach; ?>
          </select>
          <?= $err('typeSeance') ?>
        </div>
      </div>
    </fieldset>

    <div class="actions">
      <button class="btn" type="submit"><?= $editing ? 'Enregistrer' : 'Ajouter la séance' ?></button>
      <a class="btn light" href="/admin/seances">Annuler</a>
    </div>
  </form>
</div>
