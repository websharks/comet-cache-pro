<?php
namespace quick_cache // Root namespace.
	{
		if(!defined('WPINC')) // MUST have WordPress.
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/*
		 * This file serves as a template for the Quick Cache plugin in WordPress.
		 * The Quick Cache plugin will fill the `%%` replacement codes automatically.
		 *    e.g. this file becomes: `/wp-content/advanced-cache.php`.
		 *
		 * Or, if you prefer; you can set the PHP constants below in your `/wp-config.php` file.
		 * Then, you could simply drop this file into: `/wp-content/advanced-cache.php` on your own :-)
		 * ~ Be sure to setup a CRON job that clears your `QUICK_CACHE_DIR` periodically.
		 */

		/*
		 * Quick Cache configuration constants.
		 * ----------------------------------------------------------------------------
		 */
		/*
		 * These work as boolean flags.
		 */
		if(!defined('QUICK_CACHE_PRO')) define('QUICK_CACHE_PRO', TRUE); // Identifier.
		if(!defined('QUICK_CACHE_ENABLE')) define('QUICK_CACHE_ENABLE', '%%QUICK_CACHE_ENABLE%%');
		if(!defined('QUICK_CACHE_DEBUGGING_ENABLE')) define('QUICK_CACHE_DEBUGGING_ENABLE', '%%QUICK_CACHE_DEBUGGING_ENABLE%%');
		if(!defined('QUICK_CACHE_ALLOW_BROWSER_CACHE')) define('QUICK_CACHE_ALLOW_BROWSER_CACHE', '%%QUICK_CACHE_ALLOW_BROWSER_CACHE%%');
		if(!defined('QUICK_CACHE_CACHE_404_REQUESTS')) define('QUICK_CACHE_CACHE_404_REQUESTS', '%%QUICK_CACHE_CACHE_404_REQUESTS%%');

		/*
		 * Cache directory. Max age; e.g. `7 days` — anything compatible w/ `strtotime()`.
		 */
		if(!defined('QUICK_CACHE_DIR')) define('QUICK_CACHE_DIR', ABSPATH.'%%QUICK_CACHE_DIR%%');
		if(!defined('QUICK_CACHE_MAX_AGE')) define('QUICK_CACHE_MAX_AGE', '%%QUICK_CACHE_MAX_AGE%%');

		/*
		 * The work as boolean flags.
		 */
		if(!defined('QUICK_CACHE_WHEN_LOGGED_IN')) define('QUICK_CACHE_WHEN_LOGGED_IN', '%%QUICK_CACHE_WHEN_LOGGED_IN%%');
		if(!defined('QUICK_CACHE_GET_REQUESTS')) define('QUICK_CACHE_GET_REQUESTS', '%%QUICK_CACHE_GET_REQUESTS%%');
		if(!defined('QUICK_CACHE_FEEDS_ENABLE')) define('QUICK_CACHE_FEEDS_ENABLE', '%%QUICK_CACHE_FEEDS_ENABLE%%');

		/*
		 * These should contain empty strings; or regex patterns.
		 */
		if(!defined('QUICK_CACHE_EXCLUDE_URIS')) define('QUICK_CACHE_EXCLUDE_URIS', '%%QUICK_CACHE_EXCLUDE_URIS%%');
		if(!defined('QUICK_CACHE_EXCLUDE_REFS')) define('QUICK_CACHE_EXCLUDE_REFS', '%%QUICK_CACHE_EXCLUDE_REFS%%');
		if(!defined('QUICK_CACHE_EXCLUDE_AGENTS')) define('QUICK_CACHE_EXCLUDE_AGENTS', '%%QUICK_CACHE_EXCLUDE_AGENTS%%');

		/*
		 * Any string value; or just an empty string will do fine also.
		 */
		if(!defined('QUICK_CACHE_VERSION_SALT')) define('QUICK_CACHE_VERSION_SALT', '%%QUICK_CACHE_VERSION_SALT%%');

		/*
		 * A unique filename for the special 404 Cache File (used when 404 caching is enabled).
		 */
		if(!defined('QUICK_CACHE_404_CACHE_FILENAME')) define('QUICK_CACHE_404_CACHE_FILENAME', '----404----');

		/*
		 * Configuration for the HTML Compressor (if enabled).
		 */
		if(!defined('QUICK_CACHE_HTMLC_ENABLE')) define('QUICK_CACHE_HTMLC_ENABLE', '%%QUICK_CACHE_HTMLC_ENABLE%%');

		if(!defined('QUICK_CACHE_HTMLC_CSS_EXCLUSIONS')) define('QUICK_CACHE_HTMLC_CSS_EXCLUSIONS', '%%QUICK_CACHE_HTMLC_CSS_EXCLUSIONS%%');
		if(!defined('QUICK_CACHE_HTMLC_JS_EXCLUSIONS')) define('QUICK_CACHE_HTMLC_JS_EXCLUSIONS', '%%QUICK_CACHE_HTMLC_JS_EXCLUSIONS%%');

		if(!defined('QUICK_CACHE_HTMLC_CACHE_EXPIRATION_TIME')) define('QUICK_CACHE_HTMLC_CACHE_EXPIRATION_TIME', '%%QUICK_CACHE_HTMLC_CACHE_EXPIRATION_TIME%%');
		if(!defined('QUICK_CACHE_HTMLC_CACHE_DIR_PUBLIC')) define('QUICK_CACHE_HTMLC_CACHE_DIR_PUBLIC', ABSPATH.'%%QUICK_CACHE_HTMLC_CACHE_DIR_PUBLIC%%');
		if(!defined('QUICK_CACHE_HTMLC_CACHE_DIR_PRIVATE')) define('QUICK_CACHE_HTMLC_CACHE_DIR_PRIVATE', ABSPATH.'%%QUICK_CACHE_HTMLC_CACHE_DIR_PRIVATE%%');

		if(!defined('QUICK_CACHE_HTMLC_COMPRESS_COMBINE_HEAD_BODY_CSS')) define('QUICK_CACHE_HTMLC_COMPRESS_COMBINE_HEAD_BODY_CSS', '%%QUICK_CACHE_HTMLC_COMPRESS_COMBINE_HEAD_BODY_CSS%%');
		if(!defined('QUICK_CACHE_HTMLC_COMPRESS_COMBINE_HEAD_JS')) define('QUICK_CACHE_HTMLC_COMPRESS_COMBINE_HEAD_JS', '%%QUICK_CACHE_HTMLC_COMPRESS_COMBINE_HEAD_JS%%');
		if(!defined('QUICK_CACHE_HTMLC_COMPRESS_COMBINE_FOOTER_JS')) define('QUICK_CACHE_HTMLC_COMPRESS_COMBINE_FOOTER_JS', '%%QUICK_CACHE_HTMLC_COMPRESS_COMBINE_FOOTER_JS%%');
		if(!defined('QUICK_CACHE_HTMLC_COMPRESS_COMBINE_REMOTE_CSS_JS')) define('QUICK_CACHE_HTMLC_COMPRESS_COMBINE_REMOTE_CSS_JS', '%%QUICK_CACHE_HTMLC_COMPRESS_COMBINE_REMOTE_CSS_JS%%');
		if(!defined('QUICK_CACHE_HTMLC_COMPRESS_INLINE_JS_CODE')) define('QUICK_CACHE_HTMLC_COMPRESS_INLINE_JS_CODE', '%%QUICK_CACHE_HTMLC_COMPRESS_INLINE_JS_CODE%%');
		if(!defined('QUICK_CACHE_HTMLC_COMPRESS_CSS_CODE')) define('QUICK_CACHE_HTMLC_COMPRESS_CSS_CODE', '%%QUICK_CACHE_HTMLC_COMPRESS_CSS_CODE%%');
		if(!defined('QUICK_CACHE_HTMLC_COMPRESS_JS_CODE')) define('QUICK_CACHE_HTMLC_COMPRESS_JS_CODE', '%%QUICK_CACHE_HTMLC_COMPRESS_JS_CODE%%');
		if(!defined('QUICK_CACHE_HTMLC_COMPRESS_HTML_CODE')) define('QUICK_CACHE_HTMLC_COMPRESS_HTML_CODE', '%%QUICK_CACHE_HTMLC_COMPRESS_HTML_CODE%%');

		/*
		 * The heart of Quick Cache.
		 */

		class advanced_cache # `/wp-content/advanced-cache.php`
		{
			public $is_pro = TRUE; // Identifies the pro version of Quick Cache.
			public $timer = 0; // Microtime; defined by class constructor for debugging purposes.
			public $protocol = ''; // Calculated protocol; one of `http://` or `https://`.
			public $user_token = ''; // Calculated user token; applicable w/ user postload enabled.
			public $version_salt = ''; // Calculated version salt; set by site configuration data.
			public $cache_path = ''; // Calculated cache path; absolute relative (no leading/trailing slashes).
			public $cache_file = ''; // Calculated location; defined by `maybe_start_output_buffering()`.
			public $cache_file_404 = ''; // Calculated location; defined by `maybe_start_output_buffering()`.
			public $salt_location = ''; // Calculated location; defined by `maybe_start_output_buffering()`.
			public $text_domain = ''; // Defined by class constructor; this is for translations.
			public $postload = array(); // Off by default; just an empty array.
			public $hooks = array(); // Array of advanced cache plugin hooks.

			public function __construct() // Class constructor/cache handler.
				{
					if(!WP_CACHE || !QUICK_CACHE_ENABLE)
						return; // Not enabled.

					if(defined('WP_INSTALLING') || defined('RELOCATE'))
						return; // N/A; installing|relocating.

					$this->timer       = microtime(TRUE);
					$this->text_domain = str_replace('_', '-', __NAMESPACE__);

					$this->load_ac_plugins();
					$this->maybe_stop_browser_caching();
					$this->maybe_postload_invalidate_when_logged_in();
					$this->maybe_start_output_buffering();
				}

			public function load_ac_plugins()
				{
					if(!is_dir(WP_CONTENT_DIR.'/ac-plugins'))
						return; // Nothing to do here.

					$GLOBALS[__NAMESPACE__.'__advanced_cache']
						= $this; // Define now; so it's available for plugins.

					foreach((array)glob(WP_CONTENT_DIR.'/ac-plugins/*.php') as $_ac_plugin)
						if(is_file($_ac_plugin)) include_once $_ac_plugin;
					unset($_ac_plugin); // Houskeeping.
				}

			public function maybe_stop_browser_caching()
				{
					if(!empty($_GET['qcABC'])) return;
					if(QUICK_CACHE_ALLOW_BROWSER_CACHE) return;

					header_remove('Last-Modified');
					header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
					header('Cache-Control: no-cache, must-revalidate, max-age=0');
					header('Pragma: no-cache');
				}

			public function maybe_postload_invalidate_when_logged_in()
				{
					if(QUICK_CACHE_WHEN_LOGGED_IN !== 'postload')
						return; // Nothing to do in this case.

					if(is_admin()) // Not in admin.
						return; // No invalidations here.

					if(!$this->is_like_user_logged_in())
						return; // Nothing to do.

					if(!empty($_REQUEST[__NAMESPACE__]['clear_cache']))
						return; // Site owner is clearing cache now.

					if(!empty($_REQUEST[__NAMESPACE__]['ajax_clear_cache']))
						return; // Site owner is clearing cache now.

					if($this->is_post_put_del_request())
						$this->postload['invalidate_when_logged_in'] = TRUE;

					else if(!QUICK_CACHE_GET_REQUESTS && $this->is_get_request_w_query())
						$this->postload['invalidate_when_logged_in'] = TRUE;
				}

			public function maybe_start_output_buffering()
				{
					if(empty($_SERVER['HTTP_HOST'])) return;
					if(empty($_SERVER['REQUEST_URI'])) return;
					if(strtoupper(PHP_SAPI) === 'CLI') return;

					if(defined('DONOTCACHEPAGE')) return;
					if(isset($_SERVER['DONOTCACHEPAGE'])) return;

					if(isset($_GET['qcAC']) && !$_GET['qcAC']) return;
					if(defined('QUICK_CACHE_ALLOWED') && !QUICK_CACHE_ALLOWED) return;
					if(isset($_SERVER['QUICK_CACHE_ALLOWED']) && !$_SERVER['QUICK_CACHE_ALLOWED']) return;

					if($this->is_post_put_del_request()) return; // Do not cache `POST|PUT|DELETE` requests (ever).

					if(isset($_SERVER['REMOTE_ADDR'], $_SERVER['SERVER_ADDR']) && $_SERVER['REMOTE_ADDR'] === $_SERVER['SERVER_ADDR'])
						if(!$this->is_auto_cache_engine() && !$this->is_localhost()) return;

					if(!QUICK_CACHE_FEEDS_ENABLE && $this->is_feed()) return;

					if(preg_match('/\/(?:wp\-[^\/]+|xmlrpc)\.php(?:[?]|$)/', $_SERVER['REQUEST_URI'])) return;
					if(is_admin() || preg_match('/\/wp-admin(?:[\/?]|$)/', $_SERVER['REQUEST_URI'])) return;
					if(is_multisite() && preg_match('/\/files(?:[\/?]|$)/', $_SERVER['REQUEST_URI'])) return;

					if(!QUICK_CACHE_WHEN_LOGGED_IN && $this->is_like_user_logged_in()) return;

					if(!QUICK_CACHE_GET_REQUESTS && $this->is_get_request_w_query() && empty($_GET['qcAC'])) return;

					if(QUICK_CACHE_EXCLUDE_URIS && preg_match(QUICK_CACHE_EXCLUDE_URIS, $_SERVER['REQUEST_URI'])) return;

					if(QUICK_CACHE_EXCLUDE_AGENTS && !empty($_SERVER['HTTP_USER_AGENT']) && !$this->is_auto_cache_engine())
						if(preg_match(QUICK_CACHE_EXCLUDE_AGENTS, $_SERVER['HTTP_USER_AGENT'])) return;

					if(QUICK_CACHE_EXCLUDE_REFS && !empty($_REQUEST['_wp_http_referer']))
						if(preg_match(QUICK_CACHE_EXCLUDE_REFS, stripslashes($_REQUEST['_wp_http_referer']))) return;

					if(QUICK_CACHE_EXCLUDE_REFS && !empty($_SERVER['HTTP_REFERER']))
						if(preg_match(QUICK_CACHE_EXCLUDE_REFS, $_SERVER['HTTP_REFERER'])) return;

					$this->protocol       = $this->is_ssl() ? 'https://' : 'http://';
					$this->version_salt   = $this->apply_filters(__CLASS__.'__version_salt', QUICK_CACHE_VERSION_SALT);
					$this->cache_path     = $this->url_to_cache_path($this->protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], '', $this->version_salt);
					$this->cache_file     = QUICK_CACHE_DIR.'/'.$this->cache_path; // NOT considering a user cache; not yet.
					$this->cache_file_404 = QUICK_CACHE_DIR.'/'.$this->url_to_cache_path($this->protocol.$_SERVER['HTTP_HOST'].'/'.QUICK_CACHE_404_CACHE_FILENAME);
					$this->salt_location  = ltrim($this->version_salt.' '.$this->protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

					if(QUICK_CACHE_WHEN_LOGGED_IN === 'postload' && $this->is_like_user_logged_in())
						{
							$this->postload['when_logged_in'] = TRUE; // Enable postload check.
							return; // Do nothing here; a postload check reveals more about this user.
						}
					if(is_file($this->cache_file) && filemtime($this->cache_file) >= strtotime('-'.QUICK_CACHE_MAX_AGE))
						{
							list($headers, $cache) = explode('<!--headers-->', file_get_contents($this->cache_file), 2);

							$headers_list = headers_list(); // Headers already sent (or ready to be sent).
							foreach(unserialize($headers) as $_header) // Preserves original headers sent with this file.
								if(!in_array($_header, $headers_list) && stripos($_header, 'Last-Modified:') !== 0) header($_header);
							unset($_header); // Just a little housekeeping.

							if(QUICK_CACHE_DEBUGGING_ENABLE) // Debugging messages enabled; or no?
								{
									$total_time = number_format(microtime(TRUE) - $this->timer, 5, '.', '');
									$cache .= "\n".'<!-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ -->';
									// translators: This string is actually NOT translatable because the `__()` function is not available at this point in the processing.
									$cache .= "\n".'<!-- '.htmlspecialchars(sprintf(__('Quick Cache fully functional :-) Cache file served for (%1$s) in %2$s seconds, on: %3$s.', $this->text_domain), $this->salt_location, $total_time, date('M jS, Y @ g:i a T'))).' -->';
								}
							exit($cache); // Exit with cache contents.
						}
					else ob_start(array($this, 'output_buffer_callback_handler')); // Start output buffering.
				}

			public function maybe_invalidate_when_logged_in_postload()
				{
					if(QUICK_CACHE_WHEN_LOGGED_IN !== 'postload')
						return; // Nothing to do in this case.

					if(empty($this->postload['invalidate_when_logged_in']))
						return; // Nothing to do in this case.

					if(!($this->user_token = $this->user_token()))
						return; // Do NOT invalidate; no token.

					$regex = '/\.u\/'.preg_quote($this->user_token, '/').'[.\/]/'; // This user.

					/** @var $_file \RecursiveDirectoryIterator For IDEs. */
					foreach($this->dir_regex_iteration(QUICK_CACHE_DIR, $regex) as $_file) if($_file->isFile() || $_file->isLink())
						{
							if(!unlink($_file->getPathname())) // Throw exception if unable to delete.
								throw new \exception(sprintf(__('Unable to invalidate file: `%1$s`.', $this->text_domain), $_file->getPathname()));
						}
					unset($_file); // Just a little housekeeping.
				}

			public function maybe_start_ob_when_logged_in_postload()
				{
					if(QUICK_CACHE_WHEN_LOGGED_IN !== 'postload')
						return; // Nothing to do in this case.

					if(empty($this->postload['when_logged_in']))
						return; // Nothing to do in this case.

					if(!($this->user_token = $this->user_token()))
						return; // Do NOT cache; no token.

					$this->cache_path = $this->url_to_cache_path($this->protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $this->user_token, $this->version_salt);
					$this->cache_file = QUICK_CACHE_DIR.'/'.$this->cache_path; // NOT considering a user cache; not yet.

					if(is_file($this->cache_file) && filemtime($this->cache_file) >= strtotime('-'.QUICK_CACHE_MAX_AGE))
						{
							list($headers, $cache) = explode('<!--headers-->', file_get_contents($this->cache_file), 2);

							$headers_list = headers_list(); // Headers already sent (or ready to be sent).
							foreach(unserialize($headers) as $_header) // Preserves original headers sent with this file.
								if(!in_array($_header, $headers_list) && stripos($_header, 'Last-Modified:') !== 0) header($_header);
							unset($_header); // Just a little housekeeping.

							if(QUICK_CACHE_DEBUGGING_ENABLE) // Debugging messages enabled; or no?
								{
									$total_time = number_format(microtime(TRUE) - $this->timer, 5, '.', '');
									$cache .= "\n".'<!-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ -->';
									$cache .= "\n".'<!-- '.htmlspecialchars(sprintf(__('Quick Cache fully functional :-) Cache file served for (%1$s; user token: %2$s) in %3$s seconds, on: %4$s.', $this->text_domain), $this->salt_location, $this->user_token, $total_time, date('M jS, Y @ g:i a T'))).' -->';
								}
							exit($cache); // Exit with cache contents.
						}
					else ob_start(array($this, 'output_buffer_callback_handler')); // Start output buffering.
				}

			public function output_buffer_callback_handler($buffer)
				{
					if(defined('DONOTCACHEPAGE')) return $buffer;
					if(isset($_SERVER['DONOTCACHEPAGE'])) return $buffer;

					if(isset($_GET['qcAC']) && !$_GET['qcAC']) return $buffer;
					if(defined('QUICK_CACHE_ALLOWED') && !QUICK_CACHE_ALLOWED) return $buffer;
					if(isset($_SERVER['QUICK_CACHE_ALLOWED']) && !$_SERVER['QUICK_CACHE_ALLOWED']) return $buffer;

					if(!QUICK_CACHE_WHEN_LOGGED_IN && $this->is_like_user_logged_in()) return $buffer; // Just to make sure.
					if(!QUICK_CACHE_WHEN_LOGGED_IN && function_exists('is_user_logged_in') && is_user_logged_in()) return $buffer;

					if(function_exists('zlib_get_coding_type') && zlib_get_coding_type() && (!($zlib_oc = ini_get('zlib.output_compression')) || !preg_match('/^(?:1|on|yes|true)$/i', $zlib_oc)))
						throw new \exception(__('Unable to cache already-compressed output. Please use `mod_deflate` w/ Apache; or use `zlib.output_compression` in your `php.ini` file. Quick Cache is NOT compatible with `ob_gzhandler()` and others like this.', $this->text_domain));

					$is_404 = (function_exists('is_404') && is_404());
					if($is_404 && !QUICK_CACHE_CACHE_404_REQUESTS)
						return $buffer; // Not caching 404 errors.

					if(function_exists('is_maintenance') && is_maintenance())
						return $buffer; // <http://wordpress.org/extend/plugins/maintenance-mode>

					if(function_exists('did_action') && did_action('wm_head'))
						return $buffer; // <http://wordpress.org/extend/plugins/wp-maintenance-mode>

					$buffer        = trim($buffer); // Trim buffer.
					$cache         = $buffer; // Initialize cache value.
					$buffer_length = strlen($buffer); // Call this ONE time here.
					$headers       = headers_list(); // Need these headers below.
					$content_type  = ''; // Initialize possible content type.

					if(!$buffer_length) return $buffer; // Don't cache an empty buffer.

					if(strpos($buffer, '<body id="error-page">') !== FALSE)
						return $buffer; // Don't cache WP errors.

					foreach($headers as $_header)
						{
							if(stripos($_header, 'Content-Type:') === 0)
								$content_type = $_header; // Last one.
							/*
							 * A Retry-After header indicates the site is temporarily unavailable.
							 *    i.e. That the site is down; or is in maintenance mode.
							 *    I've seen maintenance mode plugins for WP set this.
							 *
							 * A Status header indicates that a plugin may have set a particular HTTP status code.
							 *    While WP itself no longer sends this, it's good to check for plugins that do.
							 * ~ NOTE: We should request that the WP core function `status_header()` start sending this.
							 *
							 * An HTTP status code set by WP core function `status_header()`.
							 * ~ NOTE: at this time the `headers_list()` function does NOT include this header unfortunately.
							 *    Therefore, the routine below which looks for this header will NOT find it under any circumstance.
							 *    Tested and confirmed. See also: <http://www.php.net/manual/en/function.headers-list.php>
							 *    That said, I think we should leave it here in case a future version of PHP corrects this behavior.
							 */
							else if(preg_match('/^(?:Retry\-After\:\s+(?P<retry>.+)|Status\:\s+(?P<status>[0-9]+)|HTTP\/[0-9]+\.[0-9]+\s+(?P<http_status>[0-9]+))/i', $_header, $_m))
								if(!empty($_m['retry']) || (!empty($_m['status']) && $_m['status'][0] !== '2' && $_m['status'] !== '404')
								   || (!empty($_m['http_status']) && $_m['http_status'][0] !== '2' && $_m['http_status'] !== '404')
								) return $buffer; // Don't cache (anything that's NOT a 2xx or 404 status).
						}
					unset($_header); // Just a little houskeeping.

					if($content_type && !preg_match('/xhtml|html|xml|'.preg_quote(__NAMESPACE__, '/').'/i', $content_type))
						return $buffer; // Don't cache anything that is NOT XML/HTML code.

					# Caching occurs here; we're good-to-go now :-)
					/*
					 * @raamdev ... I reviewed this section again and optimized it a bit further.
					 *    I now avoid repeated calls to `is_dir() && is_writable()` when it's not necessary.
					 *    This should speed things up a bit further. What I had before was a mess. Sorry :-)
					 */
					if(!is_dir(QUICK_CACHE_DIR) && mkdir(QUICK_CACHE_DIR, 0775, TRUE) && !is_file(QUICK_CACHE_DIR.'/.htaccess'))
						file_put_contents(QUICK_CACHE_DIR.'/.htaccess', $this->htaccess_deny); // We know it's writable here.

					if(!is_dir($cache_file_dir = dirname($this->cache_file))) $cache_file_dir_writable = mkdir($cache_file_dir, 0775, TRUE);
					if(empty($cache_file_dir_writable) && !is_writable($cache_file_dir)) // Only check if it's writable, if we didn't just successfully create it.
						throw new \exception(sprintf(__('Cache directory not writable. Quick Cache needs this directory please: `%1$s`. Set permissions to `755` or higher; `777` might be needed in some cases.', $this->text_domain), $cache_file_dir));
					/*
					 * Is this a 404 and the 404 cache file already exists?
					 * Then lets symlink this 404 cache file to the existing cache file.
					 * and return the cache; with possible debug information also.
					 *
					 * @raamdev I bumped this up here so it's BEFORE possible HTML compression below.
					 *    i.e. There is no need to compress the HTML if this is a 404 error.
					 */
					if($is_404 && is_file($this->cache_file_404))
						{
							symlink($this->cache_file_404, $this->cache_file);
							return $cache; // Nothing more to do here.
						}
					$cache = $this->maybe_compress_html($cache); // Possible HTML compression.
					// @raamdev I suggest this remain a Pro feature; i.e. NOT integrated into the lite version.

					if(QUICK_CACHE_DEBUGGING_ENABLE) // Debugging messages enabled; or no?
						{
							$total_time = number_format(microtime(TRUE) - $this->timer, 5, '.', '');
							$cache .= "\n".'<!-- '.htmlspecialchars(sprintf(__('Quick Cache file built for (%1$s%2$s) in %3$s seconds, on: %4$s.', $this->text_domain),
							                                                $this->salt_location, (($this->user_token) ? '; '.sprintf(__('user token: %1$s', $this->text_domain), $this->user_token) : ''), $total_time, date('M jS, Y @ g:i a T'))).' -->';
							$cache .= "\n".'<!-- '.htmlspecialchars(sprintf(__('This Quick Cache file will auto-expire (and be rebuilt) on: %1$s (based on your configured expiration time).', $this->text_domain), date('M jS, Y @ g:i a T', strtotime('+'.QUICK_CACHE_MAX_AGE)))).' -->';
						}
					$cache_file_tmp = $this->cache_file.'.'.uniqid('', TRUE).'.tmp'; // Cache creation is atomic; e.g. tmp file w/ rename.

					/*
					 * This is NOT a 404, or it is 404 and the 404 cache file doesn't yet exist (so we need to create it).
					 *    @raamdev All of this looks awesome to me! Nice work :-) I like that you're creating and then symlinking here.
					 */
					if($is_404) // This is a 404; let's create 404 cache file and symlink to it.
						{ // @raamdev Removing the `is_file()` check here. We know already that `$this->cache_file_404` does not exist from the previous check above.
							if(file_put_contents($cache_file_tmp, serialize($headers).'<!--headers-->'.$cache) && rename($cache_file_tmp, $this->cache_file_404))
								{
									symlink($this->cache_file_404, $this->cache_file);
									return $cache; // Return the newly built cache; with possible debug information also.
								}
						} // NOT a 404; let's write a new cache file.
					else if(file_put_contents($cache_file_tmp, serialize($headers).'<!--headers-->'.$cache) && rename($cache_file_tmp, $this->cache_file))
						return $cache; // Return the newly built cache; with possible debug information also.

					@unlink($cache_file_tmp); // Clean this up (if it exists); and throw an exception with information for the site owner.
					throw new \exception(sprintf(__('Quick Cache: failed to write cache file for: `%1$s`; possible permissions issue (or race condition), please check your cache directory: `%2$s`.', $this->text_domain), $_SERVER['REQUEST_URI'], QUICK_CACHE_DIR));
				}

			public function maybe_compress_html($cache) // <https://github.com/WebSharks/HTML-Compressor>
				{
					if(!QUICK_CACHE_HTMLC_ENABLE || !class_exists('\\'.__NAMESPACE__.'\\plugin'))
						return $cache; // Nothing to do here.

					require_once dirname(plugin()->file).'/includes/html-compressor/stub.php';

					if(($host_dir_token = $this->host_dir_token(TRUE)) === '/')
						$host_dir_token = ''; // Not necessary.
					// Deals with multisite sub-directory installs.
					// e.g. `wp-content/htmlc/cache/public/www-example-com` (main site)
					// e.g. `wp-content/htmlc/cache/public/sub/www-example-com`

					$html_compressor_options = array(
						'benchmark'                      => QUICK_CACHE_DEBUGGING_ENABLE,
						'product_title'                  => __('Quick Cache HTML Compressor', $this->text_domain),

						'regex_css_exclusions'           => QUICK_CACHE_HTMLC_CSS_EXCLUSIONS, // Regex.
						'regex_js_exclusions'            => QUICK_CACHE_HTMLC_JS_EXCLUSIONS, // Regex.

						'cache_expiration_time'          => QUICK_CACHE_HTMLC_CACHE_EXPIRATION_TIME,
						'cache_dir_public'               => QUICK_CACHE_HTMLC_CACHE_DIR_PUBLIC.$host_dir_token,
						'cache_dir_url_public'           => site_url('/'.str_replace(ABSPATH, '', QUICK_CACHE_HTMLC_CACHE_DIR_PUBLIC.$host_dir_token)),
						'cache_dir_private'              => QUICK_CACHE_HTMLC_CACHE_DIR_PRIVATE,

						'compress_combine_head_body_css' => QUICK_CACHE_HTMLC_COMPRESS_COMBINE_HEAD_BODY_CSS,
						'compress_combine_head_js'       => QUICK_CACHE_HTMLC_COMPRESS_COMBINE_HEAD_JS,
						'compress_combine_footer_js'     => QUICK_CACHE_HTMLC_COMPRESS_COMBINE_FOOTER_JS,
						'compress_combine_remote_css_js' => QUICK_CACHE_HTMLC_COMPRESS_COMBINE_REMOTE_CSS_JS,
						'compress_inline_js_code'        => QUICK_CACHE_HTMLC_COMPRESS_INLINE_JS_CODE,
						'compress_css_code'              => QUICK_CACHE_HTMLC_COMPRESS_CSS_CODE,
						'compress_js_code'               => QUICK_CACHE_HTMLC_COMPRESS_JS_CODE,
						'compress_html_code'             => QUICK_CACHE_HTMLC_COMPRESS_HTML_CODE,
					);
					$html_compressor         = new \websharks\html_compressor\core($html_compressor_options);
					$compressed_cache        = $html_compressor->compress($cache);

					return $compressed_cache;
				}

			public function dir_regex_iteration($dir, $regex)
				{
					$dir_iterator      = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_SELF | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS);
					$iterator_iterator = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::CHILD_FIRST);
					$regex_iterator    = new \RegexIterator($iterator_iterator, $regex, \RegexIterator::MATCH, \RegexIterator::USE_KEY);

					return $regex_iterator;
				}

			/*
			 * See also: `quick-cache-pro.inc.php` duplicate.
			 * NOTE: the call to `is_ssl()` in this duplicate uses `$this->is_ssl()` because `is_ssl()`
			 *    may NOT be available in this routine; i.e. it's not been loaded up yet.
			 */
			const CACHE_PATH_NO_SCHEME = 1; // Exclude scheme.
			const CACHE_PATH_NO_HOST = 2; // Exclude host (i.e. domain name).
			const CACHE_PATH_NO_PATH = 4; // Exclude path (i.e. the request URI).
			const CACHE_PATH_NO_PATH_INDEX = 8; // Exclude path index (i.e. no default `index`).
			const CACHE_PATH_NO_QUV = 16; // Exclude query, user & version salt.
			const CACHE_PATH_NO_QUERY = 32; // Exclude query string.
			const CACHE_PATH_NO_USER = 64; // Exclude user token.
			const CACHE_PATH_NO_VSALT = 128; // Exclude version salt.
			const CACHE_PATH_NO_EXT = 256; // Exclude extension.

			public function url_to_cache_path($url, $with_user_token = '', $with_version_salt = '', $flags = 0)
				{
					$cache_path        = ''; // Initialize.
					$url               = trim((string)$url);
					$with_user_token   = trim((string)$with_user_token);
					$with_version_salt = trim((string)$with_version_salt);

					if($url && strpos($url, '://') === FALSE)
						$url = '//'.ltrim($url, '/');

					if(!$url || !($url = parse_url($url)))
						return ''; // Invalid URL.

					if(!($flags & $this::CACHE_PATH_NO_SCHEME))
						{
							if(!empty($url['scheme']))
								$cache_path .= $url['scheme'].'/';
							else $cache_path .= $this->is_ssl() ? 'https/' : 'http/';
						}
					if(!($flags & $this::CACHE_PATH_NO_HOST))
						{
							if(!empty($url['host']))
								$cache_path .= $url['host'].'/';
							else $cache_path .= $_SERVER['HTTP_HOST'].'/';
						}
					if(!($flags & $this::CACHE_PATH_NO_PATH))
						{
							if(!empty($url['path']) && strlen($url['path'] = trim($url['path'], '\\/'." \t\n\r\0\x0B")))
								$cache_path .= $url['path'].'/';
							else if(!($flags & $this::CACHE_PATH_NO_PATH_INDEX)) $cache_path .= 'index/';
						}
					$cache_path = str_replace('.', '-', $cache_path);

					if(!($flags & $this::CACHE_PATH_NO_QUV))
						{
							if(!($flags & $this::CACHE_PATH_NO_QUERY))
								if(isset($url['query']) && $url['query'] !== '')
									$cache_path = rtrim($cache_path, '/').'.q/'.md5($url['query']).'/';

							if(!($flags & $this::CACHE_PATH_NO_USER))
								if($with_user_token !== '') // Allow a `0` value if desirable.
									$cache_path = rtrim($cache_path, '/').'.u/'.str_replace(array('/', '\\'), '-', $with_user_token).'/';

							if(!($flags & $this::CACHE_PATH_NO_VSALT))
								if($with_version_salt !== '') // Allow a `0` value if desirable.
									$cache_path = rtrim($cache_path, '/').'.v/'.str_replace(array('/', '\\'), '-', $with_version_salt).'/';
						}
					$cache_path = trim(preg_replace('/\/+/', '/', $cache_path), '/');
					$cache_path = preg_replace('/[^a-z0-9\/.]/i', '-', $cache_path);

					if(!($flags & $this::CACHE_PATH_NO_EXT))
						$cache_path .= '.html';

					return $cache_path;
				}

			public function host_token($dashify = FALSE)
				{
					$host = strtolower($_SERVER['HTTP_HOST']);
					return ($dashify) ? trim(preg_replace('/[^a-z0-9\/]/i', '-', $host), '-') : $host;
				}

			public function host_dir_token($dashify = FALSE)
				{
					$host_dir_token = '/'; // Assume NOT multisite; or running it's own domain.

					if(is_multisite() && (!defined('SUBDOMAIN_INSTALL') || !SUBDOMAIN_INSTALL))
						{ // Multisite w/ sub-directories; need a valid sub-directory token.

							$base = '/'; // Initial default value.
							if(defined('PATH_CURRENT_SITE')) $base = PATH_CURRENT_SITE;
							else if(!empty($GLOBALS['base'])) $base = $GLOBALS['base'];

							$uri_minus_base = // Supports `/sub-dir/child-blog-sub-dir/` also.
								preg_replace('/^'.preg_quote($base, '/').'/', '', $_SERVER['REQUEST_URI']);

							list($host_dir_token) = explode('/', trim($uri_minus_base, '/'));
							$host_dir_token = (isset($host_dir_token[0])) ? '/'.$host_dir_token.'/' : '/';

							if($host_dir_token !== '/' // Perhaps NOT the main site?
							   && (!is_file(QUICK_CACHE_DIR.'/qc-blog-paths') // NOT a read/valid blog path?
							       || !in_array($host_dir_token, unserialize(file_get_contents(QUICK_CACHE_DIR.'/qc-blog-paths')), TRUE))
							) $host_dir_token = '/'; // Main site; e.g. this is NOT a real/valid child blog path.
						}
					return ($dashify) ? trim(preg_replace('/[^a-z0-9\/]/i', '-', $host_dir_token), '-') : $host_dir_token;
				}

			public function is_post_put_del_request()
				{
					static $is; // Cache.
					if(isset($is)) return $is;

					if(!empty($_SERVER['REQUEST_METHOD']))
						if(in_array(strtoupper($_SERVER['REQUEST_METHOD']), array('POST', 'PUT', 'DELETE'), TRUE))
							return ($is = TRUE);

					return ($is = FALSE);
				}

			public function is_get_request_w_query()
				{
					static $is; // Cache.
					if(isset($is)) return $is;

					if(!empty($_GET) || (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING'])))
						if(!(isset($_GET['qcABC']) && count($_GET) === 1)) // Ignore this special case.
							return ($is = TRUE);

					return ($is = FALSE);
				}

			public function is_like_user_logged_in()
				{
					static $is; // Cache.
					if(isset($is)) return $is;

					/* This checks for a PHP session; i.e. session_start() in PHP where you're dealing with a user session.
					 * WordPress itself does not use sessions, but some plugins/themes do. If you have a theme/plugin using
					 * sessions, and there is an active session open, we consider you logged in; and thus, no caching.
					 * SID is a PHP internal constant to identify a PHP session. It's the same regardless of the app. If PHP
					 * starts a session, SID is defined.
					 */
					if(defined('SID') && SID) return ($is = TRUE); // Session.

					$logged_in_cookies[] = 'comment_author_'; // Comment (and/or reply) authors.
					$logged_in_cookies[] = 'wp-postpass_'; // Password access to protected posts.

					$logged_in_cookies[] = (defined('AUTH_COOKIE')) ? AUTH_COOKIE : 'wordpress_';
					$logged_in_cookies[] = (defined('SECURE_AUTH_COOKIE')) ? SECURE_AUTH_COOKIE : 'wordpress_sec_';
					$logged_in_cookies[] = (defined('LOGGED_IN_COOKIE')) ? LOGGED_IN_COOKIE : 'wordpress_logged_in_';
					$logged_in_cookies   = '/^(?:'.implode('|', array_map(function ($logged_in_cookie)
							{
								return preg_quote($logged_in_cookie, '/'); // Escape.

							}, $logged_in_cookies)).')/';
					$test_cookie         = (defined('TEST_COOKIE')) ? TEST_COOKIE : 'wordpress_test_cookie';

					foreach($_COOKIE as $_key => $_value) if($_key !== $test_cookie)
						if(preg_match($logged_in_cookies, $_key) && $_value) return ($is = TRUE);
					unset($_key, $_value); // Housekeeping.

					return ($is = FALSE);
				}

			public function user_token()
				{
					static $token; // Cache.
					if(isset($token)) return $token;

					if(($user_id = (integer)wp_validate_auth_cookie('', 'logged_in')))
						return ($token = $user_id); // A real user in this case.

					else if(!empty($_COOKIE['comment_author_email_'.COOKIEHASH]) && is_string($_COOKIE['comment_author_email_'.COOKIEHASH]))
						return ($token = md5(strtolower(stripslashes($_COOKIE['comment_author_email_'.COOKIEHASH]))));

					else if(!empty($_COOKIE['wp-postpass_'.COOKIEHASH]) && is_string($_COOKIE['wp-postpass_'.COOKIEHASH]))
						return ($token = md5(stripslashes($_COOKIE['wp-postpass_'.COOKIEHASH])));

					return ($token = '');
				}

			public function is_localhost()
				{
					static $is; // Cache.
					if(isset($is)) return $is;

					if(defined('LOCALHOST') && LOCALHOST) return ($is = TRUE);

					if(!defined('LOCALHOST') && !empty($_SERVER['HTTP_HOST']))
						if(preg_match('/localhost|127\.0\.0\.1/i', $_SERVER['HTTP_HOST']))
							return ($is = TRUE);

					return ($is = FALSE);
				}

			public function is_auto_cache_engine()
				{
					static $is; // Cache.
					if(isset($is)) return $is;

					if(!empty($_SERVER['HTTP_USER_AGENT']))
						if(stripos($_SERVER['HTTP_USER_AGENT'], __NAMESPACE__) !== FALSE)
							return ($is = TRUE);

					return ($is = FALSE);
				}

			public function is_feed()
				{
					static $is; // Cache.
					if(isset($is)) return $is;

					if(preg_match('/\/feed(?:[\/?]|$)/', $_SERVER['REQUEST_URI']))
						return ($is = TRUE);

					if(isset($_REQUEST['feed']))
						return ($is = TRUE);

					return ($is = FALSE);
				}

			public function is_ssl()
				{
					static $is; // Cache.
					if(isset($is)) return $is;

					if(!empty($_SERVER['SERVER_PORT']))
						if($_SERVER['SERVER_PORT'] === '443')
							return ($is = TRUE);

					if(!empty($_SERVER['HTTPS']))
						if($_SERVER['HTTPS'] === '1' || strcasecmp($_SERVER['HTTPS'], 'on') === 0)
							return ($is = TRUE);

					if(!empty($_SERVER['HTTP_X_FORWARDED_PROTO']))
						if(strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0)
							return ($is = TRUE);

					return ($is = FALSE);
				}

			public function hook_id($function)
				{
					if(is_string($function))
						return $function;

					if(is_object($function)) // Closure.
						$function = array($function, '');
					else $function = (array)$function;

					if(is_object($function[0]))
						return spl_object_hash($function[0]).$function[1];

					else if(is_string($function[0]))
						return $function[0].'::'.$function[1];

					throw new \exception(__('Invalid hook.', $this->text_domain));
				}

			public function add_hook($hook, $function, $priority = 10, $accepted_args = 1)
				{
					$this->hooks[$hook][$priority][$this->hook_id($function)]
						= array('function' => $function, 'accepted_args' => (integer)$accepted_args);
					return TRUE; // Always returns true.
				}

			public function add_action() // Simple `add_hook()` alias.
				{
					return call_user_func_array(array($this, 'add_hook'), func_get_args());
				}

			public function add_filter() // Simple `add_hook()` alias.
				{
					return call_user_func_array(array($this, 'add_hook'), func_get_args());
				}

			public function remove_hook($hook, $function, $priority = 10)
				{
					if(!isset($this->hooks[$hook][$priority][$this->hook_id($function)]))
						return FALSE; // Nothing to remove in this case.

					unset($this->hooks[$hook][$priority][$this->hook_id($function)]);
					if(!$this->hooks[$hook][$priority]) unset($this->hooks[$hook][$priority]);
					return TRUE; // Existed before it was removed in this case.
				}

			public function remove_action() // Simple `remove_hook()` alias.
				{
					return call_user_func_array(array($this, 'remove_hook'), func_get_args());
				}

			public function remove_filter() // Simple `remove_hook()` alias.
				{
					return call_user_func_array(array($this, 'remove_hook'), func_get_args());
				}

			public function do_action($hook)
				{
					if(empty($this->hooks[$hook]))
						return; // No hooks.

					$hook_actions = $this->hooks[$hook];
					ksort($hook_actions); // Sort by priority.

					$args = func_get_args(); // We'll need these below.
					foreach($hook_actions as $_hook_action) foreach($_hook_action as $_action)
						{
							if(!isset($_action['function'], $_action['accepted_args']))
								continue; // Not a valid filter in this case.

							call_user_func_array($_action['function'], array_slice($args, 1, $_action['accepted_args']));
						}
					unset($_hook_action, $_action); // Housekeeping.
				}

			public function apply_filters($hook, $value)
				{
					if(empty($this->hooks[$hook]))
						return $value; // No hooks.

					$hook_filters = $this->hooks[$hook];
					ksort($hook_filters); // Sort by priority.

					$args = func_get_args(); // We'll need these below.
					foreach($hook_filters as $_hook_filter) foreach($_hook_filter as $_filter)
						{
							if(!isset($_filter['function'], $_filter['accepted_args']))
								continue; // Not a valid filter in this case.

							$args[1] = $value; // Continously update the argument `$value`.
							$value   = call_user_func_array($_filter['function'], array_slice($args, 1, $_filter['accepted_args']));
						}
					unset($_hook_filter, $_filter); // Housekeeping.

					return $value; // With applied filters.
				}

			public $htaccess_deny = "<IfModule authz_core_module>\n\tRequire all denied\n</IfModule>\n<IfModule !authz_core_module>\n\tdeny from all\n</IfModule>";
		}

		function __($string, $text_domain) // Polyfill `\__()`.
			{
				static $__exists; // Static cache.

				if(($__exists || function_exists('__')) && ($__exists = TRUE))
					return \__($string, $text_domain);

				return $string; // Not possible (yet).
			}

		$GLOBALS[__NAMESPACE__.'__advanced_cache'] = new advanced_cache();
	}
namespace // Global namespace.
	{
		function wp_cache_postload() // See: `wp-settings.php`.
			{
				$advanced_cache = $GLOBALS['quick_cache__advanced_cache'];
				/** * @var $advanced_cache \quick_cache\advanced_cache */

				if(!empty($advanced_cache->postload['invalidate_when_logged_in']))
					$advanced_cache->maybe_invalidate_when_logged_in_postload();

				if(!empty($advanced_cache->postload['when_logged_in']))
					$advanced_cache->maybe_start_ob_when_logged_in_postload();
			}
	}