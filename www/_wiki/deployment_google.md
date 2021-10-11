# Deploying To Google

Once you have Google App Engine set up processing is reasonably simple.

## Project setup

Within your account you will need to set up a new project. (The one you set up for your RECAPTCHA for example) Keep the name simple as it will be your appspot address as well as the project code you need to make the app work. Whether you use a flexible or basic deployment method will depend on how expensive you want to be, but also the amount of traffic you expect. Standard will put everything in one VM and you will be limited to the resources there. This is fine for a lot of early running. If you use the flex system, it will deploy 3 VM's (one for each on the components - API/CRON/WWW).

You will also need to enable billing for the API bits to work.

You will need to enable the `Cloud Build API` in order to deploy your code.

You will need to enable the `Cloud Schedule` in order to deploy the scheduling activities. You will be prompted to enable this when you deploy for the first time.

## Google Cloud SDK

Installing this depends on your operating system. Google provide [these instructions](https://cloud.google.com/sdk) for you.

You will need to authenticate in the tool with an account that has the correct privileges to deploy code, and then set the default project to the project you created above.

Once all of that is done, you can deploy from the command line.

```language-console
cd /webroot/minertor
sh/deploy.sh
```

You will be shown the deployment you are about to make, and then off it will pop.