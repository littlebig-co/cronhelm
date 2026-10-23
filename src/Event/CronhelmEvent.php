<?php
/**
 * Base class for Cronhelm managed events (PHP and URL cron events).
 */

namespace Cronhelm\Event;

/**
 * Abstract base class for Cronhelm managed events.
 *
 * Provides common logic for PHP and URL cron events.
 */
abstract class CronhelmEvent extends Event {
	#[\Override]
	final public function hook_name_editable(): bool {
		return false;
	}

	#[\Override]
	final public function pausable(): bool {
		return false;
	}
}
