<?php

namespace AgoLab\Smtp;

defined( 'ABSPATH' ) || exit;

class Plugin {

    private static ?self $instance = null;
    public const OPTION_KEY = 'ago_smtp_settings';
    public const LOG_KEY    = 'ago_smtp_log';
    public const LOG_MAX    = 10;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', [ $this, 'load_textdomain' ] );
        add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'admin_post_ago_smtp_save', [ $this, 'handle_save' ] );
        add_action( 'admin_post_ago_smtp_test', [ $this, 'handle_test' ] );
        add_action( 'admin_post_ago_smtp_clear_log', [ $this, 'handle_clear_log' ] );
        add_action( 'admin_post_ago_smtp_dns_audit', [ $this, 'handle_dns_audit' ] );
        add_action( 'admin_notices', [ $this, 'maybe_legacy_password_notice' ] );

        new Mailer();
        new Logger();
        new AlertWatcher();
    }

    public function load_textdomain(): void {
        load_plugin_textdomain( 'ago-smtp', false, dirname( plugin_basename( AGO_SMTP_FILE ) ) . '/languages' );
    }

    public function register_admin_menu(): void {
        if ( empty( $GLOBALS['admin_page_hooks']['ago-tools'] ) ) {
            add_menu_page(
                __( 'aGo Tools', 'ago-smtp' ),
                __( 'aGo Tools', 'ago-smtp' ),
                'manage_options',
                'ago-tools',
                '__return_null',
                'dashicons-hammer',
                81
            );
        }

        add_submenu_page(
            'ago-tools',
            __( 'aGo SMTP', 'ago-smtp' ),
            __( 'SMTP', 'ago-smtp' ),
            'manage_options',
            'ago-smtp',
            [ Admin\Page::class, 'render' ]
        );

        remove_submenu_page( 'ago-tools', 'ago-tools' );
    }

    public function enqueue_assets( string $hook ): void {
        if ( ! str_ends_with( $hook, '_page_ago-smtp' ) ) {
            return;
        }
        wp_enqueue_style(
            'ago-smtp-admin',
            AGO_SMTP_URL . 'assets/css/admin.css',
            [],
            AGO_SMTP_VERSION
        );
        wp_enqueue_script(
            'ago-smtp-admin',
            AGO_SMTP_URL . 'assets/js/admin.js',
            [],
            AGO_SMTP_VERSION,
            true
        );
        wp_localize_script( 'ago-smtp-admin', 'agoSmtpData', [
            'presets' => Presets::all(),
            'guides'  => Presets::guides(),
        ] );
    }

    public function handle_save(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission.', 'ago-smtp' ) );
        }
        check_admin_referer( 'ago_smtp_save' );

        $current = get_option( self::OPTION_KEY, [] );
        $in      = wp_unslash( $_POST );

        $settings = [
            'preset'           => sanitize_key( $in['preset'] ?? '' ),
            'host'             => sanitize_text_field( $in['host'] ?? '' ),
            'port'             => (int) ( $in['port'] ?? 587 ),
            'encryption'       => in_array( $in['encryption'] ?? 'tls', [ 'tls', 'ssl', 'none' ], true ) ? $in['encryption'] : 'tls',
            'username'         => sanitize_text_field( $in['username'] ?? '' ),
            'from_name'        => sanitize_text_field( $in['from_name'] ?? '' ),
            'from_email'       => sanitize_email( $in['from_email'] ?? '' ),
            'alerts_enabled'   => ! empty( $in['alerts_enabled'] ),
            'alerts_threshold' => max( 1, min( 100, (int) ( $in['alerts_threshold'] ?? 30 ) ) ),
            'alerts_min_count' => max( 1, (int) ( $in['alerts_min_count'] ?? 5 ) ),
            'alerts_email'     => sanitize_email( $in['alerts_email'] ?? '' ) ?: get_option( 'admin_email' ),
        ];

        // Password: solo guardar si el campo se rellena (no sobrescribir con vacio).
        $pwd = (string) ( $in['password'] ?? '' );
        if ( '' !== $pwd ) {
            $settings['password'] = Mailer::store_password( $pwd );
        } elseif ( isset( $current['password'] ) ) {
            $settings['password'] = $current['password'];
        }

        if ( $settings['from_email'] && ! is_email( $settings['from_email'] ) ) {
            $this->redirect_back( 'error', __( 'Invalid From Email address.', 'ago-smtp' ) );
        }

        update_option( self::OPTION_KEY, $settings );
        $this->redirect_back( 'success', __( 'Settings saved.', 'ago-smtp' ) );
    }

    public function handle_test(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission.', 'ago-smtp' ) );
        }
        check_admin_referer( 'ago_smtp_test' );

        $to   = wp_get_current_user()->user_email;
        $subj = sprintf(
            /* translators: %s: site name */
            __( '[%s] aGo SMTP test email', 'ago-smtp' ),
            get_bloginfo( 'name' )
        );
        $body = __( "If you can read this, the SMTP configuration is working.\n\nSent by aGo SMTP.", 'ago-smtp' );

        // Capturar errores de PHPMailer y wp_mail_failed.
        $error_message = '';
        $smtp_debug    = '';
        $listener = function ( $err ) use ( &$error_message, &$smtp_debug ) {
            $error_message = $err->get_error_message();
            $data          = $err->get_error_data();
            if ( is_array( $data ) && ! empty( $data['phpmailer_exception_code'] ) ) {
                $smtp_debug = (string) ( $data['phpmailer_exception_code'] );
            }
        };
        add_action( 'wp_mail_failed', $listener );

        $sent = wp_mail( $to, $subj, $body );
        remove_action( 'wp_mail_failed', $listener );

        if ( $sent ) {
            $this->redirect_back( 'success', sprintf(
                /* translators: %s: email address */
                __( 'Test email sent to %s. Check your inbox.', 'ago-smtp' ),
                $to
            ) );
        }

        $detail = $error_message ?: __( 'Unknown SMTP error.', 'ago-smtp' );
        $hint   = $this->error_hint( $error_message );
        $this->redirect_back(
            'error',
            sprintf(
                /* translators: %1$s: error message, %2$s: hint */
                __( 'Test email failed: %1$s %2$s', 'ago-smtp' ),
                '<code>' . esc_html( $detail ) . '</code>',
                $hint ? '<br><strong>' . esc_html__( 'Hint:', 'ago-smtp' ) . '</strong> ' . esc_html( $hint ) : ''
            )
        );
    }

    /**
     * Heuristica simple: matchea errores comunes de PHPMailer a una pista util.
     */
    private function error_hint( string $error ): string {
        $e = strtolower( $error );
        if ( str_contains( $e, '535' ) || str_contains( $e, 'authentication' ) || str_contains( $e, 'no se pudo identificar' ) || str_contains( $e, 'identificar' ) ) {
            return __( 'Authentication failed. The username or password is wrong, or the provider requires a special user format (e.g. SES uses an AKIA... user, SendGrid uses the literal word "apikey", Mailjet uses API Key as user). Open the credentials guide above.', 'ago-smtp' );
        }
        if ( str_contains( $e, 'connect' ) || str_contains( $e, 'connection refused' ) || str_contains( $e, 'timed out' ) ) {
            return __( 'Cannot connect to the SMTP host. Check host name, port and firewall. Most providers use 587 (TLS) or 465 (SSL).', 'ago-smtp' );
        }
        if ( str_contains( $e, 'sender' ) || str_contains( $e, 'from' ) || str_contains( $e, 'address' ) ) {
            return __( 'The From email is not authorized by the provider. Verify the sender or the whole sending domain in the provider dashboard.', 'ago-smtp' );
        }
        if ( str_contains( $e, '550' ) || str_contains( $e, 'rejected' ) ) {
            return __( 'The provider rejected the message. Usually SPF, DKIM or DMARC is wrong, or the recipient blocks unverified senders.', 'ago-smtp' );
        }
        return '';
    }

    public function handle_clear_log(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission.', 'ago-smtp' ) );
        }
        check_admin_referer( 'ago_smtp_clear_log' );
        delete_option( self::LOG_KEY );
        $this->redirect_back( 'success', __( 'Log cleared.', 'ago-smtp' ) );
    }

    public function handle_dns_audit(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission.', 'ago-smtp' ) );
        }
        check_admin_referer( 'ago_smtp_dns_audit' );

        $domain   = sanitize_text_field( wp_unslash( $_POST['dns_domain'] ?? '' ) );
        $selector = sanitize_text_field( wp_unslash( $_POST['dns_selector'] ?? 'default' ) );
        if ( '' === $domain ) {
            $s      = get_option( self::OPTION_KEY, [] );
            $from   = (string) ( $s['from_email'] ?? '' );
            $domain = $from && str_contains( $from, '@' ) ? trim( substr( strrchr( $from, '@' ), 1 ) ) : '';
        }

        $result = DnsAuditor::audit( $domain, $selector ?: 'default' );

        set_transient(
            'ago_smtp_dns_result_' . get_current_user_id(),
            [
                'domain'   => $domain,
                'selector' => $selector ?: 'default',
                'result'   => $result,
            ],
            300
        );

        wp_safe_redirect( admin_url( 'admin.php?page=ago-smtp#dns' ) );
        exit;
    }

    private function redirect_back( string $status, string $message ): void {
        set_transient( 'ago_smtp_notice_' . get_current_user_id(), [
            'status'  => $status,
            'message' => $message,
        ], 60 );
        wp_safe_redirect( admin_url( 'admin.php?page=ago-smtp' ) );
        exit;
    }

    /**
     * Notice global si hay password legacy (pre-1.0.0) que no se puede decodificar.
     */
    public function maybe_legacy_password_notice(): void {
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen || ! str_contains( $screen->id ?? '', 'ago-smtp' ) ) {
            return;
        }
        $s = get_option( self::OPTION_KEY, [] );
        if ( ! empty( $s['password'] ) && Mailer::is_legacy_password( (string) $s['password'] ) ) {
            echo '<div class="notice notice-warning"><p>'
                . esc_html__( 'Re-enter the SMTP password: the storage format was upgraded and the old encrypted value cannot be decoded.', 'ago-smtp' )
                . '</p></div>';
        }
    }
}
