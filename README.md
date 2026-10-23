# Cronhelm

View, edit, run, pause and schedule WP-Cron events and custom cron schedules from your WordPress admin. Free and GPL.

Cronhelm is a fork of WP Crontrol 1.22.0 by John Blackbourn and Edward Dale. It has no upsells and no tracking beyond a privacy-friendly update check. Full description, privacy notes and changelog are in [readme.txt](readme.txt).

## Develop

PHP 7.4+, no build step. The Composer classmap in `vendor/` is committed, so after adding or renaming a class run:

    composer dump-autoload --no-dev --classmap-authoritative

## Release

GitLab (`source`) is the working repository and runs CI. GitHub (`github`) is the public copy: push to both, `git push source main && git push github main`.

Bump the version in `cronhelm.php` (header and `CRONHELM_VERSION`) and the changelog in `readme.txt`, push to `main`, then run the manual `release_to_update_server` job in GitLab CI. It builds the ZIP, uploads it to R2 and registers the version with the LittleBig.Co update server.

## License

GPL v2 or later. See [LICENSE](LICENSE). Copyright 2008 Edward Dale, 2012-2026 John Blackbourn, 2026 LittleBig.Co.
