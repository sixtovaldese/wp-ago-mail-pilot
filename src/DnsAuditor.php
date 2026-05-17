<?php

namespace AgoLab\Smtp;

defined( 'ABSPATH' ) || exit;

/**
 * Auditor DNS on-demand para SPF, DKIM, DMARC. Lectura pura (dns_get_record),
 * sin cron, sin alerts. El admin lo dispara desde la UI cuando quiere.
 */
class DnsAuditor {

    /**
     * Audita un dominio. Retorna array con 3 entries (spf, dkim, dmarc), cada una
     * con: pass (bool), record (string raw del DNS si existe), message (sugerencia humana).
     */
    public static function audit( string $domain, string $dkim_selector = 'default' ): array {
        $domain = trim( strtolower( $domain ) );
        if ( '' === $domain || ! preg_match( '/^[a-z0-9.\-]+\.[a-z]{2,}$/', $domain ) ) {
            return [
                'spf'   => self::result( false, '', __( 'Invalid domain.', 'ago-smtp' ) ),
                'dkim'  => self::result( false, '', __( 'Invalid domain.', 'ago-smtp' ) ),
                'dmarc' => self::result( false, '', __( 'Invalid domain.', 'ago-smtp' ) ),
            ];
        }

        return [
            'spf'   => self::check_spf( $domain ),
            'dkim'  => self::check_dkim( $domain, $dkim_selector ),
            'dmarc' => self::check_dmarc( $domain ),
        ];
    }

    private static function check_spf( string $domain ): array {
        $records = self::txt( $domain );
        foreach ( $records as $r ) {
            if ( str_starts_with( strtolower( $r ), 'v=spf1' ) ) {
                $msg = __( 'SPF record found. Make sure it includes your sending provider (e.g. include:sendgrid.net, include:amazonses.com, include:_spf.brevo.com).', 'ago-smtp' );
                return self::result( true, $r, $msg );
            }
        }
        return self::result(
            false,
            '',
            __( 'No SPF (v=spf1) TXT record. Without SPF most providers will mark your emails as spam. Add a TXT record at the domain root.', 'ago-smtp' )
        );
    }

    private static function check_dkim( string $domain, string $selector ): array {
        $host    = $selector . '._domainkey.' . $domain;
        $records = self::txt( $host );
        foreach ( $records as $r ) {
            if ( false !== stripos( $r, 'v=dkim1' ) || false !== stripos( $r, 'k=rsa' ) || false !== stripos( $r, 'p=' ) ) {
                return self::result(
                    true,
                    $r,
                    sprintf(
                        /* translators: %s: selector used */
                        __( 'DKIM record found at selector "%s". Confirm the selector matches the one your provider uses.', 'ago-smtp' ),
                        $selector
                    )
                );
            }
        }
        return self::result(
            false,
            '',
            sprintf(
                /* translators: 1: dkim hostname looked up, 2: selector */
                __( 'No DKIM record at %1$s. Your provider tells you which selector to use (often "default", "google", "s1", "selector1", or your provider name). Re-run with the correct selector. Without DKIM your emails fail authentication.', 'ago-smtp' ),
                $host,
                $selector
            )
        );
    }

    private static function check_dmarc( string $domain ): array {
        $records = self::txt( '_dmarc.' . $domain );
        foreach ( $records as $r ) {
            if ( str_starts_with( strtolower( $r ), 'v=dmarc1' ) ) {
                $policy = 'none';
                if ( preg_match( '/p\s*=\s*([a-z]+)/i', $r, $m ) ) {
                    $policy = strtolower( $m[1] );
                }
                return self::result(
                    true,
                    $r,
                    sprintf(
                        /* translators: %s: dmarc policy (none, quarantine, reject) */
                        __( 'DMARC record found with policy "%s". For new domains start with p=none and tighten gradually.', 'ago-smtp' ),
                        $policy
                    )
                );
            }
        }
        return self::result(
            false,
            '',
            __( 'No DMARC record at _dmarc.<domain>. Add a TXT record with at least: v=DMARC1; p=none; rua=mailto:postmaster@<domain>. Gmail and Yahoo require DMARC for bulk senders since 2024.', 'ago-smtp' )
        );
    }

    private static function txt( string $host ): array {
        if ( ! function_exists( 'dns_get_record' ) ) {
            return [];
        }
        $out     = [];
        $records = @dns_get_record( $host, DNS_TXT );
        if ( ! is_array( $records ) ) {
            return [];
        }
        foreach ( $records as $rec ) {
            if ( isset( $rec['txt'] ) ) {
                $out[] = (string) $rec['txt'];
            } elseif ( isset( $rec['entries'] ) && is_array( $rec['entries'] ) ) {
                $out[] = implode( '', $rec['entries'] );
            }
        }
        return $out;
    }

    private static function result( bool $pass, string $record, string $message ): array {
        return [
            'pass'    => $pass,
            'record'  => $record,
            'message' => $message,
        ];
    }
}
