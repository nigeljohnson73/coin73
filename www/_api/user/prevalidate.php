<?php
$ret = startJsonResponse ();

// Returns the choices that are needed to confirm the revalidation

echo "ARGS:\n";
print_r ( $args );
echo "_POST[]:\n";
print_r ( $_POST );

$success = false;
$message = "Unable to decode request\n";
// decode the none ($args["payload"])
// //Find the user
// $user = $store->findByNonce($args["payload"]);
// // Delete the nonce - we are committed
// $user["nonce"] = "";
// $store->update($user);
// // Set the return object up
// $ret->guid = $user["validatation"];
// $ret->choices = json_decode($user["validatation"]).choices;
sleep ( 2 );

endJsonResponse ( $response, $ret, $success, $message );
?>