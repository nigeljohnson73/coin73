<?php

class BlockStoreHouseKeeper extends BlockStore {

	protected function __construct() {
		logger ( LL_DBG, "BlockStoreHouseKeeper::BlockStoreHouseKeeper()" );
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
	public static function __reset() {
		$store = self::getInstance ();
		$c = 0;
		foreach ( $store->bucket->objects () as $object ) {
			$c += 1;
			Logger ( LL_SYS, "BlockStore::reset(): Deleting object: '" . $object->name () . "'" );
			$object->delete ();
		}
		if ($c > 0) {
			logger ( LL_SYS, "BlockStore::reset(): Deleted $c objects" );
		}
	}

	public static function generateNextBlock() {
		if (! InfoStore::blockCreationEnabled ()) {
			// DebugStore::log ( "Block creation is disabled" );
			logger ( LL_WRN, "Block management is disabled" );
			return;
		}
		if (InfoStore::isBlockBusy ()) {
			// DebugStore::log ( "Block creation already active" );
			logger ( LL_WRN, "Block management already active" );
			return;
		}

		// global $logger;
		logger ( LL_DBG, "################################################################" );
		logger ( LL_DBG, "#" );
		logger ( LL_DBG, "# Processing transactions into a block" );
		logger ( LL_DBG, "#" );
		logger ( LL_DBG, "################################################################" );

		logger ( LL_DBG, "Getting pending transactions" );

		// Store any failures here
		$suspect = [ ];
		$reprocess = [ ];

		InfoStore::setBlockBusy ( "YES" );
		// Start a timer for debugging later
		$pt = new ProcessTimer ();

		// $ipt = new ProcessTimer ();
		$txns = TransactionStore::getTransactions ();

		if (count ( $txns ) > 0) {
			$deltas = array ();

			// DebugStore::log ( "Starting txns -> block process" );
			// DebugStore::log ( "*** " . durationFormat ( $ipt->duration () ) . " getTransactions() (TS: " . durationFormat ( $pt->duration () ) . ")" );
			try {

				// DebugStore::log ( "clearTransactions()" );
				// logger ( LL_DBG, "clearTransactions()" );
				// $ipt = new ProcessTimer ();
				TransactionStore::clearTransactions ();
				// DebugStore::log ( "*** " . durationFormat ( $ipt->duration () ) . " clearTransactions() (TS: " . durationFormat ( $pt->duration () ) . ")" );

				// Iterate through transactions and create a deltas list.
				// DebugStore::log ( "validateTransactions()" );
				// logger ( LL_DBG, "validateTransactions()" );
				$mined_shares = 0;
				// $ipt = new ProcessTimer ();
				$audit = [ ];
				foreach ( $txns as $k => $txn ) {
					// logger ( LL_SYS, "Got transaction: " . ob_print_r ( $txn->unload () ) );
					// Just do a simple check here, we make the assumption that the storage is secure.
					// We also don't care if the reciever does not exist. :(
					if ($txn->isValid ( false )) {
						if (strpos ( $txn->message, minerRewardLabel () ) === 0) {
							$mined_shares += 1;
						} else {
							$audit [] = $txn;
						}
						$payload = json_decode ( $txn->getPayload () );
						// logger ( LL_DBG, "Processing transaction (" . $payload->amount . ")" );
						$deltas [$payload->from] = ($deltas [$payload->from] ?? 0) - $payload->amount;
						$deltas [$payload->to] = ($deltas [$payload->to] ?? 0) + $payload->amount;
					} else {
						logger ( LL_ERR, "Invalid transaction (" . $txn->getReason () . ")" );
						unset ( $txns [$k] );
						$suspect [] = $txn;
					}
				}
				// DebugStore::log ( "*** " . durationFormat ( $ipt->duration () ) . " validateTransactions() (TS: " . durationFormat ( $pt->duration () ) . ")" );

				if (InfoStore::getLastBlockHash () == "") {
					logger ( LL_INF, "Creating Genesis block" );
					$b = new Block ( "Genesis block" );
					$b->sign ();
					BlockStore::putBlock ( $b );
					InfoStore::setBlockCount ( 1 );
				}

				// DebugStore::log ( "Creating transaction block" );
				// logger ( LL_DBG, "Creating transaction block" );
				$b = new Block ();

				// DebugStore::log ( "Adding transactions" );
				// $ipt = new ProcessTimer ();
				$b->addTransactions ( $txns );
				// DebugStore::log ( "*** " . durationFormat ( $ipt->duration () ) . " Block::addTransactions() (TS: " . durationFormat ( $pt->duration () ) . ")" );

				// DebugStore::log ( "Signing block" );
				$b->sign ();

				// $ipt = new ProcessTimer ();
				// DebugStore::log ( "Validating block" );
				// $ipt = new ProcessTimer ();
				if ($b->isValid ( false )) {
					// DebugStore::log ( "*** " . durationFormat ( $ipt->duration () ) . " Block::isValid() (TS: " . durationFormat ( $pt->duration () ) . ")" );

					logger ( LL_DBG, "Last block '" . $b->last_hash . "'" );
					logger ( LL_DBG, "New block '" . $b->hash . "'" );
					InfoStore::setLastBlockHash ( $b->hash );
					InfoStore::setBlockCount ( InfoStore::getBlockCount () + 1 );

					// We can let the next call in now
					DebugStore::log ( "*** " . durationFormat ( $pt->duration () ) . ": " . number_format ( count ( $txns ) ) . " txns -> block" );
					InfoStore::setBlockBusy ( "NO" );

					foreach ( $audit as $t ) {
						$arr = array ();
						$arr ["from"] = $t->from;
						$arr ["to"] = $t->to;
						$arr ["amount"] = $t->amount;
						$arr ["message"] = $t->message ?? "";
						DebugStore::log ( "Txn: " . ob_print_r ( $arr ) );
						AuditStore::insert ( $arr );
					}
					// print_r($deltas);
					// Get the wallet deltas applied
					// DebugStore::log ( "Updating wallet deltas" );
					// logger ( LL_DBG, "Updating wallet deltas" );
					// $ipt = new ProcessTimer ();
					$suspect = UserStore::getInstance ()->updateWalletBalances ( $deltas );
					// DebugStore::log ( "*** " . durationFormat ( $ipt->duration () ) . " UserStore::updateWalletBalances()" );

					// DebugStore::log ( "Adding block to the blockchain" );
					// $ipt = new ProcessTimer ();
					if (BlockStore::putBlock ( $b )) {
						// DebugStore::log ( "*** " . durationFormat ( $ipt->duration () ) . " BlockStore::putBlock()" );
						// logger ( LL_INF, "Block stored :)" );
					} else {
						logger ( LL_ERR, "Block cannot be put into the store :( We should not be here" );
						$reprocess = array_merge ( $reprocess, $$txns );
					}
				} else {
					logger ( LL_ERR, "Block is invalid :( we REALLY should not be here" );
					$reprocess = array_merge ( $reprocess, $$txns );
				}

				if ($mined_shares) {
					InfoStore::setMinedShares ( InfoStore::getMinedShares () + $mined_shares );
				}
			} catch ( Exception $e ) {
				DebugStore::log ( "Block Exception: " . $e->getMessage () );
				$reprocess = array_merge ( $reprocess, $$txns );
			}

			if ($suspect) {
				// WARNING: could be a transaction, or a Wallet ID
				DebugStore::log ( "Got suspect transactions: " . ob_print_r ( $suspect ) );
				// TODO: do something to dig out transactions for this these and reverse them.
			}

			if ($reprocess) {
				DebugStore::log ( "Got invalid transactions: " . ob_print_r ( $reprocess ) );
				// TODO: these were deemed invalid or I failed somehere. Store them somewhere for review
			}
		} else {
			InfoStore::setBlockBusy ( "NO" );
			logger ( LL_INF, "No transactions to put in a block" );
		}
	}
}
?>