=== aGo SMTP ===
Contributors: sixtovaldese
Donate link: https://paypal.me/sixtovaldes
Tags: smtp, email, mail, phpmailer, mail-log
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Send WordPress emails via SMTP with 8 provider presets, a step-by-step credentials wizard, encrypted password storage, DNS health check (SPF / DKIM / DMARC) and failure rate alerts. Completely free.

== Description ==

aGo SMTP routes WordPress emails through the SMTP server of your choice. Pick a preset for one of 8 supported providers and the in-plugin wizard walks you, step by step, through how to obtain a username and password for that exact provider, with direct links to the right page in the provider dashboard.

This is the differentiator from other SMTP plugins: every preset includes an explainer that opens automatically, in the user's language (English, Spanish, Brazilian Portuguese). End users no longer have to leave WordPress to figure out that Gmail needs an App Password, SendGrid uses the literal username "apikey", or Amazon SES uses dedicated SMTP credentials and not the AWS account credentials.

**Provider presets included**

* Gmail (App Password)
* SendGrid (API key)
* Amazon SES (SMTP credentials from SES console)
* Brevo / Sendinblue (SMTP key)
* Resend (API key)
* MailerSend (SMTP user)
* SMTP2GO (SMTP user)
* Acumbamail (SMTP password)
* Custom (manual entry of any SMTP server)

**Features**

* SMTP host, port, encryption (TLS / SSL / none), username and password.
* From email and From name.
* Step-by-step credentials wizard per provider (English, Spanish, Portuguese).
* Test email button with the exact SMTP response on failure plus a hint.
* Recent email log (last 10 outgoing messages, stored in the WordPress options table; no extra database table).
* **DNS health check**: on-demand audit of SPF, DKIM and DMARC records for your sending domain. Read-only DNS queries; nothing is modified.
* **Encrypted password storage**: AES-256-CBC tied to your WordPress AUTH_KEY. Falls back to base64 only when the WordPress secret keys are placeholders.
* **Optional override**: define `AGO_SMTP_PASSWORD` as a constant in `wp-config.php` and the plugin reads the password from the constant, never from the database.
* **Failure rate alerts**: optional email to the admin when the failure rate across the last N emails exceeds a configurable threshold. Throttled to one alert per 24 hours.
* No third-party API calls on its own. No remote license server. No telemetry. No premium upsell that breaks the WordPress.org guidelines.

== External services ==

This plugin does not call any third-party API on its own. It connects to the SMTP server that you, the site administrator, configure under aGo Tools, SMTP.

When you save credentials and WordPress sends an email (password reset, plugin notification, contact form, etc.), the plugin opens a standard SMTP connection to the host and port you entered, authenticates with your username and password, and delivers the message. The destination of that connection is whatever server you choose (your own mail server, your hosting provider's mail server, or a third-party transactional email provider such as Gmail, SendGrid, Amazon SES, Brevo, Resend, MailerSend, SMTP2GO, Acumbamail or any other SMTP service).

The optional DNS health check uses PHP's built-in `dns_get_record()` to read SPF, DKIM and DMARC TXT records of a domain you specify. This is a standard DNS query that goes to whatever DNS resolver your hosting server is configured with. No data is sent to aGo Lab or to any third party.

Each SMTP provider has its own terms and privacy policy. Please review them before sending production traffic:

* Gmail: https://policies.google.com/privacy and https://policies.google.com/terms
* SendGrid (Twilio): https://www.twilio.com/legal/privacy and https://www.twilio.com/legal/tos
* Amazon SES: https://aws.amazon.com/privacy/ and https://aws.amazon.com/service-terms/
* Brevo: https://www.brevo.com/legal/privacypolicy/ and https://www.brevo.com/legal/termsofuse/
* Resend: https://resend.com/legal/privacy-policy and https://resend.com/legal/terms-of-service
* MailerSend: https://www.mailersend.com/legal/privacy-policy and https://www.mailersend.com/legal/terms-of-service
* SMTP2GO: https://www.smtp2go.com/privacy-policy/ and https://www.smtp2go.com/terms-of-service/
* Acumbamail: https://acumbamail.com/politica-de-privacidad/ and https://acumbamail.com/condiciones-de-uso/

No data leaves your site without an explicit administrator action (saving SMTP credentials, running a DNS check) and a WordPress event triggering an email. The plugin does not contact any aGo Lab server at any point.

== Installation ==

1. Upload the `ago-smtp` folder to `/wp-content/plugins/` or install via the Plugins screen and upload the zip.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to aGo Tools, then SMTP.
4. Pick a provider preset. The credentials wizard opens automatically with step-by-step instructions and direct links.
5. Save the settings and send a test email.
6. Run the DNS health check on your sending domain to confirm SPF, DKIM and DMARC are correctly set.

== Frequently Asked Questions ==

= Why are my emails still not delivered? =

Most delivery issues come from sender authentication, not the plugin. Run the built-in DNS health check on your sending domain. It tells you exactly which of SPF, DKIM or DMARC is missing or misconfigured, with a recommendation per record. When a test email fails, the plugin also shows the exact SMTP response plus a heuristic hint about the likely cause.

= Where is my SMTP password stored? =

In the WordPress options table, encrypted with AES-256-CBC using your site's `AUTH_KEY`. If your secret keys are still the WordPress placeholders, the plugin falls back to base64 encoding and reminds you to regenerate the salts.

For maximum hardening, define `AGO_SMTP_PASSWORD` as a constant in your `wp-config.php`. When the constant is set, the plugin uses it instead of the stored value, so the password never lives in the database.

= Does the plugin send any data to a third party? =

No. The plugin only opens an SMTP connection to the host you configure. No telemetry, no remote license server, no external analytics. No connection to any aGo Lab server. The DNS check uses your server's own DNS resolver.

= Why is the wizard suggesting I create an App Password for Gmail / a SMTP key for Brevo / etc., and not just type my account password? =

Each provider authenticates SMTP differently. Gmail requires an app-specific password (your regular password does not work for SMTP). SendGrid uses the literal username `apikey` and an API key as password. Amazon SES uses dedicated SMTP credentials generated inside the SES console, not your AWS account credentials. The wizard explains each format so you do not waste hours on authentication errors.

= Can I add another provider that is not in the list? =

Yes. Choose Custom in the preset dropdown and enter the SMTP host, port, encryption, username and password manually. The plugin works with any SMTP server.

= How do failure rate alerts work? =

When you enable alerts and set a threshold (e.g. 30%), the plugin checks the recent log every hour. If at least N emails have been sent (configurable minimum sample size) and the failure ratio exceeds the threshold, it emails the configured recipient. To avoid noise, only one alert is sent per 24 hours.

= Is there a way to switch the plugin language? =

The plugin uses your WordPress site language. UI, the credentials wizard, the DNS auditor messages and the alert email are translated to English, Spanish and Brazilian Portuguese out of the box.

== Privacy ==

aGo SMTP does not call any third-party API on its own. It does not collect telemetry, usage statistics or personal data. The only outbound network traffic is the SMTP connection that you, the administrator, explicitly configure under aGo Tools, SMTP.

The plugin stores the following on your site:

* SMTP settings (host, port, encryption, username and AES-256-CBC encrypted password) in the `wp_options` table under the key `ago_smtp_settings`.
* Email delivery log entries (recipient, subject, status, error message, timestamp) in the `wp_options` table under the key `ago_smtp_log`, capped at the last 10 entries. No custom database tables are created.

Deactivating the plugin does not delete the stored data. Uninstalling the plugin deletes both `ago_smtp_settings` and `ago_smtp_log` and unschedules the hourly alert cron event.

== Screenshots ==

1. SMTP configuration screen with provider preset selector and the credentials wizard expanded.
2. DNS health check results for a sending domain (SPF / DKIM / DMARC).
3. Failure rate alerts settings.
4. Sidebar with quick links, About, Other aGo Lab plugins and donation cards.

== Changelog ==

= 1.0.0 =
* Initial release.
* 8 provider presets: Gmail, SendGrid, Amazon SES, Brevo, Resend, MailerSend, SMTP2GO, Acumbamail.
* Step-by-step credentials wizard for each provider, in English, Spanish and Portuguese.
* Test email with detailed SMTP error feedback and heuristic hint.
* DNS health check (SPF, DKIM, DMARC) on-demand.
* AES-256-CBC encrypted password storage tied to `AUTH_KEY`.
* Optional `AGO_SMTP_PASSWORD` constant override in `wp-config.php`.
* Failure rate alerts with configurable threshold, minimum sample size and recipient.
* Recent log of last 10 outgoing emails in `wp_options` (no custom tables).

== Upgrade Notice ==

= 1.0.0 =
Initial release.
