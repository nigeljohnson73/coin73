<?php

use Elliptic\EC;

class Transaction {

	public function __construct($sender = null, $recipient = null, $amount = null, $message = "") {
		$this->created = microtime(true);
		$this->sender = $sender;
		$this->recipient = $recipient;
		$this->amount = $amount;
		$this->message = $message;
		$this->payload = null;
		$this->hash = null;
		$this->signature = null;
		$this->invalid_reason = "";
	}

	public function getReason() {
		return $this->invalid_reason;
	}

	public function getPayload() {
		$ret = new StdClass();

		$ret->created = $this->created;
		$ret->sender = $this->sender;
		$ret->recipient = $this->recipient;
		$ret->amount = $this->amount;
		$ret->message = $this->message;
		$this->payload = json_encode($ret);

		return $this->payload;
	}

	public function calculateHash() {
		return $this->hash = hash("sha256", $this->getPayload());
	}

	public function sign($privKey) {
		if ($this->created == null || $this->recipient == null || $this->sender == null || $this->amount <= 0) {
			$this->invalid_reason = "Transaction is not properly formed";
			logger(LL_DBG, "Transaction::sign(): " . $this->invalid_reason);
			return false;
		}

		$ec = new EC('secp256k1');
		$sk = $ec->keyFromPrivate($privKey, 'hex');
		if ($this->sender != $sk->getPublic('hex')) {
			$this->invalid_reason = "Signing key does not belong to sender";
			logger(LL_ERR, "Transaction::sign(): " . $this->invalid_reason);
			return false;
		}
		$signature = $sk->sign($this->calculateHash());
		$this->signature = $signature->toDER('hex');
		return true;
	}

	public function isValid($signature_check = false) {
		if ($this->created == null || $this->recipient == null || $this->sender == null || $this->amount <= 0) {
			$this->invalid_reason = "Transaction is not properly formed";
			logger(LL_DBG, "Transaction::isValid(): " . $this->invalid_reason);
			return false;
		}

		if ($this->sender == $this->recipient) {
			$this->invalid_reason = "Cannot send tranaction to yourself";
			logger(LL_DBG, "Transaction::isValid(): " . $this->invalid_reason);
			return false;
		}

		if (strlen($this->signature) == 0) {
			$this->invalid_reason = "Transaction is not signed";
			logger(LL_DBG, "Transaction::isValid(): " . $this->invalid_reason);
			return false;
		}

		if ($signature_check) {
			$ec = new EC('secp256k1');
			$vk = $ec->keyFromPublic($this->sender, 'hex');
			if (!$vk->verify($this->calculateHash(), $this->signature)) {
				$this->invalid_reason = "Transaction signature verification failed";
				logger(LL_DBG, "Transaction::isValid(): " . $this->invalid_reason);
				return false;
			}
		}

		return true;
	}

	public function isServiceable() {
		if (!$this->isValid(true)) {
			// The reason and error is posted in teh isValid() call
			logger(LL_DBG, "Transaction::isServiceable(): Transaction is not valid");
			return false;
		}

		// It can still be from god, but it needs to be signed. That's why this is not at the top.
		// If we are not validating the receiver, we will not process any further on the basis that
		// God didn't make up the receiver so they must be valid... or user entered guff in a miner,
		// either way, pick it up on the back end and return it to the pool there as the processing
		// load is way lower there - potentailly maxMinerCount() times lower.
		// if ($this->from == coinbaseWalletId () && ! $reciever_check) {
		// logger ( LL_DBG, "Transaction::isServiceable(): Transaction is from '" . str_replace ( getDataNamespace (), "", coinbaseName () ) . "'" );
		// return true;
		// }

		$store = UserStore::getInstance();

		// Check the receiver exists. We really should trust the sender more, but they are only human
		$receiver = $store->getItemByWalletId($this->recipient);
		if (!$receiver) {
			$this->invalid_reason = "Receiver does not exist";
			logger(LL_ERR, "Transaction::isServiceable(): " . $this->getReason());
			return false;
		}

		// Check the sender has funds (and exists)

		// Well obviously the coinbase exists and is good for it
		if ($this->sender == coinbaseWalletId()) {
			logger(LL_DBG, "Transaction::isServiceable(): Transaction is from '" . str_replace(getDataNamespace(), "", coinbaseName()));
			return true;
		}

		// The transaction amount will hav been checked in isValid() to be higher than zero. If a wallet
		// cannot send that (it has zero or doesn't exist), then throw an error.
		$sender_bal = $store->getWalletBalance($this->sender);
		if ($sender_bal < $this->amount) {
			$this->invalid_reason = "Sender balance is not sufficient";
			logger(LL_ERR, "Tranaction::isServiceable(): " . $this->getReason());
			logger(LL_DBG, "                             (" . $sender_bal . " < " . $this->amount . ")");
			return false;
		}

		// Performed all the checks, we must be as good as we can possibly get
		logger(LL_DBG, "Tranaction::isServiceable(): Sender has funds (" . $sender_bal . " >= " . $this->amount . ")");
		return true;
	}

	public function unload() {
		$arr = array();
		$arr["created"] = $this->created;
		$arr["sender"] = $this->sender;
		$arr["recipient"] = $this->recipient;
		$arr["amount"] = $this->amount;
		$arr["message"] = $this->message;
		$arr["hash"] = $this->calculateHash();
		$arr["payload"] = $this->getPayload();
		$arr["signature"] = $this->signature;
		return $arr;
	}

	public function load($arr) {
		return $this->fromArray($arr);
	}

	public function fromArray($arr) {
		$this->created = $arr["created"] ?? microtime(true);
		$this->sender = $arr["sender"] ?? null;
		$this->recipient = $arr["recipient"] ?? null;
		$this->amount = $arr["amount"] ?? null;
		$this->message = $arr["message"] ?? "";
		$this->payload = $arr["payload"] ?? $this->getPayload();
		$this->hash = $this->calculateHash();
		$this->signature = $arr["signature"] ?? null;
		return $this;
	}

	public function fromPayload($payload, $signature) {
		$this->payload = $payload;
		$this->hash = $this->calculateHash();
		$this->signature = $signature;

		$payload = json_decode($payload);
		$this->created = $payload->created ?? null;
		$this->sender = $payload->sender ?? null;
		$this->recipient = $payload->recipient ?? null;
		$this->amount = $payload->amount ?? null;
		$this->message = $payload->message ?? "";

		return $this;
	}
}

function __testTransaction() {
	global $logger;
	$ll = $logger->getLevel();
	$logger->setLevel(LL_DBG);

	logger(LL_INF, "------------------------------------------------------------------------------------------------------");
	logger(LL_INF, "Generate keypair");
	$ec = new EC('secp256k1');
	$key = $ec->genKeyPair();
	$pubKey = $key->getPublic('hex');
	$privKey = $key->getPrivate('hex');

	logger(LL_DBG, "pubKey    : '" . $pubKey . "'");
	logger(LL_DBG, "privKey   : '" . $privKey . "'");

	logger(LL_INF, "------------------------------------------------------------------------------------------------------");
	logger(LL_INF, "Generate and sign some data");
	// Sign message (can be hex sequence or array)
	$msg = 'Secret Data in here';
	logger(LL_DBG, "Data      : '" . $msg . "'");

	$hash = hash("sha256", $msg);
	logger(LL_DBG, "Hash      : '" . $hash . "'");

	// Sign our hashed data
	logger(LL_INF, "Sign the hash");
	$sk = $ec->keyFromPrivate($privKey, 'hex');
	$signature = $sk->sign($hash);

	// logger ( LL_INF, "------------------------------------------------------------------------------------------------------" );
	logger(LL_INF, "Export the signature");
	// Export DER encoded signature to hex string
	$derSign = $signature->toDER('hex');
	logger(LL_DBG, "Signature : '" . $derSign . "'");

	logger(LL_INF, "------------------------------------------------------------------------------------------------------");
	logger(LL_INF, "Verify signature");
	// Verify signature
	$vk = $ec->keyFromPublic($pubKey, 'hex');
	$verified = $vk->verify($hash, $derSign);
	logger(LL_INF, "Verified  : " . ($verified ? "true" : "false"));

	logger(LL_INF, "------------------------------------------------------------------------------------------------------");
	logger(LL_INF, "Create coinbase transaction");
	$t = new Transaction(coinbaseWalletId(), $pubKey, 1.23456789, "Loading some coin!");
	logger(LL_INF, "Coinbase transaction valid : expect false, got " . ($t->isValid() ? ("true") : ("false")));
	logger(LL_INF, "Signing transaction");
	$t->sign(coinbasePrivateKey());
	logger(LL_INF, "Coinbase transaction valid : expect true, got " . ($t->isValid() ? ("true") : ("false")));
	logger(LL_INF, "Transaction servicable     : expect true, got " . ($t->isServiceable() ? ("true") : ("false")));

	logger(LL_INF, "------------------------------------------------------------------------------------------------------");
	logger(LL_INF, "Create return transaction");
	$t = new Transaction($pubKey, coinbaseWalletId(), 1.23456789, "Returning some coin!");
	logger(LL_INF, "Return transaction valid      : expect false, got " . ($t->isValid() ? ("true") : ("false")));
	logger(LL_INF, "Signing transaction");
	$t->sign($privKey);
	logger(LL_INF, "Return transaction valid   : expect true, got " . ($t->isValid() ? ("true") : ("false")));
	logger(LL_INF, "Transaction servicable     : expect false, got " . ($t->isServiceable() ? ("true") : ("false")));
	logger(LL_INF, "------------------------------------------------------------------------------------------------------");
	logger(LL_INF, "Unloading transaction to stick into a database");
	$u = $t->unload();
	print_r($u);
	logger(LL_INF, "Loading transaction from a database rowset");
	$t = (new Transaction())->load($u);
	logger(LL_INF, "Loaded transaction valid   : expect true, got " . ($t->isValid() ? ("true") : ("false")));
	logger(LL_INF, "------------------------------------------------------------------------------------------------------");
	logger(LL_INF, "Loading transaction from a payload and signature - from within the block");
	$t = (new Transaction())->fromPayload($u["payload"], $u["signature"]);
	logger(LL_INF, "Payload transaction valid  : expect true, got " . ($t->isValid() ? ("true") : ("false")));
	logger(LL_INF, "------------------------------------------------------------------------------------------------------");

	$logger->setLevel($ll);
}
