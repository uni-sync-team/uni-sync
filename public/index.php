<?php
// session_start();
//
// require '../src/Router';
// require '../src/Controllers/Authorization';
// require '../src/Controllers/Student';
// require '../src/Controllers/Professor';

// NOTE: What I want to do
// create a router class
// create that object and use its method here with a givven handler
// like this
// Router router = new Router();
// router->get("/user", UserController->getAllUsers());

// $router = new Router();
//
// $authController = new $Authorization(); // authorizatoin class has not be created 
// $studnetController = new $Student(); // authorizatoin class has not be created 
// $profController = new $Professor(); // authorizatoin class has not be created 
//
// $router->get('/',  fn() => 'this is the home');
// $router->get('/login', $authController->showLogin());
// $router->post('/login', $authController->login());
//
// // admin
// $route->get('/admin/dashboard', $adminController->dashboard(), [fn() => requireRole('admin')]);
// $route->get('/student/dashboard', $studentController->dashboard(), [fn() => requireRole('student')]);
// $route->get('/prof/dashboard', $profController->dashboard(), [fn() => requireRole('prof')]);
//
// $router->run($_SERVER["REQUEST_METHOD"], $_SERVER['REQUEST_URI']);
