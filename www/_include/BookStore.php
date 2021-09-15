<?php
include_once (__DIR__ . "/DataStore.php");

class BookStore extends DataStore {

	public function __construct() {
		echo "BookStore::BookStore()\n";

		parent::__construct ( "Book" );

		$this->addField ( "isbn", "String", true, true ); // indexed and key
		$this->addField ( "author", "String", true ); // indexed
		$this->addField ( "title", "String" );
		$this->addField ( "subtitle", "String" );
		$this->addField ( "read_count", "Integer" );

		$this->init ();
	}
}

function __testDataStore() {
	global $logger;
	$ll = $logger->getLevel ();
	$logger->setLevel ( LL_DBG );

	$store = new BookStore ();

	$isbn = "12345678";

	$book = array ();
	$book ["isbn"] = $isbn;
	$book ["title"] = "How to suceed at bing a dick";
	$book ["author"] = "nige";

	logger ( LL_INF, "------------------------------------------------------------------------------------------------------" );
	logger ( LL_INF, "Attempting insert" );
	$book = $store->insert ( $book );
	if ($book === false) {
		logger ( LL_ERR, "*** Well, that didn't go well ***" );
		$book = array ();
		$book ["isbn"] = $isbn;
		$book ["title"] = "How to suceed at bing a dick";
		$book ["author"] = "nige";
	}
	print_r ( $book );

	logger ( LL_INF, "------------------------------------------------------------------------------------------------------" );
	logger ( LL_INF, "Attempting replace - updated author, added read_count" );
	$book = array ();
	$book ["isbn"] = $isbn;
	$book ["title"] = "How to suceed at bing a dick";
	$book ["author"] = "Nigel Johnson";
	$book ["read_count"] = 100;
	
	$book = $store->replace ( $book );
	if ($book === false) {
		logger ( LL_ERR, "*** Well, that didn't go well ***" );
		$book = array ();
		$book ["isbn"] = $isbn;
		$book ["title"] = "How to suceed at bing a dick";
		$book ["author"] = "Nigel Johnson";
		$book ["read_count"] = 100;
	}
	print_r ( $book );
	logger ( LL_INF, "------------------------------------------------------------------------------------------------------" );
	logger ( LL_INF, "Attempting update - updated title, added subtitle" );
	$book = array ();
	$book ["isbn"] = $isbn;
	$book ["title"] = "All Sweetness and Light";
	$book ["subtitle"] = "Especially on Wednesdays";
	
	$book = $store->update ( $book );
	if ($book === false) {
		logger ( LL_ERR, "*** Well, that didn't go well ***" );
		$book = array ();
		$book ["isbn"] = $isbn;
		$book ["title"] = "All Sweetness and Light";
		$book ["author"] = "Nigel Johnson";
		$book ["subtitle"] = "Especially on Wednesdays";
		$book ["read_count"] = 100;
	}
	print_r ( $book );
	logger ( LL_INF, "------------------------------------------------------------------------------------------------------" );
	logger ( LL_INF, "Attempting delete" );
	$book = array ();
	$book ["isbn"] = $isbn;

	$book = $store->delete ( $book );
	if ($book === false) {
		logger ( LL_ERR, "*** Well, that didn't go well ***" );
		$book = array ();
		$book ["isbn"] = $isbn;
		$book ["title"] = "All Sweetness and Light";
		$book ["author"] = "Nigel Johnson";
		$book ["read_count"] = 100;
		$book ["subtitle"] = "Especially on Wednesdays";
	}
	print_r ( $book );

	$logger->setLevel ( $ll );
}

?>