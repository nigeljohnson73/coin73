<?php include_once(__DIR__ . "/_header.php") ?>
<script src="https://www.google.com/recaptcha/api.js?render=<?php echo getRecaptchaSiteKey(); ?>"></script>
<div class="container-fluid text-center" data-ng-controller="HomeCtrl">

	<div data-ng-show="session_ended">
		<div class="shadow alert alert-secondary" role="alert">
			<h2>Your session has ended</h2>
			<span>For security reasons, please close your browser or refresh this page.</span>
		</div>
	</div>

	<div data-ng-show="!session_ended">




		<img src="/gfx/ajax-loader-bar.gif" alt="submitting" data-ng-show="loading" />
		<div data-ng-show="!loading">

			<!-- The user page shows if there is a 'user' object ot support it -->
			<div data-ng-show="user">
				<!-- 				<h1>Account details</h1> -->
				<!-- 				<div class="row"> -->
				<div class="shadow alert alert-secondary data-screen" role="alert">
					<div class="row row-cols-3">
						<div class="col-2 text-start">
							<span class="form-check form-switch" data-ng-show="!getting"> <input title="Auto refresh balance every minute" class="form-check-input" type="checkbox" data-ng-model="auto_refresh_balance" id="auto-upload-switch"> <i title="Refresh balance now" data-ng-show="!getting && !auto_refresh_balance" data-ng-click="loadUser(true)" class="bi bi-arrow-repeat"></i>
							</span>
						</div>
						<div class="col-8">
							<h2>Your Balance</h2>
						</div>
						<div class="col-2 text-end">
							<i title="Show transaction screen" data-ng-show="!transaction_visible" class="bi bi bi-arrow-right-square-fill" data-ng-click="prepareTransaction()"></i>
							<!-- 							<i title="Hide transaction screen" data-ng-show="transaction_visible" class="bi bi-x-square-fill" data-ng-click="transaction_visible=false"></i> -->
						</div>
						<div class="col-12">
							<h1 class="display-1">
								<span data-highlight-on-change="{{user.balance}}">{{user.balance | number:4}}</span>
							</h1>
							<span data-ng-show="!getting">{{user.dollar | currency : "$" }}</span><span data-ng-show="getting" class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>&nbsp;<span data-ng-show="getting">Loading...</span>
						</div>
					</div>
					<!-- 					</div> -->
				</div>





				<!-- 			<div class="row"> -->
				<div class="progress" data-ng-show="recaptcha_progress">
					<div class="progress-bar progress-bar-striped progress-bar-animated" data-ng-class="recaptcha_progress<25 ? 'bg-danger' : recaptcha_progress<50 ? 'bg-warning' : 'bg-success'" role="progressbar" aria-valuenow="{{recaptcha_progress | number:0}}" aria-valuemin="0" aria-valuemax="100" data-ng-style="{'width': recaptcha_progress + '%'}"></div>
				</div>
				<!-- 			</div> -->
				<div data-ng-show="transaction_visible">
					<div class="shadow alert alert-secondary data-screen transaction-screen" role="alert">




						<div class="row">
							<div class="col-8 offset-2">
								<h2>Send Transaction</h2>
							</div>
							<div class="col-2 text-end">
								<i title="Hide transaction screen" data-ng-show="transaction_visible" class="bi bi-x-square-fill" data-ng-click="cancelTransaction()"></i>
							</div>
						</div>
						<div data-ng-show="reason">
							<div class="alert alert-danger" role="alert">
								<p>Transaction failed.</p>
								<span data-ng-show="reason" data-ng-bind-html="reason"></span>
							</div>
							<button class="btn btn-custom" data-ng-click="prepareTransaction()" data-ng-hide="preparing">Retry</button>
						</div>
						<div data-ng-show="preparing">
							<img src="/gfx/ajax-loader-bar.gif" alt="preparing" data-ng-show="preparing" />
						</div>
						<div data-ng-show="transaction_sent">
							<div class="alert alert-success" role="alert">
								<span>Transaction Sent</span>
							</div>
						</div>
						<div data-ng-show="!reason && !preparing && !transaction_sent">
							<div class="row">
								<div class="col-12 col-sm-8 col-md-9 col-lg-10">
									<label for="recipient" class="form-label">Recipient</label> <input type="text" class="form-control" id="recipient" data-ng-model="txn.recipient" data-ng-class="recipient_valid ? 'is-valid' : 'is-invalid'" data-ng-change="recipientValidate($event)" required>
									<div class="invalid-feedback">{{recipient_reason}}</div>
								</div>
								<div class="col-12 col-sm-4 col-md-3 col-lg-2">
									<label for="amount" class="form-label">Amount</label> <input type="number" class="form-control" id="amount" data-ng-model="txn.amount" data-ng-class="amount_valid ? 'is-valid' : 'is-invalid'" data-ng-change="amountValidate($event)" required>
									<div class="invalid-feedback">{{amount_reason}}</div>
								</div>
							</div>
							<div class="row">
								<div class="col-12">
									<label hidden for="message" class="form-label">Message</label> <input type="text" class="form-control" id="message" data-ng-model="txn.message" placeholder="Optionally, enter a message">
								</div>
							</div>
							<div class="row">
								<div class="col-12">
									<button data-ng-disabled="!txn_submittable" class="btn btn-custom" data-ng-hide="sending" data-ng-click="sendTransaction()">Send</button>
									<img src="/gfx/ajax-loader-spinner.gif" alt="sending" data-ng-show="sending" />
								</div>
							</div>
						</div>



					</div>
				</div>






				<!-- 			<div> -->
				<!-- 				<p> -->
				<!-- 					You'll need a mining rig/script. <a href="/wiki/mining/script">Write your own</a> if you don't have access to the ones here. -->
				<!-- 				</p> -->
				<!-- 			</div> -->




				<!-- 				<div class="row"> -->
				<div class="shadow alert alert-secondary data-screen wallet-details" role="alert">
					<div class="row">
						<div class="col-8 offset-2">
							<h2>Your Wallet</h2>
						</div>
						<div class="col-2 text-end">
							<i title="Show wallet details" data-ng-show="!wallet_visible" class="bi bi-box-arrow-down" data-ng-click=" wallet_visible=true"></i> <i title="Hide wallet details" data-ng-show="wallet_visible" class="bi bi-box-arrow-up" data-ng-click=" wallet_visible=false"></i>
						</div>
						<div class="col-12" data-ng-show="wallet_visible">
							<div id="qr-walletid-holder">
								<div id="qr-walletid"></div>
							</div>
						</div>
						<div data-ng-show="wallet_visible" class="col-12">
							<p class="user-details wallet-id text-truncate">{{user.public_key}}</p>
						</div>
					</div>
				</div>
				<!-- 				</div> -->




				<div class="row">
					<div class="col">
						<button class="btn btn-custom" data-ng-hide="submitting" data-ng-click="logout()">Logout</button>
						<img src="/gfx/ajax-loader-spinner.gif" alt="submitting" data-ng-show="submitting" />
					</div>
				</div>
			</div>
			<!-- End of user page -->





			<!-- Login page shows if no 'user' object is present -->
			<div data-ng-show="!user">
				<!-- RECAPTCHA progress -->
				<div class="progress" data-ng-show="recaptcha_progress">
					<div class="progress-bar progress-bar-striped progress-bar-animated" data-ng-class="recaptcha_progress<25 ? 'bg-danger' : recaptcha_progress<50 ? 'bg-warning' : 'bg-success'" role="progressbar" aria-valuenow="{{recaptcha_progress | number:0}}" aria-valuemin="0" aria-valuemax="100" data-ng-style="{'width': recaptcha_progress + '%'}"></div>
				</div>
				<div class="shadow alert alert-secondary data-screen" role="alert">
					<h1>Welcome</h1>
					<div data-ng-show="reason">
						<div class="alert alert-danger" role="alert">
							<p>Login failed.</p>
							<span data-ng-show="reason" data-ng-bind-html="reason"></span>
						</div>
					</div>
					<div data-ng-show="!disabled && !tx.token">
						<button class="btn btn-custom" data-ng-click="requestLoginCaptcha()" data-ng-hide="submitting">Retry</button>
						<img src="/gfx/ajax-loader-bar.gif" alt="submitting" data-ng-show="submitting" />
					</div>
					<form novalidate>
						<div data-ng-show="!disabled && tx.token">
							<div class="row">
								<div class="col-md-6">
									<label for="email" class="form-label">Email address</label> <input type="email" class="form-control" id="email" data-ng-model="tx.email" data-ng-class="email_valid ? 'is-valid' : 'is-invalid'" data-ng-keyup="emailAddressValidate($event)" required>
								</div>
								<div class="col-md-6">
									<label for="password" class="form-label">Password</label> <input type="password" class="form-control" id="password" data-ng-model="tx.password" data-ng-class="password_valid ? 'is-valid' : 'is-invalid'" data-ng-keyup="passwordValidate($event)" required>
								</div>
							</div>
							<div class="row">
								<div class="col-12">
									<input class="form-check-input" type="checkbox" value="" id="tocCheck" data-ng-model="tx.accept_toc" data-ng-class="tx.accept_toc ? 'is-valid' : 'is-invalid'" data-ng-change="tocValidate($event)" required> <label class="form-check-label" for="tocCheck"> I agree to the <a href="/terms" target="new">terms and
											conditions</a></label>
								</div>
							</div>
							<div class="col-12">
								<br /> <img src="/gfx/ajax-loader-spinner.gif" alt="submitting" data-ng-show="submitting" />
								<button class="btn btn-custom" data-ng-disabled="!login_submittable" data-ng-click="login()" data-ng-hide="submitting">Login</button>
								<a class="btn btn-custom" href="/signup">Signup</a> <a class="btn btn-custom" href="/recover">Recover account</a>
							</div>
						</div>
					</form>
				</div>
				<!-- End of login page -->



			</div>
			<!-- alert -->
		</div>
		<!-- session -->


	</div>
	<!-- loading -->
</div>
<!-- container -->

<?php include_once(__DIR__ . "/_footer.php") ?>