<?php
include_once (__DIR__ . "/DataStore.php");
use Elliptic\EC;

class UserStore extends DataStore {

	public function __construct() {
		echo "UserStore::UserStore()\n";

		parent::__construct ( "User" );

		$this->addField ( "email", "String", true, true ); // indexed and key
		$this->addField ( "private_key", "String" );
		$this->addField ( "public_key", "String", true ); // indexed for wallet management
		$this->addField ( "guid", "String" ); // for the password mechanism
		$this->addField ( "password", "String" );
		$this->addField ( "nonce", "String" ); // This exists if we are waiting on an email validation
		$this->addField ( "validation", "String" ); // This is the validation string array
		$this->addField ( "created", "String", true ); // Use our timestamp Library
		$this->addField ( "validated", "String", true ); // Use our timestamp Library

		$this->init ();
	}

	public function insert($arr) {
		$password = $arr ["password"];
		echo "UserStore::insert()\n";
		echo "UserStore::insert() - email address: '" . $arr ["email"] . "'\n";
		echo "UserStore::insert() - passed password: '" . $password . "'\n";
		$arr ["guid"] = GUIDv4 ();
		$arr ["created"] = timestampNow ();
		$arr ["private_key"] = "";
		$arr ["public_key"] = "";
		$arr ["validated"] = "";
		$arr ["validation"] = "";
		$arr ["nonce"] = "";

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

		if (! $this->authenticate ( $arr ["email"], $password )) {
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

	public function revalidateUser($email) {
		$user = $this->getItemById ( $email );
		if (! $user) {
			return false;
		}
		$user ["nonce"] = GUIDv4 ();
		echo "Creating word list for poormans MFA\n";
		$words = array ();
		$words [] = "Wedensday";
		$words [] = "Helper";
		$words [] = "Utility";
		$words [] = "Layout";
		$words [] = "Floating";
		$words [] = "Efficient";
		$words [] = "Miner";
		$words [] = "Apple";
		$words [] = "Verify";
		$words [] = "Brand";
		$words [] = "Power";
		$words [] = "Private";
		$words [] = "About";
		$words [] = "Document";
		$words [] = "Manual";
		$words [] = "Server";
		$words [] = "Home";
		$words [] = "Arrow";
		$words [] = "Keyboard";
		$words [] = "Words";
		$words [] = "Change";
		$words [] = "Number";
		$words [] = "Letter";
		$words [] = "Reduce";
		$words [] = "Website";
		$words [] = "Printer";
		$words [] = "Flask";
		$words [] = "Reverse";
		$words [] = "Change";
		$words [] = "Value";
		$words [] = "Slice";
		$words [] = "Email";
		$words [] = "Pencil";
		$words [] = "Ruler";

		$keys = array_rand ( $words, 3 );
		$challenge = $words [$keys [0]];
		foreach ( $keys as $key ) {
			$cwords [] = $words [$key];
		}
		shuffle ( $cwords );
		// echo "Chosen '".$challenge."' from:\n";
		// print_r($cwords);

		$validation = new StdClass ();
		$validation->expect = $challenge;
		$validation->choices = $cwords;
		$user ["validation"] = json_encode ( $validation );
		print_r ( $validation );

		return $challenge;
	}

	public function setPassword($email, $password) {
		$user = $this->getItemById ( $email );
		if (! $user) {
			return false;
		}
		$user ["password"] = $user ["guid"] . "." . $email . "." . $password;
		echo "UserStore::insert() - intermediate password: '" . $user ["password"] . "'\n";
		$user ["password"] = md5 ( $user ["password"] );
		echo "UserStore::insert() - final password: '" . $user ["password"] . "'\n";

		return $this->replace ( $user );
	}

	public function authenticate($email, $password) {
		$user = $this->getItemById ( $email );
		if (! $user) {
			return false;
		}

		$password = md5 ( $user ["guid"] . "." . $email . "." . $password );
		return $password == $user ["password"];
	}
}
?>