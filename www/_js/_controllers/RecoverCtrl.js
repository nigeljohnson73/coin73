app.controller('RecoverCtrl', ["$scope", "$sce", "$timeout", "apiSvc", function ($scope, $sce, $timeout, apiSvc) {
	$scope.submitting = false;
	$scope.reason = ""; // For failure reporting

	// Requesting variables
	$scope.email_valid = false;
	$scope.password_valid = false;
	$scope.password_verify_valid = false;
	$scope.accept_toc = false;
	$scope.request_submittable = false;
	$scope.recovery_request_success = false;
	$scope.recovery_request_failure = false;
	$scope.choices = [];

	// Perform variables
	$scope.payload = payload; // Set if we need to perform the recovery
	$scope.submittable = false;
	$scope.submitting = payload && payload.length;
	$scope.recovery_success = false;
	$scope.recovery_failure = false;
	$scope.choices = [];

	var recaptcha_action = "recover";
	$scope.tx = {};
	$scope.tx.email = "";
	$scope.tx.password = "";
	$scope.tx.accept_toc = false;
	$scope.tx.token = "";
	$scope.tx.action = recaptcha_action;
	grecaptcha.ready(function () {
		grecaptcha.execute('{{RECAPTCHA_SITE_KEY}}', { action: recaptcha_action }).then(function (token) {
			//logger("Got a RECAPTCHA token");
			//logger(token);
			$scope.tx.token = token;
		});
	});

	$scope.checkValidation = function () {
		$scope.request_submittable = $scope.email_valid && $scope.accept_toc;
		$scope.submittable = $scope.password_valid && $scope.password_verify_valid && $scope.tx.accept_toc;
		//		console.log("************************************************************");
		//		console.log("Email valid:", $scope.tx.email, $scope.email_valid);
		//		console.log("Password valid:", $scope.tx.password, $scope.password_valid);
		//		console.log("Verify valid:", $scope.password_verify, $scope.password_verify_valid);
		//		console.log("TOC (request) valid:", $scope.accept_toc, $scope.accept_toc);
		//		console.log("TOC (recover) valid:", $scope.tx.accept_toc, $scope.tx.accept_toc);
		//		console.log("Request submittable:", $scope.request_submittable);
		//		console.log("Recover submittable:", $scope.submittable);
	};
	$scope.emailAddressValidate = function () {
		$scope.email_valid = $scope.tx.email && ($scope.tx.email.length > 0);
		//		$scope.checkValidation();
	};
	$scope.passwordValidate = function () {
		var ok_password = new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})");;
		$scope.password_valid = $scope.tx.password && ok_password.test($scope.tx.password);
		$scope.checkValidation();
	};
	$scope.passwordVerifyValidate = function () {
		$scope.password_verify_valid = $scope.password_valid && ($scope.password_verify == $scope.tx.password);
		$scope.checkValidation();
	};
	$scope.tocValidate = function () {
		$scope.checkValidation();
	};

	$scope.requestRecoverAccount = function () {
		logger("RecoverCtrl::requestRecoverAccount()");
		$scope.submitting = true;
		delete ($scope.tx.password);
		if ($scope.request_submittable) {
			apiSvc.callLocal("user/recover/request", $scope.tx, function (data) {
				logger(data);
				$scope.recovery_request_success = data.success;
				$scope.recovery_request_failure = !data.success;
				if (data.success) {
					$scope.challenge = data.challenge;
				}
				$scope.reason = $sce.trustAsHtml(data.reason);

				if (data.message.length) {
					toast(data.message);
				}
				$scope.submitting = false;
			});
		} else {
			logger("RecoverCtrl::requestRecoverAccount() - inputs are not valid");
		};

		// Trigger the tooltips once the DOM haas reloaded back into the main loop
		$timeout(function () {
			$(function () {
				$('[data-toggle="tooltip"]').tooltip();
			});
		}, 100);
	};

	var prepareRecover = function () {
		if (!$scope.payload) {
			return;
		}
		logger("RecoverCtrl::prepare()", "inf");
		$scope.submitting = true;
		var tx = {};
		tx.payload = payload;
		apiSvc.queueLocal("user/recover/prepare", tx, function (data) {
			logger("RecoverCtrl::prepare(): response", "dbg");
			logger(data, "inf");
			if (data.success) {
				// Yay for us
				$scope.choices = data.choices;
				$scope.tx.guid = data.guid;
			} else {
				$scope.recovery_failure = true;
			}
			$scope.reason = $sce.trustAsHtml(data.reason);

			if (data.message.length) {
				toast(data.message);
			}

			$scope.submitting = false;

			// Trigger the tooltips once the DOM has reloaded back into the main loop
			$timeout(function () {
				$(function () {
					$('[data-toggle="tooltip"]').tooltip();
				});
			}, 100);
		});
	};

	$scope.recover = function (challenge) {
		logger("RecoverCtrl::recover('" + challenge + "')", "inf");
		delete ($scope.tx.email);
		$scope.submitting = true;
		$scope.tx.challenge = challenge;
		apiSvc.callLocal("user/recover", $scope.tx, function (data) {
			logger("RecoverCtrl::recover('" + challenge + "') - response", "dbg");
			logger(data);
			$scope.recovery_success = data.success;
			$scope.recovery_failure = !data.success;
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
	$scope.decode_payload_api_call = $timeout(prepareRecover, 500);
}]);
