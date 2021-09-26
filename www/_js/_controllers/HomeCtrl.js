app.controller('HomeCtrl', ["$scope", "$timeout", "$interval", "$sce", "apiSvc", function($scope, $timeout, $interval, $sce, apiSvc) {
	$scope.auto_refresh_balance = false;
	$scope.loading = true;
	$scope.submitting = false;
	$scope.login_failure = false;
	$scope.txn_failure = false;
	$scope.user = null;
	$scope.reason = "";

	$scope.recaptcha_progress = 0;
	$scope.recaptcha_started = null;
	$scope.recaptcha_timeout_call = null;
	$scope.recaptcha_timeout = 115;
	$scope.recaptcha_timeout_reason = "You took to long to complete the process. Please press the button below when you're ready to continue.";
	var login_action = "login";
	var txn_action = "transaction";

	$scope.tx = {};
	$scope.tx.email = "";
	$scope.tx.password = "";
	$scope.tx.accept_toc = "";
	$scope.tx.token = "";
	$scope.tx.action = login_action;
	$scope.email_valid = false;
	$scope.password_valid = false;
	$scope.login_submittable = false;

	$scope.txn = {};
	$scope.txn.recipient = "";
	$scope.txn.amount = 0;
	$scope.txn.message = "";
	$scope.txn.token = "";
	$scope.txn.action = txn_action;
	$scope.recipient_valid = false;
	$scope.amount_valid = false;
	$scope.txn_submittable = false;
	$scope.amount_enter = "You must enter an amount";
	$scope.amount_reason = $scope.amount_enter;
	$scope.recipient_enter = "Invalid wallet ID format";
	$scope.recipient_reason = $scope.recipient_enter;
	$scope.transaction_sent = false;

	function pause() {
		// Use this if you want to slow progress down cuz the API/GUI is being a pig and you want to debug it
		//var start = new Date().getTime();
		//while (new Date().getTime() < start + 1000);
	}

	$scope.checkTransactionValidation = function() {
		$scope.txn_submittable = $scope.recipient_valid && $scope.amount_valid;
		//console.log("************************************************************");
		//console.log("Recipient valid :", $scope.recipient_valid);
		//console.log("Amount valid    :", $scope.amount_valid, $scope.txn.amount);
		//console.log("Message         :", $scope.txn.message);
		//console.log("Form submittable:", $scope.txn_submittable);
	};
	$scope.recipientValidate = function() {
		$scope.recipient_valid = $scope.txn.recipient && ($scope.txn.recipient.length == 130) && ($scope.txn.recipient != $scope.user.public_key);
		if ($scope.txn.recipient == $scope.user.public_key) {
			$scope.recipient_reason = "You can't send money to yourself";
		} else {
			$scope.recipient_reason = $scope.recipient_enter;
		}
		$scope.checkTransactionValidation();
	};

	$scope.amountValidate = function() {
		$scope.amount_valid = $scope.txn.amount && ($scope.txn.amount > 0) && ($scope.txn.amount <= $scope.user.balance);
		if (!$scope.txn.amount || $scope.txn.amount <= 0) {
			$scope.amount_reason = $scope.amount_enter;
		}
		if ($scope.txn.amount > $scope.user.balance) {
			$scope.amount_reason = "You can't spend what you don't have";
		}
		$scope.checkTransactionValidation();
	};


	$scope.prepareTransaction = function() {
		$scope.requestTransactionCaptcha();
		$scope.txn.recipient = "";
		$scope.txn.amount = 0;
		$scope.txn.message = "";
		$scope.txn.token = "";
		$scope.transaction_sent = false;
		$scope.transaction_visible = true;
		$scope.reason = null;
		$scope.preparing = true;
	};
	$scope.cancelTransaction = function() {
		$scope.preparing = false;
		$scope.transaction_visible = false;
		$scope.retireCaptcha();
	};

	$scope.sendTransaction = function() {
		logger("HomeCtrl::sendTransaction() called", "dbg");
		$scope.retireCaptcha();

		$scope.sending = true;
		apiSvc.callLocal("transaction/send", $scope.txn, function(data) {
			logger("HomeCtrl::sendTransaction()", "dbg");
			logger(data, "inf");
			pause();
			$scope.sending = false;


			if (data.success) {
				$scope.transaction_sent = true;
				// Yay for us
			} else {
			}
			$scope.reason = $sce.trustAsHtml(data.reason);

			if (data.message.length) {
				toast(data.message);
			}
			$scope.submitting = false;
		});
	};







	$scope.checkLoginValidation = function() {
		$scope.login_submittable = $scope.email_valid && $scope.password_valid && $scope.tx.accept_toc;
		//console.log("************************************************************");
		//console.log("Email valid     :", $scope.email_valid, $scope.tx.email);
		//console.log("Password valid  :", $scope.password_valid, $scope.tx.password);
		//console.log("TOC valid       :", $scope.tx.accept_toc, $scope.tx.accept_toc);
		//console.log("Form login_submittable:", $scope.login_submittable);
	};
	$scope.emailAddressValidate = function() {
		$scope.email_valid = $scope.tx.email && ($scope.tx.email.length > 0);
		$scope.checkLoginValidation();
	};
	$scope.passwordValidate = function() {
		var ok_password = new RegExp("{{VALID_PASSWORD_REGEX}}");
		$scope.password_valid = $scope.tx.password && ok_password.test($scope.tx.password);
		$scope.checkLoginValidation();
	};
	$scope.tocValidate = function() {
		$scope.checkLoginValidation();
	};

	$scope.updateCaptchaProgress = function() {
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

	$scope.retireCaptcha = function() {
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

	$scope.requestTransactionCaptcha = function() {
		logger("HomeCtrl::requestTransactionCaptcha() called", "dbg");
		$scope.retireCaptcha();
		//console.trace();

		$scope.preparing = true;
		$scope.reason = null;
		grecaptcha.execute('{{RECAPTCHA_SITE_KEY}}', { action: txn_action }).then(function(token) {
			$scope.recaptcha_progress = 100;
			$scope.recaptcha_started = new Date().getTime();
			$scope.recaptcha_progress_interval = $interval($scope.updateCaptchaProgress, 1000);

			logger("HomeCtrl::requestTransactionCaptcha() - recieved a RECAPTCHA token", "dbg");
			pause();
			$scope.loading = false;
			$scope.preparing = false;
			$scope.txn.token = token;
			$scope.recaptcha_timeout_call = $timeout(function() {
				$scope.txn.token = null;
				$scope.reason = $sce.trustAsHtml($scope.recaptcha_timeout_reason);
			}, $scope.recaptcha_timeout * 1000);
		});

	};

	$scope.requestLoginCaptcha = function() {
		logger("HomeCtrl::requestLoginCaptcha() called", "dbg");
		$scope.retireCaptcha();
		//console.trace();

		$scope.submitting = true;
		$scope.reason = null;
		grecaptcha.execute('{{RECAPTCHA_SITE_KEY}}', { action: login_action }).then(function(token) {
			$scope.recaptcha_progress = 100;
			$scope.recaptcha_started = new Date().getTime();
			$scope.recaptcha_progress_interval = $interval($scope.updateCaptchaProgress, 1000);

			logger("HomeCtrl::requestLoginCaptcha() - recieved a RECAPTCHA token", "dbg");
			pause();
			$scope.loading = false;
			$scope.submitting = false;
			$scope.tx.token = token;
			$scope.recaptcha_timeout_call = $timeout(function() {
				$scope.tx.token = null;
				$scope.reason = $sce.trustAsHtml($scope.recaptcha_timeout_reason);
			}, $scope.recaptcha_timeout * 1000);
		});

	};

	$scope.renderwalletQr = function() {
		$("#qr-walletid canvas").remove();

		QrCreator.render({
			text: $scope.user.public_key,
			radius: 0.5, // 0.0 to 0.5
			ecLevel: 'H', // L, M, Q, H
			fill: '#3b0084', // foreground color
			background: null, // color or null for transparent
			size: 256 // in pixels
		}, document.querySelector('#qr-walletid'));

	};

	$scope.loadUser = function(force = false) {
		logger("HomeCtrl::loadUser(force='" + force + "', auto='" + $scope.auto_refresh_balance + "') called", "dbg");

		if (!$scope.loading) {
			if (!$scope.auto_refresh_balance) {
				// Force seems to be a number when called through interval
				if (typeof force != "boolean") {
					//console.log("not loading, auto refreshing");
					return;
				} else if (!force) {
					//console.log("not forcing");
					return;
				}
			}
		}
		$scope.getting = true;
		apiSvc.queueLocal("user", {}, function(data) {
			logger("HomeCtrl::loadUser() - API returned", "dbg");
			logger(data, "inf");
			pause();
			$scope.user = data.user;

			if (data.success) {
				logger("HomeCtrl::loadUser() - success", "dbg");
				$scope.retireCaptcha();
				$scope.renderwalletQr();
				// Yay for us
			} else {
				// Since a user is not loaded, assume that's why we are here.
				logger("HomeCtrl::loadUser() - failed", "dbg");
				$scope.requestLoginCaptcha();
			}
			$scope.disabled = data.disabled;
			$scope.reason = $sce.trustAsHtml(data.reason);

			if (data.message.length) {
				toast(data.message);
			}
			$scope.loading = false;
			$scope.getting = false;
		});
	};

	$scope.login = function() {
		logger("HomeCtrl::login() called", "dbg");
		$scope.retireCaptcha();
		$scope.submitting = true;
		$scope.login_failure = false;
		$scope.reason = "";
		apiSvc.callLocal("user/login", $scope.tx, function(data) {
			logger("HomeCtrl::login() API returned", "dbg");
			logger(data, "inf");
			pause();
			$scope.user = data.user;
			$scope.tx.email = "";
			$scope.tx.password = "";
			$scope.tx.accept_toc = "";
			$scope.email_valid = false;
			$scope.password_valid = false;
			$scope.login_submittable = false;

			if (data.success) {
				logger("HomeCtrl::login() success", "dbg");
				$scope.renderwalletQr();
				// Yay for us
			} else {
				logger("HomeCtrl::login() failed", "dbg");
				$scope.login_failure = true;
				$scope.requestLoginCaptcha();
			}
			$scope.disabled = data.disabled;
			$scope.reason = $sce.trustAsHtml(data.reason);

			if (data.message.length) {
				toast(data.message);
			}
			$scope.submitting = false;
		});
	};

	$scope.logout = function() {
		logger("HomeCtrl::logout() called", "dbg");
		$scope.retireCaptcha();

		$scope.user = null;
		$scope.loading = true;
		$scope.submitting = true;
		$scope.reason = null;
		$scope.login_failure = null;
		$scope.tx.token = null;
		apiSvc.callLocal("user/logout", {}, function(data) {
			logger("HomeCtrl::logout() API returned", "dbg");
			logger(data, "inf");
			pause();
			$scope.user = data.user;

			if (data.success) {
				logger("HomeCtrl::logout() success", "dbg");
				// Reset in case we want to login again.
				$scope.requestLoginCaptcha();

			} else {
				logger("HomeCtrl::logout() failed", "dbg");
				$scope.login_failure = true;
			}
			$scope.reason = $sce.trustAsHtml(data.reason);

			if (data.message.length) {
				toast(data.message);
			}
			//$scope.submitting = false;
		});
	};

	// Start the calling, but after a startup grace period
	$scope.load_user_api_call = $timeout($scope.loadUser, 100);
	$scope.load_user_api_interval = $interval($scope.loadUser, 60000);

}]);

