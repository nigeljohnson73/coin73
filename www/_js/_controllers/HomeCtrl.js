app.controller('HomeCtrl', ["$scope", "$timeout", "apiSvc", function($scope, $timeout, apiSvc) {
	$scope.loading = true;

	/***************************************************************************
	 * Get miner summary every 5 seconds via API queue
	 */
		var ping = function() {
			apiSvc.queue("ping", {}, function(data) {
				logger("HomeCtrl::ping()", "dbg");
				logger(data, "log");
				if (data.success) {
					// Yay for us
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
	$scope.ping_api_call = $timeout(ping, 500);

	/*	$scope.showServerStats = function() {
			//logger("showServerStats(): called");
			toast("Server statistics mode");
			$scope.miner_summary = false;
			$scope.miner_stats = false;
			$scope.miner_details = false;
			$scope.server_stats = true;
		};
	*/
}]);

