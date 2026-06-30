<?php

if ( ! class_exists( 'MUCD_Admin' ) ) {

	require_once MUCD_COMPLETE_PATH . '/lib/duplicate.php';

	/**
	 * Admin-side hooks, pages and form handling for the plugin.
	 */
	class MUCD_Admin {

		/**
		 * Register hooks used on admin side by the plugin
		 */
		public static function hooks() {
			// Network admin case
			if ( is_network_admin() ) {
				add_action( 'network_admin_menu', array( __CLASS__, 'network_menu_add_duplicate' ) );
			}
			add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );

			// ajax
			add_action( 'wp_ajax_mucd_fetch_sites', array( __CLASS__, 'mucd_fetch_sites' ) );
		}

		/**
		 * Do some actions at the beginning of an admin script
		 */
		public static function admin_init() {
			// Hook to rows on network sites listing
			add_filter( 'manage_sites_action_links', array( __CLASS__, 'add_site_row_action' ), 10, 2 );
			// Network admin bar
			add_action( 'admin_bar_menu', array( __CLASS__, 'admin_network_menu_bar' ), 300 );
			// Network setting page
			add_action( 'wpmu_options', array( __CLASS__, 'admin_network_option_page' ) );
			// Save Network setting page
			add_action( 'wpmuadminedit', array( __CLASS__, 'save_admin_network_option_page' ) );
		}

		/**
		 * Adds 'Duplicate' entry to network admin-bar
		 * @since 0.2.0
		 * @param  WP_Admin_Bar $wp_admin_bar
		 */
		public static function admin_network_menu_bar( $wp_admin_bar ) {

			if ( current_user_can( 'manage_sites' ) ) {

				$wp_admin_bar->add_menu(
					array(
						'parent' => 'network-admin',
						'id'     => 'network-admin-duplicate',
						'title'  => MUCD_NETWORK_MENU_DUPLICATION,
						'href'   => network_admin_url( 'sites.php?page=' . MUCD_SLUG_NETWORK_ACTION ),
					)
				);

				foreach ( (array) $wp_admin_bar->user->blogs as $blog ) {

					if ( MUCD_Functions::is_duplicable( $blog->userblog_id ) ) {
							$menu_id = 'blog-' . $blog->userblog_id;
							$wp_admin_bar->add_menu(
								array(
									'parent' => $menu_id,
									'id'     => $menu_id . '-duplicate',
									'title'  => MUCD_NETWORK_MENU_DUPLICATE,
									'href'   => network_admin_url( 'sites.php?page=' . MUCD_SLUG_NETWORK_ACTION . '&amp;id=' . $blog->userblog_id ),
								)
							);
					}
				}
			}
		}

		/**
		 * Adds row action 'Duplicate' on site list
		 * @since 0.2.0
		 * @param array $actions
		 * @param int $blog_id
		 */
		public static function add_site_row_action( $actions, $blog_id ) {
			if ( MUCD_Functions::is_duplicable( $blog_id ) ) {
				$actions = array_merge(
					$actions,
					array(
						'duplicate_link' => '<a href="' . network_admin_url( 'sites.php?page=' . MUCD_SLUG_NETWORK_ACTION . '&amp;id=' . $blog_id ) . '">' . MUCD_NETWORK_MENU_DUPLICATE . '</a>',
					)
				);
			}

			return $actions;
		}

		/**
		 * Adds 'Duplication' entry in sites menu
		 * @since 0.2.0
		 * @return [type] [description]
		 */
		public static function network_menu_add_duplicate() {
			add_submenu_page( 'sites.php', MUCD_NETWORK_PAGE_DUPLICATE_TITLE, MUCD_NETWORK_MENU_DUPLICATE, 'manage_sites', MUCD_SLUG_NETWORK_ACTION, array( __CLASS__, 'network_page_admin_duplicate_site' ) );
		}

		/**
		 * Check result from Duplication page / print the page
		 * @since 0.2.0
		 */
		public static function network_page_admin_duplicate_site() {
			global $current_site;

			// Capabilities test
			if ( ! current_user_can( 'manage_sites' ) ) {
				wp_die( esc_html( MUCD_GAL_ERROR_CAPABILITIES ) );
			}

			// Form Data. The 'id' GET param only preselects a dropdown value, it does not mutate state.
			$data = array(
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only, used to preselect a value.
				'source'     => ( isset( $_GET['id'] ) ) ? intval( $_GET['id'] ) : 0,
				'domain'     => '',
				'title'      => '',
				'email'      => '',
				'copy_files' => 'yes',
				'keep_users' => 'no',
				'log'        => 'no',
				'log-path'   => '',
				'advanced'   => 'hide-advanced-options',
			);

			// Manage Form Post. The actual nonce check happens in check_form() via check_admin_referer().
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce verified in check_form().
			$requested_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing -- nonce verified in check_form().
			if ( MUCD_SLUG_ACTION_DUPLICATE === $requested_action && ! empty( $_POST ) ) {

				$data = self::check_form( $data );

				if ( isset( $data['error'] ) ) {
					$form_message['error'] = $data['error']->get_error_message();
				} else {
					$form_message = MUCD_Duplicate::duplicate_site( $data );
				}
			}

			$use_select2 = ( 'yes' !== get_site_option( 'mucd_disable_enhanced_site_select' ) );
			self::enqueue_script_network_duplicate( $use_select2 );

			if ( $use_select2 ) {

				$select_site_list = self::select2_site_list();

			} else {

				$site_list = MUCD_Functions::get_site_list();

				// bail early if we don't have any sites
				if ( ! $site_list ) {
					return new WP_Error( 'mucd_error', MUCD_GAL_ERROR_NO_SITE );
				}

				$select_site_list = self::select_site_list( $site_list, $data['source'] );
			}

			require_once MUCD_COMPLETE_PATH . '/template/network_admin_duplicate_site.php';

			MUCD_Duplicate::close_log();
		}

		/**
		 * Build the select2 source select box, preselecting the site from the 'id' query param if present.
		 * @since 0.2.0
		 * @return string the output
		 */
		public static function select2_site_list() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only, used to preselect a value.
			$source_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

			$select2_html = '<select name="site[source]" id="mucd-site-source">';

			if ( $source_id ) {
				$value         = self::fetch_initial_value( $source_id );
				$select2_html .= sprintf( '<option value="%s" selected="selected">%s</option>', esc_attr( $value['id'] ), esc_html( $value['text'] ) );
			}

			$select2_html .= '</select>';
			$select2_html .= '&emsp;<a href="' . network_admin_url( 'settings.php#mucd_duplication' ) . '" title="' . MUCD_NETWORK_PAGE_DUPLICATE_TOOLTIP . '">?</a>';

			return $select2_html;
		}

		/**
		 * Get select box with duplicable site list
		 * @since 0.2.0
		 * @param  array $site_list all the sites
		 * @param  id $current_blog_id parameters
		 * @return string the output
		 */
		public static function select_site_list( $site_list, $current_blog_id = null ) {
			// return early if we're overriding
			$override = apply_filters( 'mucd_override_site_select', null, $site_list, $current_blog_id );

			if ( null !== $override ) {
				return $override;
			}

			$output = '';

			if ( 1 === count( $site_list ) ) {
				$blog_id = $site_list[0]['blog_id'];
			} elseif ( isset( $current_blog_id ) && MUCD_Functions::value_in_array( $current_blog_id, $site_list, 'blog_id' ) && MUCD_Functions::is_duplicable( $current_blog_id ) ) {
				$blog_id = $current_blog_id;
			}

			$output .= '<select name="site[source]">';
			foreach ( $site_list as $site ) {
				$option_value = esc_attr( $site['blog_id'] );
				$option_label = esc_html( substr( $site['domain'] . $site['path'], 0, -1 ) );
				if ( isset( $blog_id ) && (int) $site['blog_id'] === (int) $blog_id ) {
					$output .= '    <option selected value="' . $option_value . '">' . $option_label . '</option>';
				} else {
					$output .= '    <option value="' . $option_value . '">' . $option_label . '</option>';
				}
			}
			$output .= '</select>';
			$output .= '&emsp;<a href="' . network_admin_url( 'settings.php#mucd_duplication' ) . '" title="' . MUCD_NETWORK_PAGE_DUPLICATE_TOOLTIP . '">?</a>';
			return $output;
		}

		/**
		 * Search for sites using path
		 * @return    null    outputs a JSON string to be consumed by an AJAX call
		 */
		public static function mucd_fetch_sites() {
			if ( ! current_user_can( 'manage_sites' ) ) {
				wp_send_json_error( array( 'message' => 'Forbidden' ), 403 );
			}

			$security_check_passes = (
				! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] )
				&& 'xmlhttprequest' === strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) )
				&& isset( $_GET['nonce'], $_GET['q'] )
				&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'mucd-fetch-sites' )
			);

			if ( ! $security_check_passes ) {
				wp_send_json_error( array( 'message' => 'Invalid request' ), 400 );
			}

			// @info $site_id is actually the 'network' id
			global $wpdb, $site_id;

			$search_term    = sanitize_text_field( wp_unslash( $_GET['q'] ) );
			$path_or_domain = ( defined( 'SUBDOMAIN_INSTALL' ) && SUBDOMAIN_INSTALL ) ? 'domain' : 'path';

			// Get our sites based on the search string. No core API exists for partial domain/path site search,
			// so a direct, non-cacheable query against the live blogs table is required.
			// $path_or_domain is restricted above to the literal 'domain' or 'path', never user input.
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			$results = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT `blog_id` FROM `' . $wpdb->blogs . '` WHERE `' . $path_or_domain . '` LIKE %s AND `site_id` = %d LIMIT 10',
					'%' . $wpdb->esc_like( $search_term ) . '%',
					$site_id
				)
			);
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

			foreach ( $results as $key => $object ) {
				if ( ! MUCD_Functions::is_duplicable( $object->blog_id ) ) {
					unset( $results[ $key ] );
				}
			}

			// bail if we found no results
			if ( empty( $results ) ) {
				wp_send_json_error( array( 'message' => 'No results' ), 404 );
			}

			self::send_sites_array_value( $results );
		}

		/**
		 * Returns select2 value based on the field's saved blog id value
		 *
		 * @since  0.1.0
		 *
		 * @param  int  $id Stored blog id
		 */
		protected static function fetch_initial_value( $id ) {
			$id           = esc_attr( $id );
			$blog_details = get_blog_details( $id, true );

			$value = array(
				'id'      => $id,
				'text'    => isset( $blog_details->domain )
					? $blog_details->domain . $blog_details->path
					: MUCD_GENERAL_ERROR,
				'details' => $blog_details,
			);

			return $value;
		}

		/**
		 * Returns select2 options based on the current search query
		 *
		 * @param  array  $results Array of DB results for the queried string
		 */
		protected static function send_sites_array_value( $results ) {
			$response = array();
			foreach ( $results as $result ) {
				$blog = get_blog_details( $result->blog_id, true );

				if ( $blog && isset( $blog->domain ) ) {
					$response[] = array(
						'id'      => $result->blog_id,
						'text'    => $blog->domain . $blog->path,
						'details' => $blog,
					);
				}
			}

			wp_send_json_success( $response );
		}

		/**
		 * Print log-error box
		 * @since 0.2.0
		 */
		public static function log_error_message() {
				$log_dir = MUCD_Duplicate::log_dir();
				echo '<div id="message" class="error">';
				echo '    <p>';
			if ( '' === $log_dir ) {
				echo esc_html( MUCD_LOG_ERROR );
			} else {
				echo esc_html( MUCD_CANT_WRITE_LOG ) . ' <strong>' . esc_html( $log_dir ) . '</strong><br />';
				echo esc_html( MUCD_CHANGE_RIGHTS_LOG ) . '<br /><code>chmod 755 ' . esc_html( $log_dir ) . '</code>';
			}
				echo '    </p>';
				echo '</div>';
		}

		/**
		 * Print result message box error / updated
		 * @since 0.2.0
		 * @param  array $form_message messages to print
		 */
		public static function result_message( $form_message ) {
			if ( isset( $form_message['error'] ) ) {
				echo '<div id="message" class="error">';
				echo '    <p>' . esc_html( $form_message['error'] ) . '</p>';
				echo '</div>';
			} else {
				echo '<div id="message" class="updated">';
				echo '  <p>';
				echo '      <strong>' . esc_html( $form_message['msg'] ) . ' : </strong>';
				switch_to_blog( $form_message['site_id'] );
				$user = get_current_user_id();
				echo '      <a href="' . esc_url( get_dashboard_url( $user ) ) . '">' . esc_html( MUCD_NETWORK_PAGE_DUPLICATE_DASHBOARD ) . '</a> - ';
				echo '      <a href="' . esc_url( get_site_url() ) . '">' . esc_html( MUCD_NETWORK_PAGE_DUPLICATE_VISIT ) . '</a> - ';
				echo '      <a href="' . esc_url( admin_url( 'customize.php' ) ) . '">' . esc_html( MUCD_NETWORK_CUSTOMIZE ) . '</a>';
				$log_url = MUCD_Duplicate::log_url();
				if ( $log_url ) {
					echo ' - <a href="' . esc_url( $log_url ) . '">' . esc_html( MUCD_NETWORK_PAGE_DUPLICATE_VIEW_LOG ) . '</a>';
				}
				restore_current_blog();
				echo '  </p>';
				echo '</div>';
			}
		}

		/**
		 * Enqueue scripts for Duplication page
		 * @since 0.2.0
		 * @param bool $select2 whether to enqueue the select2 enhanced site selector
		 */
		public static function enqueue_script_network_duplicate( $select2 = true ) {
			// Enqueue script for user suggest on mail input
			wp_enqueue_script( 'user-suggest' );

			$debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;

			// Enqueue script for advanced options and enable / disable log path text input
			$dependencies = array( 'jquery' );

			// enqueue select2
			if ( $select2 ) {
				$min = $debug ? '' : '.min';
				wp_enqueue_script( 'mucd/select2', MUCD_URL . "/js/select2/js/select2$min.js", array( 'jquery' ), '4.0.0', true );
				wp_enqueue_style( 'mucd/select2', MUCD_URL . '/js/select2/css/select2.css', array(), '4.0.0' );
				$dependencies[] = 'mucd/select2';

				// Load select2 language js file
				$select2_locale = get_locale();
				$select2_locale = str_replace( '_', '-', $select2_locale );
				if ( ! file_exists( MUCD_COMPLETE_PATH . "/js/select2/js/i18n/$select2_locale.js" ) ) {
					$select2_locale = strstr( $select2_locale, '-', true );
				}
				wp_enqueue_script( 'mucd/select2-i18n', MUCD_URL . "/js/select2/js/i18n/$select2_locale.js", $dependencies, '4.0.0', true );
				$dependencies[] = 'mucd/select2-i18n';
			}

			wp_enqueue_script( 'mucd/duplicate', MUCD_URL . '/js/network_admin_duplicate_site.js', $dependencies, MUCD::VERSION, true );

			// Localize variables for Javascript usage
			$localize_args = array(
				'use_select2'                 => $select2,
				'debug'                       => $debug,
				'nonce'                       => wp_create_nonce( 'mucd-fetch-sites' ),
				'placeholder_text'            => MUCD_NETWORK_SELECT_SITE,
				'placeholder_value_text'      => MUCD_JAVASCRIPT_REQUIRED,
				'placeholder_no_results_text' => MUCD_NO_RESULTS,
				'blogname'                    => MUCD_BLOGNAME,
				'the_id'                      => MUCD_THE_ID,
				'post_count'                  => MUCD_POST_COUNT,
				'is_public'                   => MUCD_IS_PUBLIC,
				'is_archived'                 => MUCD_IS_ARCHIVED,
				'yes'                         => MUCD_YES,
				'no'                          => MUCD_NO,
			);
			// Add select2 language option
			if ( isset( $select2_locale ) && ! empty( $select2_locale ) ) {
				$localize_args['locale'] = $select2_locale;
			}
			wp_localize_script( 'mucd/duplicate', 'mucd_config', $localize_args );
		}

		/**
		 * Enqueue scripts and style for Network Settings page
		 * @since 0.2.0
		 */
		public static function enqueue_script_network_settings() {
			// Enqueue script for network settings page
			wp_enqueue_script( 'mucd/duplicate', MUCD_URL . '/js/network_admin_settings.js', array( 'jquery' ), MUCD::VERSION, true );
			// Enqueue style for network settings page
			wp_enqueue_style( 'mucd/duplicate-css', MUCD_URL . '/css/network_admin_settings.css', array(), MUCD::VERSION );
		}

		/**
		 * Duplication form validation
		 * @since 0.2.0
		 * @param  array $init_data default data
		 * @return array $data validated data, or errors
		 */
		public static function check_form( $init_data ) {

			$data               = $init_data;
			$data['copy_files'] = 'no';
			$data['keep_users'] = 'no';
			$data['log']        = 'no';

			// Check referer and nonce
			if ( check_admin_referer( MUCD_DOMAIN ) ) {

				global $current_site;

				$error = array();

				// Merge $data / $_POST['site'] to get Posted data and fill form
				if ( isset( $_POST['site'] ) && is_array( $_POST['site'] ) ) {
					$posted_site = array_map( 'sanitize_text_field', wp_unslash( $_POST['site'] ) );
					$data        = array_merge( $data, $posted_site );
				}

				// format and check source
				$data['from_site_id'] = intval( $data['source'] );
				if ( $data['from_site_id'] < 1 || ! get_blog_details( $data['from_site_id'], false ) ) {
					$error[] = new WP_Error( 'mucd_error', MUCD_NETWORK_PAGE_DUPLICATE_MISSING_FIELDS );
				}

				$domain = '';
				if ( preg_match( '|^([a-zA-Z0-9-])+$|', $data['domain'] ) ) {
					$domain = strtolower( $data['domain'] );
				}

				// If not a subdomain install, make sure the domain isn't a reserved word
				if ( ! is_subdomain_install() ) {
					/** This filter is documented in wp-includes/ms-functions.php */
					$subdirectory_reserved_names = apply_filters( 'subdirectory_reserved_names', array( 'page', 'comments', 'blog', 'files', 'feed' ) );
					if ( in_array( $domain, $subdirectory_reserved_names, true ) ) {
						$error[] = new WP_Error( 'mucd_error', sprintf( MUCD_NETWORK_PAGE_DUPLICATE_DOMAIN_ERROR_RESERVED_WORDS, implode( '</code>, <code>', $subdirectory_reserved_names ) ) );
					}
				}

				if ( empty( $domain ) ) {
					$error[] = new WP_Error( 'mucd_error', MUCD_NETWORK_PAGE_DUPLICATE_DOMAIN_ERROR_REQUIRE );
				}
				if ( is_subdomain_install() ) {
					$newdomain = $domain . '.' . preg_replace( '|^www\.|', '', $current_site->domain );
					$path      = $current_site->path;
				} else {
					$newdomain = $current_site->domain;
					$path      = $current_site->path . $domain . '/';
				}

				// format and check title
				if ( empty( $data['title'] ) ) {
					$error[] = new WP_Error( 'mucd_error', MUCD_NETWORK_PAGE_DUPLICATE_TITLE_ERROR_REQUIRE );
				}

				// format and check email admin
				if ( empty( $data['email'] ) ) {
					$error[] = new WP_Error( 'mucd_error', MUCD_NETWORK_PAGE_DUPLICATE_EMAIL_MISSING );
				}
				$valid_mail = sanitize_email( $data['email'] );
				if ( is_email( $valid_mail ) ) {
					$data['email'] = $valid_mail;
				} else {
					$error[] = new WP_Error( 'mucd_error', MUCD_NETWORK_PAGE_DUPLICATE_EMAIL_ERROR_FORMAT );
				}

				$data['domain']    = $domain;
				$data['newdomain'] = $newdomain;
				$data['path']      = $path;

				$data['public'] = ! isset( $data['private'] );

				// Network
				$data['network_id'] = $current_site->id;

				if ( isset( $data['log'] ) && 'yes' === $data['log'] && ( ! isset( $data['log-path'] ) || '' === $data['log-path'] || ! MUCD_Functions::valid_path( $data['log-path'] ) ) ) {
					$error[] = new WP_Error( 'mucd_error', MUCD_NETWORK_PAGE_DUPLICATE_VIEW_LOG_PATH_EMPTY );
				}

				if ( isset( $error[0] ) ) {
					$data['error'] = $error[0];
				}
			} else {
				$data['error'] = new WP_Error( 'mucd_error', MUCD_GAL_ERROR_CAPABILITIES );
			}

			return $data;
		}

		/**
		 * Save duplication options on network settings page
		 * @since 0.2.0
		 */
		public static function save_admin_network_option_page() {

			if ( ! empty( $_POST ) && isset( $_POST[ MUCD_SLUG_ACTION_SETTINGS ] ) ) {

				if ( check_admin_referer( 'siteoptions' ) ) {

					if ( isset( $_POST['duplicables'] ) ) {

						if ( 'all' === $_POST['duplicables'] ) {
							update_site_option( 'mucd_duplicables', 'all' );
						} else {
							update_site_option( 'mucd_duplicables', 'selected' );

							if ( isset( $_POST['duplicables-list'] ) ) {
								MUCD_Option::set_duplicable_option( array_map( 'intval', (array) $_POST['duplicables-list'] ) );
							} else {
								MUCD_Option::set_duplicable_option( array() );
							}
						}
					}

					if ( isset( $_POST['mucd_copy_files'] ) && 'yes' === $_POST['mucd_copy_files'] ) {
						update_site_option( 'mucd_copy_files', 'yes' );
					} else {
						update_site_option( 'mucd_copy_files', 'no' );
					}

					if ( isset( $_POST['mucd_keep_users'] ) && 'yes' === $_POST['mucd_keep_users'] ) {
						update_site_option( 'mucd_keep_users', 'yes' );
					} else {
						update_site_option( 'mucd_keep_users', 'no' );
					}

					if ( isset( $_POST['mucd_log'] ) && 'yes' === $_POST['mucd_log'] ) {

						update_site_option( 'mucd_log', 'yes' );

						if ( isset( $_POST['mucd_log_dir'] ) ) {
							$log_dir = sanitize_text_field( wp_unslash( $_POST['mucd_log_dir'] ) );
							if ( MUCD_Functions::valid_path( $log_dir ) ) {
								update_site_option( 'mucd_log_dir', $log_dir );
							}
						}
					} else {
						update_site_option( 'mucd_log', 'no' );
					}

					if ( isset( $_POST['mucd_disable_enhanced_site_select'] ) && 'yes' === $_POST['mucd_disable_enhanced_site_select'] ) {
						update_site_option( 'mucd_disable_enhanced_site_select', 'yes' );
					} else {
						update_site_option( 'mucd_disable_enhanced_site_select', 'no' );
					}
				}
			}
		}

		/**
		 * Print duplication options on network settings page
		 * @since 0.2.0
		 */
		public static function admin_network_option_page() {
			self::enqueue_script_network_settings();
			require_once MUCD_COMPLETE_PATH . '/template/network_admin_network_settings.php';
		}
	}
}
