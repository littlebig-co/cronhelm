<?php
/**
 * Functions related to schedules.
 */

namespace Cronhelm\Schedule;

use Cronhelm\Exception\DuplicateScheduleException;
use Cronhelm\Schedule\Schedule;

/**
 * Adds a new custom cron schedule.
 *
 * @throws DuplicateScheduleException If the schedule name already exists.
 *
 * @param string $name     The internal name of the schedule.
 * @param int    $interval The interval between executions of the new schedule.
 * @param string $display  The display name of the schedule.
 * @return void
 */
function add( $name, $interval, $display ) {
	$schedules = get();

	if ( array_key_exists( $name, $schedules ) ) {
		throw new DuplicateScheduleException(
			sprintf(
				/* translators: %s: The internal name of the schedule. */
				__( 'A schedule with the name "%s" already exists.', 'cronhelm' ),
				$name
			)
		);
	}

	/** @var array<string,int|string> */
	$old_scheds = get_option( 'cronhelm_schedules', array() );

	$old_scheds[ $name ] = array(
		'interval' => $interval,
		'display'  => $display,
	);
	update_option( 'cronhelm_schedules', $old_scheds );

	/**
	 * Fires after a new cron schedule is added.
	 *
	 * @param string $name     The internal name of the schedule.
	 * @param int    $interval The interval between executions of the new schedule.
	 * @param string $display  The display name of the schedule.
	 */
	do_action( 'cronhelm/added_new_schedule', $name, $interval, $display );
}

/**
 * Deletes a custom cron schedule.
 *
 * @param string $name The internal name of the schedule to delete.
 * @return void
 */
function delete( $name ) {
	/** @var array<string,int|string> */
	$scheds = get_option( 'cronhelm_schedules', array() );
	unset( $scheds[ $name ] );
	update_option( 'cronhelm_schedules', $scheds );

	/**
	 * Fires after a cron schedule is deleted.
	 *
	 * @param string $name The internal name of the schedule.
	 */
	do_action( 'cronhelm/deleted_schedule', $name );
}

/**
 * Gets a sorted (according to interval) list of the cron schedules
 *
 * @return array<string,Schedule> Array of Schedule objects keyed by schedule name.
 */
function get() {
	/**
	 * @phpstan-var array<string,array{
	 *   interval: int,
	 *   display?: string,
	 * }> $schedules
	 */
	$schedules = wp_get_schedules();
	uasort(
		$schedules,
		fn( array $a, array $b ) => $a['interval'] <=> $b['interval']
	);

	$result = [];
	foreach ( $schedules as $name => $schedule ) {
		$display = $schedule['display'] ?? $name;
		$result[ $name ] = Schedule::create( $name, $schedule['interval'], $display );
	}

	return $result;
}

/**
 * Displays a dropdown filled with the possible schedules, including non-repeating.
 *
 * @param ?string $current The currently selected schedule, or null for none.
 * @return void
 */
function dropdown( ?string $current = null ) {
	$schedules = get();
	?>
	<select class="postform" name="cronhelm_schedule" id="cronhelm_schedule" required>
	<option <?php selected( $current, '_oneoff' ); ?> value="_oneoff"><?php esc_html_e( 'Non-repeating', 'cronhelm' ); ?></option>
	<?php foreach ( $schedules as $schedule ) { ?>
		<option <?php selected( $current, $schedule->name ); ?> value="<?php echo esc_attr( $schedule->name ); ?>">
			<?php
			printf(
				'%s (%s)',
				esc_html( $schedule->display ),
				esc_html( $schedule->name )
			);
			?>
		</option>
	<?php } ?>
	</select>
	<?php
}
