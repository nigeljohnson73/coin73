<?php

class DataStore {

	public function __construct($kind) {
		logger ( LL_DBG, "DataStore::DataStore(" . getProjectId () . ", " . getDataNamespace () . ")" );

		$this->kind = $kind;
		$this->non_key_fields = array ();

		$this->obj_gateway = new \GDS\Gateway\RESTv1 ( getProjectId (), getDataNamespace () );
		$this->obj_schema = (new GDS\Schema ( $this->kind ));
	}

	protected function init() {
		$this->obj_store = new GDS\Store ( $this->obj_schema, $this->obj_gateway );
	}

	public function addField($name, $type, $index = false, $key = false) {
		$cmd = "add" . ucFirst ( $type );
		$this->obj_schema->$cmd ( $name, $index || $key ); // TODO: Work out why this is not being honoured - test it more
		if ($key) {
			$this->key_field = $name;
		} else {
			$this->non_key_fields [] = $name;
		}
	}

	public function getDataFields() {
		return $this->non_key_fields;
	}

	public function getKeyField() {
		return $this->key_field;
	}

	public function getItemById($key) {
		$data = $this->_raw_getItemById ( $key );
		return ($data) ? ($data->getData ()) : ($data);
	}

	private function _raw_getItemById($key) {
		$gql = "SELECT * FROM " . $this->kind . " WHERE " . $this->key_field . " = @key";
		$data = $this->obj_store->fetchOne ( $gql, [ 
				'key' => $key
		] );
		return $data;
	}

	public function insert($arr) {
		// echo "DataStore::insert()\n";
		// echo "DataStore::insert() - passed aarray\n";
		// print_r ( $arr );
		if (! isset ( $arr [$this->getKeyField ()] )) {
			logger ( LL_ERR, "DataStore::insert() - No key field set in new entity" );
			return false;
		}
		// echo "Key field is set in new data entity\n";
		$key = $this->getKeyField ();
		// echo "DataStore::insert() - '" . $key . "' => '" . $arr [$key] . "'\n";
		if ($this->getItemById ( $arr [$key] ) != null) {
			logger ( LL_ERR, "DataStore::insert() - Entity key already exists" );
			return false;
		}
		// echo "Entity doesn't exist\n";
		$fields = $this->getDataFields ();
		$obj = new GDS\Entity ();
		$obj->$key = $arr [$key];

		// echo "DataStore::insert() - adding '" . $key . "' => '" . $obj->$key . "' (key)\n";
		foreach ( $fields as $f ) {
			if (isset ( $arr [$f] )) {
				$obj->$f = $arr [$f];
				// echo "DataStore::insert() - adding '" . $f . "' => '" . $obj->$f . "'" . (($f == $key) ? " (key)" : "") . "\n";
				// } else {
				// echo "DataStore::insert() - skipping '" . $f . "'" . (($f == $key) ? " (key)" : "") . "\n";
			}
		}
		// echo "DataStore::insert() - source object pre-insert\n";
		// print_r ( $arr );
		if ($this->obj_store->upsert ( $obj )) {
			logger ( LL_ERR, "DataStore::insert() - Upsert failed" );
		}
		logger ( LL_DBG, "DataStore::insert() - Entity inserted" );
		// echo "DataStore::insert() - Entity added\n";
		// echo "DataStore::insert() - '" . $key . "' => '" . $arr [$key] . "'\n";
		// echo "DataStore::insert() - destination object post-insert\n";
		// print_r ( $obj->getData () );
		return $obj->getData ();
	}

	public function delete($arr) {
		// echo "DataStore::delete()\n";
		$key = $this->getKeyField ();
		if (! isset ( $arr [$key] )) {
			logger ( LL_ERR, "DataStore::delete() - No key field set in new entity" );
			return false;
		}
		// echo "Key field is set in new data entity\n";

		$gql = "SELECT * FROM " . $this->kind . " WHERE " . $this->key_field . " = @key";
		$data = $this->obj_store->fetchOne ( $gql, [ 
				'key' => $arr [$key]
		] );

		if ($data == null) {
			logger ( LL_ERR, "DataStore::delete() - Entity doesn't exist" );
			return false;
		}
		// echo "Entity exists\n";
		$odata = $data->getData ();
		// usleep(10000);
		if ($this->obj_store->delete ( $data )) {
			logger ( LL_DBG, "DataStore::delete() - Entity deleted" );
			// echo "DataStore::delete() - Entity deleted\n";
			return $odata;
		}
		logger ( LL_ERR, "DataStore::delete() - Delete failed" );
		return false;
	}

	public function update($arr) {
		$key = $this->getKeyField ();
		if (! isset ( $arr [$key] )) {
			logger ( LL_ERR, "DataStore::update() - No key field set in new entity" );
			return false;
		}

		$obj = $this->_raw_getItemById ( $arr [$key] );
		if (! $obj) {
			logger ( LL_ERR, "DataStore::update() - Cannot find existing entity" );
			return false;
		}

		$fields = array_merge ( $this->getDataFields (), [ 
				$this->getKeyField ()
		] );
		// Overwrite any existing data
		foreach ( $arr as $k => $v ) {
			if (in_array ( $k, $fields )) {
				$obj->$k = $v;
			} else {
				logger ( LL_ERR, "DataStore::update() - Cannot update unknown field '" . $k . "'" );
			}
		}

		if ($this->obj_store->upsert ( $obj )) {
			logger ( LL_ERR, "DataStore::update() - Upsert failed" );
		}
		logger ( LL_DBG, "DataStore::update() - Entity updated" );
		return $obj->getData ();
	}

	public function replace($arr) {
		// echo "DataStore::replace()\n";
		$key = $this->getKeyField ();
		if (! isset ( $arr [$key] )) {
			logger ( LL_ERR, "DataStore::replace() - No key field set in new entity" );
			return false;
		}
		// echo "DataStore::replace() - Key field is set in new data entity\n";

		$odata = $this->delete ( $arr );
		if ($odata == false) {
			logger ( LL_ERR, "DataStore::replace() - Delete failed" );
			return false;
		}
		// echo "DataStore::replace() - Entity Deleted\n";

		$fields = $this->getDataFields ();
		$obj = new GDS\Entity ();
		$obj->$key = $arr [$key];
		foreach ( $fields as $f ) {
			// Set it to the existing thing
			// $obj->$f = $odata [$f]; // We are replacing the entire object
			if (isset ( $arr [$f] )) {
				$obj->$f = $arr [$f];
			}
		}

		// echo "DataStore::replace() - Entity about to be upserted:\n";
		// print_r($obj->getData ());
		if ($this->obj_store->upsert ( $obj )) {
			logger ( LL_ERR, "DataStore::replace() - Upsert failed" );
		}
		logger ( LL_DBG, "DataStore::replace() - Entity replaced" );

		// echo "DataStore::replace() - Entity replaced\n";
		return $obj->getData ();
	}
}
?>