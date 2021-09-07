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
		$this->obj_schema->$cmd ( $name, $index | $key );
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
		echo "get(" . $this->key_field . "=>'" . $key . "')\n";
		echo "    '$gql'\n";
		return ($data) ? ($data->getData ()) : ($data);
	}

	public function insert($arr) {
		echo "DataStore::insert()\n";
		if (! isset ( $arr [$this->getKeyField ()] )) {
			echo "DataStore::insert() - No key field set in new entity\n";
			return false;
		}
		// echo "Key field is set in new data entity\n";
		$key = $this->getKeyField ();
		if ($this->getItemById ( $arr [$key] ) != null) {
			echo "DataStore::insert() - Entity key already exists\n";
			return false;
		}
		//echo "Entity doesn't exist\n";
		$fields = $this->getDataFields ();
		$obj = new GDS\Entity ();
		$obj->$key = $arr [$key];
		foreach ( $fields as $f ) {
			if (isset ( $arr [$f] )) {
				$obj->$f = $arr [$f];
			}
		}
		$this->obj_store->upsert ( $obj );
		echo "DataStore::insert() - Entity added\n";
		return $obj->getData ();
	}

	public function delete($arr) {
		echo "DataStore::delete()\n";
		$key = $this->getKeyField ();
		if (! isset ( $arr [$key] )) {
			echo "DataStore::delete() - No key field set in new entity\n";
			return false;
		}
		//echo "Key field is set in new data entity\n";

		$gql = "SELECT * FROM " . $this->kind . " WHERE " . $this->key_field . " = @key";
		$data = $this->obj_store->fetchOne ( $gql, [ 
				'key' => $arr [$key]
		] );

		if ($data == null) {
			echo "DataStore::delete() - Entity doesn't exist\n";
			return false;
		}
		//echo "Entity exists\n";
		$odata = $data->getData ();
		if ($this->obj_store->delete ( $data )) {
			echo "DataStore::delete() - Entity deleted\n";
			return $odata;
		} else {
			echo "DataStore::delete() - Delete failed???\n";
		}
		return false;
	}

	public function replace($arr) {
		echo "DataStore::replace()\n";
		$key = $this->getKeyField ();
		if (! isset ( $arr [$key] )) {
			echo "DataStore::replace() - No key field set in new entity\n";
			return false;
		}
		//echo "DataStore::replace() - Key field is set in new data entity\n";

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
			$obj->$f = $odata [$f];
			if (isset ( $arr [$f] )) {
				$obj->$f = $arr [$f];
			}
		}

		if (! $this->obj_store->upsert ( $obj )) {
			echo "DataStore::replace() - Upsert failed??\n";
		}
		echo "DataStore::replace() - Entity inserted\n";
		return $obj->getData ();
	}
}
?>