<?php include_once(__DIR__."/_header.php")?>

<div class="container-fluid text-center" data-ng-controller="TestCtrl">
	<h1>Testing</h1>
	<p>These tests call the various APIs and output in the debug console for the most part.</p>
	<button class="btn btn-custom" data-ng-disabled="calling" data-ng-click="callPing()">Call ping</button>
	<button class="btn btn-custom" data-ng-disabled="calling" data-ng-click="callCreateBook()">Create book</button>
	<button class="btn btn-custom" data-ng-disabled="calling" data-ng-click="callGetBookDetails()">Get book data</button>
	<button class="btn btn-custom" data-ng-disabled="calling" data-ng-click="callGetNonBookDetails()">Get N/A book data</button>
	<button class="btn btn-custom" data-ng-disabled="calling" data-ng-click="callUpdateBook()">Update book</button>
	<button class="btn btn-custom" data-ng-disabled="calling" data-ng-click="callDeleteBook()">Delete book</button>
</div>

<?php include_once(__DIR__."/_footer.php")?>
