<?php

class DataStore {

	public function __construct($kind) {
		echo "DataStore::DataStore(" . getProjectId () . ", " . getDataNamespace () . ")\n";

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
		$gql = "SELECT * FROM " . $this->kind . " WHERE " . $this->key_field . " = @key";
		$data = $this->obj_store->fetchOne ( $gql, [ 
				'key' => $key
		] );
		//echo "DataStore::getItemById('" . $this->key_field . "'=>'" . $key . "')\n";
		//echo "    '$gql'\n";
		return ($data) ? ($data->getData ()) : ($data);
	}

	public function insert($arr) {
		//echo "DataStore::insert()\n";
		// echo "DataStore::insert() - passed aarray\n";
		// print_r ( $arr );
		if (! isset ( $arr [$this->getKeyField ()] )) {
			echo "DataStore::insert() - No key field set in new entity\n";
			return false;
		}
		// echo "Key field is set in new data entity\n";
		$key = $this->getKeyField ();
		// echo "DataStore::insert() - '" . $key . "' => '" . $arr [$key] . "'\n";
		if ($this->getItemById ( $arr [$key] ) != null) {
			echo "DataStore::insert() - Entity key already exists\n";
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
		$this->obj_store->upsert ( $obj );
		//echo "DataStore::insert() - Entity added\n";
		// echo "DataStore::insert() - '" . $key . "' => '" . $arr [$key] . "'\n";
		// echo "DataStore::insert() - destination object post-insert\n";
		// print_r ( $obj->getData () );
		return $obj->getData ();
	}

	public function delete($arr) {
		//echo "DataStore::delete()\n";
		$key = $this->getKeyField ();
		if (! isset ( $arr [$key] )) {
			echo "DataStore::delete() - No key field set in new entity\n";
			return false;
		}
		// echo "Key field is set in new data entity\n";

		$gql = "SELECT * FROM " . $this->kind . " WHERE " . $this->key_field . " = @key";
		$data = $this->obj_store->fetchOne ( $gql, [ 
				'key' => $arr [$key]
		] );

		if ($data == null) {
			echo "DataStore::delete() - Entity doesn't exist\n";
			return false;
		}
		// echo "Entity exists\n";
		$odata = $data->getData ();
		// usleep(10000);
		if ($this->obj_store->delete ( $data )) {
			//echo "DataStore::delete() - Entity deleted\n";
			return $odata;
		}
		echo "DataStore::delete() - Delete failed\n";
		return false;
	}

	public function update($arr) {
		//echo "DataStore::update()\n";
		$key = $this->getKeyField ();
		if (! isset ( $arr [$key] )) {
			echo "DataStore::update() - No key field set in new entity\n";
			return false;
		}
		// echo "DataStore::replace() - Key field is set in new data entity\n";

		$odata = $this->delete ( $arr );
		if ($odata == false) {
			echo "DataStore::update() - Delete failed\n";
			return false;
		}
		//echo "DataStore::update() - Entity Deleted\n";

		$fields = $this->getDataFields ();
		$obj = new GDS\Entity ();
		$obj->$key = $arr [$key];
		foreach ( $fields as $f ) {
			if (isset ( $arr [$f] )) {
				// write the new data
				$obj->$f = $arr [$f];
			} else if (isset ( $odata [$f] )) {
				// Copy the old data
				$obj->$f = $odata [$f];
			}
		}

		if ($this->obj_store->upsert ( $obj )) {
			echo "DataStore::update() - Upsert failed\n";
			// true === failure. This seems to be the case. It returns `$arr_auto_id_required` in the RESTv1 Gateway? assume that's ok, and the positive test should be if that's false.
		}
		//echo "DataStore::update() - Entity updated\n";
		return $obj->getData ();
	}

	public function replace($arr) {
		//echo "DataStore::replace()\n";
		$key = $this->getKeyField ();
		if (! isset ( $arr [$key] )) {
			echo "DataStore::replace() - No key field set in new entity\n";
			return false;
		}
		// echo "DataStore::replace() - Key field is set in new data entity\n";

		$odata = $this->delete ( $arr );
		if ($odata == false) {
			echo "DataStore::replace() - Delete failed??\n";
			return false;
		}
		//echo "DataStore::replace() - Entity Deleted\n";

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

		if ($this->obj_store->upsert ( $obj )) {
			echo "DataStore::replace() - Upsert failed??\n";
			// true === failure. This seems to be the case. It returns `$arr_auto_id_required` in the RESTv1 Gateway? assume that's ok, and the positive test should be if that's false.
		}
		//echo "DataStore::replace() - Entity replaced\n";
		return $obj->getData ();
	}
}
?>