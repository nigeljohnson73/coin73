app.controller('ValidateCtrl', ["$scope", "$timeout", "apiSvc", function($scope, $timeout, apiSvc) {
	$scope.payload = payload;
	$scope.loading = true;

	$scope.account_validated = false;
	$scope.account_not_validated = false;
	$scope.choices = [];

	var prevalidate = function() {
		$scope.loading = true;
		var tx = {};
		tx.payload = payload;
		apiSvc.queueLocal("user/prevalidate", tx, function(data) {
			logger("ValidateCtrl::decodePayload()", "dbg");
			logger(data, "log");
			if (data.success) {
				// Yay for us
				$scope.choices = data.choices;
				$scope.guid = data.guid;
			} else {
				$scope.choices = ["Test", "This", "Out"];
				$scope.guid = "MaG1cGu1D"
			}
			if (data.message.length) {
				toast(data.message);
			}

			$scope.loading = false;
			//$timeout(getSummary, 5000);

			// Trigger the tooltips once the DOM haas reloaded back into the main loop
			$timeout(function() {
				$(function() {
					$('[data-toggle="tooltip"]').tooltip();
				});
			}, 100);
		});
	};

	var validate = function(challenge) {
		$scope.loading = true;
		var tx = {};
		tx.guid = $scope.guid;
		tx.challenge = challenge;
		apiSvc.queueLocal("user/validate", tx, function(data) {
			logger("ValidateCtrl::decodePayload()", "dbg");
			logger(data, "log");
			if (data.success) {
				// Yay for us
				$scope.account_validated = true;
			} else {
				$scope.account_not_validated = true;
			}
			if (data.message.length) {
				toast(data.message);
			}

			$scope.loading = false;
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
	$scope.decode_payload_api_call = $timeout(prevalidate, 500);
}]);
