<?php
include_once (__DIR__ . "/DataStore.php");

class JobStore extends DataStore {

	public function __construct() {
		logger(LL_INF, "JobStore::JobStore()");

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
					logger(LL_DBG, "JobStore::getItemsByWalletId('wallet_id'=>'...') - Got (arr) 'job_id' => '" . ($item->getData () ["job_id"]) . "'");
					$ret [] = $item->getData ();
				}
			} else {
				logger(LL_DBG, "JobStore::getItemsByWalletId('wallet_id'=>'...') - Got (obj) 'job_id' => '" . ($arr->getData () ["job_id"]) . "'");
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
		$arr ["created"] = msTime();
		$arr = parent::insert ( $arr );

		if ($arr == false) {
			logger(LL_ERR, "JobStore::insert() - failed - regenerating key");
			// Assume the key was broken and retry
			$arr = $oarr;
			$arr [$this->getKeyField ()] = GUIDv4 ();
			$arr ["created"] = timestampNow ();
			$arr = parent::insert ( $arr );
			if ($arr == false) {
				logger(LL_ERR, "JobStore::insert() - failed again - You got bigger problems");
			}
			// if it's still bust... well we all screwed
		}
		// Nothing else to do...
		return $arr;
	}
}
?>