<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// TODO: get some variables from the $_GET to handle account validaton and password recovery

include_once (__DIR__ . '/functions.php');
require __DIR__ . '/vendor/autoload.php';

// function logger($str) {
// $logfile = "./tmp.log";
// file_put_contents ( $logfile, trim ( $str ) . "\n", FILE_APPEND );
// }

// Instantiate App
$app = AppFactory::create ();

// Add error middleware
$app->addErrorMiddleware ( true, true, true );

// Add routes
$routes = array ();
$routes ["/"] = __DIR__ . "/_pages/home.php";
$routes ["/js/app.min.js"] = __DIR__ . "/_js/app.min.js.php";
$routes ["/css/app.min.css"] = __DIR__ . "/_css/app.min.css.php";
$routes ["/templates/cookieAlert.html"] = __DIR__ . "/_pages/tpl_cookieAlert.php";
$routes ["/privacy"] = __DIR__ . "/_pages/privacy.php";
$routes ["/terms"] = __DIR__ . "/_pages/terms.php";
$routes ["/about"] = __DIR__ . "/_pages/about.php";
$routes ["/supportus"] = __DIR__ . "/_pages/supportus.php";
$routes ["/signup"] = __DIR__ . "/_pages/signup.php";
$routes ["/validate"] = __DIR__ . "/_pages/validate.php";
$routes ["/validate/{payload}"] = __DIR__ . "/_pages/validate.php";
$routes ["/recover"] = __DIR__ . "/_pages/recover.php";
$routes ["/recover/{payload}"] = __DIR__ . "/_pages/recover.php";
$routes ["/wiki"] = __DIR__ . "/_pages/wiki.php";
$routes ["/wiki/{page}"] = __DIR__ . "/_pages/wiki.php";
$routes ["/wiki/{page}/{sub_page}"] = __DIR__ . "/_pages/wiki.php";
$routes ["/wiki/{page}/{sub_page}/{sub_sub_page}"] = __DIR__ . "/_pages/wiki.php"; // Surely this is enough
$routes ["/wiki/{page}/{sub_page}/{sub_sub_page}/{sub_sub_sub_page}"] = __DIR__ . "/_pages/wiki.php"; // No, really, this *is* enough

$routes ["/cron/tick"] = __DIR__ . "/_cron/tick.php";
$routes ["/cron/tidy"] = __DIR__ . "/_cron/tidy.php";

$routes ["/benchmark"] = __DIR__ . "/benchmark.php";
//$routes ["/svg.php"] = __DIR__ . "/svg.php"; // Testing purposes for now

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

$images = array ();
$images ["/gfx/favicon.png"] = array (
		"/_gfx/favicon.png",
		"image/png"
);
$images ["/gfx/ajax-loader-bar.gif"] = array (
		"/_gfx/ajax-loader-bar.gif",
		"image/gif"
);
$images ["/gfx/ajax-loader-spinner.gif"] = array (
		"/_gfx/ajax-loader-spinner.gif",
		"image/gif"
);
$images ["/gfx/logo-400.png"] = array (
		"/_gfx/logo-400.png",
		"image/png"
);
$images ["/gfx/logo-200.png"] = array (
		"/_gfx/logo-200.png",
		"image/png"
);
$images ["/gfx/submission_time.png"] = array (
		"/_gfx/submission_time.php",
		"image/png",
		true // Include,
);
$images ["/gfx/miner_efficiency.png"] = array (
		"/_gfx/miner_efficiency.php",
		"image/png",
		true // Include,
);
foreach ( array_keys ( $images ) as $p ) {
	$app->get ( $p, function ($request, $response) {
		global $images;
		$uri = $request->getUri ()->getPath ();
		$include_file = $images [$uri] [0];
		$content_type = $images [$uri] [1];
		$include = isset ( $images [$uri] [2] );

		if ($include) {
			ob_start ();
			include (__DIR__ . $include_file);
			$image = ob_get_contents ();
			ob_end_clean ();
		} else {
			// $fn = str_replace ( "/gfx/", "/_gfx/", $uri );
			$image = @file_get_contents ( __DIR__ . $include_file );
			if ($image === false) {
				$response->write ( "Could not find '" . $uri . "'" );
				return $response->withStatus ( 404 );
			}
		}
		$response->getBody ()->write ( $image );
		return $response->withHeader ( 'Content-Type', $content_type );
	} );
}

$apis = array ();
// BookStore testing
$apis ["/app/book/create"] = __DIR__ . "/_api/book/create.php";
$apis ["/app/book/{id}"] = __DIR__ . "/_api/book/read.php";
$apis ["/app/book/{id}/update"] = __DIR__ . "/_api/book/update.php";
$apis ["/app/book/{id}/delete"] = __DIR__ . "/_api/book/delete.php";
// User management
$apis ["/app/user"] = __DIR__ . "/_api/user/read.php";
$apis ["/app/user/login"] = __DIR__ . "/_api/user/login.php";
$apis ["/app/user/logout"] = __DIR__ . "/_api/user/logout.php";
$apis ["/app/user/create"] = __DIR__ . "/_api/user/create.php";
$apis ["/app/user/validate/request"] = __DIR__ . "/_api/user/validate_request.php";
$apis ["/app/user/validate/prepare"] = __DIR__ . "/_api/user/validate_prepare.php";
$apis ["/app/user/validate"] = __DIR__ . "/_api/user/validate.php";
$apis ["/app/user/recover/request"] = __DIR__ . "/_api/user/recover_request.php";
$apis ["/app/user/recover/prepare"] = __DIR__ . "/_api/user/recover_prepare.php";
$apis ["/app/user/recover"] = __DIR__ . "/_api/user/recover.php";
// $apis ["/app/user/prevalidate/{payload}"] = __DIR__ . "/_api/user/prevalidate.php";
// $apis ["/app/user/validate/{guid}/{challenge}"] = __DIR__ . "/_api/user/validate.php";
// $apis ["/app/user/{{id}}"] = __DIR__ . "/_api/user/read.php";
// $apis ["/app/user/{{id}}/validate"] = __DIR__ . "/_api/user/validate.php";
// $apis ["/app/user/{{id}}/authenticate"] = __DIR__ . "/_api/user/authenticate.php";
// $apis ["/app/user/{{id}}/update"] = __DIR__ . "/_api/user/update.php";
$apis ["/app/test/execute"] = __DIR__ . "/_api/testDataStore/execute.php";
foreach ( array_keys ( $apis ) as $p ) {
	$app->post ( $p, function (Request $request, Response $response, $args) {
		global $apis;
		$uri = $request->getUri ()->getPath ();
		// See if any of the api keys expand into the URI I got passed as
		foreach ( $apis as $k => $v ) {
			foreach ( $args as $ak => $av ) {
				$k = str_replace ( "{" . $ak . "}", $av, $k );
			}
			if ($uri == $k) {
				$include = $v;
			}
		}
		if (strlen ( $include )) {
			include ($include);
			return $response->withHeader ( "Content-Type", "application/json;charset=utf-8" );
		} else {
			// logger ( "Could not find '" . $uri . "'" );
			$response->getBody ()->write ( "Could not find '" . $uri . "'" );
			return $response->withStatus ( 404 );
		}
		return $response; // Should never get here
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
