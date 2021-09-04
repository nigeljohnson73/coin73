<?php include_once(__DIR__."/_header.php")?>

<div id="page-loaded" class="container-fluid text-center" data-ng-controller="AboutCtrl">
	<h1>About this app</h1>
	<p>Coming soon.</p>
	<table>
		<tr>
			<td>APP version:</td>
			<td>{{app_version}}</td>
		</tr>
		<tr>
			<td>APP build date:</td>
			<td>{{build_date}}</td>
		</tr>
		<tr>
			<td>Api build date:</td>
			<td>{{api_build_date}}</td>
		</tr>
	</table>
</div>

<?php include_once(__DIR__."/_footer.php")?>
