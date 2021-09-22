# Cron service

This is built as a Google service under the main project and handles requests for URLs like `https://<SERVER>/cron/*`

When running this on your local dev machine, run it on port 8090. This is not required anyway since you will set up your own crontab to manage calls to it.

This service is protected from general access by the mechanisms outlined in Google.
