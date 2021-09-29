<?php

class UserStoreHouseKeeper extends UserStore {

	protected function __construct() {
		logger ( LL_DBG, "UserStoreHouseKeeper::UserStoreHouseKeeper()" );
		parent::__construct ();
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
		logger ( LL_DBG, "UserStore::tidyUp(): started" );

		// Wipe any recovery requests
		$older = timestampAdd ( timestampNow (), - tokenTimeoutHours () * 60 * 60 );
		$gql = "SELECT * FROM " . $this->kind . " WHERE recovery_requested > 0 AND recovery_requested < @key";
		logger ( LL_DBG, "UserStore::tidyUp(): removing recovery tokens" );
		// logger ( LL_DBG, "UserStore::tidyUp(): '" . $gql . "'" );
		// logger ( LL_DBG, "UserStore::tidyUp(): @key: '" . $older . "'" );
		logger ( LL_DBG, "UserStore::tidyUp(): '" . str_replace ( "@key", $older, $gql ) . "'" );
		$this->obj_store->query ( $gql, [ 
				'key' => $older
		] );
		while ( $arr_page = $this->obj_store->fetchPage ( transactionsPerPage () ) ) {
			logger ( LL_DBG, "UserStore::tidyUp(): pulled " . count ( $arr_page ) . " recovery records" );
			foreach ( $arr_page as $a ) {
				$a->recovery_requested = "";
				$a->recovery_nonce = "";
				$a->recovery_data = "";
			}
			$this->obj_store->upsert ( $arr_page );
		}

		// Wipe any validation requests
		$older = timestampAdd ( timestampNow (), - tokenTimeoutHours () * 60 * 60 );
		$gql = "SELECT * FROM " . $this->kind . " WHERE validation_requested  > 0 AND validation_requested < @key";
		logger ( LL_DBG, "UserStore::tidyUp(): removing validation tokens" );
		// logger ( LL_DBG, "UserStore::tidyUp(): '" . $gql . "'" );
		// logger ( LL_DBG, "UserStore::tidyUp(): @key: '" . $older . "'" );
		logger ( LL_DBG, "UserStore::tidyUp(): '" . str_replace ( "@key", $older, $gql ) . "'" );
		$this->obj_store->query ( $gql, [ 
				'key' => $older
		] );
		while ( $arr_page = $this->obj_store->fetchPage ( transactionsPerPage () ) ) {
			logger ( LL_DBG, "UserStore::tidyUp(): pulled " . count ( $arr_page ) . " validation records" );
			foreach ( $arr_page as $a ) {
				$a->validation_nonce = "";
			}
			$this->obj_store->upsert ( $arr_page );
		}

		// Lock any accounts that have not revalidated in the grace period
		$older = timestampAdd ( timestampNow (), - actionGraceDays () * 24 * 60 * 60 );
		$gql = "SELECT * FROM " . $this->kind . " WHERE locked = 0 AND validation_reminded > 0 AND validation_reminded < @key";
		logger ( LL_DBG, "UserStore::tidyUp(): locking non-compliant validation requests" );
		// logger ( LL_DBG, "UserStore::tidyUp(): '" . $gql . "'" );
		// logger ( LL_DBG, "UserStore::tidyUp(): @key: '" . $older . "'" );
		logger ( LL_DBG, "UserStore::tidyUp(): '" . str_replace ( "@key", $older, $gql ) . "'" );
		$this->obj_store->query ( $gql, [ 
				'key' => $older
		] );
		while ( $arr_page = $this->obj_store->fetchPage ( transactionsPerPage () ) ) {
			logger ( LL_DBG, "UserStore::tidyUp(): pulled " . count ( $arr_page ) . " validation lock records" );
			foreach ( $arr_page as $a ) {
				$a->locked = timestampNow ();
			}
			$this->obj_store->upsert ( $arr_page );
		}

		logger ( LL_DBG, "UserStore::tidyUp(): complete" );
	}

	protected function _requestRevalidations() {
		logger ( LL_DBG, "UserStore::requestRevalidations(): started" );

		// send email reminders to accounts that need to get validated
		$older = timestampAdd ( timestampNow (), - revalidationPeriodDays () * 24 * 60 * 60 );
		// $gql = "SELECT * FROM " . $this->kind . " WHERE locked = 0 AND validation_reminded = 0 AND validated < @key";
		$gql = "SELECT * FROM " . $this->kind . " WHERE validated < @key";
		logger ( LL_DBG, "UserStore::tidyUp(): sending revalidation reminders" );
		logger ( LL_DBG, "UserStore::requestRevalidations(): '" . str_replace ( "@key", $older, $gql ) . "'" );
		$this->obj_store->query ( $gql, [ 
				'key' => $older
		] );

		$arr_page = $this->obj_store->fetchPage ( transactionsPerPage () );
		logger ( LL_DBG, "UserStore::requestRevalidations(): pulled " . count ( $arr_page ) . " validation request records" );

		foreach ( $arr_page as $a ) {
			$data = $a->getData ();
			// if ($data ["locked"] == 0 && $data ["validation_reminded"] == 0 && $data ["validated"] < $older) {
			if ($data ["validated"] < $older) {
				logger ( LL_DBG, "    Seems legit" );
			} else {
				// TODO: Stop coming in here
				logger ( LL_DBG, "    Datastore lied!!!" );
				$dbg = array ();
				// $dbg["locked"] = $data["locked"];
				// $dbg["validation_reminded"] = $data["validation_reminded"];
				$dbg ["validated"] = $data ["validated"];
				logger ( LL_DBG, ob_print_r ( $dbg ) );
			}
			// logger ( LL_DBG, "UserStore::requestRevalidations(): Requesting for '" . $a->getData () ["email"] . "'" );
			// $this->requestValidateUser ( $a->getData () ["email"] );
		}

		logger ( LL_DBG, "UserStore::requestRevalidations(): complete" );
	}

	protected function _reset() {
		$gql = "SELECT * FROM " . $this->kind;
		$this->obj_store->query ( $gql );
		while ( $arr_page = $this->obj_store->fetchPage ( transactionsPerPage () ) ) {
			logger ( LL_DBG, $this->kind . "Store::reset(): updating " . count ( $arr_page ) . " balance records" );
			foreach ( $arr_page as $user ) {
				$user->balance = 0;
			}
			$this->obj_store->upsert ( $arr_page );
		}
	}

	public static function tidyUp() {
		self::getInstance ()->_tidyUp ();
	}

	public static function requestRevalidations() {
		self::getInstance ()->_requestRevalidations ();
	}

	public static function __reset() {
		self::getInstance ()->_reset ();
	}
}
?>