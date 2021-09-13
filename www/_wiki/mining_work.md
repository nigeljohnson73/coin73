# Mining work

The work you are doing is solving a hash that will secure the blockchain. You are provided with a hash that identifies the latest block, and you have to add a value to it, until the signature meets some rules. The rules are pretty simple... The signature needs to start with a certain number of zeros. How many zeros? well that is where the difficulty comes in. In the Bitcoin blockchain the difficulty defines how many 'bits' need to be zero and so there is a lot more resolution, but tiny little microcontrollers cannot be throwing 64-bit numbers around the place so they have to work a little differently.

## The Algorithm

You are supplied with a hash. It looks like this: 

`a7936d49b41b62f05e561f4e7d0c5af58a001da5`

You then start counting and adding that count (called the nonce) onto the end of that hash. You then hash it using the `SHA1` algorithm to get a new 'signature' hash. Once the signature starts with the required number of zeros, you're done.

Here is the first one with a 'difficulty' of `2`:

a7936d49b41b62f05e561f4e7d0c5af58a001da5**7** - **00**17b9618303bd181d8fecae7dd944a71d83b755

Wow, seven hashes and we're there? Yep, sometimes it is that easy. But we are only looking for the first 2 digits being zero. In the first 2,000 hashes we have lots of possible options that are all equally valid:

a7936d49b41b62f05e561f4e7d0c5af58a001da5**9** -> **00**f0ffd8d7e5d50846be65afa46526eaaf75115c

a7936d49b41b62f05e561f4e7d0c5af58a001da5**23** -> **00**52fb0e8e1d81e3575a2b47b04c40703414013d

a7936d49b41b62f05e561f4e7d0c5af58a001da5**285** -> **00**d1d1058d079ed173bad5fa58e5d30e3dda07e5

a7936d49b41b62f05e561f4e7d0c5af58a001da5**660** -> **00**aa2d732b2c2538e4ac113aec3a86ac31068447

a7936d49b41b62f05e561f4e7d0c5af58a001da5**1014** -> **00**87a8314f9b07676141eb1199da58fb86ac4dbf

a7936d49b41b62f05e561f4e7d0c5af58a001da5**1731** -> **00**9e0132a6685e9b309957946277f95d36d63987

## Cranking up the difficulty

If we use the same hash as before, but now require 3 zeros, we have to wait quite a while:

a7936d49b41b62f05e561f4e7d0c5af58a001da5**9256** -> **000**ccb0dc09162d361d0f7453aef7cd93275c57d

That is the only valid hash in the first 10,000 attempts, and there are only 3 valid signatures in the first 20,000 hashes.

What about 4 zeros? Well there are only 13 valid hashes in the first 1,000,000 attempts. Here is the first one:

a7936d49b41b62f05e561f4e7d0c5af58a001da5**132073** - **0000**add9dc9bf1071af55f7306004808931acbf7

# The system difficulty

If you have something like an Arduino nano, that can only crank out around 175 hashes per second, you're going to be waiting quite a while to find a signature starting with 4 zeros (on average). That last one for example would have taken 754.7 seconds, a little over 12 and a half minutes. If you were using a massively parallel computer, you'd crack that in milliseconds. This is why the difficulty parameter is not useful in this system other than as a rule that defines how many zeros a signature has.

The system currently requires {{MINER_DIFFICULTY}} zeros for its signatures. Below is a table that shows how an Arduino nano will fair with hashing using the different 'difficulty' levels. Based on a submission time of 15 seconds, it shows how many of these submissions would have been accepted.

<table class="table">
	<thead>
		<tr>
			<td></td>
			<th scope="col">Average</th>
			<th scope="col">7.5s</th>
			<th scope="col">15s</th>
			<th scope="col">30s</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th scope="row">1 Zero</th>
			<td data-label="Average">0.11s</td>
			<td data-label="7.5s">100%</td>
			<td data-label="15s">100%</td>
			<td data-label="30s">100%</td>
		</tr>
		<tr>
			<th scope="row">2 Zeros</th>
			<td data-label="Average">1.42s</td>
			<td data-label="7.5s">100%</td>
			<td data-label="15s">100%</td>
			<td data-label="30s">100%</td>
		</tr>
		<tr>
			<th scope="row">3 Zeros</th>
			<td data-label="Average">22.64s</td>
			<td data-label="7.5s">24%</td>
			<td data-label="15s">50%</td>
			<td data-label="30s">72%</td>
		</tr>
		<tr>
			<th scope="row">4 Zeros</th>
			<td data-label="Average">396.51</td>
			<td data-label="7.5s">2%</td>
			<td data-label="15s">2%</td>
			<td data-label="30s">4%</td>
		</tr>
	</tbody>
</table>

<!--
|         | Average | 7.5s | 15s  | 30s  |
|---------|---------|------|------|------|
| 1 Zero  |   0.11s | 100% | 100% | 100% |
| 2 Zeros |   1.42s | 100% | 100% | 100% |
| 3 Zeros |  22.64s |  24% |  50% |  72% |
| 4 Zeros | 396.51s |   2% |   2% |   4% |
-->