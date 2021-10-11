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
	<h1>Validate your account</h1>
	<div data-ng-show="warning">
		<div class="alert alert-warning" role="alert">
			<span data-ng-bind-html="warning"></span>
		</div>
	</div>
	<form data-ng-show="!payload" novalidate>
		<div data-ng-show="!validation_request_success && !validation_request_failure" novalidate>
			<p>
				You are about to request a revalidation of your account. For full details behind this, please look <a href="/wiki/account/validation">on the wiki</a>.
			</p>
			<br />
			<div class="row">
				<div class="col-md-6">
					<label for="email" class="form-label">Email address</label> <input type="email" class="form-control" id="email" data-ng-model="tx.email" data-ng-class="email_valid ? 'is-valid' : 'is-invalid'" data-ng-keyup="emailAddressValidate($event)" required>
					<div class="valid-feedback">Looks good!</div>
				</div>
				<div class="col-md-6">
					<label for="password" class="form-label">Password</label> <input type="password" class="form-control" id="password" data-ng-model="tx.password" data-ng-class="password_valid ? 'is-valid' : 'is-invalid'" data-ng-keyup="passwordValidate($event)" required>
					<div class="valid-feedback">Looks good!</div>
					<div class="invalid-feedback">8 characters, 1 uppercase, 1 lowercase, 1 digit and one of !@#$%^&amp;*</div>
				</div>
			</div>
			<br />
			<div class="row">
				<div class="col-12">
					<input class="form-check-input" type="checkbox" value="" id="tocCheck" data-ng-model="accept_toc" data-ng-class="accept_toc ? 'is-valid' : 'is-invalid'" data-ng-change="tocValidate($event)" required> <label class="form-check-label" for="tocCheck"> I agree to the <a href="/terms" target="new">terms and conditions</a></label>
					<div class="invalid-feedback">You must agree before we can process your request.</div>
				</div>
			</div>
			<div class="col-12">
				<br />
				<button class="btn btn-custom" data-ng-disabled="!submittable" data-ng-click="requestValidateAccount()" data-ng-hide="submitting">Validate Account</button>
				<img src="/gfx/ajax-loader-spinner.gif" alt="submitting" data-ng-show="submitting" />
			</div>
		</div>
		<div data-ng-show="validation_request_success">
			<div class="alert alert-success" role="alert">
				<p>An account validation request has been created, and you will be receiving an email to complete the validation process. During that process, you will be asked to select a keyword. This is the keyword you will need:</p>
				<h1 class="display-5">{{challenge}}</h1>
			</div>
		</div>
		<div data-ng-show="validation_request_failure">
			<div class="alert alert-danger" role="alert">
				<p>The account validation process failed.</p>
				<span data-ng-show="reason" data-ng-bind-html="reason"></span>
			</div>
		</div>
	</form>

	<div data-ng-show="payload">
		<div data-ng-show="!validation_success && !validation_failure">
			<p>Nearly there, Please select the challenge word you were presented with.</p>
			<img src="/gfx/ajax-loader-spinner.gif" alt="submitting" data-ng-show="submitting" />
			<button data-ng-show="!submitting" data-ng-repeat="choice in choices" class="btn btn-custom" data-ng-click="validate(this.choice)">{{choice}}</button>
		</div>
		<div data-ng-show="validation_success">
			<div class="alert alert-success" role="alert">
				<p>You have successfully validated your account - thank you.</p>
				<span>You should now be able to <a href="/">log in</a>.
				</span>
			</div>
		</div>
		<div data-ng-show="validation_failure">
			<div class="alert alert-danger" role="alert">
				<p>Validation process failed</p>
				<span data-ng-show="reason" data-ng-bind-html="reason"></span>
			</div>
		</div>
	</div>
</div>
<?php include_once '_footer.php'; ?>