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
	$bookstore = BookStore::getInstance ();
	$book = array();
	$book[$bookstore->getKeyField()] = $args ["id"];
	$ret->book = $bookstore->delete ( $book );
	if ($ret->book) {
		$message = "Deleted your book";
	} else {
		$message = "Unable to find book details for provided ISBN ('" . $args ["id"] . "')";
	}
}

endJsonResponse ( $response, $ret, ($ret->book != null), $message );
?>