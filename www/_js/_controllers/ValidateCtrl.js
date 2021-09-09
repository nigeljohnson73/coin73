app.controller('ValidateCtrl', ["$scope", function($scope) {
	$scope.account_validated = false;
	$scope.account_not_validated = false;
	$scope.choices = JSON.parse('["Wasted","Yesterday","Welcome","Orange"]');

	$scope.payload = payload;
}]);
