<?php
/**
 * Plugin Name:  Cronhelm
 * Plugin URI:   https://github.com/littlebig-co/cronhelm
 * Description:  View, edit, run, pause and schedule WP-Cron events and custom cron schedules from your WordPress admin. Free, private by default.
 * Author:       LittleBig.Co (based on work by John Blackbourn and Edward Dale)
 * Author URI:   https://littlebig.co
 * Update URI:   https://license.littlebig.co/v1/update/cronhelm
 * Version:      1.0.1
 * Text Domain:  cronhelm
 * Domain Path:  /languages/
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * License URI:  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * License:      GPL v2 or later
 *
 * LICENSE
 * This file is part of Cronhelm.
 *
 * Cronhelm is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @package    cronhelm
 * @author     John Blackbourn & Edward Dale
 * @copyright  Copyright 2008 Edward Dale, 2012-2026 John Blackbourn, 2026 LittleBig.Co
 * @license    https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GPL 2.0
 * @link       https://github.com/littlebig-co/cronhelm
 */

namespace Cronhelm;

const PLUGIN_FILE = __FILE__;
const CRONHELM_VERSION = '1.0.1';

if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

// @phpstan-ignore booleanNot.alwaysFalse
if ( ! version_compare( PHP_VERSION, '7.4', '>=' ) ) {
	return;
}

if ( ! file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	return;
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/event.php';
require_once __DIR__ . '/src/schedule.php';
require_once __DIR__ . '/src/migration.php';

// Get this show on the road.
init_hooks();
