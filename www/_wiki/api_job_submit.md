#Submitting a job

There are 2 API entry points for submitting a job. If your device can handle and process JSON then this option is preferable. Both require you to send a post request, and they both optionally allow you to submit a `hashrate` and `chiptype` in the `x-www-form-urlencoded` body to help with debugging.

If you want to see this in action in PHP, check out [Performing work in PHP](/wiki/api/example/job).

### hashrate (optional)

This refereces the number of times you did the hash per second. Or, possibly, `$nonce / $execution_time`.

### chiptype (optional)

This should indicate the type of chip you are using. It's freeform text for now, but try and capitalise and keep the description to something sensible. `ARDUINO-NANO`, or `ESP32` for example

## /api/job/submit/json/{job_id}/{nonce}

You will receive a JSON encoded object on success and failure.

### job_id

This is the ID you were passed when you [requested](/wiki/api/job/request) the job.

### nonce

This will be the nonce you calculated.

### Success

```language-json
{
  "data": {},
  "success": true,
  "status": "OK",
  "console": [],
  "message": ""
}
```

The boolean field `success` is used to indicate the success of the action. This will be `true` when successful.

The `data` object will not be populated.

### Failure

```language-json
{
  "data": {},
  "success": false,
  "status": "FAIL",
  "console": [],
  "message": "",
  "reason": "Invalid nonce"
}
```

The boolean field `success` is used to indicate the success of the action. This will be `false` in the event of a failure. 

Additionally the `reason` field will be populated with the error message. The `data` object will not be populated.


## /api/job/submit/text/{job_id}/{nonce}

This call uses the JSON call, but just decodes the output into a fixed format string. 

### job_id

This is the ID you were passed when you [requested](/wiki/api/job/request) the job.

### nonce

This will be the nonce you calculated.

### Success

You will receive the letter `Y` to denote the success:

```
Y
```

### Failure

You will receive the letter `N` to denote the failure a white space, then the reason text:

```
N Invalid nonce 
```

