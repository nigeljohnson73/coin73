<?php startPage()?>
<!doctype html>
<html data-ng-app="myApp" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel=icon href="/gfx/favicon.png" type="image/png">


<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLRk2vvoC2f3B09zVXn8CA5QIVfZOJ3BCsw2P0p/We" crossorigin="anonymous">
<link rel="stylesheet" href="/css/app.min.css">

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-U1DAWAznBHeqEIlVSCgzq+c9gqGAJn5c/t99JyeKa9xxaYpSvHU5awsuZVVFIhvj" crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular-cookies.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="/js/app.min.js"></script>

<title><?php echo getAppTitle() ?></title>
</head>
<body>
	<div id="snackbar"></div>
	<div data-consent></div>
	<div id="page-loading">
		<img src="/gfx/ajax-loader-bar.gif" alt="Page loading" />
		<p>Please wait while the page loads...</p>
	</div>
	<div class="headliner text-center">
		<a href="/"><img class="img-responsive d-block d-sm-none" src="/gfx/logo-200.png" alt="small logo" /></a> <a href="/"><img class="img-responsive d-none d-sm-block" src="/gfx/logo-400.png" alt="big logo" /></a>
	</div>