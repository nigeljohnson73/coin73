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
		$this->addField ( "guid", "String" ); // for the password
		$this->addField ( "password", "String" );
		$this->addField ( "nonce", "String" );
		$this->addField ( "created", "String" ); // Use our timestamp Library
		$this->addField ( "validated", "String" ); // Use our timestamp Library
		$this->addField ( "validation", "String" );

		$this->init ();
	}

	public function insert($arr) {
		echo "UserStore::insert()\n";
		echo "UserStore::insert() - email address: '" . $arr ["email"] . "'\n";
		echo "UserStore::insert() - passed password: '" . $arr ["password"] . "'\n";

		$arr ["nonce"] = GUIDv4 ();

		$arr ["guid"] = GUIDv4 ();
		$arr ["password"] = $arr ["guid"] . "." . $arr ["email"] . "." . $arr ["password"];
		echo "UserStore::insert() - intermediate password: '" . $arr ["password"] . "'\n";
		$arr ["password"] = md5 ( $arr ["password"] );
		echo "UserStore::insert() - final password: '" . $arr ["password"] . "'\n";

		// Create and initialize EC context
		// (better do it once and reuse it)
		$ec = new EC ( 'secp256k1' );

		echo "UserStore::insert() - generating public/private key pair\n";
		$key = $ec->genKeyPair ();
		$pubKey = $key->getPublic ( 'hex' );
		$privKey = $key->getPrivate ( 'hex' );
		$arr ["private_key"] = $privKey;
		$arr ["public_key"] = $pubKey;

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
		echo "Signature verification: " . (($key->verify ( $sha1, $derSign ) == TRUE) ? "true" : "false") . "\n";

		return $arr;
		// return parent::insert($arr);
	}
}
?>