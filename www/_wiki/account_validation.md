# Account validation

You need a way of securely accessing your account. This is a centralised blockchain and so we need to able able to manage that. Your email address is generally identifiable and can be unique to you, but it also allows us to send you alerts as required by the system. This needs to be reasonably maintained for the purposes of GDPR and so we ask that you revalidate your account from time to time (you will be sent a reminder when this is required). This also allows you to familiarise yourself with any changes to the terms and conditions.

## How does it work

When the process is started, you are asked for your email address and password. This is to ensure it really is you. Once the request is setup, you are presented with a key word on screen that you will need in order to complete the validation. You will be sent an email with a link that you will need to follow to complete the process. The link in this email will only be valid for {{TOKEN_TIMEOUT_HOURS}} hours, and once you click it, it will expire immediately afterwards. The page you will be presented with will have {{MFA_WORD_COUNT}} buttons, one of which will show the key word you were provided with and you should press that button. All being well, that's it, all done.

If you follow the link in the email, that link will no longer be valid.

If you choose the wrong word, you will have to start the process again.

You will have {{ACTION_GRACE_DAYS}} days to complete the revalidation process once started before your account is locked. After this, an <a href="/wiki/account/recovery">account recovery</a> will be required.

## When does it happen

Validation happens in one of 2 instances.

### User account creation

When you set up an account in the system you will need to prove that you are able to access the email address you supplied. The email address you provide will be sent a link to complete this process.

### When you request a revalidation (after a reminder)

The system will send you a reminder {{REVALIDATION_PERIOD_DAYS}} days after you last revalidated your account.

You will have {{ACTION_GRACE_DAYS}} days to complete the revalidation request before your account is locked. After this, an <a href="/wiki/account/recovery">account recovery</a> will be required.
