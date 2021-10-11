<?php
// This file contains configuration that is not part of the software repo but
// is part of the the production system.

// Set the production system key pair - override for localhost
$coinbase_pubKey = "{COINBASE_PUBLIC_KEY}";
$coinbase_privKey = "{COINBASE_PRIVATE_KEY}";

// Set the google RECAPTCHA keys
$recaptcha_site_key = "{RECAPTCHA_SITE_KEY}";
$recaptcha_secret_key = "{RECAPTCHA_SECRET_KEY}";

// Set the system email details
$smtp_from_name = "{SMTP_NAME}";
$smtp_from_email = "{SMTP_EMAIL}";
$smtp_username = $smtp_from_email;
$smtp_password = "{SMTP_PASSWORD}";
