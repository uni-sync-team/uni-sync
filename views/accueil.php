<?php

use App\Core\View;

$departements ??= [];
$stats ??= ['etudiants' => 0, 'enseignants' => 0, 'filieres' => 0, 'formations' => 0, 'modules' => 0];
$annee ??= null;
// À remplacer par les vraies coordonnées du département
$contact = ['email' => 'contact@unisync.example', 'tel' => '+212 5 00 00 00 00', 'adresse' => 'Adresse du département'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>UniSync – Notes, réclamations et résultats</title>
<meta name="description" content="Application web du département : l'enseignant saisit les notes, l'étudiant les consulte et peut déposer une réclamation.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@500;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/style.css">
</head>
<body>
<a class="skip" href="#principal">Aller au contenu</a>

<header class="top">
  <div class="topbar"><div class="wrap">
    <span><?= $annee ? 'Année universitaire ' . View::e($annee) : 'UniSync' ?></span>
    <a href="mailto:<?= View::e($contact['email']) ?>"><?= View::e($contact['email']) ?></a>
  </div></div>
  <div class="wrap">
    <nav aria-label="Navigation principale">
      <a class="logo" href="/"><i aria-hidden="true"></i>UniSync</a>
      <ul>
        <li><a href="#departements">Départements</a></li>
        <li><a href="#filieres">Filières</a></li>
        <li><a href="#services">Services</a></li>
      </ul>
      <div class="navbtns">
        <a class="btn line small" href="#contact">Contact</a>
        <a class="btn sage small" href="#connexion">Se connecter</a>
      </div>
    </nav>

    <div class="hero">
      <div>
        <h1>Bienvenue sur votre plateforme web universitaire.</h1>
        <p>Un seul espace pour l'ensemble de votre communauté universitaire.</p>
        <div class="actions">
          <a class="btn sage" href="#connexion">Se connecter</a>
          <a class="btn line" href="#departements">Nos départements</a>
        </div>
      </div>

      <div class="pubs" role="region" aria-roledescription="carrousel" aria-label="Publications du département">
        <header><h2>Publications</h2><span>Dernières annonces</span></header>
<?php $publications = $publications ?? []; $total = count($publications); ?>
        <div id="slides" aria-live="off">
<?php if ($total === 0): ?>
          <article class="slide"><h3>Aucune publication pour le moment</h3><p>Les annonces du département apparaîtront ici.</p></article>
<?php endif; ?>
<?php foreach ($publications as $n => $p): ?>
          <article class="slide" aria-label="<?= $n + 1 ?> sur <?= $total ?>"<?= $n > 0 ? ' hidden' : '' ?>><time datetime="<?= View::e($p['datePublication']) ?>"><?= View::e(View::dateFr($p['datePublication'])) ?></time><h3><?= View::e($p['titre']) ?></h3><p><?= View::e(mb_strimwidth($p['contenu'], 0, 160, '…')) ?></p></article>
<?php endforeach; ?>
        </div>
<?php if ($total > 1): ?>
        <div class="ctrl">
          <button type="button" id="pause" aria-label="Mettre le diaporama en pause">Pause</button>
          <div class="dots" id="dots"></div>
          <div>
            <button type="button" id="prev" aria-label="Publication précédente">‹</button>
            <button type="button" id="next" aria-label="Publication suivante">›</button>
          </div>
        </div>
<?php endif; ?>
      </div>
    </div>
  </div>
</header>

<main id="principal">
<section class="chiffres" aria-label="Chiffres clés">
  <div class="wrap "><dl>
    <?php foreach (['etudiants' => 'Étudiants', 'enseignants' => 'Enseignants', 'filieres' => 'Filières', 'formations' => 'Formations'] as $k => $label): ?>
    <div><dd><?= (int) $stats[$k] ?></dd><dt><?= $label ?></dt></div>
    <?php endforeach; ?>
  </dl></div>
</section>

<section id="departements">
  <div class="wrap">
    <h2 class="big">Nos départements</h2>
    <p class="lead">Chaque département regroupe des filières et des formations, de la licence au master.</p>
    <div class="cards">
<?php if (!$departements): ?>
      <p>Aucun département pour le moment.</p>
<?php endif; ?>
<?php foreach ($departements as $d): $nbFor = array_sum(array_map(fn($f) => count($f['formations']), $d['filieres'])); ?>
      <article class="card">
        <h3><?= View::e($d['nom']) ?></h3>
        <p><?= View::e($d['description']) ?></p>
        <p class="meta"><?= count($d['filieres']) ?> filière(s) · <?= $nbFor ?> formation(s)</p>
        <ul class="tags"><?php foreach ($d['filieres'] as $f): ?><li><?= View::e($f['nom']) ?></li><?php endforeach; ?></ul>
      </article>
<?php endforeach; ?>
    </div>
  </div>
</section>

<section id="filieres">
  <div class="wrap">
    <h2 class="big">Filières et formations</h2>
    <p class="lead">Retrouvez les filières de chaque département et les formations qu’elles proposent.</p>
<?php foreach ($departements as $d): if (!$d['filieres']) continue; ?>
    <h3 class="dep"><?= View::e($d['nom']) ?></h3>
    <div class="cards">
<?php foreach ($d['filieres'] as $f): ?>
      <article class="card">
        <h4><?= View::e($f['nom']) ?></h4>
        <p><?= View::e($f['description']) ?></p>
        <ul class="formations">
<?php foreach ($f['formations'] as $fo): ?>
          <li><?= View::e($fo['intitule']) ?><span><?= View::e($fo['niveau']) ?><?= $fo['duree'] ? ' · ' . (int) $fo['duree'] . ' an(s)' : '' ?></span></li>
<?php endforeach; ?>
        </ul>
      </article>
<?php endforeach; ?>
    </div>
<?php endforeach; ?>
  </div>
</section>

<section class="band" id="services">
  <div class="wrap cols">
    <div>
      <h2 class="big">Des services près de vous</h2>
      <p class="lead">Pour toutes vos démarches et votre vie d’étudiant, tout au long de l’année.</p>
    </div>
    <ul class="regles">
      <li><strong>Secrétariat pédagogique</strong><span>Inscriptions, attestations et certificats de scolarité : adressez-vous au secrétariat du département.</span></li>
      <li><strong>Bibliothèque universitaire</strong><span>Ouvrages, salles de travail et ressources en ligne pour préparer vos cours et vos examens.</span></li>
      <li><strong>Vie étudiante</strong><span>Clubs, associations et événements pour animer le campus.</span></li>
      <li><strong>Assistance informatique</strong><span>Un souci de connexion ou de mot de passe ? Le service informatique vous répond.</span></li>
    </ul>
  </div>
</section>


<section class="cta" id="connexion">
  <div class="wrap">
    <div>
      <h2>Accédez à votre espace.</h2>
      <p>Connectez-vous avec votre login et votre mot de passe. Votre espace s’ouvre selon votre rôle.</p>
    </div>
    <form action="/login" method="post">
      <label for="login">Login</label>
      <input id="login" name="login" type="text" autocomplete="username" required>
      <label for="mdp">Mot de passe</label>
      <input id="mdp" name="motDePasse" type="password" autocomplete="current-password" required>
      <button class="btn" type="submit">Se connecter</button>
      <p class="aide">Compte oublié ? Contactez l’administrateur du département.</p>
    </form>
  </div>
</section>
</main>

<footer id="contact">
  <div class="wrap foot">
    <div><strong>UniSync</strong><p>Étudiants, enseignants, administration</p></div>
    <div><strong>Navigation</strong><p><a href="#departements">Départements</a><br><a href="#filieres">Filières</a><br><a href="#connexion">Se connecter</a></p></div>
    <div><strong>Contact</strong><p><?= View::e($contact['adresse']) ?><br><?= View::e($contact['tel']) ?><br><a href="mailto:<?= View::e($contact['email']) ?>"><?= View::e($contact['email']) ?></a></p></div>
  </div>
</footer>
<script>
(function(){
  var slides=[].slice.call(document.querySelectorAll('.slide'));
  if(slides.length<2)return;
  var box=document.getElementById('slides'),dots=document.getElementById('dots'),pause=document.getElementById('pause');
  var i=0,timer=null,paused=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  slides.forEach(function(_,n){
    var b=document.createElement('button');b.type='button';b.textContent=n+1;
    b.setAttribute('aria-label','Aller à la publication '+(n+1));
    b.onclick=function(){show(n)};dots.appendChild(b);
  });
  function show(n){
    i=(n+slides.length)%slides.length;
    slides.forEach(function(s,k){s.hidden=k!==i});
    [].forEach.call(dots.children,function(d,k){d.setAttribute('aria-current',k===i)});
  }
  function run(){stop();if(!paused)timer=setInterval(function(){show(i+1)},6000)}
  function stop(){clearInterval(timer)}
  function state(){
    pause.textContent=paused?'Lecture':'Pause';
    pause.setAttribute('aria-label',paused?'Relancer le diaporama':'Mettre le diaporama en pause');
    box.setAttribute('aria-live',paused?'polite':'off');
  }
  pause.onclick=function(){paused=!paused;state();run()};
  document.getElementById('prev').onclick=function(){show(i-1)};
  document.getElementById('next').onclick=function(){show(i+1)};
  var r=document.querySelector('.pubs');
  r.addEventListener('mouseenter',stop);r.addEventListener('mouseleave',run);
  r.addEventListener('focusin',stop);r.addEventListener('focusout',run);
  show(0);state();run();
})();
</script>
</body>
</html>
