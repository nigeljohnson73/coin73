# Mining work

The work you are doing is solving a hash that will secure the blockchain. You are provided with a hash that identifies the latest block, and you have to work on it until the signature meets some rules. The rules are pretty simple... The signature needs to start with a certain number of zeros. How many zeros? well that is where the difficulty comes in. In the Bitcoin blockchain the difficulty defines how many 'bits' need to be zero and so there is a lot more resolution and allows for simpler comparisons, but tiny little microcontrollers cannot be throwing 64-bit numbers around the place so they have to work a little differently.

## First, the algorithm

You are supplied with a hash. It will look a little something like this: 

```
a7936d49b41b62f05e561f4e7d0c5af58a001da5
```

You then start counting and adding that count (called the nonce) onto the end of that hash. You then hash your new string using the `SHA1` algorithm to get a potential 'signature' hash. Once the signature hash starts with the required number of zeros, you're done and you submit the nonce back to the server. Reasonably simple.

## The difficulty

In this application, the 'difficulty' indicates how many zeros the potential signature hash needs in order to be a valid signature hash.

Using the algorithm mentioned above, here is the first valid signature hash using a 'difficulty' of `2`:

<pre class="nohighlight"><code class="hljs">a7936d49b41b62f05e561f4e7d0c5af58a001da5<strong class="indicator">7</strong> -> <strong class="indicator">00</strong>17b9618303bd181d8fecae7dd944a71d83b755
</code></pre>

Wow, eight hashes (remember we started at zero) and we're there? Yep, sometimes it is that easy. We are looking for the first 2 digits being zero and there are a few possible options in the first 1,000 attempts that are all equally valid:

<pre class="nohighlight"><code class="hljs">a7936d49b41b62f05e561f4e7d0c5af58a001da5<strong class="indicator">9</strong> -> <strong class="indicator">00</strong>f0ffd8d7e5d50846be65afa46526eaaf75115c
a7936d49b41b62f05e561f4e7d0c5af58a001da5<strong class="indicator">23</strong> -> <strong class="indicator">00</strong>52fb0e8e1d81e3575a2b47b04c40703414013d
a7936d49b41b62f05e561f4e7d0c5af58a001da5<strong class="indicator">285</strong> -> <strong class="indicator">00</strong>d1d1058d079ed173bad5fa58e5d30e3dda07e5
a7936d49b41b62f05e561f4e7d0c5af58a001da5<strong class="indicator">660</strong> -> <strong class="indicator">00</strong>aa2d732b2c2538e4ac113aec3a86ac31068447
</code></pre>

Any of these nonce values could be submitted, but why would you continue looking after you've found what you're looking for with '7'.

## Cranking up the pain

If we use the same hash as before, but now use a 'difficulty' of `3` (requiring 3 zeros), we have to wait quite a while before we get to a valid signature hash:

<pre class="nohighlight"><code class="hljs">a7936d49b41b62f05e561f4e7d0c5af58a001da5<strong class="indicator">9256</strong> -> <strong class="indicator">000</strong>ccb0dc09162d361d0f7453aef7cd93275c57d
</code></pre>

That is the only valid signature hash in the first 10,000 attempts, and there are only 3 valid signature hashes in the first 20,000 attempts.

What about 4 zeros? Well, there are only 13 valid hashes in the first 1,000,000 attempts. Here is the first one:

<pre class="nohighlight"><code class="hljs">a7936d49b41b62f05e561f4e7d0c5af58a001da5<strong class="indicator">132073</strong> -> <strong class="indicator">0000</strong>add9dc9bf1071af55f7306004808931acbf7
</code></pre>

## How the system uses difficulty

If you have something like an Arduino nano, that can only crank out around 175 hashes per second, you're going to be waiting quite a while to find a signature starting with 4 zeros (on average). That last one for example would have taken 754.7 seconds, a little over 12 and a half minutes. If you were using a massively parallel computer, you'd crack that in a few milliseconds. This is why the standard difficulty parameter is not useful in this system other than as a rule that defines how many zeros a signature needs to have.

The system currently requires **{{MINER_DIFFICULTY}}** zeros for its signatures. Below is a table that shows how an Arduino nano will fair with hashing using the different 'difficulty' levels. Based on a submission time of 15 seconds, it also shows how many of these submissions would have been accepted based on being complete in that time frame.

<table class="table">
	<thead>
		<tr>
			<td></td>
			<th scope="col">Average time</th>
			<th scope="col">Complete at 7.5s</th>
			<th scope="col">Complete at 15s</th>
			<th scope="col">Complete at 30s</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th scope="row">1 Zero</th>
			<td data-label="Average time">0.11s</td>
			<td data-label="Complete at 7.5s">100%</td>
			<td data-label="Complete at 15s">100%</td>
			<td data-label="Complete at 30s">100%</td>
		</tr>
		<tr>
			<th scope="row">2 Zeros</th>
			<td data-label="Average time">1.42s</td>
			<td data-label="Complete at 7.5s">100%</td>
			<td data-label="Complete at 15s">100%</td>
			<td data-label="Complete at 30s">100%</td>
		</tr>
		<tr>
			<th scope="row">3 Zeros</th>
			<td data-label="Average time">22.64s</td>
			<td data-label="Complete at 7.5s">24%</td>
			<td data-label="Complete at 15s">50%</td>
			<td data-label="Complete at 30s">72%</td>
		</tr>
		<tr>
			<th scope="row">4 Zeros</th>
			<td data-label="Average time">396.51</td>
			<td data-label="Complete at 7.5s">2%</td>
			<td data-label="Complete at 15s">2%</td>
			<td data-label="Complete at 30s">4%</td>
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

## How your device will fair

Chances are, your device will be siting idle a lot because you will have found a valid nonce in a few milliseconds, but won't be able to submit a job for {{MINER_SUBMIT_TARGET_SEC}} seconds. Isn't that the best kind of work you could be doing though? Work smarter, not harder. Technology-wise though, you can run a miner in the background using spare CPU power... rather than a spare CPU. If your device is idle most of the time then it's not using a lot of energy either, so almost environmentally friendly.

The other thing to bear in mind though, it isn't just the energy you are using to power things that's creating value, but also 'your' time. This also has value. If everyone can accumulate {{ACCOUNT_MINED_COINS_PER_DAY}} coins per day then why does it matter how fast you're doing it. The slower the pace, the less energy being used, and the less likely it is that servers will break and so things are more stable in general - everybody wins.