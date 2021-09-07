<?php
include_once (__DIR__ . "/DataStore.php");

class BookStore extends DataStore {

	public function __construct() {
		echo "BookStore::BookStore()\n";

		parent::__construct ( "Book" );

		$this->addField ( "isbn", "String", true, true ); // indexed and key
		$this->addField ( "author", "String", true ); // indexed
		$this->addField ( "title", "String" );
		$this->addField ( "read_count", "Integer" );

		$this->init ();
	}
}
?>