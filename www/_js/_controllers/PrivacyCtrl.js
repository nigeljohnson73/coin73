app.controller('PrivacyCtrl', ["$scope", function ($scope) {
	$scope.website = "{{APP_HOST}}";
	$scope.terms_uri = "/terms";
	$scope.terms_label = $scope.website + $scope.terms_uri;
	$scope.company_name = "{{APP_NAME}}";
	$scope.company_address = "89 Cadbury Road, Sunbury On Thames, Middlesex, TW16 7LS";
	$scope.company_contact = "{{APP_EMAIL}}";
}]);
