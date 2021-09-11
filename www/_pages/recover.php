<?php include_once '_header.php';?>
<script src="https://www.google.com/recaptcha/api.js?render=<?php echo getRecaptchaSiteKey(); ?>"></script>
<script><?php
$str = "var payload = '" . @$args ["payload"] . "';";
$packer = new JavaScriptPacker ( $str );
$str = $packer->pack ();
echo trim ( $str );
?></script>
<div class="container-fluid text-center" data-ng-controller="RecoverCtrl">
	<h1>Recover your account</h1>
	<form data-ng-show="!payload" novalidate>
		<div data-ng-show="!recovery_request_success && !recovery_request_failure" novalidate>
			<p>
				You are about to request a recovery of your account. For full details behind this, please look <a href="/wiki/account/recovery">on the wiki</a>.
			</p>
			<br />
			<div class="row">
				<div class="col-md-6 offset-md-3">
					<label for="email" class="form-label">Email address</label> <input type="email" class="form-control" id="email" data-ng-model="tx.email" data-ng-class="email_valid ? 'is-valid' : 'is-invalid'" data-ng-keyup="emailAddressValidate($event)" required>
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
				<button class="btn btn-custom" data-ng-disabled="!request_submittable" data-ng-click="requestRecoverAccount()" data-ng-hide="submitting">Recover Account</button>
				<img src="/gfx/ajax-loader-spinner.gif" alt="submitting" data-ng-show="submitting" />
			</div>
		</div>
		<div data-ng-show="recovery_request_success">
			<div class="alert alert-success" role="alert">
				<p>An account recovery request has been created, and you will be receiving an email to complete the recovery process. During that process, you will be asked to select a keyword. This is the keyword you will need:</p>
				<h1 class="display-5">{{challenge}}</h1>
			</div>
		</div>
		<div data-ng-show="recovery_request_failure">
			<div class="alert alert-danger" role="alert">
				<p>The account recovery process failed.</p>
				<p data-ng-show="reason" data-ng-bind-html="reason"></p>
			</div>
		</div>
	</form>


	<div data-ng-show="payload">
		<div data-ng-show="!recovery_success && !recovery_failure">
			<p>Nearly there, Please enter the new password and the challenge word you were presented with.</p>
			<img src="/gfx/ajax-loader-spinner.gif" alt="submitting" data-ng-show="submitting" />
			<div data-ng-show="!submitting">
				<div class="row">
					<div class="col-md-4 offset-md-2">
						<label for="password" class="form-label">Password</label> <input type="password" class="form-control" id="password" data-ng-model="tx.password" data-ng-class="password_valid ? 'is-valid' : 'is-invalid'" data-ng-keyup="passwordValidate($event)" required>
						<div class="valid-feedback">Looks good!</div>
						<div class="invalid-feedback">8 characters, 1 uppercase, 1 lowercase, 1 digit and one of !@#$%^&amp;*</div>
					</div>
					<div class="col-md-4">
						<label for="password_verify" class="form-label">Re-enter password</label> <input type="password" class="form-control" id="password_verify" data-ng-model="password_verify" data-ng-class="password_verify_valid ? 'is-valid' : 'is-invalid'" data-ng-change="passwordVerifyValidate($event)" required>
						<div class="valid-feedback">Looks good!</div>
						<div class="invalid-feedback">Must match valid password</div>
					</div>
				</div>
				<br />
				<div class="row">
					<div class="col-12">
						<input class="form-check-input" type="checkbox" value="" id="tocCheck" data-ng-model="tx.accept_toc" data-ng-class="tx.accept_toc ? 'is-valid' : 'is-invalid'" data-ng-change="tocValidate($event)" required> <label class="form-check-label" for="tocCheck"> I agree to the <a href="/terms" target="new">terms and conditions</a></label>
						<div class="invalid-feedback">You must agree before we can process your request.</div>
					</div>
				</div>
				<br />
				<button data-ng-disabled="!submittable" data-ng-show="!submitting" data-ng-repeat="choice in choices" class="btn btn-custom" data-ng-click="recover(this.choice)">{{choice}}</button>
			</div>
		</div>
		<div data-ng-show="recovery_success">
			<div class="alert alert-success" role="alert">
				<p>
					You have successfully recovered your account. You should now be able to <a href="/">log in</a>.
				</p>
			</div>
		</div>
		<div data-ng-show="recovery_failure">
			<div class="alert alert-danger" role="alert">
				<p>Failure: coming soon</p>
				<p data-ng-show="reason" data-ng-bind-html="reason"></p>
			</div>
		</div>
	</div>
</div>

<?php include_once '_footer.php';?>