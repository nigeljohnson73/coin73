app.controller('PrivacyCtrl', [ "$scope", function($scope) {
	$scope.website = "https://minertor.appspot.com/"; // TODO: brand this
	$scope.terms_uri = "/terms";
	$scope.terms_label = $scope.website + $scope.terms_uri;
	$scope.company_name = "{{APP_NAME}}"; // TODO: brand this 
	$scope.company_address = "89 Cadbury Road, Sunbury On Thames, Middlesex, TW16 7LS";
	$scope.company_contact = "minertorblockchain@gmail.com"; // TODO: brand this
} ]);
