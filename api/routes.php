<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

include_once (__DIR__ . '/functions.php');

$api_routes = array ();
$api_routes ["/ping"] = __DIR__ . "/_api/ping.php";
$api_routes ["/job/request/json"] = __DIR__ . "/_api/job/request_json.php";
$api_routes ["/job/request/text"] = __DIR__ . "/_api/job/request_text.php";
$api_routes ["/job/submit/json/{job_id}/{nonce}"] = __DIR__ . "/_api/job/submit_json.php";
$api_routes ["/job/submit/text/{job_id}/{nonce}"] = __DIR__ . "/_api/job/submit_text.php";
$api_routes ["/coin/summary"] = __DIR__ . "/_api/coin/summary.php";
$api_routes ["/coin/balance"] = __DIR__ . "/_api/coin/balance.php";
$api_routes ["/coin/balance/{wallet_id}"] = __DIR__ . "/_api/coin/balance.php";

foreach ( array_keys ( $api_routes ) as $p ) {
	$roots = array (
			// "", // for http:// api.domain.com/
			"/api" // for http[s]://domain.com/api/
	);
	foreach ( $roots as $r ) {
		$uri = $r . $p;
		//echo "<!-- Adding api route '$uri' -->\n";
		$app->post ( $uri, function (Request $request, Response $response, $args) {
			global $api_routes;
			// Get the uri we were called as, and strip off the root
			$uri = rtrim ( $request->getUri ()->getPath (), "/" );
			$uri = preg_replace ( '/^\/api/', "", $uri );
			$include = "";
			// See if any of the api keys expand into the URI I got passed as
			foreach ( $api_routes as $u => $p ) {
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

?>