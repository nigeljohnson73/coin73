<?php include_once(__DIR__."/_header.php")?>

<div id="page-loaded" class="container-fluid text-center" data-ng-controller="AboutCtrl">
	<h1>About this app</h1>
	<p>Coming soon.</p>
	<div class="about-data">
		<div class="row">
			<div class="col-6 text-right font-weight-bold">App version:</div>
			<div class="col-6 text-left">{{app_version}}</div>
		</div>
		<div class="row">
			<div class="col-6 text-right font-weight-bold">App build date:</div>
			<div class="col-6 text-left">{{build_date}}</div>
		</div>
		<div class="row">
			<div class="col-6 text-right font-weight-bold">API build date:</div>
			<div class="col-6 text-left">{{api_build_date}}</div>
		</div>
	</div>
</div>

<?php include_once(__DIR__."/_footer.php")?>
