<?php

namespace AgoLab\MailPilot;

defined( 'ABSPATH' ) || exit;

class Presets {

    /**
     * Presets SMTP curados tras testing real con credenciales: incluye solo los
     * proveedores que funcionan con auth basico SMTP sin flujos OAuth ni quirks
     * inutiles para el usuario casual.
     */
    public static function all(): array {
        return [
            'gmail' => [
                'label'      => 'Gmail',
                'host'       => 'smtp.gmail.com',
                'port'       => 587,
                'encryption' => 'tls',
            ],
            'sendgrid' => [
                'label'      => 'SendGrid',
                'host'       => 'smtp.sendgrid.net',
                'port'       => 587,
                'encryption' => 'tls',
            ],
            'ses' => [
                'label'      => 'Amazon SES',
                'host'       => 'email-smtp.us-east-1.amazonaws.com',
                'port'       => 587,
                'encryption' => 'tls',
            ],
            'brevo' => [
                'label'      => 'Brevo (Sendinblue)',
                'host'       => 'smtp-relay.brevo.com',
                'port'       => 587,
                'encryption' => 'tls',
            ],
            'resend' => [
                'label'      => 'Resend',
                'host'       => 'smtp.resend.com',
                'port'       => 587,
                'encryption' => 'tls',
            ],
            'mailersend' => [
                'label'      => 'MailerSend',
                'host'       => 'smtp.mailersend.net',
                'port'       => 587,
                'encryption' => 'tls',
            ],
            'smtp2go' => [
                'label'      => 'SMTP2GO',
                'host'       => 'mail.smtp2go.com',
                'port'       => 587,
                'encryption' => 'tls',
            ],
            'acumbamail' => [
                'label'      => 'Acumbamail',
                'host'       => 'smtp.acumbamail.com',
                'port'       => 587,
                'encryption' => 'tls',
            ],
        ];
    }

    /**
     * Step-by-step para conseguir credenciales por proveedor. Cada step va por
     * __() asi que se traduce automaticamente al idioma del WordPress.
     */
    public static function guides(): array {
        return [
            'gmail' => [
                'title' => __( 'How to get a Gmail App Password', 'ago-mail-pilot' ),
                'note'  => __( 'Requires 2-Step Verification enabled. Your regular Gmail password does NOT work.', 'ago-mail-pilot' ),
                'steps' => [
                    [ 'text' => __( 'Turn on 2-Step Verification in your Google Account.', 'ago-mail-pilot' ), 'url' => 'https://myaccount.google.com/signinoptions/twosv' ],
                    [ 'text' => __( 'Open the App Passwords page and create a new one for "WordPress".', 'ago-mail-pilot' ), 'url' => 'https://myaccount.google.com/apppasswords' ],
                    [ 'text' => __( 'Copy the 16-character code (no spaces).', 'ago-mail-pilot' ), 'url' => '' ],
                    [ 'text' => __( 'Username: your full Gmail address. Password: the 16-character code.', 'ago-mail-pilot' ), 'url' => '' ],
                ],
            ],
            'sendgrid' => [
                'title' => __( 'How to get SendGrid SMTP credentials', 'ago-mail-pilot' ),
                'note'  => __( 'The username is literally the word "apikey". The password is the API key string (starts with SG.).', 'ago-mail-pilot' ),
                'steps' => [
                    [ 'text' => __( 'Open API Keys in SendGrid.', 'ago-mail-pilot' ), 'url' => 'https://app.sendgrid.com/settings/api_keys' ],
                    [ 'text' => __( 'Create a key with at least Mail Send permission. Copy the SG. string.', 'ago-mail-pilot' ), 'url' => '' ],
                    [ 'text' => __( 'Username: apikey (literal word). Password: the SG. string.', 'ago-mail-pilot' ), 'url' => '' ],
                    [ 'text' => __( 'Verify the From Email address with Single Sender Verification or authenticate the whole domain.', 'ago-mail-pilot' ), 'url' => 'https://app.sendgrid.com/settings/sender_auth' ],
                ],
            ],
            'ses' => [
                'title' => __( 'How to get Amazon SES SMTP credentials', 'ago-mail-pilot' ),
                'note'  => __( 'IMPORTANT: SES SMTP credentials are NOT your AWS account credentials and NOT your email address. They are a separate username/password generated inside the SES console.', 'ago-mail-pilot' ),
                'steps' => [
                    [ 'text' => __( 'Open the AWS SES console in the region where you verified your domain.', 'ago-mail-pilot' ), 'url' => 'https://console.aws.amazon.com/ses/home' ],
                    [ 'text' => __( 'Go to SMTP Settings -> Create SMTP Credentials. Download the .csv.', 'ago-mail-pilot' ), 'url' => '' ],
                    [ 'text' => __( 'Username: the SMTP user from the .csv (starts with AKIA). Password: the SMTP password from the .csv.', 'ago-mail-pilot' ), 'url' => '' ],
                    [ 'text' => __( 'Match the host to your region: email-smtp.us-east-1.amazonaws.com, email-smtp.eu-west-1.amazonaws.com, etc.', 'ago-mail-pilot' ), 'url' => '' ],
                    [ 'text' => __( 'If your account is in SES sandbox, only verified recipients can receive. Request production access first.', 'ago-mail-pilot' ), 'url' => 'https://docs.aws.amazon.com/ses/latest/dg/request-production-access.html' ],
                ],
            ],
            'brevo' => [
                'title' => __( 'How to get Brevo (Sendinblue) SMTP credentials', 'ago-mail-pilot' ),
                'note'  => __( 'Brevo exposes an SMTP key in the SMTP & API settings of your account.', 'ago-mail-pilot' ),
                'steps' => [
                    [ 'text' => __( 'Sign in and open SMTP & API settings.', 'ago-mail-pilot' ), 'url' => 'https://app.brevo.com/settings/keys/smtp' ],
                    [ 'text' => __( 'Copy the SMTP login (your Brevo email) and the SMTP key.', 'ago-mail-pilot' ), 'url' => '' ],
                    [ 'text' => __( 'Username: the SMTP login shown. Password: the SMTP key.', 'ago-mail-pilot' ), 'url' => '' ],
                    [ 'text' => __( 'Verify the From Email in Senders & IP -> Senders.', 'ago-mail-pilot' ), 'url' => 'https://app.brevo.com/senders' ],
                ],
            ],
            'resend' => [
                'title' => __( 'How to get Resend SMTP credentials', 'ago-mail-pilot' ),
                'note'  => __( 'Username is literally "resend". Password is the API key starting with re_.', 'ago-mail-pilot' ),
                'steps' => [
                    [ 'text' => __( 'Create an API key in Resend.', 'ago-mail-pilot' ), 'url' => 'https://resend.com/api-keys' ],
                    [ 'text' => __( 'Username: resend (literal word). Password: the API key (starts with re_).', 'ago-mail-pilot' ), 'url' => '' ],
                    [ 'text' => __( 'Verify the sending domain in Domains.', 'ago-mail-pilot' ), 'url' => 'https://resend.com/domains' ],
                ],
            ],
            'mailersend' => [
                'title' => __( 'How to get MailerSend SMTP credentials', 'ago-mail-pilot' ),
                'note'  => __( 'Create an SMTP user from the dashboard. Free trial requires verifying a domain you control or using their test domain (limited).', 'ago-mail-pilot' ),
                'steps' => [
                    [ 'text' => __( 'Open MailerSend Domains and add your sending domain.', 'ago-mail-pilot' ), 'url' => 'https://app.mailersend.com/domains' ],
                    [ 'text' => __( 'Inside the domain settings, create an SMTP user.', 'ago-mail-pilot' ), 'url' => '' ],
                    [ 'text' => __( 'Username: the SMTP user (starts with MS_). Password: the SMTP password shown.', 'ago-mail-pilot' ), 'url' => '' ],
                ],
            ],
            'smtp2go' => [
                'title' => __( 'How to get SMTP2GO credentials', 'ago-mail-pilot' ),
                'note'  => __( 'SMTP2GO uses a username/password pair created in the SMTP Users section of your dashboard.', 'ago-mail-pilot' ),
                'steps' => [
                    [ 'text' => __( 'Sign in and open SMTP Users.', 'ago-mail-pilot' ), 'url' => 'https://app.smtp2go.com/settings/users/smtp/' ],
                    [ 'text' => __( 'Click Add User. Choose any username (often your email) and a strong password.', 'ago-mail-pilot' ), 'url' => '' ],
                    [ 'text' => __( 'Username: the one you set. Password: the one you set (you cannot retrieve it later).', 'ago-mail-pilot' ), 'url' => '' ],
                    [ 'text' => __( 'Verify the sending domain or use the SMTP2GO subdomain for testing.', 'ago-mail-pilot' ), 'url' => 'https://app.smtp2go.com/settings/domains/' ],
                ],
            ],
            'acumbamail' => [
                'title' => __( 'How to get Acumbamail SMTP credentials', 'ago-mail-pilot' ),
                'note'  => __( 'Acumbamail provides one SMTP password per account in the API settings.', 'ago-mail-pilot' ),
                'steps' => [
                    [ 'text' => __( 'Sign in and open API & SMTP settings.', 'ago-mail-pilot' ), 'url' => 'https://acumbamail.com/app/configuracion/api/' ],
                    [ 'text' => __( 'Copy the SMTP password (32-character hex string).', 'ago-mail-pilot' ), 'url' => '' ],
                    [ 'text' => __( 'Username: your Acumbamail account email. Password: the 32-character SMTP password.', 'ago-mail-pilot' ), 'url' => '' ],
                ],
            ],
        ];
    }
}
