<?php
$ret = startJsonResponse ();

echo "ARGS:\n";
print_r ( $args );
echo "_POST[]:\n";
print_r ( $_POST );

$ret->book = null;
$message = "";

if (strlen ( $args ["id"] ) == 0) {
	$message = "Unable to find book details for blank ISBN";
} else {
	$bookstore = new BookStore ();
	$ret->book = $bookstore->getItemById( $args ["id"] );
	if ($ret->book) {
		$message = "Found your book";
		print_r($ret->book);
	} else {
		$message = "Unable to find book details for provided ISBN ('" . $args ["id"] . "')";
	}
}

endJsonResponse ( $response, $ret, ($ret->book != null), $message );
?>