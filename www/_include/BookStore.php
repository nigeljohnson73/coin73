<?php

class BookStore {

	public function __construct() {
		echo "BookStore::BookStore(" . getProjectId () . ", " . getDataNamespace () . ")\n";

		$this->key_field = "isbn";
		$this->kind = "Book";
		$this->non_key_fields = array ();

		$this->obj_gateway = new \GDS\Gateway\RESTv1 ( getProjectId (), getDataNamespace () );
		$this->obj_schema = (new GDS\Schema ( $this->kind ));
		$this->addField ( "isbn", "String", true, true ); // indexed and key
		$this->addField ( "author", "String", true ); // indexed
		$this->addField ( "title", "String" );
		$this->addField ( "read_count", "Integer" );
		// $this->obj_schema->addString ( 'title', false ); // false indicates not indexed
		// $this->obj_schema->addString ( 'author' );
		// $this->obj_schema->addString ( 'isbn' );
		// $this->obj_schema->addInteger ( 'read_count' );
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

	public function getNonKeyFields() {
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
		echo "BookStore::insert()\n";
		if (! isset ( $arr [$this->getKeyField ()] )) {
			echo "No key field set in new entity\n";
			return false;
		}
		echo "Key field is set in new data entity\n";
		$key = $this->getKeyField ();
		if ($this->getItemById ( $arr [$key] ) != null) {
			echo "Entity already exists\n";
			return false;
		}
		echo "Entity doesn't exist\n";
		$fields = $this->getNonKeyFields ();
		$obj = new GDS\Entity ();
		$obj->$key = $arr [$key];
		foreach ( $fields as $f ) {
			if (isset ( $arr [$f] )) {
				$obj->$f = $arr [$f];
			}
		}
		$this->obj_store->upsert ( $obj );
		echo "Added object to data store\n";
		return $obj->getData ();
	}

	public function delete($arr) {
		echo "BookStore::replace()\n";
		$key = $this->getKeyField ();
		if (! isset ( $arr [$key] )) {
			echo "No key field set in new entity\n";
			return false;
		}
		echo "Key field is set in new data entity\n";
		
		$gql = "SELECT * FROM " . $this->kind . " WHERE " . $this->key_field . " = @key";
		$data = $this->obj_store->fetchOne ( $gql, [
				'key' => $arr [$key]
		] );
		
		if($data == null) {
			echo "Entity doesn't exist\n";
			return false;
		}
		echo "Entity exists\n";
		$odata = $data->getData();
		if($this->obj_store->delete ( $data )) {
		return $odata;
		} else{
			echo "Delete failed???\n";
		}
		return false;
	}
	
	public function replace($arr) {
		echo "BookStore::replace()\n";
		$key = $this->getKeyField ();
		if (! isset ( $arr [$key] )) {
			echo "No key field set in new entity\n";
			return false;
		}
		echo "Key field is set in new data entity\n";
		
		$odata = $this->delete($arr);
		if($odata == false) {
			// Already echoed
			return false;
		}
		
		$fields = $this->getNonKeyFields ();
		$obj = new GDS\Entity ();
		$obj->$key = $arr [$key];
		foreach ( $fields as $f ) {
			// Set it to the existing thing
			$obj->$f = $odata [$f];
			if (isset ( $arr [$f] )) {
				$obj->$f = $arr [$f];
			}
		}
	
		if(!$this->obj_store->upsert ( $obj )) {
			echo "Upsert failed??\n";
		}
		echo "Entity inserted\n";
		return $obj->getData ();
		
// // 		$gql = "SELECT * FROM " . $this->kind . " WHERE " . $this->key_field . " = @key";
// // 		$data = $this->obj_store->fetchOne ( $gql, [
// // 				'key' => $arr [$key]
// // 		] );
		
// // 		if($data == null) {
// // 			echo "Entity doesn't exist\n";
// // 			return false;
// // 		}
// // 		echo "Entity exists\n";
// // 		$odata = $data->getData();
// // 		$this->obj_store->delete ( $data );
	
		
		
// 		$narr = $this->getItemById ( $arr [$key] );
// 		if ($narr == null) {
// 			echo "Entity doesn't exist\n";
// 			return false;
// 		}
// 		echo "Entity exist\n";
// 		$fields = $this->getNonKeyFields ();
// 		$obj = new GDS\Entity ();
// 		$obj->$key = $arr [$key];
// 		foreach ( $fields as $f ) {
// 			// Set it to the existing thing
// 			$obj->$f = $narr [$f];
// 		}
// 		echo "Deleting entity\n";
// 		print_r($obj->getData());
// 		$this->obj_store->delete ( $obj );
// 		echo "Entity deleted\n";

// 		foreach ( $fields as $f ) {
// 			// Set it to the existing thing
// 			if (isset ( $arr [$f] )) {
// 				$obj->$f = $arr [$f];
// 			}
// 		}
// 		$this->obj_store->upsert ( $obj );
// 		echo "Entity inserted\n";
// 		return $obj->getData ();
	}
}
?>