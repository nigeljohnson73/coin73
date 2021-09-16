app.controller('ValidateCtrl', ["$scope", "$sce", "$timeout", "apiSvc", function($scope, $sce, $timeout, apiSvc) {
	$scope.submitting = false;
	$scope.reason = ""; // For failure reporting
	$scope.warning = "";

	// Requesting variables
	$scope.email_valid = false;
	$scope.password_valid = false;
	$scope.password_verify_valid = false;
	$scope.accept_toc = false;
	$scope.submittable = false;
	$scope.validation_request_success = false;
	$scope.validation_request_failure = false;
	$scope.choices = [];

	// Perform varaibles
	$scope.payload = payload; // Set if we need to perform the validation
	$scope.submitting = payload && payload.length;
	$scope.validation_success = false;
	$scope.validation_failure = false;
	$scope.choices = [];

	var recaptcha_action = "validate";
	$scope.tx = {};
	$scope.tx.email = "";
	$scope.tx.password = "";
	$scope.tx.token = "";
	$scope.tx.action = recaptcha_action;
	grecaptcha.ready(function() {
		grecaptcha.execute('{{RECAPTCHA_SITE_KEY}}', { action: recaptcha_action }).then(function(token) {
			//logger("Got a RECAPTCHA token");
			//logger(token);
			$scope.tx.token = token;
		});
	});

	$scope.checkValidation = function() {
		$scope.submittable = $scope.email_valid && $scope.password_valid && $scope.accept_toc;
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
	$scope.tocValidate = function() {
		$scope.checkValidation();
	};

	$scope.requestValidateAccount = function() {
		logger("ValidateCtrl::requestAccount()");
		$scope.submitting = true;
		$scope.warning = "";
		if ($scope.submittable) {
			apiSvc.callLocal("user/validate/request", $scope.tx, function(data) {
				logger(data);
				$scope.validation_request_success = data.success;
				$scope.validation_request_failure = !data.success;
				if (data.success) {
					$scope.challenge = data.challenge;
				}
				$scope.reason = $sce.trustAsHtml(data.reason);
				$scope.warning = $sce.trustAsHtml(data.warning);

				if (data.message.length) {
					toast(data.message);
				}
				$scope.submitting = false;
			});
		} else {
			logger("SignupCtrl::requestAccount() - inputs are not valid");
		};

		// Trigger the tooltips once the DOM haas reloaded back into the main loop
		$timeout(function() {
			$(function() {
				$('[data-toggle="tooltip"]').tooltip();
			});
		}, 100);
	};

	var prepareValidate = function() {
		if (!$scope.payload) {
			return;
		}
		logger("ValidateCtrl::prepare()", "inf");
		$scope.submitting = true;
		var tx = {};
		tx.payload = payload;
		apiSvc.queueLocal("user/validate/prepare", tx, function(data) {
			logger("ValidateCtrl::prepare(): response", "dbg");
			logger(data, "inf");
			if (data.success) {
				// Yay for us
				delete ($scope.tx.email);
				delete ($scope.tx.password);
				$scope.choices = data.choices;
				$scope.tx.guid = data.guid;
			} else {
				$scope.validation_failure = true;
			}
			$scope.reason = $sce.trustAsHtml(data.reason);
			$scope.warning = $sce.trustAsHtml(data.warning);

			if (data.message.length) {
				toast(data.message);
			}

			$scope.submitting = false;

			// Trigger the tooltips once the DOM has reloaded back into the main loop
			$timeout(function() {
				$(function() {
					$('[data-toggle="tooltip"]').tooltip();
				});
			}, 100);
		});
	};

	$scope.validate = function(challenge) {
		logger("ValidateCtrl::validate('" + challenge + "')", "inf");
		$scope.submitting = true;
		$scope.tx.challenge = challenge;
		apiSvc.callLocal("user/validate", $scope.tx, function(data) {
			logger("ValidateCtrl::validate('" + challenge + "') - response", "dbg");
			logger(data);
			$scope.validation_success = data.success;
			$scope.validation_failure = !data.success;
			if (data.success) {
				$scope.challenge = data.challenge;
			}
			$scope.reason = $sce.trustAsHtml(data.reason);

			if (data.message.length) {
				toast(data.message);
			}
			$scope.submitting = false;
		});
	};
	// Start the calling, but after a startup grace period
	$scope.decode_payload_api_call = $timeout(prepareValidate, 500);
}]);
