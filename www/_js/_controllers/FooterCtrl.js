app.controller('FooterCtrl', ["$scope", "$rootScope", "$location", "$window", "netSvc", function ($scope, $rootScope, $location, $window, netSvc) {
	if (!$rootScope.loadedGaTracker) {
		$rootScope.loadedGaTracker = true;

		$rootScope.$on('$viewContentLoaded', function (event) {
			$window.ga('send', 'pageview', {
				page: $location.url()
			});
			galog("FooterCtrl::handleGoogleAnalytics('" + $location.url() + "')");
		});
	}

	// This is only used to update the copyright year to "this year". Massive overkill.
	$scope.nowDate = Date.now();

	$scope.online = false;
	netSvc.addStateListener(function (tf, defer) {
		//applog("Footer::netSvc.handleNetworkChange('" + tf + "')");
		$scope.online = tf;
		if (!defer) {
			$scope.$apply();
		}
	});
}]);
