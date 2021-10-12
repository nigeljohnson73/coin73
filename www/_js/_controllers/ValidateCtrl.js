app.controller('ValidateCtrl', ["$scope", "$sce", "$timeout", "$interval", "apiSvc", function ($scope, $sce, $timeout, $interval, apiSvc) {
	$scope.loading = true;
	$scope.submitting = false;
	$scope.submitted = false;
	$scope.reason = ""; // For failure reporting
	$scope.warning = "";
	$scope.payload = payload;

	// Requesting variables
	$scope.email_valid = false;
	$scope.password_valid = false;
	$scope.accept_toc = false;
	$scope.submittable = false;
	$scope.account_validated = false;
	$scope.choices = [];

	// Perform varaibles
	// $scope.submitting = payload && payload.length;
	// $scope.validation_success = false;
	// $scope.validation_failure = false;
	//	$scope.choices = [];

	$scope.recaptcha_progress = 0;
	$scope.recaptcha_started = null;
	$scope.recaptcha_timeout_call = null;
	$scope.recaptcha_timeout = 115;
	$scope.recaptcha_timeout_reason = "You took to long to complete the process. Please press the button below when you're ready to continue.";
	var recaptcha_action = "validate";

	$scope.tx = {};
	$scope.tx.email = "";
	$scope.tx.password = "";
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

	// Reset the page to it's entry point
	$scope.requestCaptcha = function () {
		logger("ValidateCtrl::requestCaptcha() called", "dbg");
		$scope.retireCaptcha();
		$scope.loading = true;
		$scope.submitting = true;
		$scope.submitted = false;
		$scope.reason = "";
		$scope.warning = "";

		$scope.tx.email = "";
		$scope.tx.password = "";
		$scope.tx.token = "";
		$scope.email_valid = false;
		$scope.password_valid = false;
		$scope.submittable = false;

		grecaptcha.execute('{{RECAPTCHA_SITE_KEY}}', { action: recaptcha_action }).then(function (token) {
			$scope.recaptcha_progress = 100;
			$scope.recaptcha_started = new Date().getTime();
			$scope.recaptcha_progress_interval = $interval($scope.updateCaptchaProgress, 1000);

			logger("ValidateCtrl::requestCaptcha() - recieved a RECAPTCHA token", "dbg");
			pause();
			$scope.loading = false;
			// $scope.submitting = false;
			$scope.tx.token = token;
			$scope.recaptcha_timeout_call = $timeout(function () {
				$scope.tx.token = null;
				$scope.reason = $sce.trustAsHtml($scope.recaptcha_timeout_reason);
			}, $scope.recaptcha_timeout * 1000);
		});

	};
	$scope.load_user_api_call = $timeout($scope.requestCaptcha, 100);

	$scope.checkValidation = function () {
		$scope.submittable = $scope.email_valid && $scope.password_valid && $scope.accept_toc;
		//		console.log("************************************************************");
		//		console.log("Email valid:", $scope.tx.email, $scope.email_valid);
		//		console.log("Password valid:", $scope.tx.password, $scope.password_valid);
		//		console.log("TOC valid:", $scope.accept_toc, $scope.accept_toc);
		//		console.log("Form submittable:", $scope.submittable);
	};
	$scope.emailAddressValidate = function () {
		$scope.email_valid = $scope.tx.email && ($scope.tx.email.length > 0);
		$scope.checkValidation();
	};
	$scope.passwordValidate = function () {
		var ok_password = new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})");;
		$scope.password_valid = $scope.tx.password && ok_password.test($scope.tx.password);
		$scope.checkValidation();
	};
	$scope.tocValidate = function () {
		$scope.checkValidation();
	};

	$scope.requestValidate = function () {
		logger("ValidateCtrl::requestValidate()", "inf");

		$scope.submitting = true;
		$scope.submitted = false;
		$scope.reason = "";
		$scope.warning = "";
		if ($scope.submittable) {
			$scope.retireCaptcha();
			apiSvc.callLocal("user/validate/request", $scope.tx, function (data) {
				logger(data);
				$scope.account_validated = data.success;
				if (data.success) {
					$scope.challenge = data.challenge;
				}
				$scope.reason = $sce.trustAsHtml(data.reason);
				$scope.warning = $sce.trustAsHtml(data.warning);
				$scope.submitted = true;

				if (data.message.length) {
					toast(data.message);
				}
				$scope.submitting = false;

				// Trigger the tooltips once the DOM haas reloaded back into the main loop
				$timeout(function () {
					$(function () {
						$('[data-toggle="tooltip"]').tooltip();
					});
				}, 100);
			});
		} else {
			logger("SignupCtrl::requestValidate() - inputs are not valid", "err");
		};
	};

	// Called to see if a validation requests exists. Payload is setup in the Slim handler
	// page javascript, then passed in here.
	var prepareValidate = function () {
		if (!$scope.payload) {
			$scope.submitting = false;
			return;
		}
		logger("ValidateCtrl::prepare()", "inf");
		$scope.submitting = true;
		$scope.submitted = false;
		var tx = {};
		tx.payload = payload;
		apiSvc.queueLocal("user/validate/prepare", tx, function (data) {
			logger("ValidateCtrl::prepare(): response", "dbg");
			logger(data, "inf");
			if (data.success) {
				// Yay for us
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
			$timeout(function () {
				$(function () {
					$('[data-toggle="tooltip"]').tooltip();
				});
			}, 100);
		});
	};

	$scope.validate = function (challenge) {
		logger("ValidateCtrl::validate('" + challenge + "')", "inf");
		$scope.retireCaptcha();
		$scope.submitting = true;
		$scope.submitted = false;
		$scope.tx.challenge = challenge;
		apiSvc.callLocal("user/validate", $scope.tx, function (data) {
			logger("ValidateCtrl::validate('" + challenge + "') - response", "dbg");
			logger(data);
			$scope.account_validated = data.success;
			$scope.reason = $sce.trustAsHtml(data.reason);
			$scope.warning = $sce.trustAsHtml(data.warning);
			$scope.submitted = true;

			if (data.message.length) {
				toast(data.message);
			}
			$scope.submitting = false;

			// Trigger the tooltips once the DOM haas reloaded back into the main loop
			$timeout(function () {
				$(function () {
					$('[data-toggle="tooltip"]').tooltip();
				});
			}, 100);
		});
	};
	// Start the calling, but after a startup grace period
	$scope.decode_payload_api_call = $timeout(prepareValidate, 500);
}]);

// 10:15 20th pre assesment covid
// saturd