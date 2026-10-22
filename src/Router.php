<?php

class Router {
  private array $routes = [];

  // TODO: add regex for uri parsing
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

  public function run(string $method, string $uri): void {
    // parse the wanted url
    $path = parse_url($uri, PHP_URL_PATH);
    $path = rtrim($path, '/') ?: '/';

    if (!isset($this->routes[$method][$path])) {
      http_response_code(404);
      echo '404 - Page not Found';
      return;
    }

    [$handler, $middleware] = $this->routes[$method][$path];

    foreach ($middleware as $check) {
      $check(); // middleware is array of middlewares that should be run before any code
      // like checking the requiredRole
    }
    $handler();
  }
}
