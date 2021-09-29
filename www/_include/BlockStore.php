<?php
include_once (__DIR__ . "/FileStore.php");

class BlockStore extends FileStore {

	protected function __construct() {
		logger ( LL_DBG, "BlockStore::BlockStore()" );

		$options = array ();
		$options ["defaultObjectAcl"] = "publicRead";
		// $options ["storageClass"] = "ARCHIVE" // TODO: once we go live do this in live

		parent::__construct ( "BlockStore", $options );
	}

	public static function putBlock($b) {
// 		if (! $b->isValid ()) {
// 			logger ( LL_ERR, "BlockStore::putBlock(): Cannot add an invalid block" );
// 			return false;
// 		}
		if (! self::getInstance ()->putContents ( $b->hash, $b->toPayload () )) {
			logger ( LL_ERR, "BlockStore::putBlock(): put contents failed" );
			return false;
		}

		//InfoStore::setLastBlockHash ( $b->hash );
		//InfoStore::setBlockCount ( InfoStore::getBlockCount () + 1 );

		return true;
	}

	public static function getBlock($id) {
		$payload = self::getInstance ()->getContents ( $id );
		if (! $payload) {
			logger ( LL_ERR, "BlockStore::getBlock(): block '" . $id . "' cannot be found" );
			return false;
		}
		return (new Block ())->fromPayload ( $payload );
	}

	public static function getLastBlock() {
		return BlockStore::getBlock ( InfoStore::getLastBlockHash () );
	}
}
?>