app.controller('SignupCtrl', ["$scope", "$timeout", "$interval", "$sce", "apiSvc", function ($scope, $timeout, $interval, $sce, apiSvc) {

	$scope.submitting = false;
	$scope.account_created = false;
	$scope.email_valid = false;
	$scope.password_valid = false;
	$scope.password_verify_valid = false;
	$scope.submittable = false;
	$scope.password_verify = "";
	$scope.challenge = ""; // The server generated Poor mans MFA key

	$scope.recaptcha_progress = 0;
	$scope.recaptcha_started = null;
	$scope.recaptcha_timeout_call = null;
	$scope.recaptcha_timeout = 115;
	$scope.recaptcha_timeout_reason = "You took to long to complete the process. Please press the button below when you're ready to continue.";
	var recaptcha_action = "signup";

	$scope.tx = {};
	$scope.tx.email = "";
	$scope.tx.password = "";
	$scope.tx.accept_toc = "";
	$scope.tx.token = null;
	$scope.tx.action = recaptcha_action;

	function pause() {
		// Use this if you want to slow progress down cuz the API/GUI is being a pig and you want to debug it
		//var start = new Date().getTime();
		//while (new Date().getTime() < start + 1000);
	}

	$scope.updateCaptchaProgress = function () {
		if ($scope.recaptcha_progress_interval) {
			var now = new Date().getTime();
			var dif = (now - $scope.recaptcha_started) / 1000;
			var pcnt = ($scope.recaptcha_timeout - dif) / ($scope.recaptcha_timeout);
			$scope.recaptcha_progress = pcnt * 100;
			//$scope.recaptcha_progress = Math.round($scope.recaptcha_progress);
		}

		if ($scope.recaptcha_progress <= 0) {
			$interval.cancel($scope.recaptcha_progress_interval);
			$scope.recaptcha_progress_interval = null;
			$scope.recaptcha_progress = 0; // handle the bounce case when we are a smidge late.
		}

		//console.log("RECAPTCHA progress:", $scope.recaptcha_progress);
	};

	$scope.retireCaptcha = function () {
		$scope.progress = 0;
		if ($scope.recaptcha_timeout_call) {
			$timeout.cancel($scope.recaptcha_timeout_call);
		}
		if ($scope.recaptcha_progress_interval) {
			$interval.cancel($scope.recaptcha_progress_interval);
			$scope.recaptcha_started = null;
		}
		$scope.recaptcha_progress = false;
		$scope.recaptcha_timeout_call = null;
		$scope.recaptcha_progress_interval = null;
	};

	$scope.requestSignupCaptcha = function () {
		logger("HomeCtrl::requestSignupCaptcha() called", "dbg");
		$scope.retireCaptcha();
		$scope.loading = true;
		//console.trace();

		$scope.submitting = true;
		$scope.reason = null;

		$scope.email_valid = false;
		$scope.password_valid = false;
		$scope.password_verify_valid = false;
		$scope.tx.email = "";
		$scope.tx.password = "";
		$scope.tx.accept_toc = "";
		$scope.password_verify = "";
		$scope.submittable = false;

		grecaptcha.execute('{{RECAPTCHA_SITE_KEY}}', { action: recaptcha_action }).then(function (token) {
			$scope.recaptcha_progress = 100;
			$scope.recaptcha_started = new Date().getTime();
			$scope.recaptcha_progress_interval = $interval($scope.updateCaptchaProgress, 1000);

			logger("HomeCtrl::requestSignupCaptcha() - recieved a RECAPTCHA token", "dbg");
			pause();
			$scope.loading = false;
			$scope.submitting = false;
			$scope.tx.token = token;
			$scope.recaptcha_timeout_call = $timeout(function () {
				$scope.tx.token = null;
				$scope.reason = $sce.trustAsHtml($scope.recaptcha_timeout_reason);
			}, $scope.recaptcha_timeout * 1000);
		});

	};
	$scope.load_user_api_call = $timeout($scope.requestSignupCaptcha, 100);

	$scope.requestAccount = function () {
		logger("SignupCtrl::requestAccount()", "inf");
		$scope.retireCaptcha();
		if ($scope.submittable) {
			$scope.submitting = true;
			apiSvc.callLocal("user/create", $scope.tx, function (data) {
				logger(data);
				$scope.account_created = data.success;
				if (data.success) {
					$scope.challenge = data.challenge;
				} else {
					// Start the RECAPTCHA again.
					$scope.requestSignupCaptcha();
				}
				$scope.reason = $sce.trustAsHtml(data.reason);

				if (data.message.length) {
					toast(data.message);
				}
				$scope.submitting = false;
			});
		} else {
			logger("SignupCtrl::requestAccount() - inputs are not valid");
		};
	};

	$scope.checkValidation = function () {
		$scope.submittable = $scope.email_valid && $scope.password_valid && $scope.password_verify_valid && $scope.tx.accept_toc;
		//		console.log("************************************************************");
		//		console.log("Email valid:", $scope.tx.email, $scope.email_valid);
		//		console.log("Password valid:", $scope.tx.password, $scope.password_valid);
		//		console.log("Verify valid:", $scope.password_verify, $scope.password_verify_valid);
		//		console.log("TOC valid:", $scope.accept_toc, $scope.accept_toc);
		//		console.log("Form submittable:", $scope.submittable);
	};
	$scope.emailAddressValidate = function () {
		$scope.email_valid = $scope.tx.email && ($scope.tx.email.length > 0);
		$scope.checkValidation();
	};
	$scope.passwordValidate = function () {
		var ok_password = new RegExp("{{VALID_PASSWORD_REGEX}}");
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
}]);
