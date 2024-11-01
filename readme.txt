=== Gmail Mailer ===
Contributors: naa986
Donate link: https://noorsplugin.com/
Tags: gmail, mail, smtp, email, phpmailer
Requires at least: 5.8
Tested up to: 5.8
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Send email using your Gmail account in WordPress via Gmail SMTP. Configure the wp_mail() function to use Gmail Mailer instead of the PHP mail() function.

== Description ==

[Gmail Mailer](https://noorsplugin.com/gmail-mailer-plugin-for-wordpress/) plugin connects your WordPress website to the Gmail SMTP server and send email using your Gmail account. It takes control of the wp_mail function and use Gmail SMTP instead.

=== Gmail Mailer Settings ===

* **Gmail Username**: Your Gmail account username.
* **Gmail Password**: Your Gmail account password.
* **Type of Encryption**: The encryption to be used when sending an email (TLS/SSL/No Encryption. TLS is recommended).
* **SMTP Port**: The port to be used when sending an email (587/465/25). If you choose TLS the port should be set to 587. For SSL use port 465 instead.
* **From Email Address**: The email address to be used as the From Address when sending an email.
* **From Name**: The name to be used as the From Name when sending an email.

=== Gmail Mailer Test Email ===

Once you have configured the settings you can send a test email to check the functionality of the plugin.
 
* **To**: Email address of the recipient.
* **Subject**: Subject of the email.
* **Message**: Email body.

For detailed setup instructions please visit the [Gmail Mailer](https://noorsplugin.com/gmail-mailer-plugin-for-wordpress/) plugin page.

== Installation ==

1. Go to the Add New plugins screen in your WordPress Dashboard
1. Click the upload tab
1. Browse for the plugin file (wp-gmail-mailer.zip) on your computer
1. Click "Install Now" and then hit the activate button

== Frequently Asked Questions ==

= Can I send email from my website using this plugin? =

Yes.

== Screenshots ==

1. Gmail Mailer Menu
2. Gmail Mailer General Settings
3. Gmail Mailer Test Email Tab

== Upgrade Notice ==
none

== Changelog ==

= 1.0.2 =
* Updated for WordPress 5.8.

= 1.0.1 =
* First commit
