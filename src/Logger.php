<?php

namespace AgoLab\MailPilot;

defined( 'ABSPATH' ) || exit;

class Logger {

    public function __construct() {
        add_action( 'wp_mail_succeeded', [ $this, 'log_success' ] );
        add_action( 'wp_mail_failed', [ $this, 'log_failure' ] );
    }

    public function log_success( array $mail_data ): void {
        $to = $mail_data['to'] ?? '';
        if ( is_array( $to ) ) {
            $to = implode( ', ', $to );
        }
        self::add_entry( (string) $to, (string) ( $mail_data['subject'] ?? '' ), 'ok', '' );
    }

    public function log_failure( \WP_Error $error ): void {
        $data = $error->get_error_data();
        $to   = $data['to'] ?? '';
        if ( is_array( $to ) ) {
            $to = implode( ', ', $to );
        }
        self::add_entry( (string) $to, (string) ( $data['subject'] ?? '' ), 'fail', $error->get_error_message() );
    }

    private static function add_entry( string $to, string $subject, string $status, string $error ): void {
        $log   = get_option( Plugin::LOG_KEY, [] );
        $log   = is_array( $log ) ? $log : [];
        $log[] = [
            'to'      => mb_substr( $to, 0, 255 ),
            'subject' => mb_substr( $subject, 0, 255 ),
            'status'  => $status,
            'error'   => mb_substr( $error, 0, 255 ),
            'time'    => time(),
        ];
        if ( count( $log ) > Plugin::LOG_MAX ) {
            $log = array_slice( $log, -Plugin::LOG_MAX );
        }
        update_option( Plugin::LOG_KEY, $log, false );
    }

    public static function entries(): array {
        $log = get_option( Plugin::LOG_KEY, [] );
        if ( ! is_array( $log ) ) {
            return [];
        }
        return array_reverse( $log );
    }
}
