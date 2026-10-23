<?php
/**
 * WordPress feature flag context implementation.
 *
 * @package cronhelm
 */

namespace Cronhelm\Context;

use function Cronhelm\php_cron_events_enabled;
use function Cronhelm\url_cron_events_enabled;

/**
 * FeatureContext implementation that checks WordPress constants and options.
 *
 * This is the production implementation that reads from the WordPress environment.
 */
class WordPressFeatureContext implements FeatureContext {
	#[\Override]
	public function php_crons_enabled(): bool {
		return php_cron_events_enabled();
	}

	#[\Override]
	public function url_crons_enabled(): bool {
		return url_cron_events_enabled();
	}
}
