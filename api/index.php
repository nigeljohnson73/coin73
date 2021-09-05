<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

include_once (__DIR__ . '/functions.php');
require __DIR__ . '/vendor/autoload.php';

// Instantiate App
$app = AppFactory::create ();

// Add error middleware
$app->addErrorMiddleware ( true, true, true );

// Add Lazy CORS
$app->options ( '/{routes:.+}', function ($request, $response, $args) {
	return $response;
} );

$app->add ( function ($request, $handler) {
	global $api_CORS_origin;
	$response = $handler->handle ( $request );
	$response = $response->withHeader ( 'Access-Control-Allow-Origin', $api_CORS_origin);
	//$response = $response->withHeader ( 'Access-Control-Allow-Origin', '*' ); // TODO: Secure this!!!
	$response = $response->withHeader ( 'Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization' );
	$response = $response->withHeader ( 'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS' );
	$response = $response->withHeader ( 'Access-Control-Allow-Credentials', 'true' );
	return $response;
} );

// Add routes
$app->get ( '/api/', function (Request $request, Response $response) {
	$name = $request->getQueryParams () ['name'] ?? 'World';
	$response->getBody ()->write ( "[API] Hello, $name!, <a href='/api/hello/$name'>Try /api/hello/$name</a>" );
	return $response;
} );

$app->get ( '/api/hello/{name}', function (Request $request, Response $response, $args) {
	$name = $args ['name'];
	$response->getBody ()->write ( "Hello, $name" );
	return $response;
} );

$app->post ( '/api/ping', function (Request $request, Response $response) {
	$obj = new StdClass ();
	$obj->success = true;
	$obj->status = "OK";
	$obj->console = "";
	$obj->message = "Called Ping API";
	$response->getBody ()->write ( json_encode ( $obj ) );
	return $response; // ->withHeader ( 'Access-Control-Allow-Origin', '*' );
} );

$app->map ( [ 
		'GET',
		'POST',
		'PUT',
		'DELETE',
		'PATCH'
], '/{routes:.+}', function ($request, $response) {
	throw new HttpNotFoundException ( $request );
} );
$app->run ();
?>