<?php
include_once (__DIR__ . "/DataStore.php");

class JobStore extends DataStore {

	protected function __construct() {
		logger ( LL_INF, "JobStore::JobStore()" );

		parent::__construct ( "Job" );

		$this->addField ( "job_id", "String", true, true ); // indexed and key
		$this->addField ( "wallet_id", "String", true ); // indexed
		$this->addField ( "rig_id", "String" );
		$this->addField ( "hash", "String" );
		$this->addField ( "created", "Float", true ); // msTime()*1000, indexed so we can clean it up later
		$this->addField ( "difficulty", "Integer" ); // number of zeros
		$this->addField ( "shares", "Integer" ); // how many miners are now active to split this share with

		$this->init ();
	}

	public function getItemsByWalletId($key) {
		$gql = "SELECT * FROM " . $this->kind . " WHERE wallet_id = @key";
		$arr = $this->obj_store->fetchAll ( $gql, [ 
				'key' => $key
		] );
		// echo "JobStore::getItemsByWalletId('wallet_id'=>'" . $key . "')\n";
		// echo " '$gql'\n";
		if ($arr) {
			$ret = array ();
			if (is_array ( $arr )) {
				// Assume an array of GDS objects;
				foreach ( $arr as $item ) {
					logger ( LL_DBG, "JobStore::getItemsByWalletId('wallet_id'=>'...') - Got (arr) 'job_id' => '" . ($item->getData () ["job_id"]) . "'" );
					$ret [] = $item->getData ();
				}
			} else {
				logger ( LL_DBG, "JobStore::getItemsByWalletId('wallet_id'=>'...') - Got (obj) 'job_id' => '" . ($arr->getData () ["job_id"]) . "'" );
				// Assume a single GDS object;
				$ret [] = $arr->getData ();
			}
			$arr = $ret;
		}
		return $arr;
	}

	public function insert($arr) {
		$oarr = $arr; // Save it in case the insert fails!!

		$arr [$this->getKeyField ()] = GUIDv4 ();
		$arr ["created"] = msTime ();
		$arr = parent::insert ( $arr );

		if ($arr == false) {
			logger ( LL_ERR, "JobStore::insert() - failed - regenerating key" );
			// Assume the key was broken and retry
			$arr = $oarr;
			$arr [$this->getKeyField ()] = GUIDv4 ();
			$arr ["created"] = msTime ();
			$arr = parent::insert ( $arr );
			if ($arr == false) {
				logger ( LL_ERR, "JobStore::insert() - failed again - You got bigger problems" );
			}
			// if it's still bust... well we all screwed
		}
		// Nothing else to do...
		return $arr;
	}

	// https://stackoverflow.com/questions/1820908/how-to-turn-off-the-eclipse-code-formatter-for-certain-sections-of-java-code
	// https://patorjk.com/software/taag/#p=display&f=Standard&t=Housekeeping
	// @formatter:off
	//  _   _                      _                   _
	// | | | | ___  _   _ ___  ___| | _____  ___ _ __ (_)_ __   __ _
	// | |_| |/ _ \| | | / __|/ _ \ |/ / _ \/ _ \ '_ \| | '_ \ / _` |
	// |  _  | (_) | |_| \__ \  __/   <  __/  __/ |_) | | | | | (_| |
	// |_| |_|\___/ \__,_|___/\___|_|\_\___|\___| .__/|_|_| |_|\__, |
	//                                          |_|            |___/
	// @formatter:on
	protected function _tidyUp() {
		logger ( LL_DBG, "JobStore::tidyUp(): started" );
		$older = microtime ( true ) - (minerSubmitMaxSeconds () + 1);
		$gql = "SELECT * FROM " . $this->kind . " WHERE created < @key";
		$this->obj_store->query ( $gql, [ 
				'key' => $older
		] );
		while ( $arr_page = $this->obj_store->fetchPage ( transactionsPerPage () ) ) {
			logger ( LL_DBG, "JobStore::tidyUp(): pulled " . count ( $arr_page ) . " records" );
			// $this->active_transactions = array_merge ( $this->active_transactions, $arr_page );
			$this->obj_store->delete ( $arr_page );
		}
		logger ( LL_DBG, "JobStore::tidyUp(): complete" );
	}

	public static function tidyUp() {
		self::getInstance ()->_tidyUp ();
	}
}
?>