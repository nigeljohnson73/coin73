app.controller('TestCtrl', ["$scope", "$timeout", "apiSvc", function($scope, $timeout, apiSvc) {

	/***************************************************************************
	 * Get miner summary every 5 seconds via API queue
	 */
	var ping = function() {
		apiSvc.queuePublic("ping", {}, function(data) {
			logger("HomeCtrl::ping()", "dbg");
			logger(data, "log");
			if (data.success) {
				// Yay for us
			}
			if (data.message.length) {
				toast(data.message);
			}

			//$timeout(getSummary, 5000);

			// Trigger the tooltips once the DOM haas reloaded back into the main loop
			$timeout(function() {
				$(function() {
					$('[data-toggle="tooltip"]').tooltip();
				});
			}, 100);
		});
	};
	// Start the calling, but after a startup grace period
	//$scope.ping_api_call = $timeout(ping, 500);

	/*	$scope.showServerStats = function() {
			//logger("showServerStats(): called");
			toast("Server statistics mode");
			$scope.miner_summary = false;
			$scope.miner_stats = false;
			$scope.miner_details = false;
			$scope.server_stats = true;
		};
	*/
	$scope.calling = false;
	$scope.callPing = function() {
		$scope.calling = true;
		logger("HomeCtrl::callPing()");
		apiSvc.callPublic("ping", {}, function(data) {
			logger(data);
			if (data.success) {
				// Yay for us
			}
			if (data.message.length) {
				toast(data.message);
			}
			$scope.calling = false;
		});
	};
	$scope.book = {};
	$scope.book.title = 'Romeo and Juliet';
	$scope.book.author = 'William Shakespeare';
	$scope.book.isbn = '1840224339';
	$scope.book.read_count = "0";

	$scope.callCreateBook = function() {
		$scope.calling = true;
		logger("HomeCtrl::callCreateBook()");
		apiSvc.callLocal("book/create", $scope.book, function(data) {
			logger(data);
			if (data.success) {
				// Yay for us
				$scope.book = data.book;
				logger($scope.book)
			}
			if (data.message.length) {
				toast(data.message);
			}
			$scope.calling = false;
		});
	};
	$scope.callGetBookDetails = function() {
		$scope.calling = true;
		logger("HomeCtrl::callGetBookDetails()");
		apiSvc.callLocal("book/" + $scope.book.isbn, {}, function(data) {
			logger(data);
			if (data.success) {
				// Yay for us
				$scope.book = data.book;
				logger($scope.book)
			}
			if (data.message.length) {
				toast(data.message);
			}
			$scope.calling = false;
		});
	};
	$scope.callGetNonBookDetails = function() {
		$scope.calling = true;
		logger("HomeCtrl::callGetNonBookDetails()");
		apiSvc.callLocal("book/999", {}, function(data) {
			logger(data);
			if (data.success) {
				// Yay for us
				$scope.book = data.book;
				logger($scope.book)
			}
			if (data.message.length) {
				toast(data.message);
			}
			$scope.calling = false;
		});
	};
	$scope.callUpdateBook = function() {
		$scope.calling = true;
		logger("HomeCtrl::callUpdateBook()");
		tx = {};
		tx.read_count = parseInt($scope.book.read_count) + 1;
		apiSvc.callLocal("book/" + $scope.book.isbn + "/update", tx, function(data) {
			logger(data);
			if (data.success) {
				// Yay for us
				$scope.book = data.book;
				logger($scope.book)
			}
			if (data.message.length) {
				toast(data.message);
			}
			$scope.calling = false;
		});
	};
	$scope.callDeleteBook = function() {
		$scope.calling = true;
		logger("HomeCtrl::callDeleteBook()");
		apiSvc.callLocal("book/" + $scope.book.isbn + "/delete", {}, function(data) {
			logger(data);
			if (data.success) {
				// Yay for us
				//$scope.book = data.book;
				//logger($scope.book)
			}
			if (data.message.length) {
				toast(data.message);
			}
			$scope.calling = false;
		});
	};
}]);

