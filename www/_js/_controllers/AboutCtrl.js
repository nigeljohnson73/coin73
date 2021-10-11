app.controller('AboutCtrl', ["$scope", function ($scope) {
	//	$scope.app_id = app_id;
	//	$scope.cache_id = cache_id;
	$scope.build_date = "{{APP_DATE}}";
	$scope.api_build_date = "{{API_DATE}}";
	$scope.app_version = "{{APP_VERSION}}";
}]);