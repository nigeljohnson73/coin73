<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

//include_once (__DIR__ . '/functions.php');

$cron_routes = array ();
$cron_routes ["/cron/tick"] = __DIR__ . "/_cron/tick.php";
$cron_routes ["/cron/every_minute"] = __DIR__ . "/_cron/every_minute.php";
$cron_routes ["/cron/every_hour"] = __DIR__ . "/_cron/every_hour.php";
$cron_routes ["/cron/every_day"] = __DIR__ . "/_cron/every_day.php";

foreach ( array_keys ( $cron_routes ) as $p ) {
	//echo "<!-- Adding cron route '$p' -->\n";
	$app->get ( $p, function (Request $request, Response $response, $args) {
		global $cron_routes;
		$uri = $request->getUri ()->getPath ();
		if (strlen ( $uri ) > 1) {
			$uri = rtrim ( $request->getUri ()->getPath (), "/" );
		}
		$include = "";
		// See if any of the api keys expand into the URI I got passed as
		foreach ( $cron_routes as $k => $v ) {
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

?>
