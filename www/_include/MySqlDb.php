<?php

class MySqlDb {
	private static $instances = [];

	protected function __clone() {
	}

	public function __wakeup() {
		throw new \Exception("Cannot unserialize a singleton.");
	}

	public static function getInstance() {
		$cls = static::class;
		if (!isset(self::$instances[$cls])) {
			self::$instances[$cls] = new static();
		}

		return self::$instances[$cls];
	}

	protected function __construct() {
		global $db_server, $db_user, $db_pass, $db_name;
		$this->conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

		// Check connection
		if ($this->conn->connect_error) {
			logger(LL_ERR, "Database connection ('$db_server', '$db_user', '$db_pass', '$db_name') failed: " . $this->conn->connect_error);
			if ($this->conn) {
				@$this->conn->close();
				$this->conn = null;
			}
		} else {
			logger(LL_DBG, "Database connection ('$db_server', '$db_user', '$db_pass', '$db_name') established");
		}
	}

	public static function getError() {
		return MySqlDb::getInstance()->conn->error;
	}

	public static function query($sql, $type = null, $params = null) {
		$mysql = MySqlDb::getInstance();

		$mysql->query = $sql;
		$stmt = $mysql->conn->prepare($mysql->query);
		if ($stmt == false) {
			logger(LL_ERR, "mySqlDb::query(): prepare statement failed: " . $sql);
			logger(LL_ERR, "mySqlDb::query(): prepare statement error: " . $mysql->conn->error);
			// echo "mySqlDb::query(): prepare statement failed: ".$this->conn->error."\n";
			return null;
		}

		if ($type) {
			$stmt->bind_param($type, ...$params);
		}
		logger(LL_XDBG, "mySqlDb::query(): " . $sql);
		logger(LL_XDBG, "mySqlDb::query(): \$stmt->execute(): " . ob_print_r(tfn($stmt->execute())));
		logger(LL_XDBG, "mySqlDb::query(): \$stmt->store_result(): " . ob_print_r(tfn($stmt->store_result())));

		$meta = $stmt->result_metadata();
		// logger ( LL_DBG, "mySqlDb::query(): \$stmt->result_metadata(): " . ob_print_r ( $meta ) );

		if ($meta) {
			$results = array();
			while ($column = $meta->fetch_field()) {
				$bindVarsArray[$column->name] = &$results[$column->name];
			}
			call_user_func_array(array(
				$stmt,
				'bind_result'
			), $bindVarsArray);

			$results = array();
			logger(LL_XDBG, "mySqlDb::query(): Statement Object: \n" . ob_print_r($stmt));
			while ($stmt->fetch()) {
				// echo "Got fetch: ".ob_print_r($bindVarsArray)."\n";
				$results[] = array_map("__my_sql_db_copy_value", $bindVarsArray);
				// var_dump ( $bindVarsArray );
			}

			$stmt->free_result();

			return $results;
		} else {
			// logger ( LL_ERR, "mySqlDb::query(): \$stmt->result_metadata() failed: " . ob_print_r ( tfn ( $this->conn->error ) ) );
			// echo "mySqlDb::query(): \$stmt->result_metadata() failed: " . ob_print_r ( tfn ( $this->conn->error ) );
		}

		logger(LL_XDBG, "mySqlDb::query(): no meta data returned");
	}

	public static function queryExplode($sql, $type = null, $args = null) {
		$t = str_split($type);
		foreach ($t as $i => $ts) {
			$repl = $args[$i];
			if ($ts == "s") {
				$repl = "'" . $repl . "'";
			}
			$sql = str_replace_first("?", $repl, $sql);
		}
		return $sql;
	}



	// public static function performSql($sql, $type = null, $params = null) {
	// $mysql = MySqlDb::getInstance();

	// $res = $mysql->query ( $sql, $type, $params );
	// $err = $mysql->errorMessage ();
	// if (strlen ( $err )) {
	// $err .= "\n" . $sql . "\n";
	// // echo $err . "\n";
	// logger ( LL_ERR, $err );
	// }
	// return $res;
	// }
}

// Function used for the query function above
function __my_sql_db_copy_value($v) {
	return $v;
}

// global $use_gae;
// if (! $use_gae) {
// global $db_server, $db_user, $db_pass, $db_name;
// $mysql = new MySqlDb ( $db_server, $db_user, $db_pass, $db_name );
// $mysql = $mysql;
// }
// $mysql->query("INSERT INTO MyGuests (firstname, lastname, email) VALUES (?, ?, ?)", "sss", array("Nigel", "Johnson", "nigel@nigeljohnson.net"));
// $mysql->query("SELECT * FROM MyGuests");
// $mysql->query("SELECT * FROM MyGuests WHERE id < ?", "i", array(9));
// $tmp = $mysql->query("SELECT * FROM MyGuests WHERE id < 9");
// print_r($tmp);
