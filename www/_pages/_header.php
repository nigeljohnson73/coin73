<?php
// session_id ( getDataNamespace () );
// session_start ();
startPage();
?>
<!doctype html>
<html data-ng-app="myApp" lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- https://fonts.google.com/specimen/Lato -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lato:wght@400;900&family=Space+Mono&display=swap">

	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLRk2vvoC2f3B09zVXn8CA5QIVfZOJ3BCsw2P0p/We" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css" integrity="sha512-xnP2tOaCJnzp2d2IqKFcxuOiVCbuessxM6wuiolT9eeEJCyy0Vhcwa4zQvdrZNVqlqaxXhHqsSV1Ww7T2jSCUQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" href="//unpkg.com/@highlightjs/cdn-assets@11.2.0/styles/default.min.css">
	<link rel="stylesheet" href="/css/app.min.css">
	<link rel=icon href="/gfx/favicon.png" type="image/png">

	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-U1DAWAznBHeqEIlVSCgzq+c9gqGAJn5c/t99JyeKa9xxaYpSvHU5awsuZVVFIhvj" crossorigin="anonymous"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular-cookies.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
	<script src="//unpkg.com/@highlightjs/cdn-assets@11.2.0/highlight.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/qr-creator/dist/qr-creator.min.js"></script>
	<script src="/js/app.min.js"></script>

	<title><?php echo getAppTitle() ?></title>
</head>

<body class="text-center">
	<div id="snackbar"></div>
	<div data-consent></div>
	<div id="page-loading">
		<img src="/gfx/ajax-loader-bar.gif" alt="Page loading" />
		<p>Please wait while the page loads...</p>
	</div>
	<div id="page-loaded" class="d-none">
		<!-- Ended in the footer -->
		<div class="headliner">
			<a href="/"><img class="img-responsive d-block d-sm-none" src="/gfx/logo-200.png" alt="small logo" /></a> <a href="/"><img class="img-responsive d-none d-sm-block" src="/gfx/logo-400.png" alt="big logo" /></a>
		</div>
		<div class="container-lg">
			<!-- Ended in the footer -->
			<?php
			global $coinbase_privKey;
			if (strlen($coinbase_privKey) == 0) {
				echo "<div class='alert alert-danger'> No coinbase keys are setup in config_" . $_SERVER["SERVER_NAME"] . ".php</div>";
			}
			?>