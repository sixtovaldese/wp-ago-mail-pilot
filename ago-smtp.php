<?php
/**
 * Plugin Name: aGo SMTP
 * Plugin URI:  https://ago.cl
 * Description: Send WordPress emails via SMTP with provider presets, a step-by-step credentials wizard, and a simple test email. Minimal, no external API calls.
 * Version:     1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Author:      aGo Lab
 * Author URI:  https://ago.cl
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ago-smtp
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'AGO_SMTP_VERSION', '1.0.0' );
define( 'AGO_SMTP_FILE', __FILE__ );
define( 'AGO_SMTP_PATH', plugin_dir_path( __FILE__ ) );
define( 'AGO_SMTP_URL', plugin_dir_url( __FILE__ ) );

spl_autoload_register( function ( string $class ): void {
    $prefix = 'AgoLab\\Smtp\\';
    if ( strncmp( $class, $prefix, strlen( $prefix ) ) !== 0 ) {
        return;
    }
    $relative = substr( $class, strlen( $prefix ) );
    $file     = AGO_SMTP_PATH . 'src/' . str_replace( '\\', '/', $relative ) . '.php';
    if ( file_exists( $file ) ) {
        require_once $file;
    }
} );

add_action( 'plugins_loaded', [ AgoLab\Smtp\Plugin::class, 'instance' ] );

// Limpieza de cron al desactivar.
register_deactivation_hook( __FILE__, [ AgoLab\Smtp\AlertWatcher::class, 'deactivate' ] );
