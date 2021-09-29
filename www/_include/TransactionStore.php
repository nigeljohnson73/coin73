<?php
include_once (__DIR__ . "/DataStore.php");

class TransactionStore extends DataStore {

	protected function __construct() {
		logger ( LL_DBG, "TransactionStore::TransactionStore()" );

		parent::__construct ( "Transaction" );

		$this->addField ( "txn_id", "String", true, true ); // indexed and key
		$this->addField ( "created", "Float", true );
		$this->addField ( "from", "String" );
		$this->addField ( "to", "String" );
		$this->addField ( "amount", "Float" );
		$this->addField ( "message", "String" );
		$this->addField ( "payload", "String" );
		$this->addField ( "hash", "String" );
		$this->addField ( "signature", "String" );

		$this->init ();
		$this->active_transactions = array ();
		$this->reason = "";
	}

	public function insert($arr) {
		logger ( LL_DBG, "TransactionStore::insert()" );

		// Allow the coinbase to insert as many transctions as it likes.
		if ($arr ["from"] == coinbaseWalletId ()) {
			$arr ["txn_id"] = GUIDv4 () . "-" . timestampNow ();
		} else {
			$arr ["txn_id"] = $arr ["from"];
		}

		$ret = parent::insert ( $arr );
		if (! $ret) {
			$this->reason = "Unable to submit transaction to this block";
		}
		return $ret;
	}

	protected function _getReason() {
		return $this->reason;
	}

	public static function getReason() {
		return TransactionStore::getInstance ()->_getReason ();
	}

	protected function _addTransaction($t) {
		if (! $t->isServiceable ()) {
			$this->reason = $t->getReason ();
			logger ( LL_ERR, "TransactionStore::addTransaction(): transaction is not serviceable" );
			logger ( LL_ERR, "    Reason: '" . $t->getReason () . "'" );
			return null;
		}

		return self::insert ( $t->unload () );
	}

	public static function addTransaction($t) {
		return TransactionStore::getInstance ()->_addTransactions ( $t );
	}

	protected function _getTransactions() {
		$this->obj_store->query ( "SELECT * FROM " . $this->kind );
		while ( count ( $this->active_transactions ) < transactionsPerBlock () && $arr_page = $this->obj_store->fetchPage ( transactionsPerPage () ) ) {
			logger ( LL_DBG, "Transactions::getTransactions(): pulled " . count ( $arr_page ) . " records" );
			$this->active_transactions = array_merge ( $this->active_transactions, $arr_page );
			// $this->obj_store->delete ( $arr_page );
		}
		logger ( LL_DBG, "Transactions::getTransactions(): total of " . count ( $this->active_transactions ) . " records" );

		$ret = array ();
		foreach ( $this->active_transactions as $txn ) {
			$ret [] = (new Transaction ())->load ( $txn->getData () );
		}

		return $ret;
	}

	public static function getTransactions() {
		return TransactionStore::getInstance ()->_getTransactions ();
	}

	protected function _clearTransactions() {
		while ( count ( $this->active_transactions ) ) {
			$arr_page = array_splice ( $this->active_transactions, 0, transactionsPerPage () );
			logger ( LL_DBG, "Transactions::clearTransactions(): deleting " . count ( $arr_page ) . " records" );
			$this->obj_store->delete ( $arr_page );
		}
	}

	public static function clearTransactions() {
		TransactionStore::getInstance ()->_clearTransactions ();
	}
}

?>