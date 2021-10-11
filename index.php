<?php

use Slim\Factory\AppFactory;

include_once(__DIR__ . '/www/functions.php');
require __DIR__ . '/vendor/autoload.php';

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add Lazy CORS
$app->options('/{routes:.+}', function ($request, $response, $args) {
	// Called as a warmup, dont do anything special
	return $response;
});

$app->add(function ($request, $handler) {
	// For every page we serve, add the CORS management stuff
	global $api_CORS_origin;
	$response = $handler->handle($request);
	$response = $response->withHeader('Access-Control-Allow-Origin', $api_CORS_origin);
	$response = $response->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization');
	$response = $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
	$response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
	return $response;
});

include_once(__DIR__ . "/www/routes.php");
include_once(__DIR__ . "/cron/routes.php");
include_once(__DIR__ . "/api/routes.php");

$app->map([
	'GET',
	'POST',
	'PUT',
	'DELETE',
	'PATCH'
], '/{routes:.+}', function ($request, $response) {
	// Anything we didn't handle before. Tell the requestor we didn't find it.
	$uri = rtrim($request->getUri()->getPath(), "/");
	$ret = startJsonResponse();
	endJsonResponse($response, $ret, false, "API not found '" . $uri . "'");
	return $response->withStatus(404)->withHeader("Content-Type", "application/json;charset=utf-8");
});

$app->run();

// $str = "";
// $str .= "core\n";
// $str .= ob_print_r ( $core_routes );
// $str .= "images\n";
// $str .= ob_print_r ( $image_routes );
// $str .= "app\n";
// $str .= ob_print_r ( $app_routes );
// $str .= "cron\n";
// $str .= ob_print_r ( $cron_routes );
// $str .= "api\n";
// $str .= ob_print_r ( $api_routes );

// @file_put_contents ( sys_get_temp_dir () . "/minertor.core.txt", $str );
