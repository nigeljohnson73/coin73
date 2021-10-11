<?php
include_once(__DIR__ . "/DataStore.php");

class TransactionStore extends DataStore {

	protected function __construct() {
		logger(LL_DBG, "TransactionStore::TransactionStore()");

		parent::__construct("Transaction");

		$this->addField("txn_id", "String", true, true); // indexed and key
		$this->addField("created", "Float", true);
		$this->addField("sender", "String");
		$this->addField("recipient", "String");
		$this->addField("amount", "Float");
		$this->addField("message", "String");
		$this->addField("payload", "String");
		$this->addField("hash", "String");
		$this->addField("signature", "String");

		$this->init();
		$this->active_transactions = array();
		$this->reason = "";
	}

	public static function insert($arr) {
		logger(LL_DBG, "TransactionStore::insert()");

		// Allow the coinbase to insert as many transctions as it likes.
		if ($arr["sender"] == coinbaseWalletId()) {
			$arr["txn_id"] = GUIDv4() . "-" . timestampNow();
		} else {
			$arr["txn_id"] = $arr["sender"];
		}

		$ret = parent::insert($arr);
		if (!$ret) {
			self::getInstance()->reason = "Unable to submit transaction to this block";
		}
		return $ret;
	}

	public static function getReason() {
		return self::getInstance()->reason;
	}

	public static function addTransaction($t) {
		if (!$t->isServiceable()) {
			self::getInstance()->reason = $t->getReason();
			logger(LL_ERR, "TransactionStore::addTransaction(): transaction is not serviceable");
			logger(LL_ERR, "    Reason: '" . $t->getReason() . "'");
			return null;
		}

		return self::insert($t->unload());
	}

	public static function getTransactions() {
		$store = self::getInstance();
		$ret = array();
		if (usingGae()) {

			$store->obj_store->query("SELECT * FROM " . $store->kind);
			while (count($store->active_transactions) < transactionsPerBlock() && $arr_page = $store->obj_store->fetchPage(transactionsPerPage())) {
				logger(LL_DBG, "Transactions::getTransactions(): pulled " . count($arr_page) . " records");
				$store->active_transactions = array_merge($store->active_transactions, $arr_page);
				// $this->obj_store->delete ( $arr_page );
			}
			logger(LL_DBG, "Transactions::getTransactions(): total of " . count($store->active_transactions) . " records");

			foreach ($store->active_transactions as $txn) {
				$ret[] = (new Transaction())->load($txn->getData());
			}
		} else {
			$sql = "SELECT * FROM " . $store->kind;
			$store->active_transactions = MySqlDb::query($sql);
			foreach ($store->active_transactions as $txn) {
				$ret[] = (new Transaction())->load($txn);
			}
		}

		return $ret;
	}

	public static function clearTransactions() {
		$store = self::getInstance();
		if (usingGae()) {
			while (count($store->active_transactions)) {
				$arr_page = array_splice($store->active_transactions, 0, transactionsPerPage());
				logger(LL_DBG, "Transactions::clearTransactions(): deleting " . count($arr_page) . " records");
				$store->obj_store->delete($arr_page);
			}
		} else {
			$sql = "DELETE FROM " . $store->kind;
			MySqlDb::query($sql);
		}
	}
}
