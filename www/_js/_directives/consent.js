app.directive('consent', ["$cookies", function ($cookies) {
	return {
		scope: {},
		templateUrl: '/templates/cookieAlert.html',
		controller: function ($scope) {
			var _consent = $cookies.get('consent');
			$scope.consent = function (consent) {
				if (consent === undefined) {
					return _consent;
				} else if (consent) {
					$cookies.put('consent', true);
					_consent = true;
				}
			};
		}
	};
}]);
