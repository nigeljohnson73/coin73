<?php
include_once (__DIR__ . "/DataStore.php");

class TransactionStore extends DataStore {

	public function __construct() {
		logger ( LL_ERR, "TransactionStore::TransactionStore()" );

		parent::__construct ( "Transaction" );

		$this->addField ( "txn_id", "String", true, true ); // indexed and key
		$this->addField ( "from", "String" );
		$this->addField ( "to", "String" );
		$this->addField ( "amount", "Float" );
		$this->addField ( "message", "String" );
		$this->addField ( "payload", "String" );
		$this->addField ( "hash", "String" );
		$this->addField ( "signature", "String" );

		$this->init ();
	}

	public function insert($arr) {
		logger ( LL_DBG, "TransactionStore::insert()" );
		$t = (new Transaction ())->load ( $arr );
		if (! $t->isValid ()) {
			logger ( LL_ERR, "TransactionStore::insert(): transaction is not valid" );
			return;
		}
		$arr = $t->unload ();
		if ($arr ["from"] == coinbaseWalletId ()) {
			$arr ["txn_id"] = GUIDv4 () . "-" . timestampNow ();
		} else {
			$arr ["txn_id"] = $arr ["from"];
		}

		return parent::insert ( $arr );
	}
}

?>