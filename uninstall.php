<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Plugin solo guarda options. Limpieza al desinstalar.
delete_option( 'agomp_settings' );
delete_option( 'agomp_log' );
delete_transient( 'agomp_alert_last' );

// Cron de alertas: unschedule cualquier evento programado.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$agomp_ts = wp_next_scheduled( 'agomp_alert_tick' );
if ( $agomp_ts ) {
    wp_unschedule_event( $agomp_ts, 'agomp_alert_tick' );
}

global $wpdb;

// Per-user transients (notices + dns results) creados con get_current_user_id().
// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ago-mail-pilot\_%' OR option_name LIKE '_transient_timeout_ago-mail-pilot\_%'" );

// Si alguna instalacion antigua tenia tabla de log, dropearla. DROP TABLE no
// admite caching ni preparacion alternativa; las warnings de phpcs son falsos
// positivos para uninstall.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}agomp_log" );
