<?php

if ( ! class_exists( 'MUCD_Functions' ) ) {

	/**
	 * Shared helper functions used throughout the plugin.
	 */
	class MUCD_Functions {

		/**
		 * Check if a path is valid MS-windows path
		 * @since 0.2.0
		 * @param  string $path the path
		 * @return boolean true | false
		 */
		public static function valid_windows_dir_path( $path ) {
			if ( 1 === strpos( $path, ':' ) && preg_match( '/[a-zA-Z]/', $path[0] ) ) {
						$tmp  = substr( $path, 2 );
						$bool = preg_match( '/^[^*?"<>|:]*$/', $tmp );
						return ( 1 === $bool ); // so that it will return only true and false
			}
					return false;
		}

		/**
		 * Check if a path is valid UNIX path
		 * @since 0.2.0
		 * @param  string $path the path
		 * @return boolean true | false
		 */
		public static function valid_unix_dir_path( $path ) {
			$reg  = '/^(\/([a-zA-Z0-9+$_.-])+)*\/?$/';
			$bool = preg_match( $reg, $path );
			return ( 1 === $bool );
		}

		/**
		 * Check if a path is valid MS-windows or UNIX path
		 * @since 0.2.0
		 * @param  string $path the path
		 * @return boolean true | false
		 */
		public static function valid_path( $path ) {
			// Reject path traversal: the dir-path regexes treat '.' as a valid segment char,
			// so a '..' segment would otherwise pass and escape the intended directory.
			if ( in_array( '..', preg_split( '#[\\\\/]#', $path ), true ) ) {
				return false;
			}
			return ( self::valid_unix_dir_path( $path ) || self::valid_windows_dir_path( $path ) );
		}

		/**
		 * Removes completely a blog from the network
		 * @since 0.2.0
		 * @param  int $blog_id the blog id
		 */
		public static function remove_blog( $blog_id ) {
			switch_to_blog( $blog_id );
			$wp_upload_info = wp_upload_dir();
			$dir            = str_replace( ' ', '\\ ', trailingslashit( $wp_upload_info['basedir'] ) );
			restore_current_blog();

			wpmu_delete_blog( $blog_id, true );

			// wpmu_delete_blog leaves an empty site upload directory, that we want to remove :
			MUCD_Files::rrmdir( $dir );
		}

		/**
		 * Check if site is duplicable
		 * @since 0.2.0
		 * @param  int $blog_id the blog id
		 * @return boolean true | false
		 */
		public static function is_duplicable( $blog_id ) {
			if ( 'all' === get_site_option( 'mucd_duplicables', 'all' ) ) {
				return true;
			}

			if ( 'yes' === get_blog_option( $blog_id, 'mucd_duplicable', 'no' ) ) {
				return true;
			}
			return false;
		}

		/**
		 * Get all duplicable sites
		 * @since 0.2.0
		 * @return array of blog data
		 */
		public static function get_site_list() {
			$site_list     = array();
			$network_blogs = self::get_sites( apply_filters( 'mucd_get_site_list_args', array() ) );
			foreach ( $network_blogs as $blog ) {
				if ( self::is_duplicable( $blog['blog_id'] ) && (string) MUCD_SITE_DUPLICATION_EXCLUDE !== (string) $blog['blog_id'] ) {
					$site_list[] = $blog;
				}
			}

			return $site_list;
		}

		/**
		 * Check if a value is in an array for a specific key
		 * @since 0.2.0
		 * @param  mixte  $value the value
		 * @param  array  $rows  the array of rows to search
		 * @param  string $key   the key
		 * @return boolean true | false
		 */
		public static function value_in_array( $value, $rows, $key ) {
			foreach ( $rows as $row ) {
				// Loose comparison is intentional: $key is caller-defined and not guaranteed to hold
				// values of a consistent type (e.g. int vs numeric string blog IDs).
				// phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
				if ( isset( $row[ $key ] ) && $value == $row[ $key ] ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Get upload directory of the entire network
		 * @since 0.2.0
		 * @return string path of the upload directory
		 */
		public static function get_primary_upload_dir() {
			$current_blog = get_current_blog_id();
			switch_to_blog( MUCD_PRIMARY_SITE_ID );
			$wp_upload_info = wp_upload_dir();
			switch_to_blog( $current_blog );

			return $wp_upload_info['basedir'];
		}

		/**
		 * Check if site exists
		 * @since 1.3.0
		 * @param  int $blog_id the blog id
		 * @return boolean true | false
		 */
		public static function site_exists( $blog_id ) {
			return ( get_blog_details( $blog_id ) !== false );
		}

		/**
		 * Set locale to en_US
		 * @since 1.3.1
		 */
		public static function set_locale_to_en_US() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- en_US is a locale code, not a word to split.
			add_filter(
				'locale',
				function () {
					return 'en_US';
				}
			);
		}

		/**
		 * Get network data for a given id.
		 *
		 * @author wp-cli
		 * @see https://github.com/wp-cli/wp-cli/blob/master/php/commands/site.php
		 *
		 * @param int     $network_id
		 * @return bool|array False if no network found with given id, array otherwise
		 */
		public static function get_network( $network_id ) {
			global $wpdb;

			// Load network data. No caching: a one-off lookup from the wp-cli command, not a hot path.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$networks = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM $wpdb->site WHERE id = %d",
					$network_id
				)
			);

			if ( ! empty( $networks ) ) {
				// Only care about domain and path which are set here
				return $networks[0];
			}

			return false;
		}

		/**
		 * Get network sites, using the modern get_sites() API on WP 4.6+ and falling back to the
		 * legacy wp_get_sites() on older installs (the old API has no replacement before 4.6).
		 *
		 * @since 0.2.0
		 * @param array $args query args, see WP_Site_Query / wp_get_sites()
		 * @return array of site data
		 */
		public static function get_sites( $args = array() ) {
			if ( version_compare( get_bloginfo( 'version' ), '4.6', '>=' ) ) {
				$defaults = array( 'number' => MUCD_MAX_NUMBER_OF_SITE );
				$args     = wp_parse_args( $args, $defaults );
				$args     = apply_filters( 'mucd_get_sites_args', $args );
				$sites    = get_sites( $args );
				foreach ( $sites as $key => $site ) {
					$sites[ $key ] = (array) $site;
				}
				return $sites;
			} else {
				$defaults = array( 'limit' => MUCD_MAX_NUMBER_OF_SITE );
				$args     = apply_filters( 'mucd_get_sites_args', $args );
				$args     = wp_parse_args( $args, $defaults );
				// wp_get_sites() has no replacement before WP 4.6, the version gated above.
				// phpcs:ignore WordPress.WP.DeprecatedFunctions.wp_get_sitesFound
				return wp_get_sites( $args );
			}
		}

		/**
		 * Deactivate the plugin if we are not on a multisite installation
		 * @since 0.2.0
		 */
		public static function check_if_multisite() {
			if ( ! function_exists( 'is_multisite' ) || ! is_multisite() ) {
				deactivate_plugins( MUCD_PATH . '/multisite-clone-duplicator.php' );
				wp_die( 'multisite-clone-duplicator works only for multisite installation' );
			}
		}

		/**
		 * Deactivate the plugin if we are not on the network admin
		 * @since 1.4.0
		 */
		public static function check_if_network_admin() {
			if ( ! is_network_admin() ) {
				deactivate_plugins( MUCD_PATH . '/multisite-clone-duplicator.php' );
				wp_die( 'multisite-clone-duplicator works only as multisite network-wide plugin' );
			}
		}
	}
}
