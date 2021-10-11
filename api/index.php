<?php

use Slim\Factory\AppFactory;

include_once(__DIR__ . '/functions.php');
require __DIR__ . '/vendor/autoload.php';

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

include_once(__DIR__ . "/routes.php");

$app->run();
