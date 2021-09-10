<?php
$ret = startJsonResponse ();

echo "ARGS:\n";
print_r ( $args );
echo "_POST[]:\n";
print_r ( $_POST );

$success = false;
$message = "Unable to decode request\n";
// Delete the nonce, and send back the choices
sleep ( 2 );

endJsonResponse ( $response, $ret, $success, $message );
?>