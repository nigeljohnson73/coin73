<?php
include_once (dirname ( __FILE__ ) . "/../functions.php");

logger ( LL_INF, "Cron::m1(): Starting" );

if (! InfoStore::cronEnabled ()) {
	logger ( LL_INF, "Cron::m1(): Disabled by switch" );
	exit ();
}

function blockchainResetAbortHandler() {
	if (connection_status () != CONNECTION_NORMAL) {
		logger ( LL_INF, "BlockChain::resetAbortHandler(): abort/timeout occurred. Setting restart flag" );
		DebugStore::log ( "BlockChain::resetAbortHandler(): abort/timeout occurred. Setting restart flag" );
		InfoStore::set ( "switch_reset_blockchain", switchEnabled () );
	} else {
		logger ( LL_INF, "BlockChain::resetAbortHandler(): exiting normally" );
		DebugStore::log ( "BlockChain::resetAbortHandler(): exiting normally" );
	}
}

if (strtoupper ( InfoStore::get ( "switch_reset_blockchain", "DISABLED" ) ) == strtoupper ( switchEnabled () )) {
	InfoStore::set ( "blockchain_reset_circulation", InfoStore::getCirculation () . " : " . timestampFormat ( timestampNow (), "Y/m/d H:i:s" ) );
	InfoStore::set ( "switch_reset_blockchain", "DISABLED" );
	register_shutdown_function ( 'blockchainResetAbortHandler' );

	logger ( LL_INF, "Cron::m1(): Resetting blockchain" );
	DebugStore::log ( "BlockChain::reset(): Disabling block based activities" );
	InfoStore::set ( switchKeyMining (), "RESETTING" );
	InfoStore::set ( switchKeyBlockCreation (), "RESETTING" );
	InfoStore::set ( switchKeyTransactions (), "RESETTING" );

	DebugStore::log ( "BlockChain::reset(): Waiting for activity to stop" );
	sleep ( minerSubmitMaxSeconds () + 1 );

	DebugStore::log ( "BlockChain::reset(): Resetting core components" );
	UserStoreHouseKeeper::__reset ();
	JobStoreHouseKeeper::__reset ();
	TransactionStoreHouseKeeper::__reset ();
	AuditStoreHouseKeeper::__reset ();
	BlockStoreHouseKeeper::__reset ();

	DebugStore::log ( "BlockChain::reset(): Resetting core data" );
	InfoStore::setCirculation ( 0 );
	InfoStore::setMinedShares ( 0 );
	InfoStore::setBlockCount ( 0 );
	InfoStore::setLastBlockHash ( "" );

	DebugStore::log ( "BlockChain::reset(): Re-enabling block based activities" );
	InfoStore::set ( switchKeyMining (), switchEnabled () );
	InfoStore::set ( switchKeyBlockCreation (), switchEnabled () );
	InfoStore::set ( switchKeyTransactions (), switchEnabled () );
}

if (InfoStore::get ( switchKeyBlockCreation (), switchEnabled () ) == "RESETTING") {
	logger ( LL_INF, "Cron::m1(): Disabled by resetting blockchain" );
	DebugStore::log ( "Cron::m1(): Disabled by resetting blockchain" );
	exit ();
}

logger ( LL_INF, "Cron::m1(): Debug housekeeping" );
DebugStoreHouseKeeper::tidyUp ();

// TODO: should probably go into system tick
logger ( LL_INF, "Cron::m1(): Job housekeeping" );
JobStoreHouseKeeper::tidyUp ();

// TODO: Move this to the system tick handling so it's called every 10 seconds... once it's tuned
logger ( LL_INF, "Cron::m1(): Block check" );
BlockStoreHouseKeeper::generateNextBlock ();

InfoStore::set ( cronMinuteDebugInfoKey (), "Completed: " . timestampFormat ( timestampNow (), "Y/m/d H:i:s" ) );

logger ( LL_INF, "Cron::m1(): Checking for load testing" );
if (strtoupper ( InfoStore::get ( "switch_load_test_transactions", "DISABLED" ) ) == strtoupper ( switchEnabled () )) {
	InfoStore::set ( "switch_load_test_transactions", "DISABLED" );
	DebugStore::log ( "Starting test transaction load" );
	InfoStore::setBlockBusy ( "YES" );

	global $logger;
	$ll = $logger->getLevel ();
	$logger->setLevel ( LL_WRN );

	$test = transactionsPerBlock ();
	$c = 0;
	$le = false;
	global $demo_to_wallet;
	for($i = 0; $i < $test; $i ++) {
		$t = new Transaction ( coinbaseWalletId (), $demo_to_wallet, 1 / $test, minerRewardLabel () . " TESTING" );
		if ($t->sign ( coinbasePrivateKey () )) {
			logger ( LL_INF, "Storing transaction: " . ob_print_r ( $t->unload () ) );
			if (TransactionStore::getInstance ()->insert ( $t->unload () )) {
				$c += 1;
			} else {
				logger ( LL_ERR, "Insert transaction failed: " . ($t->getReason ()) );
			}
		} else {
			logger ( LL_ERR, "Signing transaction failed: " . ($t->getReason ()) );
		}
		if (($i > 0) && ($i % 100 == 0)) {
			DebugStore::log ( "Test transaction load count: " . $i . "/" . $test );
		}
	}
	$logger->setLevel ( $ll );
	DebugStore::log ( "Complete: Loaded " . $c . " test transactions" );
	InfoStore::setBlockBusy ( "NO" );
}
logger ( LL_INF, "Cron::m1(): Complete" );
?>