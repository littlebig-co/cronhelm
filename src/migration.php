<?php
/**
 * One-time import of data stored by the plugin Cronhelm was forked from.
 *
 * This is the only place that still mentions the old names, because the old
 * option and cron hook names are what is in the database of an existing site.
 */

namespace Cronhelm;

use Cronhelm\Event\PHPCronEvent;
use Cronhelm\Event\URLCronEvent;

const IMPORTED_OPTION = 'cronhelm_imported_legacy';

/**
 * Hook names used by the original plugin, mapped to their Cronhelm equivalents.
 *
 * @return array<string,string>
 */
function legacy_hook_map(): array {
	return array(
		'crontrol_cron_job'     => PHPCronEvent::HOOK_NAME,
		'crontrol_url_cron_job' => URLCronEvent::HOOK_NAME,
	);
}

/**
 * Copies custom schedules, paused hooks and PHP/URL cron events from the original plugin, once.
 *
 * Skipped while the original plugin is still active, so nothing is pulled out from under it.
 * Existing Cronhelm data is never overwritten.
 *
 * @return void
 */
function maybe_import_legacy_data() {
	if ( get_option( IMPORTED_OPTION ) ) {
		return;
	}

	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( is_plugin_active( 'wp-crontrol/wp-crontrol.php' ) ) {
		return;
	}

	$hook_map = legacy_hook_map();

	$schedules = get_option( 'crontrol_schedules' );
	if ( is_array( $schedules ) && $schedules && ! get_option( 'cronhelm_schedules' ) ) {
		update_option( 'cronhelm_schedules', $schedules );
	}

	$paused = get_option( 'wp_crontrol_paused' );
	if ( is_array( $paused ) && $paused && ! get_option( PAUSED_OPTION ) ) {
		$renamed = array();
		foreach ( $paused as $hook => $value ) {
			$renamed[ $hook_map[ $hook ] ?? $hook ] = $value;
		}
		update_option( PAUSED_OPTION, $renamed, true );
	}

	$crons = _get_cron_array();
	if ( is_array( $crons ) ) {
		$changed = false;
		foreach ( $crons as $timestamp => $hooks ) {
			if ( ! is_array( $hooks ) ) {
				continue;
			}
			foreach ( $hook_map as $old => $new ) {
				if ( ! isset( $hooks[ $old ] ) ) {
					continue;
				}
				$crons[ $timestamp ][ $new ] = array_merge( $crons[ $timestamp ][ $new ] ?? array(), $hooks[ $old ] );
				unset( $crons[ $timestamp ][ $old ] );
				$changed = true;
			}
		}
		if ( $changed ) {
			_set_cron_array( $crons );
		}
	}

	update_option( IMPORTED_OPTION, 1, true );
}
