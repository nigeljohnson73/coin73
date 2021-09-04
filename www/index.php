<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

include_once (__DIR__ . '/functions.php');
require __DIR__ . '/vendor/autoload.php';

function logger($str) {
	$logfile = "./tmp.log";
	file_put_contents ( $logfile, trim ( $str ) . "\n", FILE_APPEND );
}

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
$routes ["/merch"] = __DIR__ . "/_pages/merch.php";
$routes ["/signup"] = __DIR__ . "/_pages/signup.php";

foreach ( array_keys ( $routes ) as $p ) {
	$app->get ( $p, function (Request $request, Response $response) {
		global $routes;
		$uri = $request->getUri ()->getPath ();
		if (isset ( $routes [$uri] )) {
			ob_start ();
			include ($routes [$uri]);
			$c = ob_get_contents ();
			ob_end_clean ();
			$response->getBody ()->write ( $c );
		} else {
			logger ( "Could not find '" . $uri . "'" );
			$response->getBody ()->write ( "Could not find '" . $uri . "'" );
			return $response->withStatus ( 404 );
		}
		return $response;
	} );
}

$images = array ();
$images ["/gfx/favicon.png"] = "image/png";
$images ["/gfx/ajax-loader-bar.gif"] = "image/gif";
$images ["/gfx/logo-400.png"] = "image/png";
$images ["/gfx/logo-200.png"] = "image/png";
foreach ( array_keys ( $images ) as $p ) {
	$app->get ( $p, function ($request, $response) {
		global $images;
		$uri = $request->getUri ()->getPath ();
		$content_type = $images [$uri];
		$fn = str_replace ( "/gfx/", "/_gfx/", $uri );
		$image = @file_get_contents ( __DIR__ . $fn );
		if ($image === false) {
			$response->write ( "Could not find '" . $uri . "'" );
			return $response->withStatus ( 404 );
		}
		$response->getBody ()->write ( $image );
		return $response->withHeader ( 'Content-Type', $content_type );
	} );
}

$app->run ();
?>
