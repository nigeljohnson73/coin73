<?php include_once '_header.php';?>
<script src="https://www.google.com/recaptcha/api.js?render=<?php echo getRecaptchaSiteKey(); ?>"></script>
<div class="container-fluid text-center" data-ng-controller="SignupCtrl">
	<form data-ng-show="!account_created && !account_not_created" novalidate>
		<h1>Sign up</h1>
		<p>Thanks for wanting to join the alliance, however we are not accepting requests at this point. The interface below is just for testing purposes, but please check back again soon.</p>
		<p>Passwords need to be strong: at least 8 charaters long, with at least 1 lower case letter, 1 upper case letter, 1 digit and one special character (!@#$%^&amp;*).</p>
		<p>You will recieve an email at the address you provide to complete the sign-up process, so please ensure it is your email address.</p>
		<br />

		<div class="row">
			<div class="col-md-4">
				<label for="email" class="form-label">Email address</label> <input type="email" class="form-control" id="email" data-ng-model="tx.email" data-ng-class="email_valid ? 'is-valid' : 'is-invalid'" data-ng-keyup="emailAddressValidate($event)" required>
				<div class="valid-feedback">Looks good!</div>
			</div>
			<div class="col-md-4">
				<label for="password" class="form-label">Password</label> <input type="password" class="form-control" id="password" data-ng-model="tx.password" data-ng-class="password_valid ? 'is-valid' : 'is-invalid'" data-ng-keyup="passwordValidate($event)" required>
				<div class="valid-feedback">Looks good!</div>
				<div class="invalid-feedback">8 characters, 1 uppercase, 1 lowercase, 1 digit and one of !@#$%^&amp;*</div>
			</div>
			<div class="col-md-4">
				<label for="password_verify" class="form-label">Re-enter password</label> <input type="password" class="form-control" id="password_verify" data-ng-model="password_verify" data-ng-class="password_verify_valid ? 'is-valid' : 'is-invalid'" data-ng-change="passwordVerifyValidate($event)" required>
				<div class="valid-feedback">Looks good!</div>
				<div class="invalid-feedback">Must match password</div>
			</div>
		</div>
		<br />
		<div class="row">
			<div class="col-12">
				<input class="form-check-input" type="checkbox" value="" id="tocCheck" data-ng-model="accept_toc" data-ng-class="accept_toc ? 'is-valid' : 'is-invalid'" data-ng-change="tocValidate($event)" required> <label class="form-check-label" for="tocCheck"> I agree to the <a href="/terms" target="new">terms and conditions</a></label>
				<div class="invalid-feedback">You must agree before you will be given an account.</div>
			</div>
		</div>
		<div class="col-12">
			<br />
			<button class="btn btn-custom" data-ng-disabled="!submittable" data-ng-click="requestAccount()" data-ng-hide="submitting">Request Account</button>
			<img src="/gfx/ajax-loader-spinner.gif" alt="submitting" data-ng-show="submitting" />
		</div>
	</form>
	<div data-ng-show="account_created">
		<div class="alert alert-success" role="alert">
			<p>An account has been created, and you will be receiving an email to complete the sign-up process. During that process, you will be asked to select a keyword. This is the keyword you will need:</p>
			<h1 class="display-5">{{challenge}}</h1>
		</div>
	</div>
	<div data-ng-show="account_not_created">
		<div class="alert alert-danger" role="alert">
			<p>The account creation process failed.</p>
		</div>
	</div>
</div>

<?php include_once '_footer.php';?>