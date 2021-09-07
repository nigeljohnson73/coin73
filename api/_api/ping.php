<?php
$ret = startJsonResponse ();

// echo "ARGS:\n";
// print_r ( $args );
// echo "_POST[]:\n";
// print_r ( $_POST );

endJsonResponse ( $response, $ret, true, "Called Ping API" );
?>