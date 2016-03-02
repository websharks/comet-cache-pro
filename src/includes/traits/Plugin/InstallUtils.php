<?php
namespace WebSharks\CometCache\Pro\Traits\Plugin;

use WebSharks\CometCache\Pro\Classes;

trait InstallUtils {

    /**
     * Plugin activation hook.
     *
     * @since 150422 Rewrite.
     *
     * @attaches-to {@link \register_activation_hook()}
     */
    public function activate()
    {
        $this->setup(); // Ensure setup is complete.

        if (!$this->options['welcomed'] && !$this->options['enable']) {
            $settings_url = add_query_arg(urlencode_deep(['page' => GLOBAL_NS]), network_admin_url('/admin.php'));
            $this->enqueueMainNotice(sprintf(__('<strong>%1$s</strong> successfully installed! :-) <strong>Please <a href="%2$s">enable caching and review options</a>.</strong>', SLUG_TD), esc_html(NAME), esc_attr($settings_url)), ['push_to_top' => true]);
            $this->updateOptions(['welcomed' => '1']);
        }

        if (!$this->options['enable']) {
            return; // Nothing to do.
        }

        $this->addWpCacheToWpConfig();
        $this->addWpHtaccess();
        $this->addAdvancedCache();
        $this->updateBlogPaths();
        $this->autoClearCache();
    }

    /**
     * Check current plugin version that is installed in WP.
     *
     * @since 150422 Rewrite.
     *
     * @attaches-to `admin_init` hook.
     */
    public function checkVersion()
    {
        $prev_version = $this->options['version'];
        if (version_compare($prev_version, VERSION, '>=')) {
            return; // Nothing to do; up-to-date.
        }
        $this->updateOptions(['version' => VERSION]);

        new Classes\VsUpgrades($prev_version);

        if ($this->options['enable']) {
            $this->addWpCacheToWpConfig();
            $this->addWpHtaccess();
            $this->addAdvancedCache();
            $this->updateBlogPaths();
        }
        $this->wipeCache(); // Fresh start now.

        $this->enqueueMainNotice(sprintf(__('<strong>%1$s:</strong> detected a new version of itself. Recompiling w/ latest version... wiping the cache... all done :-)', SLUG_TD), esc_html(NAME)), ['push_to_top' => true]);
    }

    /**
     * Plugin deactivation hook.
     *
     * @since 150422 Rewrite.
     *
     * @attaches-to {@link \register_deactivation_hook()}
     */
    public function deactivate()
    {
        $this->setup(); // Ensure setup is complete.

        $this->removeWpCacheFromWpConfig();
        $this->removeWpHtaccess();
        $this->removeAdvancedCache();
        $this->clearCache();
        $this->resetCronSetup();
    }

    /**
     * Plugin uninstall hook.
     *
     * @since 150422 Rewrite.
     */
    public function uninstall()
    {
        $this->setup(); // Ensure setup is complete.

        if (!defined('WP_UNINSTALL_PLUGIN')) {
            return; // Disallow.
        }
        if (empty($GLOBALS[GLOBAL_NS.'_uninstalling'])) {
            return; // Not uninstalling.
        }
        if (!current_user_can($this->uninstall_cap)) {
            return; // Extra layer of security.
        }
        $this->removeWpCacheFromWpConfig();
        $this->removeWpHtaccess();
        $this->removeAdvancedCache();
        $this->wipeCache();
        $this->resetCronSetup();

        if (!$this->options['uninstall_on_deletion']) {
            return; // Nothing to do here.
        }
        $this->deleteAdvancedCache();
        $this->deleteBaseDir();

        $wpdb = $this->wpdb(); // WordPress DB.
        $like = '%'.$wpdb->esc_like(GLOBAL_NS).'%';

        if (is_multisite()) { // Site options for a network installation.
            $wpdb->query('DELETE FROM `'.esc_sql($wpdb->sitemeta).'` WHERE `meta_key` LIKE \''.esc_sql($like).'\'');

            switch_to_blog(get_current_site()->blog_id); // In case it started as a standard WP installation.
            $wpdb->query('DELETE FROM `'.esc_sql($wpdb->options).'` WHERE `option_name` LIKE \''.esc_sql($like).'\'');
            restore_current_blog(); // Restore current blog.
            //
        } else { // Standard WP installation.
            $wpdb->query('DELETE FROM `'.esc_sql($wpdb->options).'` WHERE `option_name` LIKE \''.esc_sql($like).'\'');
        }
    }

    /**
     * Adds `define('WP_CACHE', TRUE);` to the `/wp-config.php` file.
     *
     * @since 150422 Rewrite.
     *
     * @return string The new contents of the updated `/wp-config.php` file;
     *                else an empty string if unable to add the `WP_CACHE` constant.
     */
    public function addWpCacheToWpConfig()
    {
        if (!$this->options['enable']) {
            return ''; // Nothing to do.
        }
        if (!($wp_config_file = $this->findWpConfigFile())) {
            return ''; // Unable to find `/wp-config.php`.
        }
        if (!is_readable($wp_config_file)) {
            return ''; // Not possible.
        }
        if (!($wp_config_file_contents = file_get_contents($wp_config_file))) {
            return ''; // Failure; could not read file.
        }
        if (!($wp_config_file_contents_no_whitespace = php_strip_whitespace($wp_config_file))) {
            return ''; // Failure; file empty
        }
        if (preg_match('/\bdefine\s*\(\s*([\'"])WP_CACHE\\1\s*,\s*(?:\-?[1-9][0-9\.]*|TRUE|([\'"])(?:[^0\'"]|[^\'"]{2,})\\2)\s*\)\s*;/i', $wp_config_file_contents_no_whitespace)) {
            return $wp_config_file_contents; // It's already in there; no need to modify this file.
        }
        if (!($wp_config_file_contents = $this->removeWpCacheFromWpConfig())) {
            return ''; // Unable to remove previous value.
        }
        if (!($wp_config_file_contents = preg_replace('/^\s*(\<\?php|\<\?)\s+/i', '${1}'."\n"."define('WP_CACHE', TRUE);"."\n", $wp_config_file_contents, 1))) {
            return ''; // Failure; something went terribly wrong here.
        }
        if (strpos($wp_config_file_contents, "define('WP_CACHE', TRUE);") === false) {
            return ''; // Failure; unable to add; unexpected PHP code.
        }
        if (defined('DISALLOW_FILE_MODS') && DISALLOW_FILE_MODS) {
            return ''; // We may NOT edit any files.
        }
        if (!is_writable($wp_config_file)) {
            return ''; // Not possible.
        }
        if (!file_put_contents($wp_config_file, $wp_config_file_contents)) {
            return ''; // Failure; could not write changes.
        }
        return $wp_config_file_contents;
    }

    /**
     * Removes `define('WP_CACHE', TRUE);` from the `/wp-config.php` file.
     *
     * @since 150422 Rewrite.
     *
     * @return string The new contents of the updated `/wp-config.php` file;
     *                else an empty string if unable to remove the `WP_CACHE` constant.
     */
    public function removeWpCacheFromWpConfig()
    {
        if (!($wp_config_file = $this->findWpConfigFile())) {
            return ''; // Unable to find `/wp-config.php`.
        }
        if (!is_readable($wp_config_file)) {
            return ''; // Not possible.
        }
        if (!($wp_config_file_contents = file_get_contents($wp_config_file))) {
            return ''; // Failure; could not read file.
        }
        if (!($wp_config_file_contents_no_whitespace = php_strip_whitespace($wp_config_file))) {
            return ''; // Failure; file empty
        }
        if (!preg_match('/([\'"])WP_CACHE\\1/i', $wp_config_file_contents_no_whitespace)) {
            return $wp_config_file_contents; // Already gone.
        }
        if (preg_match('/\bdefine\s*\(\s*([\'"])WP_CACHE\\1\s*,\s*(?:0|FALSE|NULL|([\'"])0?\\2)\s*\)\s*;/i', $wp_config_file_contents_no_whitespace) && !is_writable($wp_config_file)) {
            return $wp_config_file_contents; // It's already disabled, and since we can't write to this file let's let this slide.
        }
        if (!($wp_config_file_contents = preg_replace('/\bdefine\s*\(\s*([\'"])WP_CACHE\\1\s*,\s*(?:\-?[0-9\.]+|TRUE|FALSE|NULL|([\'"])[^\'"]*\\2)\s*\)\s*;/i', '', $wp_config_file_contents))) {
            return ''; // Failure; something went terribly wrong here.
        }
        if (preg_match('/([\'"])WP_CACHE\\1/i', $wp_config_file_contents)) {
            return ''; // Failure; perhaps the `/wp-config.php` file contains syntax we cannot remove safely.
        }
        if (defined('DISALLOW_FILE_MODS') && DISALLOW_FILE_MODS) {
            return ''; // We may NOT edit any files.
        }
        if (!is_writable($wp_config_file)) {
            return ''; // Not possible.
        }
        if (!file_put_contents($wp_config_file, $wp_config_file_contents)) {
            return ''; // Failure; could not write changes.
        }
        return $wp_config_file_contents;
    }

    /**
     * Checks to make sure the `advanced-cache.php` file still exists;
     *    and if it doesn't, the `advanced-cache.php` is regenerated automatically.
     *
     * @since 150422 Rewrite.
     *
     * @attaches-to `init` hook.
     *
     * @note This runs so that remote deployments which completely wipe out an
     *    existing set of website files (like the AWS Elastic Beanstalk does) will NOT cause Comet Cache
     *    to stop functioning due to the lack of an `advanced-cache.php` file, which is generated by Comet Cache.
     *
     *    For instance, if you have a Git repo with all of your site files; when you push those files
     *    to your website to deploy them, you most likely do NOT have the `advanced-cache.php` file.
     *    Comet Cache creates this file on its own. Thus, if it's missing (and CC is active)
     *    we simply regenerate the file automatically to keep Comet Cache running.
     */
    public function checkAdvancedCache()
    {
        if (!$this->options['enable']) {
            return; // Nothing to do.
        }
        if (!empty($_REQUEST[GLOBAL_NS])) {
            return; // Skip on plugin actions.
        }
        $cache_dir                 = $this->cacheDir();
        $advanced_cache_file       = WP_CONTENT_DIR.'/advanced-cache.php';
        $advanced_cache_check_file = $cache_dir.'/'.strtolower(SHORT_NAME).'-advanced-cache';

        // Fixes zero-byte advanced-cache.php bug related to migrating from ZenCache
        //      See: <https://github.com/websharks/zencache/issues/432>

        // Also fixes a missing `define('WP_CACHE', TRUE)` bug related to migrating from ZenCache
        //      See <https://github.com/websharks/zencache/issues/450>

        if (!is_file($advanced_cache_check_file) || !is_file($advanced_cache_file) || filesize($advanced_cache_file) === 0) {
            $this->addAdvancedCache();
            $this->addWpCacheToWpConfig();
        }
    }

    /**
     * Creates and adds the `advanced-cache.php` file.
     *
     * @since 150422 Rewrite.
     *
     * @return bool|null `TRUE` on success. `FALSE` or `NULL` on failure.
     *                   A special `NULL` return value indicates success with a single failure
     *                   that is specifically related to the `[SHORT_NAME]-advanced-cache` file.
     */
    public function addAdvancedCache()
    {
        if (!$this->removeAdvancedCache()) {
            return false; // Still exists.
        }
        $cache_dir                 = $this->cacheDir();
        $advanced_cache_file       = WP_CONTENT_DIR.'/advanced-cache.php';
        $advanced_cache_check_file = $cache_dir.'/'.strtolower(SHORT_NAME).'-advanced-cache';
        $advanced_cache_template   = dirname(dirname(__DIR__)).'/templates/advanced-cache.txt';

        if (is_file($advanced_cache_file) && !is_writable($advanced_cache_file)) {
            return false; // Not possible to create.
        }
        if (!is_file($advanced_cache_file) && !is_writable(dirname($advanced_cache_file))) {
            return false; // Not possible to create.
        }
        if (!is_file($advanced_cache_template) || !is_readable($advanced_cache_template)) {
            return false; // Template file is missing; or not readable.
        }
        if (!($advanced_cache_contents = file_get_contents($advanced_cache_template))) {
            return false; // Template file is missing; or is not readable.
        }
        $possible_advanced_cache_constant_key_values = array_merge(
            $this->options, // The following additional keys are dynamic.
            [
                'cache_dir'               => $this->basePathTo($this->cache_sub_dir),
                /*[pro strip-from="lite"]*/
                'htmlc_cache_dir_public'  => $this->basePathTo($this->htmlc_cache_sub_dir_public),
                'htmlc_cache_dir_private' => $this->basePathTo($this->htmlc_cache_sub_dir_private),
                /*[/pro]*/
            ]
        );
        if ($this->applyWpFilters(GLOBAL_NS.'_exclude_uris_client_side_too', true)) {
            $possible_advanced_cache_constant_key_values['exclude_client_side_uris'] .= "\n".$this->options['exclude_uris'];
        }
        foreach ($possible_advanced_cache_constant_key_values as $_option => $_value) {
            $_value = (string) $_value; // Force string.

            switch ($_option) {
                case 'exclude_uris': // Converts to regex (caSe insensitive).
                case 'exclude_client_side_uris': // Converts to regex (caSe insensitive).
                case 'exclude_refs': // Converts to regex (caSe insensitive).
                case 'exclude_agents': // Converts to regex (caSe insensitive).

                    /*[pro strip-from="lite"]*/
                case 'htmlc_css_exclusions': // Converts to regex (caSe insensitive).
                case 'htmlc_js_exclusions': // Converts to regex (caSe insensitive).
                case 'htmlc_uri_exclusions': // Converts to regex (caSe insensitive).
                    /*[/pro]*/

                    $_value = "'".$this->escSq($this->lineDelimitedPatternsToRegex($_value))."'";

                    break; // Break switch handler.

                /*[pro strip-from="lite"]*/
                case 'version_salt': // This is PHP code; and we MUST validate syntax.

                    if ($_value && !is_wp_error($_response = wp_remote_post('http://phpcodechecker.com/api/', ['body' => ['code' => $_value]]))
                        && is_object($_response = json_decode(wp_remote_retrieve_body($_response))) && !empty($_response->errors) && strcasecmp($_response->errors, 'true') === 0
                    ) {
                        $_value = ''; // PHP syntax errors; empty this.
                        $this->enqueueMainError(sprintf(__('<strong>%1$s</strong>: ignoring your Version Salt; it seems to contain PHP syntax errors.', SLUG_TD), esc_html(NAME)));
                    }
                    if (!$_value) {
                        $_value = "''"; // Use an empty string (default).
                    }
                    break; // Break switch handler.
                /*[/pro]*/

                default: // Default case handler.

                    $_value = "'".$this->escSq($_value)."'";

                    break; // Break switch handler.
            }
            $advanced_cache_contents = // Fill replacement codes.
                str_ireplace(
                    [
                        "'%%".GLOBAL_NS.'_'.$_option."%%'",
                        "'%%".GLOBAL_NS.'_'.preg_replace('/^cache_/i', '', $_option)."%%'",
                    ],
                    $_value,
                    $advanced_cache_contents
                );
        }
        unset($_option, $_value, $_values, $_response); // Housekeeping.

        if (strpos(PLUGIN_FILE, WP_CONTENT_DIR) === 0) {
            $plugin_file = "WP_CONTENT_DIR.'".$this->escSq(str_replace(WP_CONTENT_DIR, '', PLUGIN_FILE))."'";
        } else {
            $plugin_file = "'".$this->escSq(PLUGIN_FILE)."'"; // Full absolute path.
        }
        // Make it possible for the `advanced-cache.php` handler to find the plugin directory reliably.
        $advanced_cache_contents = str_ireplace("'%%".GLOBAL_NS."_PLUGIN_FILE%%'", $plugin_file, $advanced_cache_contents);

        // Ignore; this is created by Comet Cache; and we don't need to obey in this case.
        #if(defined('DISALLOW_FILE_MODS') && DISALLOW_FILE_MODS)
        #	return FALSE; // We may NOT edit any files.

        if (!file_put_contents($advanced_cache_file, $advanced_cache_contents)) {
            return false; // Failure; could not write file.
        }
        $cache_lock = $this->cacheLock(); // Lock cache.

        if (!is_dir($cache_dir)) {
            mkdir($cache_dir, 0775, true);
        }
        if (is_writable($cache_dir) && !is_file($cache_dir.'/.htaccess')) {
            file_put_contents($cache_dir.'/.htaccess', $this->htaccess_deny);
        }
        if (!is_dir($cache_dir) || !is_writable($cache_dir) || !is_file($cache_dir.'/.htaccess') || !file_put_contents($advanced_cache_check_file, time())) {
            $this->cacheUnlock($cache_lock); // Release.
            return; // Special return value (NULL).
        }
        $this->cacheUnlock($cache_lock); // Release.

        $this->clearAcDropinFromOpcacheByForce();

        return true;
    }

    /**
     * Removes the `advanced-cache.php` file.
     *
     * @since 150422 Rewrite.
     *
     * @return bool `TRUE` on success. `FALSE` on failure.
     *
     * @note The `advanced-cache.php` file is NOT actually deleted by this routine.
     *    Instead of deleting the file, we simply empty it out so that it's `0` bytes in size.
     *
     *    The reason for this is to preserve any file permissions set by the site owner.
     *    If the site owner previously allowed this specific file to become writable, we don't want to
     *    lose that permission by deleting the file; forcing the site owner to do it all over again later.
     *
     *    An example of where this is useful is when a site owner deactivates the CC plugin,
     *    but later they decide that CC really is the most awesome plugin in the world and they turn it back on.
     */
    public function removeAdvancedCache()
    {
        $advanced_cache_file = WP_CONTENT_DIR.'/advanced-cache.php';

        if (!is_file($advanced_cache_file)) {
            return true; // Already gone.
        }
        if (is_readable($advanced_cache_file) && filesize($advanced_cache_file) === 0) {
            return true; // Already gone; i.e. it's empty already.
        }
        if (!is_writable($advanced_cache_file)) {
            return false; // Not possible.
        }
        /* Empty the file only. This way permissions are NOT lost in cases where
            a site owner makes this specific file writable for Comet Cache. */
        if (file_put_contents($advanced_cache_file, '') !== 0) {
            return false; // Failure.
        }
        $this->clearAcDropinFromOpcacheByForce();

        return true;
    }

    /**
     * Deletes the `advanced-cache.php` file.
     *
     * @since 150422 Rewrite.
     *
     * @return bool `TRUE` on success. `FALSE` on failure.
     *
     * @note The `advanced-cache.php` file is deleted by this routine.
     */
    public function deleteAdvancedCache()
    {
        $cache_dir                 = $this->cacheDir();
        $advanced_cache_file       = WP_CONTENT_DIR.'/advanced-cache.php';
        $advanced_cache_check_file = $cache_dir.'/'.strtolower(SHORT_NAME).'-advanced-cache';

        if (is_file($advanced_cache_file)) {
            if (!is_writable($advanced_cache_file) || !unlink($advanced_cache_file)) {
                return false; // Not possible; or outright failure.
            }
        }
        if (is_file($advanced_cache_check_file)) {
            if (!is_writable($advanced_cache_check_file) || !unlink($advanced_cache_check_file)) {
                return false; // Not possible; or outright failure.
            }
        }
        $this->clearAcDropinFromOpcacheByForce();

        return true; // Deletion success.
    }

    /**
     * Checks to make sure the `[SHORT_NAME]-blog-paths` file still exists;
     *    and if it doesn't, the `[SHORT_NAME]-blog-paths` file is regenerated automatically.
     *
     * @since 150422 Rewrite.
     *
     * @attaches-to `init` hook.
     *
     * @note This runs so that remote deployments which completely wipe out an
     *    existing set of website files (like the AWS Elastic Beanstalk does) will NOT cause Comet Cache
     *    to stop functioning due to the lack of a `[SHORT_NAME]-blog-paths` file, which is generated by Comet Cache.
     *
     *    For instance, if you have a Git repo with all of your site files; when you push those files
     *    to your website to deploy them, you most likely do NOT have the `[SHORT_NAME]-blog-paths` file.
     *    Comet Cache creates this file on its own. Thus, if it's missing (and CC is active)
     *    we simply regenerate the file automatically to keep Comet Cache running.
     */
    public function checkBlogPaths()
    {
        if (!$this->options['enable']) {
            return; // Nothing to do.
        }
        if (!is_multisite()) {
            return; // N/A.
        }
        if (!empty($_REQUEST[GLOBAL_NS])) {
            return; // Skip on plugin actions.
        }
        $cache_dir       = $this->cacheDir();
        $blog_paths_file = $cache_dir.'/'.strtolower(SHORT_NAME).'-blog-paths';

        if (!is_file($blog_paths_file)) {
            $this->updateBlogPaths();
        }
    }

    /**
     * Creates and/or updates the `[SHORT_NAME]-blog-paths` file.
     *
     * @since 150422 Rewrite.
     *
     * @attaches-to `enable_live_network_counts` filter.
     *
     * @param mixed $enable_live_network_counts Optional, defaults to a `NULL` value.
     *
     * @return mixed The value of `$enable_live_network_counts` (passes through).
     *
     * @note While this routine is attached to a WP filter, we also call upon it directly at times.
     */
    public function updateBlogPaths($enable_live_network_counts = null)
    {
        $value = $enable_live_network_counts; // This hook actually rides on a filter.

        if (!$this->options['enable']) {
            return $value; // Nothing to do.
        }
        if (!is_multisite()) {
            return $value; // N/A.
        }
        $cache_dir       = $this->cacheDir();
        $blog_paths_file = $cache_dir.'/'.strtolower(SHORT_NAME).'-blog-paths';

        $cache_lock = $this->cacheLock();

        if (!is_dir($cache_dir)) {
            mkdir($cache_dir, 0775, true);
        }
        if (is_writable($cache_dir) && !is_file($cache_dir.'/.htaccess')) {
            file_put_contents($cache_dir.'/.htaccess', $this->htaccess_deny);
        }
        if (is_dir($cache_dir) && is_writable($cache_dir)) {
            $paths = // Collect child `[/base]/path/`s from the WordPress database.
                $this->wpdb()->get_col('SELECT `path` FROM `'.esc_sql($this->wpdb()->blogs)."` WHERE `deleted` <= '0'");

            $host_base_token = $this->hostBaseToken(); // Pull this once only.

            foreach ($paths as $_key => &$_path) {
                if ($_path && $_path !== '/' && $host_base_token && $host_base_token !== '/') {
                    // Note that each `path` in the DB looks like: `[/base]/path/` (i.e., it includes base).
                    $_path = '/'.ltrim(preg_replace('/^'.preg_quote($host_base_token, '/').'/', '', $_path), '/');
                }
                if (!$_path || $_path === '/') {
                    unset($paths[$_key]); // Exclude main site.
                }
            }
            unset($_key, $_path); // Housekeeping.

            file_put_contents($blog_paths_file, serialize($paths));
        }
        $this->cacheUnlock($cache_lock); // Release.

        return $value; // Pass through untouched (always).
    }

    /**
     * Deletes base directory.
     *
     * @since 151002 Improving multisite compat.
     *
     * @return int Total files removed by this routine (if any).
     */
    public function deleteBaseDir()
    {
        $counter = 0; // Initialize.

        @set_time_limit(1800); // @TODO Display a warning.

        $counter += $this->deleteAllFilesDirsIn($this->wpContentBaseDirTo(''), true);

        return $counter;
    }
}