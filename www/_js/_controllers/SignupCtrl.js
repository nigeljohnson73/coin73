app.controller('SignupCtrl', ["$scope", "apiSvc", function($scope, apiSvc) {

	$scope.submitting = false;
	$scope.account_created = false;
	$scope.account_not_created = false;
	$scope.email_valid = false;
	$scope.password_valid = false;
	$scope.password_verify_valid = false;
	$scope.accept_toc = false;
	$scope.submittable = false;
	$scope.password_verify = "";
	$scope.signup_challenge = "WELCOME";

	var signup_action = "signup";
	$scope.tx = {};
	$scope.tx.email = "";
	$scope.tx.password = "";
	$scope.tx.token = "";
	$scope.tx.action = signup_action;
	grecaptcha.ready(function() {
		grecaptcha.execute('{{RECAPTCHA_SITE_KEY}}', { action: signup_action }).then(function(token) {
			//logger("Got a RECAPTCHA token");
			//logger(token);
			$scope.tx.token = token;
		});
	});
	$scope.requestAccount = function() {
		logger("SignupCtrl::requestAccount()");
		$scope.submitting = true;
		if ($scope.submittable) {
			apiSvc.callLocal("user/create", $scope.tx, function(data) {
				logger(data);
				$scope.account_created = data.success;
				$scope.account_not_created = !$scope.account_created;
				if (data.success) {
					$scope.signup_challenge = data.challenge;
				}
				if (data.message.length) {
					toast(data.message);
				}
				$scope.submitting = false;
			});
		} else {
			logger("SignupCtrl::requestAccount() - inputs are not valid");
		};
	};

	$scope.checkValidation = function() {
		$scope.submittable = $scope.email_valid && $scope.password_valid && $scope.password_verify_valid && $scope.accept_toc;
		//		console.log("************************************************************");
		//		console.log("Email valid:", $scope.tx.email, $scope.email_valid);
		//		console.log("Password valid:", $scope.tx.password, $scope.password_valid);
		//		console.log("Verify valid:", $scope.password_verify, $scope.password_verify_valid);
		//		console.log("TOC valid:", $scope.accept_toc, $scope.accept_toc);
		//		console.log("Form submittable:", $scope.submittable);
	};
	$scope.emailAddressValidate = function() {
		$scope.email_valid = $scope.tx.email && ($scope.tx.email.length > 0);
		$scope.checkValidation();
	};
	$scope.passwordValidate = function() {
		var ok_password = new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})");;
		$scope.password_valid = $scope.tx.password && ok_password.test($scope.tx.password);
		$scope.checkValidation();
	};
	$scope.passwordVerifyValidate = function() {
		$scope.password_verify_valid = $scope.password_valid && ($scope.password_verify == $scope.tx.password);
		$scope.checkValidation();
	};
	$scope.tocValidate = function() {
		$scope.checkValidation();
	};
}]);
