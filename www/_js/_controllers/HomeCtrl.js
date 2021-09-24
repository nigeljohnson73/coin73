app.controller('HomeCtrl', ["$scope", "$timeout", "$interval", "$sce", "apiSvc", function($scope, $timeout, $interval, $sce, apiSvc) {
	$scope.auto_refresh_balance = false;
	$scope.loading = true;
	$scope.submitting = false;
	$scope.login_failure = false;
	$scope.user = null;
	$scope.reason = "";

	var recaptcha_action = "login";
	$scope.tx = {};
	$scope.tx.email = "";
	$scope.tx.password = "";
	$scope.tx.accept_toc = "";
	$scope.tx.token = "";
	$scope.tx.action = recaptcha_action;
	$scope.email_valid = false;
	$scope.password_valid = false;
	$scope.submittable = false;

	$scope.checkValidation = function() {
		$scope.submittable = $scope.email_valid && $scope.password_valid && $scope.tx.accept_toc;
		//console.log("************************************************************");
		//console.log("Email valid     :", $scope.email_valid, $scope.tx.email);
		//console.log("Password valid  :", $scope.password_valid, $scope.tx.password);
		//console.log("TOC valid       :", $scope.tx.accept_toc, $scope.tx.accept_toc);
		//console.log("Form submittable:", $scope.submittable);
	};
	$scope.emailAddressValidate = function() {
		$scope.email_valid = $scope.tx.email && ($scope.tx.email.length > 0);
		$scope.checkValidation();
	};
	$scope.passwordValidate = function() {
		var ok_password = new RegExp("{{VALID_PASSWORD_REGEX}}");
		$scope.password_valid = $scope.tx.password && ok_password.test($scope.tx.password);
		$scope.checkValidation();
	};
	$scope.tocValidate = function() {
		$scope.checkValidation();
	};

	//	var ping = function() {
	//		apiSvc.queuePublic("ping", {}, function(data) {
	//			logger("HomeCtrl::ping()", "dbg");
	//			logger(data, "inf");
	//			if (data.success) {
	//				// Yay for us
	//			}
	//			if (data.message.length) {
	//				toast(data.message);
	//			}
	//		});
	//	};

	var loadUser = function(force = false) {
		//console.log("loadUser(force='" + force + "', auto='" + $scope.auto_refresh_balance + "')");
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
			logger("HomeCtrl::loadUser()", "dbg");
			logger(data, "inf");
			$scope.user = data.user;

			if (data.success) {
				// Yay for us
			} else {
				// Since a user is not loaded, assume that's why we are here.
				grecaptcha.ready(function() {
					grecaptcha.execute('{{RECAPTCHA_SITE_KEY}}', { action: recaptcha_action }).then(function(token) {
						logger("HomeCtrl::loadUser() - recieved a RECAPTCHA token", "inf");
						$scope.tx.token = token;
					});
				});
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

	$scope.loadUser = loadUser;

	$scope.login = function() {
		$scope.submitting = true;
		$scope.login_failure = false;
		apiSvc.callLocal("user/login", $scope.tx, function(data) {
			logger("HomeCtrl::login()", "inf");
			logger(data, "inf");
			$scope.user = data.user;
			$scope.tx.email = "";
			$scope.tx.password = "";
			$scope.tx.accept_toc = "";
			$scope.email_valid = false;
			$scope.password_valid = false;
			$scope.submittable = false;
			$scope.reason = "";


			if (data.success) {
				// Yay for us
			} else {
				$scope.login_failure = true;
				// Reset in case we trying again.
				grecaptcha.ready(function() {
					grecaptcha.execute('{{RECAPTCHA_SITE_KEY}}', { action: recaptcha_action }).then(function(token) {
						logger("HomeCtrl::loadUser() - recieved a RECAPTCHA token", "inf");
						$scope.tx.token = token;
					});
				});
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
		$scope.submitting = true;
		$scope.login_failure = false;
		apiSvc.callLocal("user/logout", {}, function(data) {
			logger("HomeCtrl::logout()", "inf");
			logger(data, "inf");
			$scope.user = data.user;

			if (data.success) {
				// Reset in case we trying again.
				grecaptcha.ready(function() {
					grecaptcha.execute('{{RECAPTCHA_SITE_KEY}}', { action: recaptcha_action }).then(function(token) {
						logger("HomeCtrl::loadUser() - recieved a RECAPTCHA token", "inf");
						$scope.tx.token = token;
					});
				});
			} else {
				$scope.login_failure = true;
			}
			$scope.reason = $sce.trustAsHtml(data.reason);

			if (data.message.length) {
				toast(data.message);
			}
			$scope.submitting = false;
		});
	};

	// Start the calling, but after a startup grace period
	$scope.load_user_api_call = $timeout(loadUser, 100);
	$scope.load_user_api_interval = $interval(loadUser, 60000);
	//	$scope.ping_api_call = $timeout(ping, 500);

}]);

