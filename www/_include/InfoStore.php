<?php
include_once (__DIR__ . "/DataStore.php");

class InfoStore extends DataStore {

	protected function __construct() {
		logger ( LL_DBG, "InfoStore::InfoStore()" );

		parent::__construct ( "Info" );

		$this->addField ( "key", "String", true, true ); // indexed and key
		$this->addField ( "value", "String" ); // indexed
		$this->addField ( "updated", "Float" );

		$this->init ();
		$this->local = array ();
	}

	public function insert($arr) {
		$arr ["updated"] = microtime ( true );
		return parent::insert ( $arr );
	}

	public function update($arr) {
		$arr ["updated"] = microtime ( true );
		return parent::update ( $arr );
	}

	public function getInfo($key, $fallback = null) {
		if (isset ( $this->local [$key] )) {
			logger ( LL_XDBG, "InfoStore::getInfo('$key') - locally cached" );
			return $this->local [$key];
		}

		$arr = $this->getItemById ( $key );
		if (! $arr) {
			if ($this->insert ( [ 
					"key" => $key,
					"value" => $fallback
			] )) {
				logger ( LL_XDBG, "InfoStore::getInfo('$key') - creating fallback" );
			} else {
				logger ( LL_XDBG, "InfoStore::getInfo('$key') - datastore insert failed" );
			}
			$this->local [$key] = $fallback;
			return $fallback;
		}

		logger ( LL_XDBG, "InfoStore::getInfo('$key') - database value returned" );
		return $arr ["value"];
	}

	public function setInfo($key, $value) {
		$this->local [$key] = $value;
		$arr = [ 
				"key" => $key,
				"value" => $value
		];
		if (! $this->update ( $arr )) {
			logger ( LL_XDBG, "InfoStore::setInfo('$key') - update failed" );
			if (! $this->insert ( $arr )) {
				logger ( LL_WRN, "InfoStore::setInfo('$key') - insert failed - the sky will now fall" );
				return false;
			}
		}
		return true;
	}

	protected function _getAll() {
		$data = array ();
		$gql = "SELECT * FROM " . $this->kind;
		$this->obj_store->query ( $gql );
		while ( count ( $data ) < transactionsPerBlock () && $arr_page = $this->obj_store->fetchPage ( transactionsPerPage () ) ) {
			logger ( LL_DBG, "InfoStore::_getAll(): pulled " . count ( $arr_page ) . " records" );
			$data = array_merge ( $data, $arr_page );
			// $store->delete ( $arr_page );
		}

		$ret = array ();
		if ($data) {
			foreach ( $data as $r ) {
				$x = $r->getData ();
				$ret [$x ["key"]] = $x ["value"];
			}
		}

		return $ret;
	}

	public static function getAll() {
		return InfoStore::getInstance ()->_getAll ();
	}

	public static function getCirculation() {
		return InfoStore::getInstance ()->getInfo ( circulationInfoKey (), 0 );
	}

	public static function setCirculation($v) {
		return InfoStore::getInstance ()->setInfo ( circulationInfoKey (), $v );
	}

	public static function getMinedShares() {
		return InfoStore::getInstance ()->getInfo ( minedSharesInfoKey (), 0 );
	}

	public static function setMinedShares($v) {
		return InfoStore::getInstance ()->setInfo ( minedSharesInfoKey (), $v );
	}

	public static function getLastBlockHash() {
		return InfoStore::getInstance ()->getInfo ( lastBlockHashInfoKey (), "" );
	}

	public static function setLastBlockHash($v) {
		return InfoStore::getInstance ()->setInfo ( lastBlockHashInfoKey (), $v );
	}

	public static function getBlockCount() {
		return InfoStore::getInstance ()->getInfo ( blockCountInfoKey (), 0 );
	}

	public static function setBlockCount($v) {
		return InfoStore::getInstance ()->setInfo ( blockCountInfoKey (), $v );
	}

	public static function isBlockBusy() {
		return InfoStore::getInstance ()->getInfo ( switchKeyBlockBusy (), "NO" ) == "YES";
	}

	public static function setBlockBusy($v) {
		return InfoStore::getInstance ()->setInfo ( switchKeyBlockBusy (), strtoupper ( $v ) );
	}

	public static function signupEnabled() {
		return strtolower ( InfoStore::getInstance ()->getInfo ( switchKeySignup (), switchEnabled () ) ) == strtolower ( switchEnabled () );
	}

	public static function loginEnabled() {
		return strtolower ( InfoStore::getInstance ()->getInfo ( switchKeyLogin (), switchEnabled () ) ) == strtolower ( switchEnabled () );
	}

	public static function miningEnabled() {
		return strtolower ( InfoStore::getInstance ()->getInfo ( switchKeyMining (), switchEnabled () ) ) == strtolower ( switchEnabled () );
	}

	public static function blockCreationEnabled() {
		return strtolower ( InfoStore::getInstance ()->getInfo ( switchKeyBlockCreation (), switchEnabled () ) ) == strtolower ( switchEnabled () );
	}

	public static function transactionsEnabled() {
		return strtolower ( InfoStore::getInstance ()->getInfo ( switchKeyTransactions (), switchEnabled () ) ) == strtolower ( switchEnabled () );
	}
	
}
?>