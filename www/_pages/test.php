<?php include_once(__DIR__."/_header.php")?>

<div id="page-loaded" class="container-fluid text-center" data-ng-controller="TestCtrl">
	<h1>Testing</h1>
	<p>To be loaded momentarily.</p>
	<button data-ng-disabled="calling" data-ng-click="callPing()">Call ping</button>
	<button data-ng-disabled="calling" data-ng-click="callCreateBook()">Create book</button>
	<button data-ng-disabled="calling" data-ng-click="callGetBookDetails()">Get book data</button>
	<button data-ng-disabled="calling" data-ng-click="callGetNonBookDetails()">Get book data (not existing)</button>
	<button data-ng-disabled="calling" data-ng-click="callUpdateBook()">Update book</button>
	<button data-ng-disabled="calling" data-ng-click="callDeleteBook()">Delete book</button>
</div>

<?php include_once(__DIR__."/_footer.php")?>
