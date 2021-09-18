<?php
include_once (__DIR__ . "/DataStore.php");

class PendingTransactionStore extends DataStore {

	protected function __construct() {
		logger ( LL_ERR, "PendingTransactionStore::PendingTransactionStore()" );

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
	}

	public function insert($arr) {
		logger ( LL_DBG, "PendingTransactionStore::insert()" );
		$t = (new Transaction ())->load ( $arr );
		if (! $t->isValid ()) {
			logger ( LL_ERR, "PendingTransactionStore::insert(): transaction is not valid" );
			return;
		}
		$arr = $t->unload ();
		// Allow the
		if ($arr ["from"] == coinbaseWalletId ()) {
			$arr ["txn_id"] = GUIDv4 () . "-" . timestampNow ();
		} else {
			$arr ["txn_id"] = $arr ["from"];
		}

		return parent::insert ( $arr );
	}

	public function getTransactions() {
		// $gql = "SELECT * FROM " . $this->kind . " WHERE validation_nonce = @key";
		// $data = $this->obj_store->fetchOne ( $gql, [
		// 'key' => $key
		// ] );
		// // echo "UserStore::getItemByValidationNonce('validation_nonce'=>'" . $key . "')\n";
		// // echo " '$gql'\n";
		// return ($data) ? ($data->getData ()) : ($data);
		$this->obj_store->query ( "SELECT * FROM " . $this->kind );
		while ( count ( $this->active_transactions ) < transactionsPerBlock () && $arr_page = $this->obj_store->fetchPage ( transactionsPerPage () ) ) {
			logger ( LL_DBG, "PendingTransactions::getTransactions(): pulled ". count ( $arr_page ) . " records" );
			$this->active_transactions = array_merge ( $this->active_transactions, $arr_page );
			// $store->delete ( $arr_page );
		}
		logger ( LL_DBG, "PendingTransactions::getTransactions(): total of ". count ( $this->active_transactions ) . " records" );
		
		$ret = array ();
		foreach ( $this->active_transactions as $txn ) {
			$ret [] = (new Transaction ())->load ( $txn->getData () );
		}
		
		return $ret;
	}

	public function clearTransactions() {
		while ( count ( $this->active_transactions ) ) {
			$arr_page = array_splice ( $this->active_transactions, 0, transactionsPerPage () );
			logger ( LL_DBG, "PendingTransactions::clearTransactions(): deleting ". count ( $arr_page ) . " records" );
			//$this->obj_store->delete ( $arr_page );
		}
	}
}

?>