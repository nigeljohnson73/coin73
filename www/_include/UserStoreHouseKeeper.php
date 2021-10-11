<?php

class UserStoreHouseKeeper extends UserStore {

	protected function __construct() {
		logger(LL_DBG, "UserStoreHouseKeeper::UserStoreHouseKeeper()");
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
		logger(LL_DBG, "UserStore::tidyUp(): started");

		if (usingGae()) {
			// Wipe any recovery requests
			$older = timestampAdd(timestampNow(), -tokenTimeoutHours() * 60 * 60);
			$gql = "SELECT * FROM " . $store->kind . " WHERE recovery_requested > 0 AND recovery_requested < @key";
			logger(LL_DBG, "UserStore::tidyUp(): removing recovery tokens");
			// logger ( LL_DBG, "UserStore::tidyUp(): '" . $gql . "'" );
			// logger ( LL_DBG, "UserStore::tidyUp(): @key: '" . $older . "'" );
			logger(LL_DBG, "UserStore::tidyUp(): '" . str_replace("@key", $older, $gql) . "'");
			$store->obj_store->query($gql, [
				'key' => $older
			]);
			while ($arr_page = $store->obj_store->fetchPage(transactionsPerPage())) {
				logger(LL_DBG, "UserStore::tidyUp(): pulled " . count($arr_page) . " recovery records");
				foreach ($arr_page as $a) {
					$a->recovery_requested = "";
					$a->recovery_nonce = "";
					$a->recovery_data = "";
				}
				$store->obj_store->upsert($arr_page);
			}

			// Wipe any validation requests
			$older = timestampAdd(timestampNow(), -tokenTimeoutHours() * 60 * 60);
			$gql = "SELECT * FROM " . $store->kind . " WHERE validation_requested > 0 AND validation_requested < @key";
			logger(LL_DBG, "UserStore::tidyUp(): removing validation tokens");
			// logger ( LL_DBG, "UserStore::tidyUp(): '" . $gql . "'" );
			// logger ( LL_DBG, "UserStore::tidyUp(): @key: '" . $older . "'" );
			logger(LL_DBG, "UserStore::tidyUp(): '" . str_replace("@key", $older, $gql) . "'");
			$store->obj_store->query($gql, [
				'key' => $older
			]);
			while ($arr_page = $store->obj_store->fetchPage(transactionsPerPage())) {
				logger(LL_DBG, "UserStore::tidyUp(): pulled " . count($arr_page) . " validation records");
				foreach ($arr_page as $a) {
					$a->validation_nonce = "";
				}
				$store->obj_store->upsert($arr_page);
			}

			// Lock any accounts that have not revalidated in the grace period
			$older = timestampAdd(timestampNow(), -actionGraceDays() * 24 * 60 * 60);
			$gql = "SELECT * FROM " . $store->kind . " WHERE locked = 0 AND validation_reminded > 0 AND validation_reminded < @key";
			logger(LL_DBG, "UserStore::tidyUp(): locking non-compliant validation requests");
			// logger ( LL_DBG, "UserStore::tidyUp(): '" . $gql . "'" );
			// logger ( LL_DBG, "UserStore::tidyUp(): @key: '" . $older . "'" );
			logger(LL_DBG, "UserStore::tidyUp(): '" . str_replace("@key", $older, $gql) . "'");
			$store->obj_store->query($gql, [
				'key' => $older
			]);
			while ($arr_page = $store->obj_store->fetchPage(transactionsPerPage())) {
				logger(LL_DBG, "UserStore::tidyUp(): pulled " . count($arr_page) . " validation lock records");
				foreach ($arr_page as $a) {
					$a->locked = timestampNow();
				}
				$store->obj_store->upsert($arr_page);
			}
		} else {
			$older = timestampAdd(timestampNow(), -tokenTimeoutHours() * 60 * 60);
			$sql = "UPDATE " . $store->kind . " SET recovery_requested = '', recovery_nonce = '', recovery_data = '' WHERE recovery_requested > 0 AND recovery_requested < " . $older;
			MySqlDb::query($sql);

			$sql = "UPDATE " . $store->kind . " SET validation_nonce = '' WHERE validation_requested > 0 AND validation_requested < " . $older;
			MySqlDb::query($sql);

			$older = timestampAdd(timestampNow(), -actionGraceDays() * 24 * 60 * 60);
			$gql = "UPDATE " . $store->kind . " SET locked = " . timestampNow() . " WHERE locked = 0 AND validation_reminded > 0 AND validation_reminded < " . $older;
			MySqlDb::query($sql);
		}
		logger(LL_DBG, "UserStore::tidyUp(): complete");
	}

	public static function requestRevalidations() {
		// $store = self::getInstance ();
		// logger ( LL_DBG, "UserStore::requestRevalidations(): started" );

		// // send email reminders to accounts that need to get validated
		// $older = timestampAdd ( timestampNow (), - revalidationPeriodDays () * 24 * 60 * 60 );
		// // $gql = "SELECT * FROM " . $this->kind . " WHERE locked = 0 AND validation_reminded = 0 AND validated < @key";
		// $gql = "SELECT * FROM " . $store->kind . " WHERE validated < @key";
		// logger ( LL_DBG, "UserStore::tidyUp(): sending revalidation reminders" );
		// logger ( LL_DBG, "UserStore::requestRevalidations(): '" . str_replace ( "@key", $older, $gql ) . "'" );
		// $store->obj_store->query ( $gql, [
		// 'key' => $older
		// ] );

		// $arr_page = $store->obj_store->fetchPage ( transactionsPerPage () );
		// logger ( LL_DBG, "UserStore::requestRevalidations(): pulled " . count ( $arr_page ) . " validation request records" );

		// foreach ( $arr_page as $a ) {
		// $data = $a->getData ();
		// // if ($data ["locked"] == 0 && $data ["validation_reminded"] == 0 && $data ["validated"] < $older) {
		// if ($data ["validated"] < $older) {
		// logger ( LL_DBG, " Seems legit" );
		// } else {
		// // TODO: Stop coming in here
		// logger ( LL_DBG, " Datastore lied!!!" );
		// $dbg = array ();
		// // $dbg["locked"] = $data["locked"];
		// // $dbg["validation_reminded"] = $data["validation_reminded"];
		// $dbg ["validated"] = $data ["validated"];
		// logger ( LL_DBG, ob_print_r ( $dbg ) );
		// }
		// // logger ( LL_DBG, "UserStore::requestRevalidations(): Requesting for '" . $a->getData () ["email"] . "'" );
		// // $this->requestValidateUser ( $a->getData () ["email"] );
		// }

		// logger ( LL_DBG, "UserStore::requestRevalidations(): complete" );
	}

	public static function __reset() {
		$store = self::getInstance();
		if (usingGae()) {
			$gql = "SELECT * FROM " . $store->kind;
			$store->obj_store->query($gql);
			while ($arr_page = $store->obj_store->fetchPage(transactionsPerPage())) {
				logger(LL_DBG, $store->kind . "Store::reset(): updating " . count($arr_page) . " balance records");
				foreach ($arr_page as $user) {
					$user->balance = 0;
				}
				$store->obj_store->upsert($arr_page);
			}
		} else {
			$sql = "UPDATE " . $store->kind . " SET balance = 0";
			MySqlDb::query($sql);
		}
	}
}
