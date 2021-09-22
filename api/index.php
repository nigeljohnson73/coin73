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
	// Called as a warmup, dont do anything special
	return $response;
} );

$app->add ( function ($request, $handler) {
	// For every page we serve, add the CORS management stuff
	global $api_CORS_origin;
	$response = $handler->handle ( $request );
	$response = $response->withHeader ( 'Access-Control-Allow-Origin', $api_CORS_origin );
	$response = $response->withHeader ( 'Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization' );
	$response = $response->withHeader ( 'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS' );
	$response = $response->withHeader ( 'Access-Control-Allow-Credentials', 'true' );
	return $response;
} );

// // Add routes
// $app->get ( '/api/', function (Request $request, Response $response) {
// $name = $request->getQueryParams () ['name'] ?? 'World';
// $response->getBody ()->write ( "[API] Hello, $name!, <a href='/api/hello/$name'>Try /api/hello/$name</a>" );
// return $response;
// } );

// $app->get ( '/api/hello/{name}', function (Request $request, Response $response, $args) {
// $name = $args ['name'];
// $response->getBody ()->write ( "Hello, $name" );
// return $response;
// } );

$apis = array ();
$apis ["/ping"] = __DIR__ . "/_api/ping.php";
$apis ["/job/request/json"] = __DIR__ . "/_api/job/request_json.php";
$apis ["/job/request/text"] = __DIR__ . "/_api/job/request_text.php";
$apis ["/job/submit/json/{job_id}/{nonce}"] = __DIR__ . "/_api/job/submit_json.php";
$apis ["/job/submit/text/{job_id}/{nonce}"] = __DIR__ . "/_api/job/submit_text.php";
$apis ["/coin/summary"] = __DIR__ . "/_api/coin/summary.php";
$apis ["/coin/balance"] = __DIR__ . "/_api/coin/balance.php";

foreach ( array_keys ( $apis ) as $p ) {
	$roots = array (
			"", // for http:// api.domain.com/
			"/api" // for http[s]://domain.com/api/
	);
	foreach ( $roots as $r ) {
		$uri = $r . $p;
		$app->post ( $uri, function (Request $request, Response $response, $args) {
			global $apis;
			// Get the uri we were called as, and strip off the root
			$uri = rtrim ( $request->getUri ()->getPath (), "/" );
			$uri = preg_replace ( '/^\/api/', "", $uri );
			// See if any of the api keys expand into the URI I got passed as
			foreach ( $apis as $u => $p ) {
				foreach ( $args as $ak => $av ) {
					$u = str_replace ( "{" . $ak . "}", $av, $u );
				}
				if ($uri == $u) {
					$include = $p;
				}
			}
			if (strlen ( $include )) {
				include ($include);
				return $response->withHeader ( "Content-Type", "application/json;charset=utf-8" );
			}

			// The file needed is missing... be nice in the error message
			$ret = startJsonResponse ();
			endJsonResponse ( $response, $ret, false, "API not supported '" . $uri . "'" );
			return $response->withStatus ( 404 )->withHeader ( "Content-Type", "application/json;charset=utf-8" );
		} );
	}
}
$app->map ( [ 
		'GET',
		'POST',
		'PUT',
		'DELETE',
		'PATCH'
], '/{routes:.+}', function ($request, $response) {
	// Anything we didn't handle before. Tell the requestor we didn't find it.
	$uri = rtrim ( $request->getUri ()->getPath (), "/" );
	$ret = startJsonResponse ();
	endJsonResponse ( $response, $ret, false, "API not found '" . $uri . "'" );
	return $response->withStatus ( 404 )->withHeader ( "Content-Type", "application/json;charset=utf-8" );
} );

$app->run ();
?>