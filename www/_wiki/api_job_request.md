#Requesting a job

There are 2 API entry points for requesting a job. If your device can handle and process JSON then this option is preferable. Both require you to send a post request, and they both require your `wallet_id` and `rig_id` in the `x-www-form-urlencoded` body.

If you want to see this in action in PHP, check out [Performing work in PHP](/wiki/api/job/php).

### wallet_id

This is your Wallet ID as defined on your miner dashboard.

### rig_id

This is a rig-specific ID. It can contain any upper/lowercase characters or digits, as well as a hyphen, but any other character are not valid. This value must also be unique among the miners on your account.

## /api/job/request/json

You will receive a JSON encoded object on success and failure

### Success

```
{
  "data": {
    "job_id": "654a136f-b382-4666-ad42-de4c85b444ef",
    "hash": "b7d75d652c8efca587a42f4a3d6fb68e0fe3b1eb",
    "difficulty": 2,
    "target_seconds": 5
  },
  "success": true,
  "status": "OK",
  "console": [],
  "message": ""
}
```

The boolean field `success` is used to indicate the success of the action. This will be `true` when successful.

The data object will also be present and populated.

The `job_id` must be returned when you [submit the job](/wiki/api/job/submit). It is in a standard GUID v4 format.

The `hash` value must be combined with the NONCE you are calculating to generate the signature.

The `difficulty` indicates the number of zeros the signature must have to be valid.

The `target_seconds` value indicates the target submission time in seconds.

### Failure

```
{
  "data": {},
  "success": false,
  "status": "FAIL",
  "console": [],
  "message": "",
  "reason": "Miner limit reached"
}
```

The boolean field `success` is used to indicate the success of the action. This will be `false` in the event of a failure. 

Additionally the `reason` field will be populated with the error message. The `data` object will not be populated.


## /api/job/request/text

This call uses the JSON call, but just decodes the output into a fixed format string. 

### Success

You will receive a whitespace separated string.

```
Y 6f7e7378-6ae2-4135-a9f7-043d58781c53 07fbbe0f96faeb037ee1f00984e9774ee4206aec 02 05
```

The first character will be `Y` to denote the success.

The next section (`6f7e7378-6ae2-4135-a9f7-043d58781c53`) is the `job_id` and this must be returned when you [submit the job](/wiki/api/job/submit). It is in a standard GUID v4 format.

The next section (`07fbbe0f96faeb037ee1f00984e9774ee4206aec`) is the hash that must be combined with the NONCE you are calculating to generate the signature.

The next section (`02`) is the difficulty - the number of zeros the signature must have to be valid.

The last section (`05`) is the target submission time in seconds.

### Failure

You will receive the letter `N` to denote the failure a white space, then the reason text:

```
N Miner limit reached 
```