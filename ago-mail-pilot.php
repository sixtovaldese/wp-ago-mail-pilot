<?php
/**
 * Plugin Name: aGo Mail Pilot
 * Plugin URI:  https://ago.cl/herramientas/
 * Description: Send WordPress emails via SMTP with provider presets, a step-by-step credentials wizard, and a simple test email. Minimal, no external API calls.
 * Version:     1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Author:      aGo Lab
 * Author URI:  https://ago.cl/
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ago-mail-pilot
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'AGOMP_VERSION', '1.0.0' );
define( 'AGOMP_FILE', __FILE__ );
define( 'AGOMP_PATH', plugin_dir_path( __FILE__ ) );
define( 'AGOMP_URL', plugin_dir_url( __FILE__ ) );

spl_autoload_register( function ( string $class ): void {
    $prefix = 'AgoLab\\MailPilot\\';
    if ( strncmp( $class, $prefix, strlen( $prefix ) ) !== 0 ) {
        return;
    }
    $relative = substr( $class, strlen( $prefix ) );
    $file     = AGOMP_PATH . 'src/' . str_replace( '\\', '/', $relative ) . '.php';
    if ( file_exists( $file ) ) {
        require_once $file;
    }
} );

add_action( 'plugins_loaded', [ AgoLab\MailPilot\Plugin::class, 'instance' ] );

// Limpieza de cron al desactivar.
register_deactivation_hook( __FILE__, [ AgoLab\MailPilot\AlertWatcher::class, 'deactivate' ] );
