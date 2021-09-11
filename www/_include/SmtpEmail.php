<?php
include_once (__DIR__ . "/../vendor/phpmailer/phpmailer/src/PHPMailer.php");
include_once (__DIR__ . "/../vendor/phpmailer/phpmailer/src/SMTP.php");
include_once (__DIR__ . "/../vendor/phpmailer/phpmailer/src/Exception.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Michelf\MarkdownExtra;

function sendEmail($to, $subject, $txt, $attachments = null) {
	global $smtp_server;
	global $smtp_port;
	global $smtp_auth;
	global $smtp_security;
	global $smtp_from_name;
	global $smtp_from_email;
	global $smtp_username;
	global $smtp_password;

	$logo = file_get_contents ( __DIR__ . "/../_gfx/logo-200.png" );
	$logo_src = "inline_logo";

	$html = "<img src=\"cid:$logo_src\" alt=\"application logo\" />\n";
	$html .= "<div style='width:400px;border-top:solid 1px #999;border-bottom:solid 1px #999;padding-top:20px;padding-bottom:20px;margin-top:20px;;margin-bottom:20px;'>\n";
	$html .= "\n";
	$html .= MarkdownExtra::defaultTransform ( $txt );
	$html .= "\n";
	$html .= "</div>\n";

	/**
	 * This example shows settings to use when sending via Google's Gmail servers.
	 * This uses traditional id & password authentication - look at the gmail_xoauth.phps
	 * example to see how to use XOAUTH2.
	 */

	// Create a new PHPMailer instance
	$mail = new PHPMailer ();

	// Tell PHPMailer to use SMTP
	$mail->isSMTP ();

	// Enable SMTP debugging
	// SMTP::DEBUG_OFF = off (for production use)
	// SMTP::DEBUG_CLIENT = client messages
	// SMTP::DEBUG_SERVER = client and server messages
	$mail->SMTPDebug = SMTP::DEBUG_OFF;
	// $mail->SMTPDebug = SMTP::DEBUG_SERVER;

	// Set the hostname of the mail server
	$mail->Host = $smtp_server;
	// Use `$mail->Host = gethostbyname('smtp.gmail.com');`
	// if your network does not support SMTP over IPv6,
	// though this may cause issues with TLS

	// Set the SMTP port number:
	// - 465 for SMTP with implicit TLS, a.k.a. RFC8314 SMTPS or
	// - 587 for SMTP+STARTTLS
	$mail->Port = $smtp_port;

	// Set the encryption mechanism to use:
	// - SMTPS (implicit TLS on port 465) or
	// - STARTTLS (explicit TLS on port 587)
	$mail->SMTPSecure = $smtp_security;

	// Whether to use SMTP authentication
	$mail->SMTPAuth = $smtp_auth;

	if ($smtp_auth) {
		// Username to use for SMTP authentication - use full email address for gmail
		$mail->Username = $smtp_username;

		// Password to use for SMTP authentication
		$mail->Password = $smtp_password;
	}

	// Set who the message is to be sent from
	// Note that with gmail you can only use your account address (same as `Username`)
	// or predefined aliases that you have configured within your account.
	// Do not use user-submitted addresses in here
	$mail->setFrom ( $smtp_from_email, $smtp_from_name );

	// Set an alternative reply-to address
	// This is a good place to put user-submitted addresses
	// $mail->addReplyTo('replyto@example.com', 'First Last');

	// Set who the message is to be sent to
	// $mail->addAddress ( 'nigel@nigeljohnson.net', 'Nigel Johnson' );
	$mail->addAddress ( $to, getAppTitle () . " User" );

	// Set the subject line
	$mail->Subject = $subject;

	// Read an HTML message body from an external file, convert referenced images to embedded,
	// convert HTML into a basic plain-text alternative body
	// $mail->msgHTML(file_get_contents('contents.html'), __DIR__);
	// $mail->msgHTML ( $html );
	$mail->Body = $html;

	// Replace the plain text body with one created manually
	$mail->AltBody = $txt;

	if ($attachments) {
		// $mail->addStringAttachment($newyork, 'new_york_street.jpg', PHPMailer::ENCODING_BASE64, 'image/jpeg', "attachment");
		foreach ( $attachments as $fn => $data ) {
			$mail->addStringAttachment ( $data, $fn );
		}
	}
	// $mail->addAttachment('images/phpmailer_mini.png');
	$mail->addStringEmbeddedImage ( $logo, $logo_src, 'logo.png' );

	// send the message, check for errors
	if (! $mail->send ()) {
		echo "Mailer Error: " . $mail->ErrorInfo . "\n";
		return false;
	}
	echo "Message sent!\n";
	return true;
}
?>