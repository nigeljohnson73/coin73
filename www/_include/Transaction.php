<?php
use Elliptic\EC;

class Transaction {

	public function __construct($from = null, $to = null, $amount = null, $message = "") {
		$this->from = $from;
		$this->to = $to;
		$this->amount = $amount;
		$this->message = $message;
		$this->payload = null;
		$this->hash = null;
		$this->signature = null;
	}

	public function getPayload() {
		if (! $this->payload) {
			$ret = new StdClass ();
			$ret->from = $this->from;
			$ret->to = $this->to;
			$ret->amount = $this->amount;
			$ret->message = $this->message;
			$this->payload = json_encode ( $ret );
		}
		return $this->payload;
	}

	public function calculateHash() {
		if ($this->hash && strlen ( $this->hash )) {
			return $this->hash;
		}
		return $this->hash = hash ( "sha256", $this->getPayload () );
	}

	public function sign($privKey) {
		$ec = new EC ( 'secp256k1' );
		$sk = $ec->keyFromPrivate ( $privKey, 'hex' );
		if ($this->from != $sk->getPublic ( 'hex' )) {
			logger ( LL_ERR, "Transaction::sign(): Private key does not match sender public key" );
			return false;
		}
		$signature = $sk->sign ( $this->calculateHash () );
		$this->signature = $signature->toDER ( 'hex' );
		return true;
	}

	public function isValid() {
		if ($this->to == null || $this->from == null || $this->amount <= 0) {
			return false;
		}

		if (strlen ( $this->signature ) == 0) {
			return false;
		}
		$ec = new EC ( 'secp256k1' );
		$vk = $ec->keyFromPublic ( $this->from, 'hex' );
		return $vk->verify ( $this->calculateHash (), $this->signature );
	}

	public function unload() {
		$arr = array ();
		$arr ["from"] = $this->from;
		$arr ["to"] = $this->to;
		$arr ["amount"] = $this->amount;
		$arr ["message"] = $this->message;
		$arr ["payload"] = $this->getPayload ();
		// $arr ["hash"] = $this->calculateHash();
		$arr ["signature"] = $this->signature;
		return $arr;
	}

	public function load($arr) {
		return $this->fromArray ( $arr );
	}

	public function fromArray($arr) {
		$this->from = $arr ["from"] ?? null;
		$this->to = $arr ["to"] ?? null;
		$this->amount = $arr ["amount"] ?? null;
		$this->message = $arr ["message"] ?? "";
		$this->payload = $arr ["payload"] ?? $this->getPayload ();
		$this->hash = $this->calculateHash ();
		$this->signature = $arr ["signature"] ?? null;
		return $this;
	}

	public function fromPayload($payload, $signature) {
		$this->payload = $payload;
		$this->hash = $this->calculateHash ();
		$this->signature = $signature;

		$payload = json_decode ( $payload );
		$this->from = $payload->from ?? null;
		$this->to = $payload->to ?? null;
		$this->amount = $payload->amount ?? null;
		$this->message = $payload->message ?? "";

		return $this;
	}
}

function __testTransaction() {
	global $logger;
	$ll = $logger->getLevel ();
	$logger->setLevel ( LL_DBG );

	logger ( LL_INF, "------------------------------------------------------------------------------------------------------" );
	logger ( LL_INF, "Generate keypair" );
	$ec = new EC ( 'secp256k1' );
	$key = $ec->genKeyPair ();
	$pubKey = $key->getPublic ( 'hex' );
	$privKey = $key->getPrivate ( 'hex' );

	logger ( LL_DBG, "pubKey    : '" . $pubKey . "'" );
	logger ( LL_DBG, "privKey   : '" . $privKey . "'" );

	logger ( LL_INF, "------------------------------------------------------------------------------------------------------" );
	logger ( LL_INF, "Sign our data" );
	// Sign message (can be hex sequence or array)
	$msg = 'Secret Data in here';
	logger ( LL_DBG, "Data      : '" . $msg . "'" );

	$hash = hash ( "sha256", $msg );
	logger ( LL_DBG, "Hash      : '" . $hash . "'" );

	// Sign our hashed data
	$sk = $ec->keyFromPrivate ( $privKey, 'hex' );
	$signature = $sk->sign ( $hash );

	logger ( LL_INF, "------------------------------------------------------------------------------------------------------" );
	logger ( LL_INF, "Export the signature" );
	// Export DER encoded signature to hex string
	$derSign = $signature->toDER ( 'hex' );
	logger ( LL_DBG, "Signature : '" . $derSign . "'" );

	logger ( LL_INF, "------------------------------------------------------------------------------------------------------" );
	logger ( LL_INF, "Verify signature" );
	// Verify signature
	$vk = $ec->keyFromPublic ( $pubKey, 'hex' );
	$verified = $vk->verify ( $hash, $derSign );
	logger ( LL_INF, "Verified  : " . ($verified ? "true" : "false") );

	logger ( LL_INF, "------------------------------------------------------------------------------------------------------" );
	logger ( LL_INF, "Create coinbase transaction" );
	$t = new Transaction ( coinbaseWalletId (), $pubKey, 1.23456789, "Loading some coin!" );
	logger ( LL_INF, "Coinbase transaction valid : expect false, got " . ($t->isValid () ? ("true") : ("false")) );
	logger ( LL_INF, "Signing transaction" );
	$t->sign ( coinbasePrivateKey () );
	logger ( LL_INF, "Coinbase transaction valid : expect true, got " . ($t->isValid () ? ("true") : ("false")) );
	
	logger ( LL_INF, "------------------------------------------------------------------------------------------------------" );
	logger ( LL_INF, "Create return transaction" );
	$t = new Transaction ( $pubKey, coinbaseWalletId (), 1.23456789, "Returning some coin!" );
	logger ( LL_INF, "Return transaction valid   : expect false, got " . ($t->isValid () ? ("true") : ("false")) );
	logger ( LL_INF, "Signing transaction" );
	$t->sign ( $privKey );
	logger ( LL_INF, "Return transaction valid   : expect true, got " . ($t->isValid () ? ("true") : ("false")) );
	logger ( LL_INF, "------------------------------------------------------------------------------------------------------" );
	logger ( LL_INF, "Unloading transaction to stick into a database" );
	$u = $t->unload ();
	print_r ( $u );
	logger ( LL_INF, "Loading transaction from a database rowset" );
	$t = (new Transaction ())->load ( $u );
	logger ( LL_INF, "Rebuilt transaction valid  : expect true, got " . ($t->isValid () ? ("true") : ("false")) );
	logger ( LL_INF, "------------------------------------------------------------------------------------------------------" );
	logger ( LL_INF, "Loading transaction from a payload and signature - from within the block" );
	$t = (new Transaction ())->fromPayload ( $u ["payload"], $u ["signature"] );
	logger ( LL_INF, "Payload transaction valid  : expect true, got " . ($t->isValid () ? ("true") : ("false")) );
	logger ( LL_INF, "------------------------------------------------------------------------------------------------------" );

	$logger->setLevel ( $ll );
}

?>