<?php use App\Core\View; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= View::e($title ?? 'UniSync') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@500;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/style.css">
<?php foreach ($styles ?? [] as $css): ?>
  <link rel="stylesheet" href="<?= View::e($css) ?>">
<?php endforeach; ?>
<?php foreach ($scripts ?? [] as $js): ?>
  <script src="<?= View::e($js) ?>" defer></script>
<?php endforeach; ?>
</head>
<body>
