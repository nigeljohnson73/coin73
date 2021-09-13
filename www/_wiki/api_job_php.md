#Performing work in PHP

I write predominantly in PHP, so the code presented here is PHP. The concepts are what you need to get to grips with. If you do use this code, you will need to define a few things.

## Variables

The following variables will need to be defined

### wallet_id

This is your wallet. You can use mine if you like, but that hardly seems fair, you're doing all the work after all. This is the code on your account dashboard.

### rig_id

This is a unique (for your account - or at least this job) string so you can (eventually) track the miners you use.

### api_host

This is where you will send your API calls. This could be the live server, or a test server... mine looks like this because I test on my local server: `http://localhost:8085/api/`

## Functions

You will need to build at least one function.

### jsonApi($url, $vars)

This call handles the API call to `$url` as a `POST` call, and passing `$vars` as the post variables in the `x-www-form-urlencoded` body of call. I can't really provide code here as most implementations are dependent on the modules you have in your PHP stack.

The return variable needs to be an object that has a `data` attribute set to the decoded JSON object the server sent you.

```
function jsonApi($url, $post_object) {
	// Fake a server response
	$data = new StdClass ();
	$data->success = false;
	$data->reason = "jsonApi(): Call not implemented";

	// Wrap it up in the API call wrapper
	$ret = new StdClass ();
	$ret->data = $data;
	return $ret;
}
```

### logger($level, $string)

A simple option is provided that just prints everything, but you can modify it to handle levels etc.

```
define ( "LL_SYS", 0 );
define ( "LL_ERR", 1 );
define ( "LL_WRN", 2 );
define ( "LL_INF", 3 );
define ( "LL_DBG", 4 );

// You can write some stuff to prettify the output and check the log level
function logger($ll, $str) {
	echo trim ( $str ) . "\n";
}
```

### ob_print_r($thing)

This is a wrapped version of the PHP inbuilt `print_r` call, but it does it and captures the output, returning it as a string.

```
function ob_print_r($thing) {
	ob_start ();
	print_r ( $thing );
	$c = ob_get_contents ();
	ob_end_clean ();
	return $c;
}
```

## Putting it all together

Here is a script that will give you a starting point, and allow you to define the tweaks above.

```
<?php
if (! function_exists ( "logger" )) {
	define ( "LL_SYS", 0 );
	define ( "LL_ERR", 1 );
	define ( "LL_WRN", 2 );
	define ( "LL_INF", 3 );
	define ( "LL_DBG", 4 );

	// You can write some stuff to prettify the output and check the log level
	function logger($ll, $str) {
		echo trim ( $str ) . "\n";
	}
}

if (! function_exists ( "jsonApi" )) {

	// write a function that calls the URL with the post variables.
	// It should return an object about the call, with a varable called 'data'
	// which is the decoded response from the server
	function jsonApi($url, $post_object) {
		// Fake a server response
		$data = new StdClass ();
		$data->success = false;
		$data->reason = "jsonApi(): Call not implemented";

		// Wrap it up in the API call wrapper
		$ret = new StdClass ();
		$ret->data = $data;
		return $ret;
	}
}

if (! function_exists ( "ob_print_r" )) {

	// Prints anything (like objects) and returns the string
	function ob_print_r($thing) {
		ob_start ();
		print_r ( $thing );
		$c = ob_get_contents ();
		ob_end_clean ();
		return $c;
	}
}

logger ( LL_SYS, "Starting miner test" );
// Prepare the post details
$post = new StdClass ();
$post->wallet_id = "********YOUR_WALLET_ID_HERE********";
$post->rig_id = "PHP-TEST";

// Setup where the calls need to occur
// $api_host = "http://coin73.appspot.com/api/"; // the server
$api_host = "http://localhost:8085/api/"; // my test rig

logger ( LL_INF, "Requesting job" );
// Request a job through the JSON response API and discard the call information wrapper
$data = jsonApi ( $api_host . "job/request/json", $post )->data;

if ($data->success) {
	// Log the start time so we can maximise profit :)
	$started = microtime ( true );

	// Output the data the sever sent me.
	logger ( LL_DBG, ob_print_r ( $data ) );

	// Discard the api information wrapper
	$data = $data->data;

	// Prepare the post response
	$post = new StdClass ();
	$post->hashrate = 0;
	$post->chiptype = "Power iMac Pro (PHP)";

	// Prepare the REST post response
	$job_id = $data->job_id;
	$nonce = - 1;

	// Start the counter at zero
	$cnonce = 0;
	// Calulate the beginning we need from the difficulty
	$begins = str_pad ( "", $data->difficulty, "0" );

	logger ( LL_INF, "Processing job" );
	// Repeat the looping until we find a valid signature, or we run out of time (being twice the target)
	while ( ($nonce < 0) && (microtime ( true ) < ($started + (2 * $data->target_seconds))) ) {
		// Calculate the signature hash
		$signed = hash ( "sha1", $data->hash . $cnonce );

		// Check if the signature starts with the expected number of zeros
		if (strpos ( $signed, $begins ) === 0) { // If it has, we found one
			$duration = microtime ( true ) - $started;
			$post->hashrate = $cnonce / $duration;
			logger ( LL_INF, "Found nonce: '" . $cnonce . "' in " . number_format ( $duration, 3 ) . " seconds" );
			logger ( LL_INF, "Signature: '" . $signed . "'" );
			logger ( LL_INF, "Hashrate: " . $post->hashrate );
			// Set the nonce so we can end this loop
			$nonce = $cnonce;
		}
		$cnonce = $cnonce + 1;
		// Yield the processor to be nice.
		usleep ( 100 );
	}

	$loggedit = false;
	// At this point we have either solved the hash, or timed out. If we still have to wait a bit, do that here
	while ( microtime ( true ) < ($started + $data->target_seconds) ) {
		if (! $loggedit) {
			$loggedit = true;
			logger ( LL_WRN, "We still have to wait for the target submission time" );
		}
		// Yield the processor to be nice.
		usleep ( 1000 );
	}

	logger ( LL_INF, "Submitting job" );
	// Submit the job through the JSON response API and discard the call information wrapper
	$data = jsonApi ( $api_host . "job/submit/json/" . $job_id . "/" . (($nonce < 0) ? (0) : ($nonce)), $post )->data;

	if ($data->success) {
		logger ( LL_INF, "Submission was successful!" );
	} else {
		logger ( LL_ERR, "Submission API failed: '" . $data->reason . "'" );
	}
	
	// Output the data the sever sent me.
	logger ( LL_DBG, ob_print_r ( $data ) );
} else {
	logger ( LL_ERR, "Request API failed: '" . $data->reason . "'" );
	// Output the data the sever sent me.
	logger ( LL_DBG, ob_print_r ( $data ) );
}
?>
```

## Does it work

Once you have filled in the blanks, you should get some output like this:

```
bash-3.2$ php test.php 
09:32:21 ; SYS ; Starting miner test
09:32:21 ; INF ; Requesting job
09:32:22 ; INF ; jsonApi(): call duration: 1s 41ms
09:32:22 ; INF ; Found nonce: '130' in 0.017 seconds
09:32:22 ; INF ; Signature: '001b65b07668008d93f288a6bae264f14f433b1e'
09:32:22 ; INF ; Hashrate: 7663.3055992804
09:32:22 ; WRN ; We still have to wait for the target submission time
09:32:27 ; INF ; Submitting job
09:32:27 ; INF ; jsonApi(): call duration: 483ms
09:32:27 ; INF ; Submission was successful!
bash-3.2$ 
```