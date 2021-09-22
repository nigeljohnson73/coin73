<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// TODO: get some variables from the $_GET to handle account validaton and password recovery

include_once (__DIR__ . '/functions.php');
require __DIR__ . '/vendor/autoload.php';

// function logger($str) {
// Instantiate App
$app = AppFactory::create ();

// Add error middleware
$app->addErrorMiddleware ( true, true, true );

$routes ["/cron/tick"] = __DIR__ . "/_cron/tick.php";
$routes ["/cron/every_minute"] = __DIR__ . "/_cron/every_minute.php";
$routes ["/cron/every_hour"] = __DIR__ . "/_cron/every_hour.php";
$routes ["/cron/every_day"] = __DIR__ . "/_cron/every_day.php";

foreach ( array_keys ( $routes ) as $p ) {
	$app->get ( $p, function (Request $request, Response $response, $args) {
		global $routes;
		$uri = $request->getUri ()->getPath ();
		if (strlen ( $uri ) > 1) {
			$uri = rtrim ( $request->getUri ()->getPath (), "/" );
		}
		// See if any of the api keys expand into the URI I got passed as
		foreach ( $routes as $k => $v ) {
			foreach ( $args as $ak => $av ) {
				$k = str_replace ( "{" . $ak . "}", $av, $k );
			}
			if ($uri == $k) {
				$include = $v;
			}
		}
		if (strlen ( $include )) {
			include ($include);
			return $response;
		} else {
			$response->getBody ()->write ( "Could not find '" . $uri . "'" );
			return $response->withStatus ( 404 );
		}
	} );
}

$app->map ( [ 
		'GET',
		'POST',
		'PUT',
		'DELETE',
		'PATCH'
], '/{routes:.+}', function ($request, $response) {
	// Anything we didn't handle before. Tell the requestor we didn't find it.
	$uri = $request->getUri ()->getPath ();
	include (__DIR__ . "/_pages/404.php");
	// $response->getBody ()->write ( "Could not find '" . $uri . "'" );
	return $response->withStatus ( 404 );
} );

$app->run ();
?>
