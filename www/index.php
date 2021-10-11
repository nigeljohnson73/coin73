<?php
use Slim\Factory\AppFactory;

include_once (__DIR__ . '/functions.php');
require __DIR__ . '/vendor/autoload.php';

// Instantiate App
$app = AppFactory::create ();

// Add error middleware
$app->addErrorMiddleware ( true, true, true );

include_once (__DIR__ . "/routes.php");

$app->map ( [ 
		'GET',
		'POST',
		'PUT',
		'DELETE',
		'PATCH'
], '/{routes:.+}', function ($request, $response) {
	// Anything we didn't handle before. Tell the requestor we didn't find it.
	include (__DIR__ . "/_pages/404.php");
	return $response->withStatus ( 404 );
} );

$app->run ();

// $str = "";
// $str .= "core\n";
// $str .= ob_print_r ( $core_routes );
// $str .= "images\n";
// $str .= ob_print_r ( $image_routes );
// $str .= "app\n";
// $str .= ob_print_r ( $app_routes );

// @file_put_contents ( sys_get_temp_dir () . "/minertor.www.txt", $str );
?>
