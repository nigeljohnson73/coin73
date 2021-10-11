<?php

class JobStoreHouseKeeper extends JobStore {

	protected function __construct() {
		logger(LL_DBG, "JobStoreHouseKeeper::JobStoreHouseKeeper()");
		parent::__construct();
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
	public static function tidyUp() {
		$store = self::getInstance();
		logger(LL_DBG, "JobStore::tidyUp(): started");
		$older = microtime(true) - (minerSubmitMaxSeconds() + 1);
		if (usingGae()) {
			$gql = "SELECT * FROM " . $store->kind . " WHERE created < @key";
			$store->obj_store->query($gql, [
				'key' => $older
			]);
			while ($arr_page = $store->obj_store->fetchPage(transactionsPerPage())) {
				logger(LL_DBG, "JobStore::tidyUp(): pulled " . count($arr_page) . " records");
				// $this->active_transactions = array_merge ( $this->active_transactions, $arr_page );
				$store->obj_store->delete($arr_page);
			}
		} else {
			$sql = "DELETE FROM " . $store->kind . " WHERE created < " . $older;
			//logger(LL_DBG, MySqlDb::queryExplode ( $sql ));
			MySqlDb::query($sql);
		}
		logger(LL_DBG, "JobStore::tidyUp(): complete");
	}

	public static function __reset() {
		$store = self::getInstance();
		if (usingGae()) {
			$gql = "SELECT * FROM " . $store->kind;
			$store->obj_store->query($gql);
			while ($arr_page = $store->obj_store->fetchPage(transactionsPerPage())) {
				logger(LL_DBG, $store->kind . "Store::reset(): deleting " . count($arr_page) . " records");
				$store->obj_store->delete($arr_page);
			}
		} else {
			$sql = "DELETE FROM " . $store->kind;
			MySqlDb::query($sql);
		}
	}
}
