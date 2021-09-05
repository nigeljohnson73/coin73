<?php

$ret = startJsonResponse ();

echo "ARGS:\n";
print_r ( $args );
echo "_POST[]:\n";
print_r ( $_POST );

// $title = $_POST ["title"];
// $author = $_POST ["author"];
// $isbn = $_POST ["isbn"];
// $read_count = $_POST ["read_count"];

$book = array();
// $book["title"] = $title;
// $book["author"] = $author;
// $book["isbn"] = $isbn;
// $book["read_count"] = $read_count;

$bookstore = new BookStore ();
$fields = $bookstore->getNonKeyFields();
$fields[] = $bookstore->getKeyField();
foreach ( $fields as $k ) {
	if(isset($_POST[$k])) {
		$book[$k] = $_POST[$k];
	}
}


$message = "Book created\n";
$ret->book = $bookstore->insert ( $book );
if (! $ret->book) {
	$message = "Book creation failed";
}

endJsonResponse ( $response, $ret, ($ret->book != null), $message );
?>