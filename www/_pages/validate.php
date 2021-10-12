<?php include_once '_header.php'; ?>
<script src="https://www.google.com/recaptcha/api.js?render=<?php echo getRecaptchaSiteKey(); ?>"></script>
<script>
	<?php
	$str = "var payload = '" . @$args["payload"] . "';";
	$packer = new JavaScriptPacker($str);
	$str = $packer->pack();
	echo trim($str);
	?>
</script>
<div class="container-fluid text-center" data-ng-controller="ValidateCtrl">
	<div data-ng-show="loading">
		<img src="/gfx/ajax-loader-bar.gif" alt="loading" />
	</div>
	<div data-ng-show="!loading">
		<h1>Validate your account</h1>
		<div data-ng-show="reason">
			<div class="alert alert-danger" role="alert">
				<p>Validation failed</p>
				<span data-ng-show="reason" data-ng-bind-html="reason"></span>
			</div>
		</div>
		<div data-ng-show="warning">
			<div class="alert alert-warning" role="alert">
				<span data-ng-bind-html="warning"></span>
			</div>
		</div>
		<div data-ng-show="!tx.token">
			<button class="btn btn-custom" data-ng-click="requestCaptcha()" data-ng-hide="submitting">Retry</button>
			<img src="/gfx/ajax-loader-bar.gif" alt="submitting" data-ng-show="submitting" />
		</div>
		<form data-ng-show="!payload && tx.token" novalidate>
			<div data-ng-show="!submitted && !account_validated" novalidate>
				<p>
					You are about to request a revalidation of your account. For full details behind this, please look <a href="/wiki/account/validation">on the wiki</a>.
				</p>
				<div class="progress" data-ng-show="recaptcha_progress">
					<div class="progress-bar progress-bar-striped progress-bar-animated" data-ng-class="recaptcha_progress<25 ? 'bg-danger' : recaptcha_progress<50 ? 'bg-warning' : 'bg-success'" role="progressbar" aria-valuenow="{{recaptcha_progress | number:0}}" aria-valuemin="0" aria-valuemax="100" data-ng-style="{'width': recaptcha_progress + '%'}"></div>
				</div>
				<div class="shadow alert alert-secondary data-screen" role="alert">
					<div class="row">
						<div class="col-md-6">
							<label for="email" class="form-label">Email address</label> <input type="email" class="form-control" id="email" data-ng-model="tx.email" data-ng-class="email_valid ? 'is-valid' : 'is-invalid'" data-ng-keyup="emailAddressValidate($event)" required>
							<div class="valid-feedback">Looks good!</div>
						</div>
						<div class="col-md-6">
							<label for="password" class="form-label">Password</label> <input type="password" class="form-control" id="password" data-ng-model="tx.password" data-ng-class="password_valid ? 'is-valid' : 'is-invalid'" data-ng-keyup="passwordValidate($event)" required>
							<div class="valid-feedback">Looks good!</div>
							<!-- <div class="invalid-feedback">8 characters, 1 uppercase, 1 lowercase, 1 digit and one of !@#$%^&amp;*</div> -->
						</div>
					</div>
					<div class="row">
						<div class="col-12">
							<input class="form-check-input" type="checkbox" value="" id="tocCheck" data-ng-model="accept_toc" data-ng-class="accept_toc ? 'is-valid' : 'is-invalid'" data-ng-change="tocValidate($event)" required> <label class="form-check-label" for="tocCheck"> I agree to the <a href="/terms" target="new">terms and conditions</a></label>
							<div class="invalid-feedback">You must agree before we can process your request.</div>
						</div>
					</div>
					<div class="col-12">
						<button class="btn btn-custom" data-ng-disabled="!submittable" data-ng-click="requestValidate()" data-ng-hide="submitting">Validate Account</button>
						<img src="/gfx/ajax-loader-spinner.gif" alt="submitting" data-ng-show="submitting" />
					</div>
				</div>
			</div>
		</form>
		<div data-ng-show="!payload && account_validated">
			<div class="alert alert-success" role="alert">
				<p>An account validation request has been created, and you will be receiving an email to complete the validation process. During that process, you will be asked to select a keyword. This is the keyword you will need:</p>
				<h1 class="display-5">{{challenge}}</h1>
			</div>
		</div>
		<!-- A payload variable is set up when a user got here from a link in an email -->
		<div data-ng-show="payload && !reason">
			<div data-ng-show="!submitted">
				<p>Nearly there, Please select the challenge word you were presented with.</p>
				<div class="progress" data-ng-show="recaptcha_progress">
					<div class="progress-bar progress-bar-striped progress-bar-animated" data-ng-class="recaptcha_progress<25 ? 'bg-danger' : recaptcha_progress<50 ? 'bg-warning' : 'bg-success'" role="progressbar" aria-valuenow="{{recaptcha_progress | number:0}}" aria-valuemin="0" aria-valuemax="100" data-ng-style="{'width': recaptcha_progress + '%'}"></div>
				</div>
				<div class="shadow alert alert-secondary data-screen" role="alert" data-ng-show="!submitting">
					<button data-ng-repeat="choice in choices" class="btn btn-custom" data-ng-click="validate(this.choice)">{{choice}}</button>
				</div>
				<img src="/gfx/ajax-loader-spinner.gif" alt="submitting" data-ng-show="submitting" />
			</div>
			<div data-ng-show="account_validated">
				<div class="alert alert-success" role="alert">
					<p>You have successfully validated your account - thank you.</p>
					<span>You should now be able to <a href="/">log in</a>.
					</span>
				</div>
			</div>
		</div>
	</div>
</div>
<?php include_once '_footer.php'; ?>