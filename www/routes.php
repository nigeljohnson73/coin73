<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Add routes
$core_routes = array();
$core_routes["/"] = __DIR__ . "/_pages/home.php";
$core_routes["/js/app.min.js"] = __DIR__ . "/_js/app.min.js.php";
$core_routes["/css/app.min.css"] = __DIR__ . "/_css/app.min.css.php";
$core_routes["/templates/cookieAlert.html"] = __DIR__ . "/_pages/tpl_cookieAlert.php";
$core_routes["/privacy"] = __DIR__ . "/_pages/privacy.php";
$core_routes["/terms"] = __DIR__ . "/_pages/terms.php";
$core_routes["/about"] = __DIR__ . "/_pages/about.php";
$core_routes["/supportus"] = __DIR__ . "/_pages/supportus.php";
$core_routes["/signup"] = __DIR__ . "/_pages/signup.php";
$core_routes["/validate"] = __DIR__ . "/_pages/validate.php";
$core_routes["/validate/{payload}"] = __DIR__ . "/_pages/validate.php";
$core_routes["/recover"] = __DIR__ . "/_pages/recover.php";
$core_routes["/recover/{payload}"] = __DIR__ . "/_pages/recover.php";
$core_routes["/wiki"] = __DIR__ . "/_pages/wiki.php";
$core_routes["/wiki/{page}"] = __DIR__ . "/_pages/wiki.php";
$core_routes["/wiki/{page}/{sub_page}"] = __DIR__ . "/_pages/wiki.php";
$core_routes["/wiki/{page}/{sub_page}/{sub_sub_page}"] = __DIR__ . "/_pages/wiki.php"; // Surely this is enough
$core_routes["/wiki/{page}/{sub_page}/{sub_sub_page}/{sub_sub_sub_page}"] = __DIR__ . "/_pages/wiki.php"; // No, really, this *is* enough

foreach (array_keys($core_routes) as $p) {
	//echo "<!-- Adding core route '$p' -> '" . $core_routes [$p] . "' -->\n";
	$app->get($p, function (Request $request, Response $response, $args) {
		global $core_routes;
		$uri = $request->getUri()->getPath();
		if (strlen($uri) > 1) {
			$uri = rtrim($request->getUri()->getPath(), "/");
		}
		//$response->getBody ()->write ( "<!-- Requested '" . $uri . "' -->\n" );

		$include = "";
		// See if any of the api keys expand into the URI I got passed as
		foreach ($core_routes as $k => $v) {
			foreach ($args as $ak => $av) {
				$k = str_replace("{" . $ak . "}", $av, $k);
			}
			//$response->getBody ()->write ( "<!--      Checking '" . $k . "'" );
			if ($uri == $k) {
				$include = $v;
				//$response->getBody ()->write ( " FOUND IT" );
			}
			//$response->getBody ()->write ( " -->\n" );
		}
		if (strlen($include)) {
			include($include);
			return $response;
		} else {
			$response->getBody()->write("Could not find '" . $uri . "'");
			return $response->withStatus(404);
		}
	});
}

$image_routes = array();
$image_routes["/gfx/favicon.png"] = array(
	"/_gfx/favicon.png",
	"image/png"
);
$image_routes["/gfx/ajax-loader-bar.gif"] = array(
	"/_gfx/ajax-loader-bar.gif",
	"image/gif"
);
$image_routes["/gfx/ajax-loader-spinner.gif"] = array(
	"/_gfx/ajax-loader-spinner.gif",
	"image/gif"
);
$image_routes["/gfx/logo-400.png"] = array(
	"/_gfx/logo-400.png",
	"image/png"
);
$image_routes["/gfx/logo-200.png"] = array(
	"/_gfx/logo-200.png",
	"image/png"
);
$image_routes["/gfx/submission_time.png"] = array(
	"/_gfx/submission_time.php",
	"image/png",
	true // Include,
);
$image_routes["/gfx/miner_efficiency.png"] = array(
	"/_gfx/miner_efficiency.php",
	"image/png",
	true // Include,
);
foreach (array_keys($image_routes) as $p) {
	//echo "<!-- Adding img route '$p' -->\n";
	$app->get($p, function ($request, $response) {
		global $image_routes;
		$uri = $request->getUri()->getPath();
		$include_file = $image_routes[$uri][0];
		$content_type = $image_routes[$uri][1];
		$include = isset($image_routes[$uri][2]);

		if ($include) {
			ob_start();
			include(__DIR__ . $include_file);
			$image = ob_get_contents();
			ob_end_clean();
		} else {
			// $fn = str_replace ( "/gfx/", "/_gfx/", $uri );
			$image = @file_get_contents(__DIR__ . $include_file);
			if ($image === false) {
				$response->write("Could not find '" . $uri . "'");
				return $response->withStatus(404);
			}
		}
		$response->getBody()->write($image);
		return $response->withHeader('Content-Type', $content_type);
	});
}

$app_routes = array();
$app_routes["/app/user"] = __DIR__ . "/_api/user/read.php";
$app_routes["/app/user/login"] = __DIR__ . "/_api/user/login.php";
$app_routes["/app/user/logout"] = __DIR__ . "/_api/user/logout.php";
$app_routes["/app/user/create"] = __DIR__ . "/_api/user/create.php";
$app_routes["/app/user/validate/request"] = __DIR__ . "/_api/user/validate_request.php";
$app_routes["/app/user/validate/prepare"] = __DIR__ . "/_api/user/validate_prepare.php";
$app_routes["/app/user/validate"] = __DIR__ . "/_api/user/validate.php";
$app_routes["/app/user/recover/request"] = __DIR__ . "/_api/user/recover_request.php";
$app_routes["/app/user/recover/prepare"] = __DIR__ . "/_api/user/recover_prepare.php";
$app_routes["/app/user/recover"] = __DIR__ . "/_api/user/recover.php";
$app_routes["/app/transaction/send"] = __DIR__ . "/_api/transaction/send.php";
foreach (array_keys($app_routes) as $p) {
	//echo "<!-- Adding app route '$p' -->\n";
	$app->post($p, function (Request $request, Response $response, $args) {
		global $app_routes;
		$uri = $request->getUri()->getPath();
		// See if any of the api keys expand into the URI I got passed as
		foreach ($app_routes as $k => $v) {
			foreach ($args as $ak => $av) {
				$k = str_replace("{" . $ak . "}", $av, $k);
			}
			if ($uri == $k) {
				$include = $v;
			}
		}
		if (strlen($include)) {
			include($include);
			return $response->withHeader("Content-Type", "application/json;charset=utf-8");
		} else {
			// logger ( "Could not find '" . $uri . "'" );
			$response->getBody()->write("Could not find '" . $uri . "'");
			return $response->withStatus(404);
		}
		return $response; // Should never get here
	});
}
