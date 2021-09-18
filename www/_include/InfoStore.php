<?php
include_once (__DIR__ . "/DataStore.php");

class InfoStore extends DataStore {

	public function __construct() {
		logger ( LL_INF, "InfoStore::InfoStore()" );

		parent::__construct ( "Info" );

		$this->addField ( "key", "String", true, true ); // indexed and key
		$this->addField ( "value", "String" ); // indexed

		$this->init ();
		$this->local = array();
	}

	public function getInfo($key, $fallback = null) {
		if(isset($this->local[$key])) {
			return $this->local[$key];
		}
		
		$arr = $this->getItemById($key);
		if(!$arr) {
			return $fallback;
		}
		
		return $arr["value"];
	}

	public function putInfo($key, $value) {
		$arr = array ();
		$arr ["key"] = $key;
		$arr ["value"] = $value;
		if (parent::insert ( $arr )) {
			$this->local [$key] = $value;
			return true;
		}
		return false;
	}
}
?>