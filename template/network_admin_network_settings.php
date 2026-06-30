<?php echo '<input type="hidden" id="' . esc_attr( MUCD_SLUG_ACTION_SETTINGS ) . '" name="' . esc_attr( MUCD_SLUG_ACTION_SETTINGS ) . '" value="_' . esc_attr( MUCD_SLUG_ACTION_SETTINGS ) . '" />'; ?>

<h3 id="mucd_duplication"><?php echo esc_html( MUCD_NETWORK_MENU_DUPLICATION ); ?></h3>
<table class="form-table">

	<tr>
		<th scope="row"><?php echo esc_html( MUCD_NETWORK_SETTINGS_DUPLICABLE_WEBSITES ); ?></th>
		<td>
			<label><input <?php checked( get_site_option( 'mucd_duplicables', 'all' ), 'all' ); ?> type="radio" id="radio-duplicables-all" name="duplicables" value="all"><?php echo esc_html( MUCD_NETWORK_SETTINGS_DUPLICABLE_ALL ); ?></label><br><br>
			<label><input <?php checked( get_site_option( 'mucd_duplicables', 'all' ), 'selected' ); ?> type="radio" id="radio-duplicables-selected" name="duplicables" value="selected"><?php echo esc_html( MUCD_NETWORK_SETTINGS_DUPLICABLE_SELECTED ); ?></label><br><br>


			<?php
			// Networks with too many sites to list individually here would turn this into thousands of
			// checkboxes plus a get_blog_option() call per site on every load of this settings page.
			// get_blog_count() is a cheap, cached lookup, so we can decide before querying the
			// (possibly truncated, see MUCD_MAX_NUMBER_OF_SITE) full site list whether to cap it.
			$mucd_site_list_limit = (int) apply_filters( 'mucd_site_list_limit', 200 );
			$mucd_site_count      = (int) get_blog_count();

			if ( $mucd_site_count > $mucd_site_list_limit ) {
				$mucd_site_list_preview = (int) apply_filters( 'mucd_site_list_preview', 100 );
				echo '<p>' . esc_html(
					sprintf(
						/* translators: 1: number of sites on the network, 2: number of sites listed below */
						__( 'This network has %1$d sites; only the first %2$d are listed below. Use the "mucd_duplicable" blog option, or the mucd_override_site_select / mucd_get_site_list_args filters, to manage duplicable sites for large networks.', 'multisite-clone-duplicator' ),
						$mucd_site_count,
						$mucd_site_list_preview
					)
				) . '</p>';
				$network_blogs = MUCD_Functions::get_sites( array( 'number' => $mucd_site_list_preview ) );
			} else {
				$network_blogs = MUCD_Functions::get_sites();
			}

			echo '<div class="multiselect" id="site-select-box">';
			foreach ( $network_blogs as $blog ) {
				echo '    <label><input ' . checked( get_blog_option( $blog['blog_id'], 'mucd_duplicable', 'no' ), 'yes', false ) . ' class="duplicables-list" type="checkbox" name="duplicables-list[]" value="' . esc_attr( $blog['blog_id'] ) . '" />' . esc_html( substr( $blog['domain'] . $blog['path'], 0, -1 ) ) . '</label>';
			}
			echo '</div>';
			?>
		</td>
	</tr>

	<tr>
		<th scope="row"><?php echo esc_html( MUCD_NETWORK_PAGE_USE_ENHANCED_FOR_SITE_SELECT ); ?></th>
		<td>
			<label><input <?php checked( get_site_option( 'mucd_disable_enhanced_site_select', 'no' ), 'yes' ); ?> id="use-enhanced-select" name="mucd_disable_enhanced_site_select" type="checkbox" value="yes" /><?php echo esc_html( MUCD_NETWORK_PAGE_USE_ENHANCED_FOR_SITE_SELECT_TEXT_1 ); ?></label>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php echo esc_html( MUCD_NETWORK_PAGE_DUPLICATE_FILES ); ?></th>
		<td>
			<label><input <?php checked( get_site_option( 'mucd_copy_files', 'yes' ), 'yes' ); ?> name="mucd_copy_files" type="checkbox" value="yes" /><?php echo esc_html( MUCD_NETWORK_PAGE_DUPLICATE_FILES_TEXT_1 ); ?></label>
		</td>
	</tr>

	<tr>
		<th scope="row"><?php echo esc_html( MUCD_NETWORK_PAGE_DUPLICATE_USERS ); ?></th>
		<td>
			<label><input <?php checked( get_site_option( 'mucd_keep_users', 'yes' ), 'yes' ); ?> name="mucd_keep_users" type="checkbox" value="yes" /><?php echo esc_html( MUCD_NETWORK_PAGE_DUPLICATE_USERS_TEXT_1 ); ?></label>
		</td>
	</tr>

	<tr>
		<th scope="row"><?php echo esc_html( MUCD_NETWORK_PAGE_DUPLICATE_LOG ); ?></th>
		<td>
			<label><input <?php checked( get_site_option( 'mucd_log', 'no' ), 'yes' ); ?> id="log-box" name="mucd_log" type="checkbox" value="yes" /><?php echo esc_html( MUCD_NETWORK_PAGE_DUPLICATE_LOG_TEXT_1 ); ?></label>
			<br /><br /><label><?php echo esc_html( MUCD_NETWORK_PAGE_DUPLICATE_LOG_TEXT_2 ); ?> : <input id="log-path" name="mucd_log_dir" type="text"  class="large-text" value="<?php echo esc_attr( MUCD_Option::get_option_log_directory() ); ?>" /></label>
		</td>
	</tr>

</table>
