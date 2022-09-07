<?php
$router = $di->getRouter();

// Define your routes here
$router->setDefaultController('index');
$router->setDefaultAction('index');
$router->removeExtraSlashes(true);

$router->handle($_SERVER['REQUEST_URI']);
