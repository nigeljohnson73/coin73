<?php

class Block {

	public function __construct($message = "") {
		$this->created = microtime ( true );
		$this->message = $message;
		$this->last_hash = InfoStore::getLastBlockHash ();
		$this->transactions = [ ];
		$this->hash = null;
		$this->nonce = - 1;
		$this->signature = null;
		$this->payload = null;
	}

	public function addTransaction($t) {
		// We have to assume that the servicability has been baselined already because of where we would be in the process
		if (! $t->isValid ()) {
			logger ( LL_ERR, "Block::addTransaction(): Cannot add an invalid transaction" );
			return false;
		}
		$txn = new stdClass ();
		$txn->payload = $t->getPayload ();
		$txn->signature = $t->signature;
		$this->transactions [] = $txn;
		return true;
	}

	public function addTransactions($arr) {
		$ret = true;
		foreach ( $arr as $t ) {
			$ret &= $this->addTransaction ( $t );
		}
		return $ret;
	}

	public function getHashablePayload() {
		if (! $this->payload) {
			$ret = new StdClass ();
			$ret->hash = $this->hash;
			$ret->last_hash = $this->last_hash;
			$ret->created = $this->created;
			$ret->message = $this->message;
			$ret->transactions = $this->transactions;
			$this->payload = json_encode ( $ret );
		}
		return $this->payload;
	}

	protected function calculateHash() {
		if ($this->hash && strlen ( $this->hash )) {
			return $this->hash;
		}
		return $this->hash = hash ( "sha1", $this->getHashablePayload () );
	}

	public function sign() {
		$this->calculateHash ();
		$begins = str_pad ( "", minerDifficulty (), "0" );

		$cnonce = 0;
		while ( $this->nonce < 0 ) {
			$signed = hash ( "sha1", $this->hash . $cnonce );
			// Check if the signature starts with the expected number of zeros
			if (strpos ( $signed, $begins ) === 0) { // If it has, we found one
				$this->nonce = $cnonce;
				$this->signature = $signed;
			}
			$cnonce = $cnonce + 1;
		}
		return true;
	}

	public function isValid() {
		if ($this->created == null || $this->nonce < 0 || $this->signature == null) {
			logger ( LL_ERR, "Block::isValid(): Block is not properly formed" );
			return false;
		}

		if (strlen ( $this->signature ) == 0) {
			logger ( LL_ERR, "Block::isValid(): Block is not signed" );
			return false;
		}

		$this->calculateHash ();
		if (hash ( "sha1", $this->hash . $this->nonce ) != $this->signature) {
			logger ( LL_ERR, "Block::isValid(): Block has been tampered with" );
			return false;
		}

		$begins = str_pad ( "", minerDifficulty (), "0" );
		if (strpos ( $this->signature, $begins ) !== 0) { // If it has, we found one
			logger ( LL_ERR, "Block::isValid(): Block has invalid signature" );
			return false;
		}

		foreach ( $this->transactions as $k => $txn ) {
			$t = (new Transaction ())->fromPayload ( $txn->payload, $txn->signature );
			if (! $t->isValid ()) {
				logger ( LL_ERR, "Transaction #" . ($k + 1) . " valid: " . (($t->isValid ()) ? ("true") : ("false")) );
				return false;
			}
		}
		return true;
	}

	public function toPayload() {
		$ret = new StdClass ();
		$ret->hash = $this->hash;
		$ret->payload = $this->getHashablePayload ();
		$ret->nonce = $this->nonce;
		$ret->signature = $this->signature;
		return json_encode ( $ret );
	}

	public function fromPayload($payload) {
		$payload = json_decode ( $payload );
		$this->hash = $payload->hash;
		$this->nonce = $payload->nonce;
		$this->signature = $payload->signature;

		$payload = json_decode ( $payload->payload );

		$this->last_hash = $payload->last_hash;
		$this->message = $payload->message;
		$this->created = $payload->created;
		$this->transactions = $payload->transactions;

		return $this;
	}
}

function __testBlock() {
	global $logger;
	$ll = $logger->getLevel ();
	$logger->setLevel ( LL_DBG );

	$from = coinbaseWalletId ();
	$to = "04d329153bacfc18f8400b53904729fecbe44637e0b7902254f1a55d1f47b109b1e6d045d45b826234c04e35902eb5423f4b6d6104fde6a05ef3621a86a19f8171";
	$amt = 0.001234;

	$t1 = new Transaction ( $from, $to, $amt, "Test Transaction 1" );
	$t1->sign ( coinbasePrivateKey () );
	logger ( LL_INF, "T1 valid: " . (($t1->isValid ()) ? ("true") : ("false")) );

	$tarr = array ();
	for($i = 0; $i < 2; $i ++) {
		$t = new Transaction ( $from, $to, $amt, "Test Transaction " . ($i + 2) );
		$t->sign ( coinbasePrivateKey () );
		logger ( LL_INF, "T" . ($i + 2) . " valid: " . (($t->isValid ()) ? ("true") : ("false")) );
		$tarr [] = $t;
	}

	$b = new Block ( "Genesis block" );
	$b->addTransaction ( $t1 );
	$b->addTransactions ( $tarr );

	$b->sign ();
	logger ( LL_INF, "B1 valid: " . (($b->isValid ()) ? ("true") : ("false")) );
	// print_r ( $b );

	$payload = $b->toPayload ();

	$b = (new Block ())->fromPayload ( $payload );
	logger ( LL_INF, "B2 valid: " . (($b->isValid ()) ? ("true") : ("false")) );
	// print_r ( $b );

	// Testing in here

	foreach ( $b->transactions as $k => $txn ) {
		$t = (new Transaction ())->fromPayload ( $txn->payload, $txn->signature );
		logger ( LL_INF, "T" . ($k + 1) . " valid: " . (($t->isValid ()) ? ("true") : ("false")) );
	}

	if (! BlockStore::putBlock ( $b )) {
		logger ( LL_ERR, "Unable to put block into block store" );
	} else {
		$b = BlockStore::getBlock ( $b->hash );
		logger ( LL_INF, "B3 valid: " . (($b->isValid ()) ? ("true") : ("false")) );

		foreach ( $b->transactions as $k => $txn ) {
			$t = (new Transaction ())->fromPayload ( $txn->payload, $txn->signature );
			logger ( LL_INF, "T" . ($k + 1) . " valid: " . (($t->isValid ()) ? ("true") : ("false")) );
		}
	}

	$logger->setLevel ( $ll );
}

function transactionToBlock() {
	global $logger;
	logger ( LL_DBG, "################################################################" );
	logger ( LL_DBG, "#" );
	logger ( LL_DBG, "# Processing transactions into a block" );
	logger ( LL_DBG, "#" );
	logger ( LL_DBG, "################################################################" );

	logger ( LL_DBG, "Getting pending transactions" );
	$deltas = array ();

	// Start a timer for debugging later
	$pt = new ProcessTimer ();
	$txns = PendingTransactionStore::getInstance ()->getTransactions ();

	if (count ( $txns ) > 0) {
		InfoStore::setMinedShares ( InfoStore::getMinedShares () + count ( $txns ) );

		// Store the current log level so we can switch it off and restore it later.
		$ll = $logger->getLevel ();
		$logger->setLevel ( LL_WRN );

		// Iterate through transactions and create a deltas list.
		logger ( LL_DBG, "Gathering wallet deltas" );
		foreach ( $txns as $txn ) {
			if ($txn->isServiceable ( true )) {
				$payload = json_decode ( $txn->getPayload () );
				logger ( LL_DBG, "Processing transaction (" . $payload->amount . ")" );
				$deltas [$payload->from] = ($deltas [$payload->from] ?? 0) - $payload->amount;
				$deltas [$payload->to] = ($deltas [$payload->to] ?? 0) + $payload->amount;
			} else {
				logger ( LL_ERR, "Invalid transaction (" . $txn->getReason () . ")" );
			}
		}

		// Get the wallet deltas applied
		logger ( LL_DBG, "Applying the wallet deltas" );
		$suspect = UserStore::getInstance ()->updateWalletBalances ( $deltas );
		if ($suspect) {
			// do something to weeld out the ids I just got passed.
		}

		logger ( LL_DBG, "Applying the transaction cleanup" );
		if (UserStore::getInstance ()->applyWalletBalances ()) {
			PendingTransactionStore::getInstance ()->clearTransactions ();
		} else {
			// An error occurred
			// remove transactions that are thrown back.
		}

		// Reset Log levels
		$logger->setLevel ( $ll );
		$t = $pt->duration ();

		$str = number_format ( count ( $txns ) ) . " transactions: " . number_format ( $t, 3 ) . "s";
		if (count ( $txns ) > 0) {
			$str .= " (" . number_format ( count ( $txns ) / $t, 2 ) . " t/s)";
		}
		logger ( LL_INF, "Processed " . $str );
		InfoStore::getInstance ()->setInfo ( blockDebugInfoKey (), $str );

		logger ( LL_XDBG, "Deltas: " . ob_print_r ( $deltas ) );

		if (InfoStore::getLastBlockHash () == "") {
			logger ( LL_INF, "Creating Genesis block" );
			$b = new Block ( "Genesis block" );
			$b->sign ();
			BlockStore::putBlock ( $b );
		}

		logger ( LL_DBG, "Creating transaction block" );
		$b = new Block ();
		$b->addTransactions ( $txns );
		$b->sign ();
		logger ( LL_DBG, "Last block '" . $b->last_hash . "'" );
		logger ( LL_DBG, "New block '" . $b->hash . "'" );
		if (! $b->isValid ()) {
			logger ( LL_ERR, "Block is invalid :( " );
		} else {
			if (! BlockStore::putBlock ( $b )) {
				logger ( LL_ERR, "Block cannot be put into the store :( " );
			} else {
				logger ( LL_INF, "Block stored :) " );
			}
		}
	} else {
		logger ( LL_INF, "No transactions to put in a block" );
	}
}

?>