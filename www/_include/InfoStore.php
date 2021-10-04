<?php
include_once (__DIR__ . "/DataStore.php");

class InfoStore extends DataStore {

	protected function __construct() {
		logger ( LL_DBG, "InfoStore::InfoStore()" );

		parent::__construct ( "Info" );

		$this->addField ( "xkey", "String", true, true ); // indexed and key
		$this->addField ( "value", "String" ); // indexed
		$this->addField ( "updated", "Float" );

		$this->init ();
		$this->local = array ();
	}

	public static function insert($arr) {
		$arr ["updated"] = microtime ( true );
		return parent::insert ( $arr );
	}

	public static function update($arr) {
		$arr ["updated"] = microtime ( true );
		return parent::update ( $arr );
	}

	protected function _get($key, $fallback = null) {
		if (isset ( $this->local [$key] )) {
			logger ( LL_XDBG, "InfoStore::getInfo('$key') - locally cached" );
			return $this->local [$key];
		}

		$arr = self::getItemById ( $key );
		if (! $arr) {
			if (self::insert ( [ 
					"xkey" => $key,
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

	public static function get($key, $fallback = null) {
		return InfoStore::getInstance ()->_get ( $key, $fallback );
	}

	protected function _set($key, $value) {
		$this->local [$key] = $value;
		$arr = [ 
				"xkey" => $key,
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

	public static function set($key, $value) {
		return InfoStore::getInstance ()->_set ( $key, $value );
	}

	protected function _getAll() {
		$data = array ();
		if (usingGae ()) {
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
					$ret [$x ["xkey"]] = $x ["value"];
				}
			}
		} else {
			$sql = "SELECT * FROM " . $this->kind;
			$data = MySqlDb::query ( $sql );

			$ret = array ();
			if ($data) {
				foreach ( $data as $r ) {
					// $x = $r->getData ();
					$ret [$r ["xkey"]] = $r ["value"];
				}
			}
		}

		return $ret;
	}

	public static function getAll() {
		return InfoStore::getInstance ()->_getAll ();
	}

	public static function getCirculation() {
		return InfoStore::get ( circulationInfoKey (), 0 );
	}

	public static function setCirculation($v) {
		return InfoStore::set ( circulationInfoKey (), $v );
	}

	public static function getMinedShares() {
		return InfoStore::get ( minedSharesInfoKey (), 0 );
	}

	public static function setMinedShares($v) {
		return InfoStore::set ( minedSharesInfoKey (), $v );
	}

	public static function getLastBlockHash() {
		return InfoStore::get ( lastBlockHashInfoKey (), "" );
	}

	public static function setLastBlockHash($v) {
		return InfoStore::set ( lastBlockHashInfoKey (), $v );
	}

	public static function getBlockCount() {
		return InfoStore::get ( blockCountInfoKey (), 0 );
	}

	public static function setBlockCount($v) {
		return InfoStore::set ( blockCountInfoKey (), $v );
	}

	public static function blockBusy() {
		return InfoStore::get ( switchKeyBlockBusy (), "NO" ) == "YES";
	}

	public static function setBlockBusy($v = true) {
		$v = (($v === true) || (strtoupper ( $v ) == "YES")) ? ("YES") : ("NO");
		return InfoStore::set ( switchKeyBlockBusy (), strtoupper ( $v ) );
	}

	public static function cronEnabled() {
		return InfoStore::get ( switchKeyCron (), switchEnabled () ) == switchEnabled ();
	}

	public static function setCronEnabled($v = true) {
		$v = (($v === true) || (strtoupper ( $v ) == switchEnabled ())) ? (switchEnabled ()) : ("DISABLED");
		return InfoStore::set ( switchKeyCron (), $v );
	}

	public static function signupEnabled() {
		return strtoupper ( InfoStore::get ( switchKeySignup (), switchEnabled () ) ) == strtoupper ( switchEnabled () );
	}

	public static function setSignupEnabled($v = true) {
		$v = (($v === true) || (strtoupper ( $v ) == switchEnabled ())) ? (switchEnabled ()) : ("DISABLED");
		return InfoStore::set ( switchKeySignup (), $v );
	}

	public static function loginEnabled() {
		return strtoupper ( InfoStore::get ( switchKeyLogin (), switchEnabled () ) ) == strtoupper ( switchEnabled () );
	}

	public static function setLoginEnabled($v = true) {
		$v = (($v === true) || (strtoupper ( $v ) == switchEnabled ())) ? (switchEnabled ()) : ("DISABLED");
		return InfoStore::set ( switchKeyLogin (), $v );
	}

	public static function miningEnabled() {
		return strtoupper ( InfoStore::get ( switchKeyMining (), switchEnabled () ) ) == strtoupper ( switchEnabled () );
	}

	public static function setMiningEnabled($v = true) {
		$v = (($v === true) || (strtoupper ( $v ) == switchEnabled ())) ? (switchEnabled ()) : ("DISABLED");
		return InfoStore::set ( switchKeyMining (), $v );
	}

	public static function blockCreationEnabled() {
		return strtoupper ( InfoStore::get ( switchKeyBlockCreation (), switchEnabled () ) ) == strtoupper ( switchEnabled () );
	}

	public static function setBlockCreationEnabled($v = true) {
		$v = (($v === true) || (strtoupper ( $v ) == switchEnabled ())) ? (switchEnabled ()) : ("DISABLED");
		return InfoStore::set ( switchKeyBlockCreation (), $v );
	}

	public static function transactionsEnabled() {
		return strtoupper ( InfoStore::get ( switchKeyTransactions (), switchEnabled () ) ) == strtoupper ( switchEnabled () );
	}

	public static function setTransactionsEnabled($v = true) {
		$v = (($v === true) || (strtoupper ( $v ) == switchEnabled ())) ? (switchEnabled ()) : ("DISABLED");
		return InfoStore::set ( switchKeyTransactions (), $v );
	}

	protected static function setSystemOperational($tf) {
		self::setCronEnabled ( $tf );
		self::setSignupEnabled ( $tf );
		self::setLoginEnabled ( $tf );
		self::setMiningEnabled ( $tf );
		self::setBlockCreationEnabled ( $tf );
		self::setTransactionsEnabled ( $tf );
	}

	public static function lockdown() {
		self::setSystemOperational ( false );
	}

	public static function restart() {
		self::setSystemOperational ( true );
	}
}
?>