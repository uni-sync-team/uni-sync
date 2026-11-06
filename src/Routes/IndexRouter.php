<?php

namespace App\Routes;

use App\Core\Router;
use App\Core\Database;
use App\Core\View;
use App\Models\Publication;
use App\Models\Catalogue;

$indexRouter = Router::subRouter();

$indexRouter->get('/health', function() {
  echo "App is Up";
});

$indexRouter->get('/database', function() {
  Database::isUp();
});

$indexRouter->get('/', function() {
  View::render('accueil', [
    'publications' => Publication::findActive(4),
    'departements' => Catalogue::departements(),
    'stats'        => Catalogue::stats(),
    'annee'        => Catalogue::anneeCourante(),
  ]);
});

return $indexRouter;
