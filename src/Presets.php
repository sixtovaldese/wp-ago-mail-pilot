<?php

namespace AgoLab\Smtp;

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
                'title' => __( 'How to get a Gmail App Password', 'ago-smtp' ),
                'note'  => __( 'Requires 2-Step Verification enabled. Your regular Gmail password does NOT work.', 'ago-smtp' ),
                'steps' => [
                    [ 'text' => __( 'Turn on 2-Step Verification in your Google Account.', 'ago-smtp' ), 'url' => 'https://myaccount.google.com/signinoptions/twosv' ],
                    [ 'text' => __( 'Open the App Passwords page and create a new one for "WordPress".', 'ago-smtp' ), 'url' => 'https://myaccount.google.com/apppasswords' ],
                    [ 'text' => __( 'Copy the 16-character code (no spaces).', 'ago-smtp' ), 'url' => '' ],
                    [ 'text' => __( 'Username: your full Gmail address. Password: the 16-character code.', 'ago-smtp' ), 'url' => '' ],
                ],
            ],
            'sendgrid' => [
                'title' => __( 'How to get SendGrid SMTP credentials', 'ago-smtp' ),
                'note'  => __( 'The username is literally the word "apikey". The password is the API key string (starts with SG.).', 'ago-smtp' ),
                'steps' => [
                    [ 'text' => __( 'Open API Keys in SendGrid.', 'ago-smtp' ), 'url' => 'https://app.sendgrid.com/settings/api_keys' ],
                    [ 'text' => __( 'Create a key with at least Mail Send permission. Copy the SG. string.', 'ago-smtp' ), 'url' => '' ],
                    [ 'text' => __( 'Username: apikey (literal word). Password: the SG. string.', 'ago-smtp' ), 'url' => '' ],
                    [ 'text' => __( 'Verify the From Email address with Single Sender Verification or authenticate the whole domain.', 'ago-smtp' ), 'url' => 'https://app.sendgrid.com/settings/sender_auth' ],
                ],
            ],
            'ses' => [
                'title' => __( 'How to get Amazon SES SMTP credentials', 'ago-smtp' ),
                'note'  => __( 'IMPORTANT: SES SMTP credentials are NOT your AWS account credentials and NOT your email address. They are a separate username/password generated inside the SES console.', 'ago-smtp' ),
                'steps' => [
                    [ 'text' => __( 'Open the AWS SES console in the region where you verified your domain.', 'ago-smtp' ), 'url' => 'https://console.aws.amazon.com/ses/home' ],
                    [ 'text' => __( 'Go to SMTP Settings -> Create SMTP Credentials. Download the .csv.', 'ago-smtp' ), 'url' => '' ],
                    [ 'text' => __( 'Username: the SMTP user from the .csv (starts with AKIA). Password: the SMTP password from the .csv.', 'ago-smtp' ), 'url' => '' ],
                    [ 'text' => __( 'Match the host to your region: email-smtp.us-east-1.amazonaws.com, email-smtp.eu-west-1.amazonaws.com, etc.', 'ago-smtp' ), 'url' => '' ],
                    [ 'text' => __( 'If your account is in SES sandbox, only verified recipients can receive. Request production access first.', 'ago-smtp' ), 'url' => 'https://docs.aws.amazon.com/ses/latest/dg/request-production-access.html' ],
                ],
            ],
            'brevo' => [
                'title' => __( 'How to get Brevo (Sendinblue) SMTP credentials', 'ago-smtp' ),
                'note'  => __( 'Brevo exposes an SMTP key in the SMTP & API settings of your account.', 'ago-smtp' ),
                'steps' => [
                    [ 'text' => __( 'Sign in and open SMTP & API settings.', 'ago-smtp' ), 'url' => 'https://app.brevo.com/settings/keys/smtp' ],
                    [ 'text' => __( 'Copy the SMTP login (your Brevo email) and the SMTP key.', 'ago-smtp' ), 'url' => '' ],
                    [ 'text' => __( 'Username: the SMTP login shown. Password: the SMTP key.', 'ago-smtp' ), 'url' => '' ],
                    [ 'text' => __( 'Verify the From Email in Senders & IP -> Senders.', 'ago-smtp' ), 'url' => 'https://app.brevo.com/senders' ],
                ],
            ],
            'resend' => [
                'title' => __( 'How to get Resend SMTP credentials', 'ago-smtp' ),
                'note'  => __( 'Username is literally "resend". Password is the API key starting with re_.', 'ago-smtp' ),
                'steps' => [
                    [ 'text' => __( 'Create an API key in Resend.', 'ago-smtp' ), 'url' => 'https://resend.com/api-keys' ],
                    [ 'text' => __( 'Username: resend (literal word). Password: the API key (starts with re_).', 'ago-smtp' ), 'url' => '' ],
                    [ 'text' => __( 'Verify the sending domain in Domains.', 'ago-smtp' ), 'url' => 'https://resend.com/domains' ],
                ],
            ],
            'mailersend' => [
                'title' => __( 'How to get MailerSend SMTP credentials', 'ago-smtp' ),
                'note'  => __( 'Create an SMTP user from the dashboard. Free trial requires verifying a domain you control or using their test domain (limited).', 'ago-smtp' ),
                'steps' => [
                    [ 'text' => __( 'Open MailerSend Domains and add your sending domain.', 'ago-smtp' ), 'url' => 'https://app.mailersend.com/domains' ],
                    [ 'text' => __( 'Inside the domain settings, create an SMTP user.', 'ago-smtp' ), 'url' => '' ],
                    [ 'text' => __( 'Username: the SMTP user (starts with MS_). Password: the SMTP password shown.', 'ago-smtp' ), 'url' => '' ],
                ],
            ],
            'smtp2go' => [
                'title' => __( 'How to get SMTP2GO credentials', 'ago-smtp' ),
                'note'  => __( 'SMTP2GO uses a username/password pair created in the SMTP Users section of your dashboard.', 'ago-smtp' ),
                'steps' => [
                    [ 'text' => __( 'Sign in and open SMTP Users.', 'ago-smtp' ), 'url' => 'https://app.smtp2go.com/settings/users/smtp/' ],
                    [ 'text' => __( 'Click Add User. Choose any username (often your email) and a strong password.', 'ago-smtp' ), 'url' => '' ],
                    [ 'text' => __( 'Username: the one you set. Password: the one you set (you cannot retrieve it later).', 'ago-smtp' ), 'url' => '' ],
                    [ 'text' => __( 'Verify the sending domain or use the SMTP2GO subdomain for testing.', 'ago-smtp' ), 'url' => 'https://app.smtp2go.com/settings/domains/' ],
                ],
            ],
            'acumbamail' => [
                'title' => __( 'How to get Acumbamail SMTP credentials', 'ago-smtp' ),
                'note'  => __( 'Acumbamail provides one SMTP password per account in the API settings.', 'ago-smtp' ),
                'steps' => [
                    [ 'text' => __( 'Sign in and open API & SMTP settings.', 'ago-smtp' ), 'url' => 'https://acumbamail.com/app/configuracion/api/' ],
                    [ 'text' => __( 'Copy the SMTP password (32-character hex string).', 'ago-smtp' ), 'url' => '' ],
                    [ 'text' => __( 'Username: your Acumbamail account email. Password: the 32-character SMTP password.', 'ago-smtp' ), 'url' => '' ],
                ],
            ],
        ];
    }
}
