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
	$book = array();
	$book[$bookstore->getKeyField()] = $args ["id"];
	$fields = $bookstore->getDataFields();
	foreach ( $fields as $k ) {
		if(isset($_POST[$k])) {
			$book[$k] = $_POST[$k];
		}
	}
	$ret->book = $bookstore->replace ( $book );
	if ($ret->book) {
		$message = "Updated your book";
	} else {
		$message = "Unable to find book details for provided ISBN ('" . $args ["id"] . "')";
	}
}

endJsonResponse ( $response, $ret, ($ret->book != null), $message );
?>