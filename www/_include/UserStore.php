<?php
include_once (__DIR__ . "/DataStore.php");

// Store this globally as wallet access is used in a few places... could move UserStore to a singleton??
class UserStore extends DataStore {
	private static $_wallet = array ();
	private static $_updated_wallet = array ();

	protected function __construct() {
		logger ( LL_DBG, "UserStore::UserStore()" );

		parent::__construct ( "User" );

		$this->addField ( "email", "String", true, true ); // indexed and key
		$this->addField ( "guid", "String", true ); // for the password mechanism, and passing around instead of the email address
		$this->addField ( "password", "String" );
		$this->addField ( "public_key", "String", true ); // indexed for wallet management
		$this->addField ( "balance", "Float", true );
		$this->addField ( "created", "Integer", true ); // timestamp to denote creation - birthdays etc ??? :D
		$this->addField ( "validated", "Integer", true ); // Send validation request if this is older than X days and both validation_reminded and validation_requested are 0. Set this when validate suceeds
		$this->addField ( "validation_reminded", "Integer", true ); // Set by the system when we are approaching the validation window
		$this->addField ( "validation_requested", "Integer", true ); // Set if the system set it and sent a reminder or the user is requesting a vaildation
		$this->addField ( "validation_nonce", "String", true ); // This exists if are waiting on an email reponse to the validation request
		$this->addField ( "validation_data", "String" ); // This is the validation string array that will hold the choices - lock the account while this is processing
		$this->addField ( "recovery_requested", "Integer", true ); // Set if the user is requesting a recovery
		$this->addField ( "recovery_nonce", "String", true ); // This exists if are waiting on an email reponse to the recovery request
		$this->addField ( "recovery_data", "String" ); // This is the validation string array that will hold the choices - lock the account while this is processing
		$this->addField ( "locked", "Integer", true ); // the timestamp of the locking of this account. X days after this, funds will be re-distributed, cleared on account recovery
		$this->addField ( "logged_in", "Integer", true ); // the timestamp of the last login of this account.

		$this->init ();
	}

	public function getItemByValidationNonce($key) {
		$gql = "SELECT * FROM " . $this->kind . " WHERE validation_nonce = @key";
		$data = $this->obj_store->fetchOne ( $gql, [ 
				'key' => $key
		] );
		// echo "UserStore::getItemByValidationNonce('validation_nonce'=>'" . $key . "')\n";
		// echo " '$gql'\n";
		return ($data) ? ($data->getData ()) : ($data);
	}

	public function getItemByPublicKey($key) {
		if (isset ( self::$_wallet [$key] )) {
			logger ( LL_XDBG, "UserStore::getItemByPublicKey() - returning cached values" );
			return self::$_wallet [$key]->getData ();
		}

		$gql = "SELECT * FROM " . $this->kind . " WHERE public_key = @key";
		$data = $this->obj_store->fetchOne ( $gql, [ 
				'key' => $key
		] );

		// Store it even if it's garbage so we don't go look for it again.
		self::$_wallet [$key] = $data;
		if ($data) {
			logger ( LL_XDBG, "UserStore::getItemByPublicKey() - storing cached values" );
			return $data->getData ();
		}
		logger ( LL_WRN, "UserStore::getItemByPublicKey() - no such wallet" );
		return $data;
	}

	public function getItemByWalletId($key) {
		return $this->getItemByPublicKey ( $key );
	}

	public function getWalletBalance($key) {
		$data = $this->getItemByWalletId ( $key );
		return $data ["balance"] ?? 0;
	}

	public function updateWalletBalances($arr) {
		$suspect = array ();
		$users = [ ];

		foreach ( $arr as $id => $delta ) {
			if ($id == coinbaseWalletId ()) {
				// Delta will be negative unless people are returning coins
				$delta *= - 1;

				$circulation = InfoStore::getCirculation ();
				// logger ( LL_INF, "UserStore::updateWalletBalances() - increasing circulation (" . number_format ( $created, 6 ) . ") by '" . number_format ( $delta, 6 ) . "'" );
				logger ( LL_SYS, "UserStore::updateWalletBalances() - increasing circulation (" . number_format ( $circulation, 6 ) . ") by '" . number_format ( $delta, 6 ) . "'" );
				InfoStore::setCirculation ( $circulation + $delta );
			} else {
				$data = self::$_wallet [$id] ?? null;
				if (! $data) {
					$user = self::getItemByPublicKey ( $id );
					if (! $user) {
						// User does not exist
						logger ( LL_SYS, "UserStore::updateWalletBalances() - Delta for '" . $data->getData () ["email"] . "' by '" . number_format ( $delta, 6 ) . "' seems suspect" );
						$suspect [] = $id;
					}
					$data = self::$_wallet [$id] ?? null;
				}

				if ($data) {
					$data->balance = $data->getData () ["balance"] + $delta;
					self::$_updated_wallet [] = $data;
					logger ( LL_SYS, "UserStore::updateWalletBalances() - Updating wallet balance for '" . $data->getData () ["email"] . "' by '" . number_format ( $delta, 6 ) . "'" );
				}
			}
		}

		while ( count ( self::$_updated_wallet ) ) {
			$arr_page = array_splice ( self::$_updated_wallet, 0, transactionsPerPage () );
			logger ( LL_SYS, "UserStore::updateWalletBalances(): updating " . count ( $arr_page ) . " records" );
			$this->obj_store->upsert ( $arr_page );
		}

		return (count ( $suspect ) > 0) ? $suspect : null;
	}

	// public function applyWalletBalances() {
	// while ( count ( self::$_updated_wallet ) ) {
	// $arr_page = array_splice ( self::$_updated_wallet, 0, transactionsPerPage () );
	// // $arr_page = self::$_updated_wallet;
	// // self::$_updated_wallet = [ ];
	// //logger ( LL_DBG, "UserStore::applyWalletBalances(): updating " . count ( $arr_page ) . " records" );
	// logger ( LL_SYS, "UserStore::applyWalletBalances(): updating " . count ( $arr_page ) . " records" );
	// $this->obj_store->upsert ( $arr_page );
	// }
	// return true;
	// }
	public function getItemByRecoveryNonce($key) {
		$gql = "SELECT * FROM " . $this->kind . " WHERE recovery_nonce = @key";
		$data = $this->obj_store->fetchOne ( $gql, [ 
				'key' => $key
		] );
		// echo "UserStore::getItemByValidationNonce('validation_nonce'=>'" . $key . "')\n";
		// echo " '$gql'\n";
		return ($data) ? ($data->getData ()) : ($data);
	}

	public function getItemByGuid($key) {
		$gql = "SELECT * FROM " . $this->kind . " WHERE guid = @key";
		$data = $this->obj_store->fetchOne ( $gql, [ 
				'key' => $key
		] );
		// echo "UserStore::getItemByGuid('guid'=>'" . $key . "')\n";
		// echo " '$gql'\n";
		return ($data) ? ($data->getData ()) : ($data);
	}

	public function insert($arr) {
		$arr ["email"] = strtolower ( $arr ["email"] );

		$password = $arr ["password"];
		$arr ["password"] = "";
		logger ( LL_DBG, "UserStore::insert()" );
		logger ( LL_DBG, "UserStore::insert() - email address: '" . $arr ["email"] . "'" );
		// logger ( LL_DBG, "UserStore::insert() - passed password: '" . $password . "'" );
		$arr ["guid"] = GUIDv4 ();
		$arr ["created"] = ( int ) timestampNow ();
		$arr ["locked"] = 0; // timestamp
		$arr ["logged_in"] = 0; // timestamp
		$arr ["public_key"] = "";
		$arr ["balance"] = 0;
		$arr ["validated"] = 0; // timestamp
		$arr ["validation_requested"] = 0; // timestamp
		$arr ["validation_reminded"] = 0; // timestamp
		$arr ["validation_nonce"] = "";
		$arr ["validation_data"] = "";
		$arr ["recovery_requested"] = 0; // timestamp
		$arr ["recovery_nonce"] = "";
		$arr ["recovery_data"] = "";

		$arr = parent::insert ( $arr );
		if (! is_array ( $arr )) {
			logger ( LL_ERR, "UserStore::insert() - insert of base user failed" );
			return false;
		}
		echo "UserStore::insert() - base user created\n";

		$arr = $this->setPassword ( $arr ["email"], $password );
		if (! is_array ( $arr )) {
			logger ( LL_ERR, "UserStore::insert() - unable to set password" );
			$this->delete ( $arr );
			return false;
		}

		if (! is_array ( $this->authenticate ( $arr ["email"], $password ) )) {
			logger ( LL_ERR, "UserStore::insert() - unable to authenticate user details" );
			$this->delete ( $arr );
			return false;
		}

		logger ( LL_DBG, "UserStore::insert() - obtaining public/private key pair" );

		$keystore = KeyStore::getInstance ();
		$keys = $keystore->getKeys ( $arr ["email"] );

		if ($keys) {
			logger ( LL_DBG, "Keystore provided key pair for '" . $arr ["email"] . "'" );
			$arr ["public_key"] = $keys->public;
			logger ( LL_DBG, "Public key: '" . $keys->public . "'" );
			$user = $this->update ( $arr );
			if (! is_array ( $user )) {
				logger ( LL_ERR, "UserStore::insert() - final update failed" );
				$this->delete ( $arr );
				return false;
			} else {
				logger ( LL_DBG, "UserStore::insert() - User has been created" );
			}
		} else {
			logger ( LL_DBG, "Keystore failed to provide keys for '" . $arr ["email"] . "'" );
			$this->delete ( $arr );
			return false;
		}

		return $user;
	}

	private function generateMfa() {
		global $mfa_words;
		global $mfa_word_count;

		// echo "UserStore::revalidateUser('$email'): Creating word list for poormans MFA\n";
		$keys = array_rand ( $mfa_words, $mfa_word_count );
		$expect = $mfa_words [$keys [0]];
		foreach ( $keys as $key ) {
			$cwords [] = $mfa_words [$key];
		}
		sort ( $cwords );

		$validation = new StdClass ();
		$validation->expect = $expect;
		$validation->choices = $cwords;
		return $validation;
	}

	public function revalidateUser($email) {
		global $www_host;

		$user = $this->getItemById ( $email );
		if (! $user) {
			logger ( LL_ERR, "UserStore::revalidateUser('$email'): Unable to find user" );
			return false;
		}
		// Because we have authenticated - accept that user asked for it, the user is gonna get it
		// if (strlen ( $user ["validation_nonce"] )) {
		// logger ( LL_ERR, "UserStore::revalidateUser('$email'): Already an email outstanding - don't spam" );
		// return false;
		// }
		$validation = $this->generateMfa ();
		print_r ( $validation );
		$user ["validation_nonce"] = GUIDv4 ();
		$user ["validation_reminded"] = 0;
		$user ["validation_requested"] = ( int ) timestampNow ();
		$user ["validation_data"] = json_encode ( $validation );

		$recovery_url = $www_host . "recover";
		$validation_url = $www_host . "validate";
		$payload_url = $validation_url . "/"/*"?payload=" */. $user ["validation_nonce"];
		$subject = "Account validation request";
		$body = "";
		$body .= "This account has requested an account validation. In order to complete this request please head on over to [the validation page](" . $payload_url . ").\n\n";
		$body .= "If you cannot remember the challenge word you were shown, you should probably [validate your account](" . $validation_url . ") again.\n\n";
		$body .= "If you did not make this request, then you should probably secure your account by [recovering your account](" . $recovery_url . ").";

		if (sendEmail ( $user ["email"], $subject, $body )) {
			if ($this->update ( $user )) {
				logger ( LL_DBG, "UserStore::revalidateUser('$email'): Sucessfully requested" );
				return $validation->expect;
			} else {
				logger ( LL_ERR, "UserStore::revalidateUser('$email'): Failed to save challenge" );
			}
		} else {
			logger ( LL_ERR, "UserStore::revalidateUser('$email'): Failed to send email" );
		}
		return false;
	}

	public function recoverUser($email) {
		global $www_host;

		$user = $this->getItemById ( $email );
		if (! $user) {
			logger ( LL_ERR, "UserStore::recovereUser('$email'): Unable to find user" );
			return false;
		}
		// Handled avove my paygrade
		// if (strlen ( $user ["recovery_nonce"] )) {
		// logger ( LL_ERR, "UserStore::recoverUser('$email'): Already an email outstanding - don't spam" );
		// return false;
		// }
		$validation = $this->generateMfa ();
		print_r ( $validation );
		// $user ["locked"] = timestampNow ();
		$user ["recovery_nonce"] = GUIDv4 ();
		$user ["recovery_requested"] = ( int ) timestampNow ();
		$user ["recovery_data"] = json_encode ( $validation );

		$recovery_url = $www_host . "recover";
		$payload_url = $recovery_url . "/" . $user ["recovery_nonce"];
		$subject = "Account recovery request";
		$body = "";
		$body .= "This account has requested an account recovery. In order to complete this request please head on over to [the recovery page](" . $payload_url . ").\n\n";
		$body .= "If you cannot remember the challenge word you were shown, you should probably [recover your account](" . $recovery_url . ") again.\n\n";
		$body .= "If you did not make this request, then you you can ignore this email, and apologies for interfering with your day.";

		if (sendEmail ( $user ["email"], $subject, $body )) {
			if ($this->update ( $user )) {
				logger ( LL_DBG, "UserStore::recovereUser('$email'): Sucessfully requested" );
				return $validation->expect;
			} else {
				logger ( LL_ERR, "UserStore::recovereUser('$email'): Failed to save challenge" );
			}
		} else {
			logger ( LL_ERR, "UserStore::recovereUser('$email'): Failed to send email" );
		}
		return false;
	}

	// Called by the system
	public function requestValidateUser($email) {
		global $www_host;

		$user = $this->getItemById ( $email );
		if (! $user) {
			logger ( LL_ERR, "UserStore::requestValidateUser('$email'): Unable to find user" );
			return false;
		}
		// Handled avove my paygrade
		// if (strlen ( $user ["recovery_nonce"] )) {
		// logger ( LL_ERR, "UserStore::recoverUser('$email'): Already an email outstanding - don't spam" );
		// return false;
		// }
		$user ["validation_reminded"] = ( int ) timestampNow ();

		$validation_url = $www_host . "validate";
		$payload_url = $validation_url . "/"/*"?payload=" */. $user ["validation_nonce"];
		$subject = "Account validation request";
		$body = "";
		$body .= "It has been " . revalidationPeriodDays () . " days since this account was last validated. Within the next " . actionGraceDays () . " days please head on over to [the validation page](" . $payload_url . ") and revalidate this account.\n\n";
		$body .= "If you have not performed this process within the next" . actionGraceDays () . " days, your account will be locked and you will not be able to mine or login to your account.";

		// if (sendEmail ( $user ["email"], $subject, $body )) {
		// if ($this->update ( $user )) {
		// logger ( LL_DBG, "UserStore::requestValidateUser('$email'): Sucessfully requested" );
		// return true;
		// } else {
		// logger ( LL_ERR, "UserStore::requestValidateUser('$email'): Failed to save challenge" );
		// }
		// } else {
		// logger ( LL_ERR, "UserStore::requestValidateUser('$email'): Failed to send email" );
		// }
		return false;
	}

	public function setPassword($email, $password) {
		$user = $this->getItemById ( $email );
		if (! $user) {
			return false;
		}
		$user ["password"] = $user ["guid"] . "." . $email . "." . $password;
		logger ( LL_DBG, "UserStore::setPassword() - intermediate password: '" . $user ["password"] . "'" );
		$user ["password"] = md5 ( $user ["password"] );
		logger ( LL_DBG, "UserStore::setPassword() - final password: '" . $user ["password"] . "'" );

		return $this->update ( $user );
	}

	public function authenticate($email, $password) {
		$user = $this->getItemById ( $email );
		if (! $user) {
			logger ( LL_DBG, "UserStore::authenticate('$email'): User authentication failed: Unable to find user" );
			return false;
		}

		$password = md5 ( $user ["guid"] . "." . $email . "." . $password );
		if ($password == $user ["password"]) {
			logger ( LL_DBG, "UserStore::authenticate('$email'): User authenticated" );
			//logger(LL_SYS, "UserStore::authenticate(): Got user: ".ob_print_r($user));
			return $user;
		}
		logger ( LL_DBG, "UserStore::authenticate('$email'): User authentication failed: Password does not match" );
		return false;
	}
}
?>