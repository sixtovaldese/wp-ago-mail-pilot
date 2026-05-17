<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Plugin solo guarda options. Limpieza al desinstalar.
delete_option( 'ago_smtp_settings' );
delete_option( 'ago_smtp_log' );
delete_transient( 'ago_smtp_alert_last' );

// Cron de alertas: unschedule cualquier evento programado.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$ago_smtp_ts = wp_next_scheduled( 'ago_smtp_alert_tick' );
if ( $ago_smtp_ts ) {
    wp_unschedule_event( $ago_smtp_ts, 'ago_smtp_alert_tick' );
}

// Si alguna instalacion antigua tenia tabla de log, dropearla. DROP TABLE no
// admite caching ni preparacion alternativa; las warnings de phpcs son falsos
// positivos para uninstall.
global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ago_smtp_log" );
