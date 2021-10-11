<?php

class DataStore {
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

	protected function __construct($kind) {
		logger(LL_DBG, "DataStore::DataStore(" . getProjectId() . ", " . getDataNamespace() . ")");

		$this->kind = $kind;
		$this->key_field = null;
		$this->non_key_fields = array();

		// Used for MySQL table generation
		$this->field_types = array();
		$this->field_indexes = array();
		$this->type_map = array();
		$this->type_map["STRING"] = "VARCHAR(512)";
		$this->type_map["INTEGER"] = "BIGINT";
		// $this->type_map["DATETIME"] = "INTEGER";
		$this->type_map["FLOAT"] = "REAL";
		// $this->type_map["DOUBLE"] = "REAL";
		$this->type_map["BOOLEAN"] = "INTEGER";
		// $this->type_map["STRINGLIST"] = "VARCHAR(1024)";
		// $this->type_map ["GEOPOINT"] = "VARCHAR(64)";

		$this->prep_map["STRING"] = "s";
		$this->prep_map["INTEGER"] = "i";
		// $this->prep_map["DATETIME"] = "INTEGER";
		$this->prep_map["FLOAT"] = "d";
		// $this->prep_map["DOUBLE"] = "REAL";
		$this->prep_map["BOOLEAN"] = "i";
		// $this->prep_map["STRINGLIST"] = "VARCHAR(1024)";
		// $this->prep_map ["GEOPOINT"] = "VARCHAR(64)";

		if (usingGae()) {
			$this->obj_gateway = new \GDS\Gateway\RESTv1(getProjectId(), getDataNamespace());
			$this->obj_schema = (new GDS\Schema($this->kind));
		} else {
			// MySQL stuff
		}
	}

	protected function init() {
		// logger ( LL_DBG, "DataStore::init()" );
		if (usingGae()) {
			logger(LL_DBG, "DataStore::init(): Using GAE");
			$this->obj_store = new GDS\Store($this->obj_schema, $this->obj_gateway);
		} else {
			logger(LL_DBG, "DataStore::init(): Using MySQL");
			$sql = "CREATE TABLE IF NOT EXISTS " . $this->kind . " (\n";
			$sql .= "    " . $this->key_field . " " . $this->type_map[$this->field_types[$this->key_field]] . " NOT NULL,\n";
			foreach ($this->non_key_fields as $field) {
				$sql .= "    " . $field . " " . $this->type_map[$this->field_types[$field]];
				if ($this->field_indexes[$field] ?? false) {
					$sql .= " NOT NULL";
				}
				$sql .= ",\n";
			}
			$sql .= "    PRIMARY KEY (" . $this->key_field . "),\n";

			foreach ($this->non_key_fields as $field) {
				if ($this->field_indexes[$field] ?? false) {
					$sql .= "    KEY (" . $field . "),\n";
				}
			}
			$sql = rtrim(trim($sql), ",");
			$sql .= "\n)\n";

			// logger ( LL_INF, "SQL: " . $sql );
			MysqlDb::query($sql);
		}
	}

	protected function addField($name, $type, $index = false, $key = false) {
		if (usingGae()) {
			$cmd = "add" . ucFirst($type);
			$this->obj_schema->$cmd($name, $index || $key); // TODO: Work out why this is not being honoured - test it more
		}
		if ($key) {
			$this->key_field = $name;
		} else {
			$this->non_key_fields[] = $name;
		}

		$this->field_types[$name] = strtoupper($type);
		if ($index) {
			$this->field_indexes[$name] = true;
		}
	}

	public static function getDataFields() {
		return self::getInstance()->non_key_fields;
	}

	public static function getKeyField() {
		return self::getInstance()->key_field;
	}

	public static function insert($arr) {
		$key = self::getKeyField();
		//logger ( LL_INF, "Looking for '" . self::getKeyField () . "' in: " . ob_print_r ( $arr ) );
		if (!isset($arr[self::getKeyField()])) {
			logger(LL_ERR, "DataStore::insert() - No key field set in new entity");
			return false;
		}
		if (self::getItemById($arr[$key]) != null) {
			logger(LL_ERR, "DataStore::insert() - Entity key already exists");
			return false;
		}

		$store = self::getInstance();
		if (usingGae()) {
			// echo "DataStore::insert()\n";
			// echo "DataStore::insert() - passed aarray\n";
			// print_r ( $arr );
			// echo "Key field is set in new data entity\n";

			// echo "DataStore::insert() - '" . $key . "' => '" . $arr [$key] . "'\n";
			// echo "Entity doesn't exist\n";
			$fields = self::getDataFields();
			$obj = new GDS\Entity();
			$obj->$key = $arr[$key];

			// echo "DataStore::insert() - adding '" . $key . "' => '" . $obj->$key . "' (key)\n";
			foreach ($fields as $f) {
				if (isset($arr[$f])) {
					$obj->$f = $arr[$f];
					// echo "DataStore::insert() - adding '" . $f . "' => '" . $obj->$f . "'" . (($f == $key) ? " (key)" : "") . "\n";
					// } else {
					// echo "DataStore::insert() - skipping '" . $f . "'" . (($f == $key) ? " (key)" : "") . "\n";
				}
			}
			// echo "DataStore::insert() - source object pre-insert\n";
			// print_r ( $arr );
			if ($store->obj_store->upsert($obj)) {
				logger(LL_ERR, "DataStore::insert() - Upsert failed");
			}
			logger(LL_XDBG, "DataStore::insert() - Entity inserted");
			// echo "DataStore::insert() - Entity added\n";
			// echo "DataStore::insert() - '" . $key . "' => '" . $arr [$key] . "'\n";
			// echo "DataStore::insert() - destination object post-insert\n";
			// print_r ( $obj->getData () );
			return $obj->getData();
		} else {
			// $item = self::getItemById ( $arr [self::getKeyField ()] );
			// if ($item) {
			// logger ( LL_ERR, "DataStore::insert() - Entity key already exists" );
			// return false;
			// }
			// MySQL then
			$type = "";
			$sql = "INSERT INTO " . $store->kind . " (";
			$comma = "";
			foreach ($arr as $k => $v) {
				$type .= $store->prep_map[$store->field_types[$k]];
				$sql .= trim($comma . " " . $k);
				$comma = ",";
			}
			$sql .= ") VALUES (";
			$comma = "";
			foreach ($arr as $k => $v) {
				$sql .= trim($comma . " ?");
				$comma = ",";
			}
			$sql .= ")";

			// 			logger ( LL_DBG, "SQL: " . $sql );
			// 			logger ( LL_DBG, "type: " . $type );
			// 			logger ( LL_DBG, "args: " . ob_print_r ( array_values ( $arr ) ) );
			// 			logger ( LL_DBG, "exec: " . MySqlDb::queryExplode ( $sql, $type, array_values ( $arr ) ) );
			MysqlDb::query($sql, $type, array_values($arr));
			$err = MySqlDb::getError();
			if (strlen($err)) {
				// $err .= "\n" . $sql . "\n";
				// echo $err . "\n";
				logger(LL_ERR, $err);
				return false;
			}
			return $arr;
		}
	}

	public static function delete($arr) {
		$key = self::getKeyField();
		if (!isset($arr[$key])) {
			logger(LL_ERR, "DataStore::delete() - No key field set in entity");
			return false;
		}
		$ret = self::getItemById($arr[$key]);
		if ($ret == null) {
			logger(LL_ERR, "DataStore::delete() - Entity does not exist");
			return false;
		}
		// echo "Key field is set in new data entity\n";

		$store = self::getInstance();
		if (usingGae()) {

			$gql = "SELECT * FROM " . $store->kind . " WHERE " . $store->key_field . " = @key";
			$data = $store->obj_store->fetchOne($gql, [
				'key' => $arr[$key]
			]);

			if ($data == null) {
				logger(LL_ERR, "DataStore::delete() - Entity doesn't exist");
				return false;
			}
			// echo "Entity exists\n";
			$odata = $data->getData();
			// usleep(10000);
			if ($store->obj_store->delete($data)) {
				logger(LL_XDBG, "DataStore::delete() - Entity deleted");
				// echo "DataStore::delete() - Entity deleted\n";
				return $odata;
			}
			logger(LL_ERR, "DataStore::delete() - Delete failed");
			return false;
		} else {
			$sql = "DELETE FROM " . $store->kind . " WHERE " . $store->key_field . " = ?";
			$type = $store->prep_map[$store->field_types[$store->key_field]];
			MySqlDb::query($sql, $type, [
				$arr[$key]
			]);
			$err = MySqlDb::getError();
			if (strlen($err)) {
				// $err .= "\n" . $sql . "\n";
				// echo $err . "\n";
				logger(LL_ERR, $err);
				return false;
			}
			return $ret;
		}
	}

	public static function update($arr) {
		$key = self::getKeyField();
		//logger ( LL_INF, "Looking for '" . $key . "' in: " . ob_print_r ( $arr ) );
		//logger(LL_SYS, $arr [$key]);
		if (!isset($arr[$key])) {
			logger(LL_ERR, "DataStore::update() - No key field set in entity");
			return false;
		}
		if (self::getItemById($arr[$key]) == null) {
			logger(LL_ERR, "DataStore::update() - Entity key does not exist");
			return false;
		}

		$store = self::getInstance();
		if (usingGae()) {
			$obj = self::_getItemByKeyField($store->key_field, $arr[$key]);
			$fields = array_merge(self::getDataFields(), [
				self::getKeyField()
			]);
			// Overwrite any existing data
			foreach ($arr as $k => $v) {
				if (in_array($k, $fields)) {
					$obj->$k = $v;
				} else {
					logger(LL_ERR, "DataStore::update() - Cannot update unknown field '" . $k . "'");
				}
			}

			if ($store->obj_store->upsert($obj)) {
				logger(LL_ERR, "DataStore::update() - Upsert failed");
			}
			logger(LL_XDBG, "DataStore::update() - Entity updated");
			return $obj->getData();
		} else {
			$type = "";
			$sql = "UPDATE " . $store->kind . "\n    SET ";
			$comma = "";
			$vals = array();
			foreach ($arr as $k => $v) {
				if ($k != $key) {
					$type .= $store->prep_map[$store->field_types[$k]];
					$sql .= trim($comma . " " . $k . " = ?\n    ");
					$vals[] = $v;
					$comma = ",";
				}
			}
			$type .= $store->prep_map[$store->field_types[$key]];
			$vals[] = $arr[$key];
			$sql = trim($sql) . "\nWHERE " . $key . " = ?";

			// 			logger ( LL_DBG, "SQL: " . $sql );
			// 			logger ( LL_DBG, "type: " . $type );
			// 			logger ( LL_DBG, "args: " . ob_print_r ( $vals ) );
			MysqlDb::query($sql, $type, $vals);
			$err = MySqlDb::getError();
			if (strlen($err)) {
				// $err .= "\n" . $sql . "\n";
				// echo $err . "\n";
				logger(LL_ERR, $err);
				return false;
			}
			return self::getItemById($arr[self::getKeyField()]);
		}
	}

	public static function replace($arr) {
		// echo "DataStore::replace()\n";
		$key = self::getKeyField();
		if (!isset($arr[$key])) {
			logger(LL_ERR, "DataStore::replace() - No key field set in new entity");
			return false;
		}
		// echo "DataStore::replace() - Key field is set in new data entity\n";

		$odata = self::delete($arr);
		if ($odata == false) {
			logger(LL_ERR, "DataStore::replace() - Delete failed");
			return false;
		}
		// echo "DataStore::replace() - Entity Deleted\n";
		$ndata = self::insert($arr);
		if ($ndata == false) {
			logger(LL_ERR, "DataStore::replace() - Insert failed");
			$ndata = self::insert($odata);
			if ($ndata == false) {
				logger(LL_ERR, "DataStore::replace() - Reinsert failed");
			}
			return false;
		}

		// echo "DataStore::replace() - Entity replaced\n";
		return $ndata;
	}

	protected static function _getAllItemsByKeyField($key_field, $key, $raw = false) {
		$ret = false;
		$store = self::getInstance();
		if (usingGae()) {
			$data = array();
			$gql = "SELECT * FROM " . $store->kind . " WHERE " . $key_field . " = @key";
			$store->obj_store->query($gql);
			while ($arr_page = $store->obj_store->fetchPage(transactionsPerPage())) {
				logger(LL_DBG, $store->kind . "Store::reset(): deleting " . count($arr_page) . " records");
				$data = array_merge($data, $arr_page);
			}
			if (!$raw) {
				foreach ($data as $row) {
					$ret[] = $row->getData();
				}
			} else {
				$ret = $data;
			}
		} else {
			$sql = "SELECT * FROM " . $store->kind . " WHERE " . $key_field . " = ?";
			$type = $store->prep_map[$store->field_types[$key_field]];
			$ret = MySqlDb::query($sql, $type, [
				$key
			]);
		}
		return $ret;
	}

	protected static function _getItemByKeyField($key_field, $key, $raw = false) {
		return self::_getAllItemsByKeyField($key_field, $key, $raw)[0] ?? null;
	}

	public static function getItemById($key) {
		return self::_getItemByKeyField(self::getInstance()->key_field, $key);
	}
}
