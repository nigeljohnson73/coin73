# Account recovery

Account recovery is about getting back into an account you own but no longer have access to for whatever reason (forgotten the password, or it has been locked by the system).

## How does it work

When the process is started, you are asked for your email address. You are presented with a key word on screen that you will need in order to complete the validation. You will be sent an email with a link that you will need to follow to complete the process. The link in this email will only be valid for {{TOKEN_TIMEOUT_HOURS}} hours, and once you click it, it will expire immediately afterwards. The page you will be presented with will have {{MFA_WORD_COUNT}} buttons, one of which will show the key word you were provided with and you should press that button. All being well, that's it, all done.

If you follow the link in the email, that link will no longer be valid.

If you choose the wrong word, you will have to start the process again.

Your account will be locked out (if it isn't already) while the recovery process is in progress.
