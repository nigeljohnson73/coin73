<?php
$ret = startJsonResponse ();

echo "ARGS:\n";
print_r ( $args );
echo "_POST[]:\n";
print_r ( $_POST );

$book = array ();
$bookstore = new BookStore ();
$fields = $bookstore->getDataFields ();
$fields [] = $bookstore->getKeyField ();
foreach ( $fields as $k ) {
	if (isset ( $_POST [$k] )) {
		$book [$k] = $_POST [$k];
	}
}

$message = "Book created\n";
$ret->book = $bookstore->insert ( $book );
if (! $ret->book) {
	$message = "Book creation failed";
	$ret->book = null;
}

endJsonResponse ( $response, $ret, ($ret->book != null), $message );
?>