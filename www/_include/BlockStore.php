<?php
include_once (__DIR__ . "/FileStore.php");

class BlockStore extends FileStore {

	protected function __construct() {
		logger ( LL_DBG, "BlockStore::BlockStore()" );

		$options = array ();
		$options ["defaultObjectAcl"] = "publicRead";
		// $options ["storageClass"] = "ARCHIVE" // TODO: nce we go live do this in live

		parent::__construct ( "BlockStore", $options );
	}
}
?>