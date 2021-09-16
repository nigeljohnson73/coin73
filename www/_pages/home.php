<?php include_once(__DIR__."/_header.php")?>
<script src="https://www.google.com/recaptcha/api.js?render=<?php echo getRecaptchaSiteKey(); ?>"></script>
<div class="container-fluid text-center" data-ng-controller="HomeCtrl">

	<img src="/gfx/ajax-loader-bar.gif" alt="submitting" data-ng-show="loading" />
	<div data-ng-show="!loading">
		<div data-ng-show="user">
			<h1>Account details</h1>
			<div class="row">
				<div class="col-md-12">
					<div class="shadow alert alert-secondary" role="alert">
						<p>
							Your Wallet ID <span class="icon-popover"><i class="bi bi-info-circle-fill" data-bs-toggle="popover" title="Wallet ID" data-bs-content="You will use this in any miners you set up and where you can receive transactions."></i></span>
						</p>
						<p class="user-details wallet-id text-truncate">{{user.public_key}}</p>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div class="shadow alert alert-secondary" role="alert">
						<p>Your Balance</p>
						<h1 class="display-1">{{user.balance | number:4}}</h1>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<button class="btn btn-custom" data-ng-hide="submitting" data-ng-click="logout()">Logout</button>
					<img src="/gfx/ajax-loader-spinner.gif" alt="submitting" data-ng-show="submitting" class="float-end" />
				</div>
			</div>
		</div>
		<form data-ng-show="!user" novalidate>
			<h1>Welcome</h1>
			<div data-ng-show="reason">
				<div class="alert alert-danger" role="alert">
					<p>Login failed.</p>
					<span data-ng-show="reason" data-ng-bind-html="reason"></span>
				</div>
			</div>
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
					<input class="form-check-input" type="checkbox" value="" id="tocCheck" data-ng-model="tx.accept_toc" data-ng-class="tx.accept_toc ? 'is-valid' : 'is-invalid'" data-ng-change="tocValidate($event)" required> <label class="form-check-label" for="tocCheck"> I agree to the <a href="/terms" target="new">terms and
							conditions</a></label>
					<div class="invalid-feedback">You must agree before we can process your request.</div>
				</div>
			</div>
			<div class="col-12">
				<br />
				<button class="btn btn-custom" data-ng-disabled="!submittable" data-ng-click="login()" data-ng-hide="submitting">Login</button>
				<img src="/gfx/ajax-loader-spinner.gif" alt="submitting" data-ng-show="submitting" />
			</div>
			<div class="col-12">
				<br /> <a class="btn btn-custom" href="/signup">Signup</a> <a class="btn btn-custom" href="/recover">Recover account</a>
			</div>
		</form>
	</div>
</div>

<?php include_once(__DIR__."/_footer.php")?>
