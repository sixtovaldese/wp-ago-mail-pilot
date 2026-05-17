<?php

namespace AgoLab\Smtp;

defined( 'ABSPATH' ) || exit;

class Mailer {

    public function __construct() {
        add_action( 'phpmailer_init', [ $this, 'configure' ] );
    }

    public function configure( \PHPMailer\PHPMailer\PHPMailer $phpmailer ): void {
        $s = get_option( Plugin::OPTION_KEY, [] );

        if ( empty( $s['host'] ) ) {
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->Host       = (string) $s['host'];
        $phpmailer->Port       = (int) ( $s['port'] ?? 587 );
        $phpmailer->SMTPAuth   = ! empty( $s['username'] );
        $phpmailer->Username   = (string) ( $s['username'] ?? '' );
        $phpmailer->Password   = self::read_password( $s['password'] ?? '' );
        $phpmailer->SMTPSecure = $s['encryption'] ?? 'tls';

        if ( 'none' === $phpmailer->SMTPSecure ) {
            $phpmailer->SMTPSecure  = '';
            $phpmailer->SMTPAutoTLS = false;
        }

        if ( ! empty( $s['from_email'] ) ) {
            $phpmailer->From = (string) $s['from_email'];
        }
        if ( ! empty( $s['from_name'] ) ) {
            $phpmailer->FromName = (string) $s['from_name'];
        }
    }

    /**
     * Store password. Preferencia: definir constante `AGO_SMTP_PASSWORD` en wp-config.php.
     * Si no se define, se guarda en wp_options con prefijo "aes:" + AES-256-CBC encrypted
     * usando AUTH_KEY (con fallback a LOGGED_IN_KEY si AUTH_KEY no esta seteada).
     */
    public static function store_password( string $pwd ): string {
        if ( '' === $pwd ) {
            return '';
        }
        $key = self::cipher_key();
        if ( '' === $key ) {
            // Sin WP secret keys reales: fallback a base64 (NO es seguro pero el plugin
            // muestra un notice pidiendo al admin que regenere las salts).
            return 'b64:' . base64_encode( $pwd );
        }
        $iv     = substr( md5( $key ), 0, 16 );
        $cipher = openssl_encrypt( $pwd, 'aes-256-cbc', $key, 0, $iv );
        return $cipher === false ? 'b64:' . base64_encode( $pwd ) : 'aes:' . base64_encode( $cipher );
    }

    /**
     * Read password. Soporta tres formatos: aes:, b64:, y constante en wp-config.
     */
    public static function read_password( string $stored ): string {
        if ( defined( 'AGO_SMTP_PASSWORD' ) && '' !== AGO_SMTP_PASSWORD ) {
            return (string) AGO_SMTP_PASSWORD;
        }
        if ( '' === $stored ) {
            return '';
        }
        if ( str_starts_with( $stored, 'aes:' ) ) {
            return self::decrypt_aes( substr( $stored, 4 ) );
        }
        if ( str_starts_with( $stored, 'b64:' ) ) {
            // Legacy formato pre-AES; intentar migracion al leer + re-storage en proximo save.
            $raw = base64_decode( substr( $stored, 4 ), true );
            return $raw !== false ? $raw : '';
        }
        return '';
    }

    private static function decrypt_aes( string $b64 ): string {
        $key = self::cipher_key();
        if ( '' === $key ) {
            return '';
        }
        $raw = base64_decode( $b64, true );
        if ( false === $raw ) {
            return '';
        }
        $iv     = substr( md5( $key ), 0, 16 );
        $clear  = openssl_decrypt( $raw, 'aes-256-cbc', $key, 0, $iv );
        return $clear !== false ? $clear : '';
    }

    /**
     * Cipher key: AUTH_KEY si esta seteada y no es placeholder, sino LOGGED_IN_KEY,
     * sino NONCE_KEY. Si todas son placeholders default, retorna string vacio (fallback b64).
     */
    private static function cipher_key(): string {
        $candidates = [ 'AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'SECURE_AUTH_KEY' ];
        foreach ( $candidates as $c ) {
            if ( defined( $c ) ) {
                $val = constant( $c );
                if ( '' !== $val && 'put your unique phrase here' !== $val ) {
                    return (string) $val;
                }
            }
        }
        return '';
    }

    public static function is_legacy_password( string $stored ): bool {
        if ( '' === $stored ) {
            return false;
        }
        return ! str_starts_with( $stored, 'aes:' ) && ! str_starts_with( $stored, 'b64:' );
    }

    /**
     * Verdadero si la password se almaceno con cifrado AES (no fallback b64).
     */
    public static function is_encrypted( string $stored ): bool {
        return str_starts_with( $stored, 'aes:' );
    }
}
