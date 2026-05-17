<?php

namespace AgoLab\Smtp;

defined( 'ABSPATH' ) || exit;

/**
 * Vigila el log de envios. Si la tasa de fallos en las ultimas N horas supera
 * el umbral configurado, envia un email de alerta al admin. Throttle 24h: no
 * envia mas de una alerta por dia.
 */
class AlertWatcher {

    private const TRANSIENT_LAST = 'ago_smtp_alert_last';
    private const THROTTLE_SECS  = DAY_IN_SECONDS;

    public function __construct() {
        add_action( 'ago_smtp_alert_tick', [ $this, 'tick' ] );

        if ( ! wp_next_scheduled( 'ago_smtp_alert_tick' ) ) {
            wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', 'ago_smtp_alert_tick' );
        }
    }

    public static function deactivate(): void {
        $ts = wp_next_scheduled( 'ago_smtp_alert_tick' );
        if ( $ts ) {
            wp_unschedule_event( $ts, 'ago_smtp_alert_tick' );
        }
    }

    public function tick(): void {
        $settings = get_option( Plugin::OPTION_KEY, [] );
        $enabled  = ! empty( $settings['alerts_enabled'] );
        if ( ! $enabled ) {
            return;
        }

        $threshold = (int) ( $settings['alerts_threshold'] ?? 30 );
        $min_count = (int) ( $settings['alerts_min_count'] ?? 5 );

        $entries = Logger::entries();
        if ( count( $entries ) < $min_count ) {
            return;
        }

        $total = count( $entries );
        $fail  = 0;
        foreach ( $entries as $e ) {
            if ( ( $e['status'] ?? '' ) !== 'ok' ) {
                $fail++;
            }
        }
        $rate = $total > 0 ? round( ( $fail / $total ) * 100 ) : 0;

        if ( $rate < $threshold ) {
            return;
        }

        if ( get_transient( self::TRANSIENT_LAST ) ) {
            return;
        }

        $to   = (string) ( $settings['alerts_email'] ?? get_option( 'admin_email' ) );
        $site = get_bloginfo( 'name' );
        $subj = sprintf(
            /* translators: %s: site name */
            __( '[%s] SMTP failure rate alert', 'ago-smtp' ),
            $site
        );
        $body = sprintf(
            /* translators: 1: failure rate, 2: total emails, 3: failures, 4: admin URL */
            __( "Your site %4\$s is failing to deliver email.\n\nFailure rate in the last %2\$d outgoing emails: %1\$d%% (%3\$d failed).\n\nReview the log: %5\$s\nRun the DNS audit: %6\$s\n\nThis alert is throttled to one per 24 hours.", 'ago-smtp' ),
            $rate,
            $total,
            $fail,
            $site,
            admin_url( 'admin.php?page=ago-smtp#log' ),
            admin_url( 'admin.php?page=ago-smtp#dns' )
        );

        wp_mail( $to, $subj, $body );
        set_transient( self::TRANSIENT_LAST, time(), self::THROTTLE_SECS );
    }
}
