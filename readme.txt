=== Cronhelm ===
Contributors: littlebigco
Tags: cron, wp-cron, scheduled events, debug, action scheduler
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

View, edit, run, pause and schedule WP-Cron events and custom cron schedules from your WordPress admin. Free, private by default.

== Description ==

Cronhelm shows what is happening in WP-Cron and lets you steer it. From the admin screens you can:

* See every scheduled event with its arguments, schedule, callbacks and next run time.
* Edit, delete, pause, resume and run events right away.
* Add new events and bulk delete old ones.
* Add and remove custom cron schedules.
* Export the event list as a CSV file.

It is aware of timezones, warns about events without actions or with missed schedules, and tells you when it detects a problem with your cron system. Action Scheduler events (WooCommerce and others) are supported.

= Where to find it =

1. `Tools > Cron Events` manages events.
2. `Settings > Cron Schedules` manages schedules.

= Privacy =

Cronhelm collects nothing about you or your visitors. Updates come from the LittleBig.Co update server rather than WordPress.org. An update check sends your site address and the installed plugin version, as WordPress.org update checks do. The server turns the address into a one-way hash that changes every day, and stores only that hash together with the version, so it can count roughly how many sites are active. No site address is stored.

= Origin =

Cronhelm is a fork of WP Crontrol 1.22.0 by John Blackbourn and Edward Dale, released under the same GPL. It has no upsells, no suggestions for other plugins and no tracking beyond the update check above. If you used the original, your custom schedules, paused hooks and PHP and URL cron events are imported automatically once you deactivate the original and activate Cronhelm.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or install the ZIP from the Plugins screen.
2. Activate it. If WP Crontrol is active, deactivate it first so its data can be imported.

== Frequently Asked Questions ==

= Why is the plugin not on WordPress.org? =

It updates itself from its own server. The code is free and open source, and you can read it in the repository: https://github.com/littlebig-co/cronhelm

= Can I run PHP and URL cron events? =

Yes, if your user may edit files. Both kinds are protected by an integrity hash so that a tampered database entry will not run. Constants `CRONHELM_DISALLOW_PHP_EVENTS` and `CRONHELM_DISALLOW_URL_EVENTS` switch them off completely. These replace the matching constants of the original plugin, so rename them in `wp-config.php` if you used those.

= How do I report a security problem? =

Privately, by email to hello@littlebig.co. See SECURITY.md in the repository.

== Changelog ==

= 1.0.1 =
* Links now point to the public GitHub repository.

= 1.0.0 =
* First release as Cronhelm, based on WP Crontrol 1.22.0.
* Renamed everything, removed the upsell notices and links to other products.
* Automatic one-time import of schedules, paused hooks and PHP and URL cron events from the original plugin.
* Updates through the LittleBig.Co update server with a privacy-friendly install count.
