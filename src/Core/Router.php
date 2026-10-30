<?php

namespace App\Core;

class Router {
  private array $routes = [];

  public function get(string $path, callable $handler, array $middleware = []): void
  {
    $this->routes['GET'][$path] = [$handler, $middleware];
  }

  public function post(string $path, callable $handler, array $middleware = []): void
  {
    $this->routes['POST'][$path] = [$handler, $middleware];
  }

  public function put(string $path, callable $handler, array $middleware = []): void
  {
    $this->routes['PUT'][$path] = [$handler, $middleware];
  }

  public function delete(string $path, callable $handler, array $middleware = []): void
  {
    $this->routes['DELETE'][$path] = [$handler, $middleware];
  }

  public function run(): void {
    // parse the wanted url
    $method = $_SERVER["REQUEST_METHOD"];
    $uri    = $_SERVER['REQUEST_URI'];
    $path = parse_url($uri, PHP_URL_PATH);
    $path = rtrim($path, '/') ?: '/';

    
    foreach ($this->routes[$method] ?? [] as $route => [$handler, $middleware]) {
      $pattern = "#^" . preg_replace('/\{[\w]+\}/', '([^/]+)', $route) . "$#";

      if (preg_match($pattern, $path, $matches)) {
        array_shift($matches);

        foreach ($middleware as $check) {
          $check();
        }
        
        $handler(...$matches);
        return;
      }
    }

    http_response_code(404);
    // TODO: good error handling
    echo '404 - Page not Found';
  }
}
