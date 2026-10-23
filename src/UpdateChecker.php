<?php

declare(strict_types=1);

namespace Cronhelm;

/**
 * Keeps Cronhelm updated from its own update server. Hooks the native WordPress
 * filters (pre_set_site_transient_update_plugins, plugins_api) and asks
 * GET /v1/update/:slug. No licence key is needed or sent.
 *
 * The shape transform is load-bearing, not cosmetic: the server returns
 * {version, download_url, ...} but the WordPress update UI reads {new_version,
 * package, ...} — see fetchVersionInfo() below.
 *
 * Config keys used: slug, basename, version, api_url, license_required,
 * license_key or license_key_callback (used when license_required is not
 * false), show_check_update, purchase_url.
 */
final class UpdateChecker
{
    /** @var array<string, mixed> */
    private array $config;

    private string $cacheKey;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $defaults = [
            'slug'                 => '',
            'basename'             => '',
            'version'              => '',
            'api_url'              => '',
            'purchase_url'         => '',
            'license_key'          => '',
            'license_key_callback' => '',
            'license_required'     => true,
            'show_check_update'    => false,
        ];

        $this->config = wp_parse_args($config, $defaults);
        $this->cacheKey = 'lbl_' . md5((string) $this->config['basename']) . '_version_info';

        add_filter('pre_set_site_transient_update_plugins', [$this, 'checkPluginUpdate']);
        add_filter('plugins_api', [$this, 'pluginsApiFilter'], 10, 3);

        if ($this->config['show_check_update']) {
            $this->initCheckUpdateLink();
        }
    }

    public function checkPluginUpdate(mixed $transientData): mixed
    {
        global $pagenow;

        if (!is_object($transientData)) {
            $transientData = new \stdClass();
        }

        if ($pagenow === 'plugins.php' && is_multisite()) {
            return $transientData;
        }

        if (!empty($transientData->response) && !empty($transientData->response[$this->config['basename']])) {
            return $transientData;
        }

        $versionInfo = $this->getVersionInfo();

        if ($versionInfo instanceof \stdClass && isset($versionInfo->new_version)) {
            if (version_compare((string) $this->config['version'], (string) $versionInfo->new_version, '<')) {
                $transientData->response[$this->config['basename']] = $versionInfo;
            } else {
                $transientData->no_update[$this->config['basename']] = $versionInfo;
            }
            $transientData->last_checked = time();
            $transientData->checked[$this->config['basename']] = $this->config['version'];
        }

        return $transientData;
    }

    public function pluginsApiFilter(mixed $result, string $action = '', mixed $args = null): mixed
    {
        if ($action !== 'plugin_information' || !$args || !isset($args->slug) || $args->slug !== $this->config['slug']) {
            return $result;
        }

        $data = $this->getVersionInfo();

        return $data ?: $result;
    }

    private function initCheckUpdateLink(): void
    {
        $getParam = 'lbl_check_update_' . $this->config['slug'];

        add_filter('plugin_row_meta', function (array $links, string $file) use ($getParam): array {
            if ($this->config['basename'] !== $file) {
                return $links;
            }

            $checkUpdateUrl = esc_url(admin_url('plugins.php?' . $getParam . '=' . time()));
            $links['check_update'] = '<a style="color:#583fad;font-weight:600;" href="' . $checkUpdateUrl . '" aria-label="Check Update">Check Update</a>';

            return $links;
        }, 10, 2);

        if (isset($_GET[$getParam])) {
            add_action('admin_init', function () use ($getParam): void {
                if (!current_user_can('update_plugins')) {
                    return;
                }

                delete_transient($this->cacheKey);
                remove_filter('pre_set_site_transient_update_plugins', [$this, 'checkPluginUpdate']);

                $updateCache = get_site_transient('update_plugins');
                if ($updateCache && !empty($updateCache->response[$this->config['basename']])) {
                    unset($updateCache->response[$this->config['basename']]);
                }
                $updateCache = $this->checkPluginUpdate($updateCache);
                set_site_transient('update_plugins', $updateCache);

                add_filter('pre_set_site_transient_update_plugins', [$this, 'checkPluginUpdate']);

                // No search filter here on purpose: WordPress's plugin-list
                // search matches header fields (Name, Description, Text
                // Domain, ...), not the folder slug — filtering by slug only
                // "works" when Text Domain happens to equal slug, which is
                // coincidental, not guaranteed. A plain unfiltered redirect
                // always shows the freshly-recomputed transient correctly.
                wp_redirect(admin_url('plugins.php?plugin_status=all'));
                exit();
            });
        }
    }

    private function getVersionInfo(): \stdClass|false
    {
        global $pagenow;

        if ($pagenow !== 'update-core.php' && !($pagenow === 'plugin-install.php' && !empty($_GET['plugin']))) {
            $cached = get_transient($this->cacheKey);
            if ($cached instanceof \stdClass) {
                return $cached;
            }
        }

        $info = $this->fetchVersionInfo();
        if ($info) {
            set_transient($this->cacheKey, $info, 3 * HOUR_IN_SECONDS);
        }

        return $info;
    }

    private function licenseKey(): string
    {
        if (!empty($this->config['license_key'])) {
            return (string) $this->config['license_key'];
        }
        if (!empty($this->config['license_key_callback'])) {
            return (string) call_user_func($this->config['license_key_callback']);
        }

        return '';
    }

    private function siteDomain(): string
    {
        return (string) (wp_parse_url(home_url(), PHP_URL_HOST) ?? '');
    }

    private function fetchVersionInfo(): \stdClass|false
    {
        $licenseKey = '';
        if ($this->config['license_required'] !== false) {
            $licenseKey = $this->licenseKey();
            if ($licenseKey === '') {
                return false;
            }
        }

        $query = 'domain=' . rawurlencode($this->siteDomain())
            . '&installed_version=' . rawurlencode((string) $this->config['version']);

        if ($this->config['license_required'] !== false) {
            $query = 'license_key=' . rawurlencode($licenseKey) . '&' . $query;
        }

        $url = rtrim((string) $this->config['api_url'], '/')
            . '/v1/update/' . rawurlencode((string) $this->config['slug'])
            . '?' . $query;

        $response = wp_remote_get($url, ['timeout' => 15]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response));
        if (!is_object($body) || !isset($body->version)) {
            return false;
        }

        // Map littlebig-core's response shape onto what WordPress's native
        // update UI / plugins_api reads. Property names below are NOT
        // arbitrary — they're what core expects.
        $info = new \stdClass();
        $info->name = $body->name ?? '';
        $info->slug = $body->slug ?? $this->config['slug'];
        $info->plugin = $this->config['basename'];
        $info->new_version = $body->version;
        $info->version = $body->version;
        $info->package = $body->download_url ?? '';
        $info->download_link = $body->download_url ?? '';
        $info->url = $this->config['purchase_url'] ?? '';
        $info->requires = $body->requires ?? '';
        $info->requires_php = $body->requires_php ?? '';
        $info->tested = $body->tested ?? '';
        $info->last_updated = $body->last_updated ?? '';
        $info->sections = isset($body->sections) ? (array) $body->sections : [];
        $info->banners = [];
        $info->icons = [];

        return $info;
    }
}
