<?php

/**
 * ERRORS
 */
define( 'MUCD_GAL_ERROR_CAPABILITIES', __( 'Sorry, you don\'t have permissions to use this page.', 'multisite-clone-duplicator' ) );
define( 'MUCD_GAL_ERROR_NO_SITE', __( 'Sorry, there is no site available for duplication.', 'multisite-clone-duplicator' ) );
define( 'MUCD_LOG_ERROR', __( 'The log file cannot be written', 'multisite-clone-duplicator' ) );
define( 'MUCD_CANT_WRITE_LOG', __( 'The log file cannot be written to location', 'multisite-clone-duplicator' ) );
define( 'MUCD_CHANGE_RIGHTS_LOG', __( 'To enable logging, change permissions on log directory', 'multisite-clone-duplicator' ) );
define( 'MUCD_JAVASCRIPT_REQUIRED', __( 'This feature will not work without javascript', 'multisite-clone-duplicator' ) );
define( 'MUCD_NO_RESULTS', __( 'No results found', 'multisite-clone-duplicator' ) );
define( 'MUCD_GENERAL_ERROR', __( 'ERROR', 'multisite-clone-duplicator' ) );

/**
 * LABELS
 */
define( 'MUCD_NETWORK_MENU_DUPLICATE', __( 'Duplicate', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_MENU_DUPLICATION', __( 'Duplication', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_DUPLICABLE', __( 'Duplicable', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_SELECT_SITE', __( 'Start typing to search for a site', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_CUSTOMIZE', __( 'Customize', 'multisite-clone-duplicator' ) );
define( 'MUCD_YES', __( 'Yes', 'multisite-clone-duplicator' ) );
define( 'MUCD_NO', __( 'No', 'multisite-clone-duplicator' ) );
define( 'MUCD_BLOGNAME', __( 'Blog Name', 'multisite-clone-duplicator' ) );
define( 'MUCD_THE_ID', __( 'ID', 'multisite-clone-duplicator' ) );
define( 'MUCD_POST_COUNT', __( 'Post Count', 'multisite-clone-duplicator' ) );
define( 'MUCD_IS_PUBLIC', __( 'Public', 'multisite-clone-duplicator' ) );
define( 'MUCD_IS_ARCHIVED', __( 'Archived', 'multisite-clone-duplicator' ) );

/**
 * Admin Page Duplicate MESSAGES
 */
define( 'MUCD_NETWORK_PAGE_DUPLICATE_DASHBOARD', __( 'Dashboard', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_VISIT', __( 'Visit', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_VIEW_LOG', __( 'View log', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_MISSING_FIELDS', __( 'Missing fields', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_TITLE_ERROR_REQUIRE', __( 'Missing or invalid title', 'multisite-clone-duplicator' ) );
// translators: %s is the comma-separated list of reserved subdirectory names.
define( 'MUCD_NETWORK_PAGE_DUPLICATE_DOMAIN_ERROR_RESERVED_WORDS', __( 'The following words are reserved for use by WordPress functions and cannot be used as blog names : <code>%s</code>', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_DOMAIN_ERROR_REQUIRE', __( 'Missing or invalid site address', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_EMAIL_MISSING', __( 'Missing admin email address', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_EMAIL_ERROR_FORMAT', __( 'Invalid admin email address', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_VIEW_LOG_PATH_EMPTY', __( 'Missing or invalid log directory path', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_NOTICE_CREATED', __( 'New site was created', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_ADMIN_ERROR_CREATE_USER', __( 'There was an error creating the user.', 'multisite-clone-duplicator' ) );
// translators: %s is the upload directory path that needs writable permissions.
define( 'MUCD_NETWORK_PAGE_DUPLICATE_COPY_FILE_ERROR', __( 'Failed to copy files : check permissions on <strong>%s</strong>', 'multisite-clone-duplicator' ) );

/**
 * Admin Page Duplicate FORM
 */
define( 'MUCD_NETWORK_PAGE_DUPLICATE_TITLE', __( 'Duplicate Site', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_FIELD_SOURCE', __( 'Original site to copy', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_FIELD_ADDRESS', __( 'New Site - Address', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_FIELD_ADDRESS_INFO', __( 'Only lowercase letters (a-z) and numbers are allowed.', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_FIELD_TITLE', __( 'New Site - Title', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_FIELD_EMAIL', __( 'New Site - Admin Email', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_FIELD_EMAIL_INFO_1', __( 'A new user will be created if the above email address is not in the database.', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_FIELD_EMAIL_INFO_2', __( 'The username and password will be mailed to this email address.', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_ADVANCED_SHOW', __( 'Show advanced options', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_ADVANCED_HIDE', __( 'Hide advanced options', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_FILES', __( 'Files', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_FILES_TEXT_1', __( 'Duplicate files from duplicated site upload directory', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_USERS', __( 'Users and roles', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_USERS_TEXT_1', __( 'Keep users and roles from duplicated site', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_LOG', __( 'Log', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_LOG_TEXT_1', __( 'Generate log file', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_LOG_TEXT_2', __( 'Log directory', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_BUTTON_COPY', __( 'Duplicate', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_DUPLICATE_TOOLTIP', __( 'Edit duplicable sites list', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_USE_ENHANCED_FOR_SITE_SELECT', __( 'Disable Enhanced Site Select', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_PAGE_USE_ENHANCED_FOR_SITE_SELECT_TEXT_1', __( 'Disable Select2 for Site Select ', 'multisite-clone-duplicator' ) );

/**
 * Settings
 */
define( 'MUCD_NETWORK_SETTINGS_DUPLICABLE_WEBSITES', __( 'Dublicable websites', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_SETTINGS_DUPLICABLE_ALL', __( 'Allow duplication of all sites of the network', 'multisite-clone-duplicator' ) );
define( 'MUCD_NETWORK_SETTINGS_DUPLICABLE_SELECTED', __( 'Allow duplication of following sites only :', 'multisite-clone-duplicator' ) );
