<?php
/**
 * @Package: Comet Cache WP CLI integration
 * @Author: Arunas Liuiza <aliuiza@kayak.com>
 * @Description: Exposes Comet Cache via WP CLI
 * @Version: 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	// disable direct access to php file.
	exit;
}

add_action( 'plugins_loaded', 'comet_cache_cli', 100 );

function comet_cache_cli() {
	if ( class_exists( 'WP_CLI' ) && class_exists( 'comet_cache' ) ) {
		WP_CLI::add_command( 'comet-cache', 'Comet_Cache_CLI_Class' );
	}
}


/**
 * Execute Comet Cache actions via WP-CLI.
 *
 * ## EXAMPLES
 *
 *     # Clear all cache files (on one site in Multisite)
 *     $ wp comet-cache clear
 *     Success: Cleared cache files: 0.
 *
 *     # Clear files for single post
 *     $ wp comet-cache clear post 15
 *     Success: Cleared cache for post ID '8596', removes files: 1.
 *
 *     # Clear files for user with email user@example.com
 *     $ wp comet-cache clear user user@example.com
 *     Success: Cleared cache for user ID '494', removes files: 21.
 *
 *     # Clear files for all category archives
 *     $ wp comet-cache clear url https://example.com/category/*
 *     Success: Cleared cache for url 'https://example.com/category/*', removes files: 73.
 *
 *     # Wipe all cache files (for all sites in Multisite)
 *     $ wp comet-cache wipe
 *     Success: Cleared cache files: 0 (on all sites in Multisite).
 *
 *     # Purge old cache files
 *     $ wp comet-cache purge
 *     Purged old cache files. Removed files: 0.
 *
 *     # Check Comet Cache version.
 *     $ wp comet-cache version
 *     Success: Comet Cache Pro v170220.
 */
class Comet_Cache_CLI_Class {

	/**
	 * Clears cache files. On Multisite installs - clears files for one site only. Use `wp comet-cache wipe` to clear cache in all sites at once.
	 *
	 * ## OPTIONS
	 *
	 * <context>
	 * : Which part to clear?
	 * ---
	 * default: all
	 * options:
	 *   - all
	 *   - post
	 *   - user
	 *   - url
	 * ---
	 *
	 * [<item>]
	 * : Which item to clear - not needed if `context` is `all`. Otherwise you can pass:
	 *   - Post ID for `wp comet-cache clear post`;
	 *   - User ID, login or email for `wp comet-cache clear user`;
	 *   - URL for `wp comet-cache clear url` (supports Waterd-Down Regex Syntax - https://cometcache.com/kb-article/watered-down-regex-syntax/).
	 *
	 * ## EXAMPLES
	 *
	 *     # Clear all cache files (on one site in Multisite)
	 *     $ wp comet-cache clear
	 *     Success: Cleared cache files: 0.
	 *
	 *     # Clear files for single post
	 *     $ wp comet-cache clear post 15
	 *     Success: Cleared cache for post ID '8596', removes files: 1.
	 *
	 *     # Clear files for user with email user@example.com
	 *     $ wp comet-cache clear user user@example.com
	 *     Success: Cleared cache for user ID '494', removes files: 21.
	 *
	 *     # Clear files for all category archives
	 *     $ wp comet-cache clear url https://example.com/category/*
	 *     Success: Cleared cache for url 'https://example.com/category/*', removes files: 73.
	 */
	public function clear( $args, $options ) {
		$sub_action = isset( $args[1] ) ? $args[1] : 'all';
		$sub_action = in_array( $sub_action, array( 'all', 'post', 'user', 'url' ), true ) ? $sub_action : 'all';
		if ( 'all' === $sub_action ) {
			$v = comet_cache::clear();
			// translators: %s - number of cleared files.
			return WP_CLI::success( sprintf( __( 'Cleared cache files: %s.', 'comet-cache' ), $v ) );
		}
		if ( ! method_exists( 'comet_cache', 'clearPost' ) ) {
			return WP_CLI::error( __( 'You need to upgrade to Comet Cache Pro to use this feature.', 'comet-cache' ) );
		}
		if ( ! isset( $args[2] ) ) {
			return WP_CLI::error( __( 'No item to clear provided.', 'comet-cache' ) );
		}
		$item = $args[2];
		switch ( $sub_action ) {
			case 'post':
				$this->_cli_clear_post( $item, $args, $options );
				break;
			case 'user':
				$this->_cli_clear_user( $item, $args, $options );
				break;
			case 'url':
				$this->_cli_clear_url( $item, $args, $options );
				break;
		}
	}
	protected function _cli_clear_post( $item, $args, $options ) {
		if ( null === get_post( $item ) ) {
			// translators: %s - post ID.
			WP_CLI::error( sprintf( __( 'Post ID \'%s\' not found.', 'comet-cache' ), $item ) );
		}
		$response = comet_cache::clearPost( $item );
		// translators: %s - Post ID;
		$item = sprintf( __( 'post ID \'%s\'', 'comet-cache' ), $item );
		// translators: %1$s - item, %2$s - number of cleared files.
		WP_CLI::success( sprintf( __( 'Cleared cache for %1$s, removes files: %2$s.', 'comet-cache' ), $item, $response ) );
	}
	protected function _cli_clear_user( $item, $args, $options ) {
		$user   = false;
		$fields = array( 'ID', 'login', 'email' );
		foreach ( $fields as $field ) {
			$user = get_user_by( $field, $item );
			if ( false !== $user ) {
				break;
			}
		}
		if ( false === $user ) {
			// translators: %s - user ID, login or email.
			WP_CLI::error( sprintf( __( 'User \'%s\' not found.', 'comet-cache' ), $item ) );
		}
		$item     = $user->ID;
		$response = comet_cache::clearUser( $item );
		// translators: %s - User ID;
		$item = sprintf( __( 'user ID \'%s\'', 'comet-cache' ), $item );
		// translators: %1$s - item, %2$s - number of cleared files.
		WP_CLI::success( sprintf( __( 'Cleared cache for %1$s, removes files: %2$s.', 'comet-cache' ), $item, $response ) );
	}
	protected function _cli_clear_url( $item, $args, $options ) {
		$response = comet_cache::clearUrl( $item );
		// translators: %s - URL;
		$item = sprintf( __( 'url \'%s\'', 'comet-cache' ), $item );
		// translators: %1$s - item, %2$s - number of cleared files.
		WP_CLI::success( sprintf( __( 'Cleared cache for %1$s, removes files: %2$s.', 'comet-cache' ), $item, $response ) );
	}

	/**
	 * Wipes all cache files. For single WordPress installations works the same way as `wp comet-cache clear`, for Multisite installations, clear caches on all sites at once.
	 *
	 * ## EXAMPLES
	 *
	 *     # Wipe all cache files (for all sites in Multisite)
	 *     $ wp comet-cache wipe
	 *     Success: Cleared cache files: 0 (on all sites in Multisite).
	 */
	public function wipe( $args, $options ) {
		$v = comet_cache::wipe();
		// translators: %s - number of cleared files.
		WP_CLI::success( sprintf( __( 'Cleared cache files: %s (on all sites in Multisite).', 'comet-cache' ), $v ) );
	}

	/**
	 * Purges old cache files.
	 *
	 * ## EXAMPLES
	 *
	 *     # Purge old cache files
	 *     $ wp comet-cache purge
	 *     Purged old cache files. Removed files: 0.
	 */
	public function purge( $args, $options ) {
		$v = comet_cache::purge();
		// translators: %s - number of cleared files.
		WP_CLI::success( sprintf( __( 'Purged old cache files. Removed files: %s.', 'comet-cache' ), $v ) );
	}

	/**
	 * Gets current version and type (Lite/Pro) of Comet Cache plugin.
	 *
	 * ## EXAMPLES
	 *
	 *     # Check Comet Cache version.
	 *     $ wp comet-cache version
	 *     Success: Comet Cache Pro v170220.
	 */
	public function version( $args, $options ) {
		$plugin  = method_exists( 'comet_cache', 'clearPost' ) ? 'Comet Cache Pro' : 'Comet Cache';
		$version = comet_cache::version();
		// translators: %s - plugin version.
		WP_CLI::success( sprintf( __( '%1$s v%2$s.', 'comet-cache' ), $plugin, $version ) );
	}
}
