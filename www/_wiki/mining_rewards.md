# Mining rewards

Please remember throughout this page that you are mining an arbitrary amount of a not-real currency that is designed for learning and social benefit, **NOT** for getting rich.

For doing work on behalf of the blockchain (mining) each miner is rewarded with some of the currency. The amount can be quite a complex subject and is even more so here because of the in-built limitations to flatten out the mining experience.

In summary though, if you mine with the maximum number of miners ({{MINER_MAX_COUNT}}) and nail the submission time ({{MINER_SUBMIT_TARGET_SEC}} seconds) you should see your balance grow by:

**Approximately {{ACCOUNT_MINED_COINS_PER_DAY}} coins per day**

## Targets and limits

In order to attempt to ensure fairness for all, there is a targeted submission time for jobs. If you submit too early you don't get the best deal, and if you are way early, your work is just rejected. If you are a little late, then you are rewarded more for not breaking the servers, but if you are very late, your work is also rejected.

You are also limited on the number of miners you can attach to your account. Each subsequent miner added is less efficient than the previous one.

The system is designed to target a single miner mining rate of {{MINER_REWARD_TARGET_DAY}} coins per day. Let's look at the things that affect this amount.

## Submission time

We will be referring to this graph which shows the submission time in seconds along the bottom, and the reward share along the side.

![Submission time graph](/gfx/submission_time.png)

The target submission time is **{{MINER_SUBMIT_TARGET_SEC}}** seconds.

If you submit a job in under half this time your job will be rejected. If you submit a job after twice this, your job will be rejected.

As you can see, even submitting a job bang on time will not get the highest reward. In fact, the reward is only {{MINER_SUBMIT_TARGET_REWARD_PERCENT}}. This means that if you want to run on a very under powered and slow device, then you can still get a reasonable reward, up to a point.

## Miner efficiency

We will be referring to this graph which shows the the number of physical miners along the bottom, and the perceived total miners along the side.

![Miner efficiency graph](/gfx/miner_efficiency.png)

The maximum number of miners on an account is **{{MINER_MAX_COUNT}}**.

Your first miner will be 100% efficient. Your second miner will be {{MINER_DEGREDATION_PERCENT}} less efficient that the first one. Your third miner will be {{MINER_DEGREDATION_PERCENT}} less efficient that the second one, all the way up to the maximum allowed.

Having the maximum number of physical miners will be the same as having **{{MINER_PERCEIVED_MAX}}** actual miners.

## Bringing it all together

In order to get to the number of coins you will get, you need to factor in all of the above points. Lets take a look the maths.

`total = mining_rate_per_day x average_submission_time_reward x overall_miner_efficency`

Doing the substitution and you get:

`total = {{MINER_REWARD_TARGET_DAY}} x {{MINER_SUBMIT_TARGET_REWARD_PERCENT}} x {{MINER_PERCEIVED_MAX}}`

This gives you the {{ACCOUNT_MINED_COINS_PER_DAY}} coins per day shown at the top of the page. Bear in mind though, that your milage **WILL** vary to the lower side, due to the timings and number of miners submitting on your account at any one time, as well as the submission times you actually get and the fact that to calculate the rate per job you need to work out a fraction of day rate for the target time. If you are running over the darkweb then timings will also be hindered in a negative way.

Yep, seems low, but please refer to the very first sentence on this page.
