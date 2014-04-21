<?php
namespace quick_cache // Root namespace.
	{
		if(!defined('WPINC')) // MUST have WordPress.
			exit('Do NOT access this file directly: '.basename(__FILE__));

		class auto_cache // Auto-cache engine.
		{
			/**
			 * @var plugin Quick Cache instance.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * Class constructor.
			 */
			public function __construct()
				{
					$this->plugin = plugin();
				}

			/**
			 * Public runner; attach to WP-Cron.
			 */
			public function run()
				{
					if(!$this->plugin->options['enable'])
						return; // Nothing to do.

					if(!$this->plugin->options['auto_cache_enable'])
						return; // Nothing to do.

					if(!$this->plugin->options['auto_cache_sitemap_url'])
						if(!$this->plugin->options['auto_cache_other_urls'])
							return; // Nothing to do.

					if(!is_dir(ABSPATH.$this->plugin->options['cache_dir']))
						return; // Not possible; cache directory missing.

					if(!is_writable(ABSPATH.$this->plugin->options['cache_dir']))
						return; // Not possible; cache directory not writable.

					@set_time_limit(900); // 15 minutes maximum.
					ignore_user_abort(TRUE); // Keep running.

					$micro_start_time = microtime(TRUE);
					$start_time       = time(); // Initialize.
					$total_urls       = $total_time = 0; // Initialize.
					$sitemap_urls     = $other_urls = $all_urls = array();

					if($this->plugin->options['auto_cache_sitemap_url']) // Memory-optimized routine.
						$sitemap_urls = $this->get_sitemap_urls_deep(site_url('/'.$this->plugin->options['auto_cache_sitemap_url']));

					if($this->plugin->options['auto_cache_other_urls'])
						$other_urls = preg_split('/\s+/', $this->plugin->options['auto_cache_other_urls'], NULL, PREG_SPLIT_NO_EMPTY);

					$all_urls = array_unique(array_merge($sitemap_urls, $other_urls));
					shuffle($all_urls); // Randomize the order; i.e. don't always start from the top.

					foreach($all_urls as $_url)
						{
							$total_urls++;
							$this->auto_cache_url($_url);
							// Stop before execution timeout occurs.
							if((time() - $start_time) > 870) break;
						}
					unset($_url); // A little housekeeping.

					$total_time = number_format(microtime(TRUE) - $micro_start_time, 5, '.', '').' seconds';

					$this->log_auto_cache_run($total_urls, $total_time);
				}

			/**
			 * Auto-cache a specific URL.
			 *
			 * @param string $url The URL to auto-cache.
			 */
			protected function auto_cache_url($url)
				{
					if(!($url = trim((string)$url)))
						return; // Nothing to do.

					if(!$this->plugin->options['get_requests'] && strpos($url, '?') !== FALSE)
						return; // We're NOT caching URLs with a query string.

					$cache_path      = $this->plugin->url_to_cache_path($url);
					$cache_file_path = ABSPATH.$this->plugin->options['cache_dir'].'/'.$cache_path;

					if(is_file($cache_file_path)) // If it's already cached (and still fresh); just bypass silently.
						if(filemtime($cache_file_path) >= strtotime('-'.$this->plugin->options['cache_max_age']))
							return; // Cached already.

					$this->log_auto_cache_url($url, wp_remote_get($url, array('blocking'   => FALSE, // Non-blocking for speedy auto-caching.
					                                                          'user-agent' => $this->plugin->options['auto_cache_user_agent'].'; '.__NAMESPACE__.' '.$this->plugin->version)));
				}

			/**
			 * Logs an attempt to auto-cache a specific URL.
			 *
			 * @param string    $url The URL we attempted to auto-cache.
			 * @param \WP_Error $wp_remote_get_response For IDEs.
			 *
			 * @throws \exception If log file exists already; but is NOT writable.
			 */
			protected function log_auto_cache_url($url, $wp_remote_get_response)
				{
					$cache_dir           = ABSPATH.$this->plugin->options['cache_dir'];
					$auto_cache_log_file = $cache_dir.'/auto-cache.log';

					if(is_file($auto_cache_log_file) && !is_writable($auto_cache_log_file))
						throw new \exception(sprintf(__('Auto-cache log file is NOT writable: `%1$s`. Please set permissions to `644` (or higher). `666` might be needed in some cases.', $this->plugin->text_domain), $auto_cache_log_file));

					if(is_wp_error($wp_remote_get_response)) // Log HTTP communication errors.
						$log_entry = 'Time: '.date(DATE_RFC822)."\n".'URL: '.$url."\n".'Error: '.$wp_remote_get_response->get_error_message()."\n\n";
					else $log_entry = 'Time: '.date(DATE_RFC822)."\n".'URL: '.$url."\n\n";

					file_put_contents($auto_cache_log_file, $log_entry, FILE_APPEND);
					if(filesize($auto_cache_log_file) > 2097152) // 2MB is the maximum log file size.
						rename($auto_cache_log_file, substr($auto_cache_log_file, 0, -4).'-archived-'.time().'.log');
				}

			/**
			 * Logs auto-cache run totals.
			 *
			 * @param integer $total_urls Total URLs processed by the run.
			 * @param string  $total_time Total time it took to complete processing.
			 *
			 * @throws \exception If log file exists already; but is NOT writable.
			 */
			protected function log_auto_cache_run($total_urls, $total_time)
				{
					$cache_dir           = ABSPATH.$this->plugin->options['cache_dir'];
					$auto_cache_log_file = $cache_dir.'/auto-cache.log';

					if(is_file($auto_cache_log_file) && !is_writable($auto_cache_log_file))
						throw new \exception(sprintf(__('Auto-cache log file is NOT writable: `%1$s`. Please set permissions to `644` (or higher). `666` might be needed in some cases.', $this->plugin->text_domain), $auto_cache_log_file));

					$log_entry = 'Run Completed: '.date(DATE_RFC822)."\n".'Total URLs: '.$total_urls."\n".'Total Time: '.$total_time."\n\n";

					file_put_contents($auto_cache_log_file, $log_entry, FILE_APPEND);
					if(filesize($auto_cache_log_file) > 2097152) // 2MB is the maximum log file size.
						rename($auto_cache_log_file, substr($auto_cache_log_file, 0, -4).'-archived-'.time().'.log');
				}

			/**
			 * Collects all URLs from an XML sitemap deeply.
			 *
			 * @param string $sitemap A URL to an XML sitemap file.
			 *    This supports nested XML sitemap index files too; i.e. `<sitemapindex>`.
			 *    Note that GZIP files are NOT supported at this time.
			 *
			 * @return array URLs from an XML sitemap deeply.
			 */
			protected function get_sitemap_urls_deep($sitemap)
				{
					$urls       = array();
					$sitemap    = (string)$sitemap;
					$xml_reader = new \XMLReader();

					if($sitemap && @$xml_reader->open($sitemap))
						while($xml_reader->read()) if($xml_reader->nodeType === $xml_reader::ELEMENT)
							{
								switch($xml_reader->name)
								{
									case 'sitemapindex': // e.g. <http://www.smashingmagazine.com/sitemap_index.xml>
											if(($_sitemapindex_urls = $this->_xml_get_sitemapindex_urls_deep($xml_reader)))
												$urls = array_merge($urls, $_sitemapindex_urls);
											break; // Break switch handler.

									case 'urlset': // e.g. <http://www.smashingmagazine.com/category-sitemap.xml>
											if(($_urlset_urls = $this->_xml_get_urlset_urls($xml_reader)))
												$urls = array_merge($urls, $_urlset_urls);
											break; // Break switch handler.
								}
							}
					unset($_sitemapindex_urls, $_urlset_urls); // A little housekeeping.

					return $urls; // A full set of all sitemap URLs; i.e. `<loc>` tags.
				}

			/**
			 * For internal use only.
			 *
			 * @param \XMLReader $xml_reader
			 *
			 * @return array All sitemap URLs from this `<sitemapindex>` node; deeply.
			 */
			protected function _xml_get_sitemapindex_urls_deep(\XMLReader $xml_reader)
				{
					$urls = array(); // Initialize.

					if($xml_reader->name === 'sitemapindex') // Sanity check.
						while($xml_reader->read()) if($xml_reader->nodeType === $xml_reader::ELEMENT)
							{
								switch($xml_reader->name)
								{
									case 'sitemap':
											$is_sitemap_node = TRUE;
											break; // Break switch handler.

									case 'loc': // A URL location.
											if(!empty($is_sitemap_node) && $xml_reader->read() && ($_loc = trim($xml_reader->value)))
												$urls = array_merge($urls, $this->get_sitemap_urls_deep($_loc));
											break; // Break switch handler.

									default: // Anything else.
										$is_sitemap_node = FALSE;
										break; // Break switch handler.
								}
							}
					return $urls; // All sitemap URLs from this `<sitemapindex>` node; deeply.
				}

			/**
			 * For internal use only.
			 *
			 * @param \XMLReader $xml_reader
			 *
			 * @return array All sitemap URLs from this `<urlset>` node.
			 */
			protected function _xml_get_urlset_urls(\XMLReader $xml_reader)
				{
					$urls = array(); // Initialize.

					if($xml_reader->name === 'urlset') // Sanity check.
						while($xml_reader->read()) if($xml_reader->nodeType === $xml_reader::ELEMENT)
							{
								switch($xml_reader->name)
								{
									case 'url':
											$is_url_node = TRUE;
											break; // Break switch handler.

									case 'loc': // A URL location.
											if(!empty($is_url_node) && $xml_reader->read() && ($_loc = trim($xml_reader->value)))
												$urls[] = $_loc; // Add this URL to the list :-)
											break; // Break switch handler.

									default: // Anything else.
										$is_url_node = FALSE;
										break; // Break switch handler.
								}
							}
					return $urls; // All sitemap URLs from this `<urlset>` node.
				}
		}
	}