# Mining rewards

For doing work on behalf of the blockchain (mining) each miner is rewarded with some of the currency. The amount can be quite a complex subject and is even more so here because of the in-built limitations to flatten out the mining experience.

In summary though, if you mine with the maximum number of miners ({{MINER_MAX_COUNT}}) and nail the submission time ({{MINER_SUBMIT_TARGET_SEC}} seconds) you should see your balance grow by approximately:

#### {{ACCOUNT_MINED_COINS_PER_DAY}}/day

The rest of the page is dedicate to explaining how that works.

## Targets and limits

In order to attempt to ensure fairness for all, there is a targeted submission time for jobs. This is kind of like Bitcoin where a new block is targeted to be mined in 10 minutes. In this implementation though, it is purely time based, no difficulty to fluctuate and scam/race/beat. The rewards per share are also targeted to mine a certain number of coins per day (assuming 100% efficiency - this is adjusted below). If you submit too early you don't get the best deal, and if you are way early, your work is just rejected. If you are a little late, then you are rewarded more for not breaking the servers, but if you are very late, your work is also rejected. This is discussed further below.

You are also limited on the number of miners you can attach to your account. Each subsequent miner added is less efficient than the last one.

## Submission time

We will be referring to this graph which shows the submission time in seconds along the bottom, and the reward share along the side.

![Submission time graph](/gfx/submission_time.png)

The target submission time is **{{MINER_SUBMIT_TARGET_SEC}}** seconds.

If you submit a job in under half this time your job will be rejected. If you submit a job after twice this, your job will be rejected.

As you can see, even submitting a job bang on time will not get the highest reward. In fact, the reward is only {{MINER_SUBMIT_TARGET_REWARD_PERCENT}}. This means that if you want to run on a very under powered device, then you can still get a reasonable reward, up to a point.

## Miner efficiency

We will be referring to this graph which shows the the number of physical miners along the bottom, and the perceived total miners along the side.

![Miner efficiency graph](/gfx/miner_efficiency.png)

The maximum number of miners on an account is **{{MINER_MAX_COUNT}}**.

Your first miner will be 100% efficient. Your second miner will be {{MINER_DEGREDATION_PERCENT}} less efficient that the first one. Your third miner will be {{MINER_DEGREDATION_PERCENT}} less efficient that the second one, all the way up to the maximum allowed.

Having the maximum number of physical miners will be the same as having **{{MINER_PERCEIVED_MAX}}** actual miners.