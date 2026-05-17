<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Plugin solo guarda options. Limpieza al desinstalar.
delete_option( 'ago_smtp_settings' );
delete_option( 'ago_smtp_log' );
delete_transient( 'ago_smtp_alert_last' );

// Cron de alertas: unschedule cualquier evento programado.
$ts = wp_next_scheduled( 'ago_smtp_alert_tick' );
if ( $ts ) {
    wp_unschedule_event( $ts, 'ago_smtp_alert_tick' );
}

// Si alguna instalacion antigua tenia tabla de log, dropearla.
global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ago_smtp_log" );
