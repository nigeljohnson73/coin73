<?php
include_once (__DIR__ . "/DataStore.php");
use Elliptic\EC;

class UserStore extends DataStore {

	public function __construct() {
		echo "UserStore::UserStore()\n";

		parent::__construct ( "User" );

		$this->addField ( "email", "String", true, true ); // indexed and key
		$this->addField ( "guid", "String", true ); // for the password mechanism, and passing around instead of the email address
		$this->addField ( "password", "String" );
		$this->addField ( "private_key", "String" );
		$this->addField ( "public_key", "String", true ); // indexed for wallet management
		$this->addField ( "created", "Integer", true ); // Use our timestamp Library
		$this->addField ( "validated", "Integer", true ); // Use our timestamp Library // Send validation request if this is older than X days and validation_requested is blank. Set this when validate suceeds
		$this->addField ( "validation_requested", "Integer", true ); // Use our timestamp Library // Set if the system set it and sent a reminder or the user is requesting a vaildation
		$this->addField ( "validation_nonce", "String" ); // This exists if are waiting on an email reponse to the validation request
		$this->addField ( "validation_data", "String" ); // This is the validation string array that will hold the choices - lock the account while this is processing
		$this->addField ( "recovery_requested", "Integer", true ); // Use our timestamp Library // Set if the user is requesting a recovery
		$this->addField ( "recovery_nonce", "String" ); // This exists if are waiting on an email reponse to the recovery request
		$this->addField ( "recovery_data", "String" ); // This is the validation string array that will hold the choices - lock the account while this is processing
		
		$this->init ();
	}

	public function insert($arr) {
		$password = $arr ["password"];
		$arr ["password"] = "";
		echo "UserStore::insert()\n";
		echo "UserStore::insert() - email address: '" . $arr ["email"] . "'\n";
		echo "UserStore::insert() - passed password: '" . $password . "'\n";
		$arr ["guid"] = GUIDv4 ();
		$arr ["created"] = ( int ) timestampNow ();
		$arr ["private_key"] = "";
		$arr ["public_key"] = "";
		$arr ["validated"] = 0; // timestamp
		$arr ["validation_requested"] = 0; // timestamp
		$arr ["validation_nonce"] = "";
		$arr ["validation_data"] = "";
		$arr ["recovery_requested"] = 0; // timestamp
		$arr ["recovery_nonce"] = "";
		$arr ["recovery_data"] = "";
		
		$arr = parent::insert ( $arr );
		if (! is_array ( $arr )) {
			echo "UserStore::insert() - insert of base user failed\n";
			return false;
		}
		echo "UserStore::insert() - base user created\n";

		$arr = $this->setPassword ( $arr ["email"], $password );
		if (! is_array ( $arr )) {
			echo "UserStore::insert() - unable to set password\n";
			$this->delete ( $arr );
			return false;
		}

		if (! is_array ( $this->authenticate ( $arr ["email"], $password ) )) {
			echo "UserStore::insert() - unable to authenticate user details\n";
			$this->delete ( $arr );
			return false;
		}

		// Create and initialize EC context
		// (better do it once and reuse it)
		echo "UserStore::insert() - generating public/private key pair\n";

		$ec = new EC ( 'secp256k1' );
		$key = $ec->genKeyPair ();
		$pubKey = $key->getPublic ( 'hex' );
		$privKey = $key->getPrivate ( 'hex' );

		echo "Public key: '" . $pubKey . "'\n";
		echo "Private key: '" . $privKey . "'\n";

		// Sign message (can be hex sequence or array)
		$msg = 'Secret Data in here';
		echo "Data: '" . $msg . "'\n";
		$sha1 = sha1 ( $msg );
		echo "SHA1: '" . $sha1 . "'\n";

		$signature = $key->sign ( $sha1 );

		// Export DER encoded signature to hex string
		$derSign = $signature->toDER ( 'hex' );
		echo "SHA1 signature: '" . $derSign . "'\n";

		// Verify signature
		$verified = $key->verify ( $sha1, $derSign );
		echo "Signature verification: " . ($verified ? "true" : "false") . "\n";

		if (! $verified) {
			echo "UserStore::insert() - keypair does not work as expected - abandonning creation\n";
			$this->delete ( $arr );
			return false;
		}
		$arr ["private_key"] = $privKey;
		$arr ["public_key"] = $pubKey;
		echo "UserStore::insert() - public/private created\n";

		// return $arr;
		$user = $this->replace ( $arr );
		if (! is_array ( $user )) {
			echo "UserStore::insert() - final replace failed\n";
		}
		echo "UserStore::insert() - User has been created\n";
		return $user;
	}

	private function generateMfa() {
		global $mfa_words;
		global $mfa_word_count;
		
		// echo "UserStore::revalidateUser('$email'): Creating word list for poormans MFA\n";
		$keys = array_rand ( $mfa_words, $mfa_word_count );
		$challenge = $mfa_words [$keys [0]];
		foreach ( $keys as $key ) {
			$cwords [] = $mfa_words [$key];
		}
		sort ( $cwords );
		
		$validation = new StdClass ();
		$validation->expect = $challenge;
		$validation->choices = $cwords;
		return $validation;
	}
	public function revalidateUser($email) {
		global $www_host;

		$user = $this->getItemById ( $email );
		if (! $user) {
			echo "UserStore::revalidateUser('$email'): Unable to find user\n";
			return false;
		}
		$validation = $this->generateMfa();
		print_r ( $validation );
		$user ["validation_nonce"] = GUIDv4 ();
		$user ["validation_requested"] = (int)timestampNow ();
		$user ["validation_data"] = json_encode ( $validation );

		$recovery_url = $www_host . "/recover";
		$validation_url = $www_host . "/validate";
		$payload_url = $validation_url."?payload=" . $user ["nonce"];
		$subject = "Account validation request";
		$body = "";
		$body .= "Your username and password were used to request an account activation or validation. In order to complete this request, you will need the challenge word that was shown on the screen when the request was made.\n\n";
		$body .= "You will also need to head on over to [the revalidation page](" . $payload_url . ").\n\n";
		$body .= "If the link above does not work, the please copy and paste the following url into you browser:\n\n";
		$body .= " * ".$payload_url."\n\n";
		$body .= "If you cannot remember the challenge word you were shown, you should probably [start the process again](".$validation_url.").\n\n";
		$body .= "If you did not make this request, then you should probably secure your account by [requesting an account recovery](".$recovery_url.").";
		
		if (sendEmail ( $user ["email"], $subject, $body )) {
			if ($this->update ( $user )) {
				echo "UserStore::revalidateUser('$email'): Sucessfully requested\n";
				return $validation->challenge;
			} else {
				echo "UserStore::revalidateUser('$email'): Failed to save challenge\n";
			}
		} else {
			echo "UserStore::revalidateUser('$email'): Failed to send email\n";
		}
		return false;
	}

	public function setPassword($email, $password) {
		$user = $this->getItemById ( $email );
		if (! $user) {
			return false;
		}
		$user ["password"] = $user ["guid"] . "." . $email . "." . $password;
		echo "UserStore::setPassword() - intermediate password: '" . $user ["password"] . "'\n";
		$user ["password"] = md5 ( $user ["password"] );
		echo "UserStore::setPassword() - final password: '" . $user ["password"] . "'\n";

		return $this->replace ( $user );
	}

	public function authenticate($email, $password) {
		$user = $this->getItemById ( $email );
		if (! $user) {
			echo "UserStore::authenticate('$email'): User authentication failed: Unable to find user\n";
			return false;
		}

		$password = md5 ( $user ["guid"] . "." . $email . "." . $password );
		if ($password == $user ["password"]) {
			echo "UserStore::authenticate('$email'): User authenticated\n";
			return $user;
		}
		echo "UserStore::authenticate('$email'): User authentication failed: Password does not match\n";
		return false;
	}
}
?>