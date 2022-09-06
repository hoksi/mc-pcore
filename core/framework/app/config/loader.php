<?php

$loader = new \Phalcon\Loader();

$loader->registerNamespaces([
    'controllers' => $config->application->controllersDir,
]);

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs(
    [
        $config->application->controllersDir,
        $config->application->modelsDir
    ]
)->register();
