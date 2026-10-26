<?php
spl_autoload_register(function (string $class): void {
  $base = __DIR__ . '/';
  $base = str_replace("public", "src", $base);

  foreach (['Core', 'Controllers', 'Models', 'Services', 'Middleware'] as $dir) {
    $file = $base . $dir . '/' . $class . '.php';
    if (is_file($file)) {
        require $file;
        return;
    }
  }
});