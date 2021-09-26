<?php
include_once (__DIR__ . "/DataStore.php");

class AuditStore extends DataStore {

	protected function __construct() {
		logger ( LL_DBG, "AuditStore::AuditStore()" );

		parent::__construct ( "Audit" );

		$this->addField ( "txn_id", "String", true, true ); // indexed and key
		$this->addField ( "created", "Float", true );
		$this->addField ( "from", "String", true);
		$this->addField ( "to", "String", true );
		$this->addField ( "amount", "Float" );
		$this->addField ( "message", "String" );

		$this->init ();
	}

	public function insert($arr) {
		logger ( LL_DBG, "AuditStore::insert()" );

		$arr ["txn_id"] = GUIDv4 ();
		$arr ["created"] = microtime(true);
		
		return parent::insert ( $arr );
	}
}

?>