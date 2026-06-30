<?php

if ( ! class_exists( 'MUCD_Log' ) ) {

	/**
	 * Writes a log file for a single duplication run.
	 */
	class MUCD_Log {

		/** @var bool Whether logging is active for this run. */
		public $mod;

		/** @var string Log directory path. */
		private $log_dir_path;

		/** @var string Log file path. */
		private $log_file_path;

		/** @var string Log file name. */
		private $log_file_name;

		/** @var string Log file URL. */
		private $log_file_url;

		/** @var resource|false File handle for the open log file. */
		private $fp;

		/**
		 * Constructor
		 *
		 * @since 0.2.0
		 * @param boolean $mod is log active
		 * @param string  $log_dir_path log directory
		 * @param string  $log_file_name log file name
		 */
		public function __construct( $mod, $log_dir_path = '', $log_file_name = '' ) {
			$this->mod = $mod;

			$this->log_dir_path  = $log_dir_path;
			$this->log_file_name = $log_file_name;
			$this->log_file_path = $log_dir_path . $log_file_name;

			$this->log_file_url = str_replace( ABSPATH, get_site_url( 1, '/' ), $log_dir_path ) . $log_file_name;

			if ( false !== $mod ) {
				$this->init_file();
			}
		}

		/**
		 * Returns log directory path
		 *
		 * @since 0.2.0
		 * @return string $this->log_dir_path
		 */
		public function dir_path() {
			return $this->log_dir_path;
		}

		/**
		 * Returns log file path
		 *
		 * @since 0.2.0
		 * @return string $this->log_file_path
		 */
		public function file_path() {
			return $this->log_file_path;
		}

		/**
		 * Returns log file name
		 *
		 * @since 0.2.0
		 * @return string $this->log_file_name
		 */
		public function file_name() {
			return $this->log_file_name;
		}

		/**
		 * Returns log file url
		 *
		 * @since 0.2.0
		 * @return string $this->log_file_url
		 */
		public function file_url() {
			return $this->log_file_url;
		}

		/**
		 * Checks if log is writable
		 *
		 * @since 0.2.0
		 * @return boolean True if plugin can writes the log, or false
		 */
		public function can_write() {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable -- local disk log file.
			return ( is_resource( $this->fp ) && is_writable( $this->log_file_path ) );
		}

		/**
		 * Returns log mod (active or not)
		 *
		 * @since 0.2.0
		 * @return boolean $this->mod
		 */
		public function mod() {
			return $this->mod;
		}

		/**
		 * Initialize file before writing
		 *
		 * @since 0.2.0
		 * @return boolean True on success, False on failure
		 */
		private function init_file() {
			if ( MUCD_Files::init_dir( $this->log_dir_path ) !== false ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- local disk log file.
				$this->fp = fopen( $this->log_file_path, 'a' );
				if ( ! $this->fp ) {
					return false;
				}
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_chmod -- local disk log file.
				chmod( $this->log_file_path, 0640 );
				return true;
			}
			return false;
		}

		/**
		 * Writes a message in log file
		 *
		 * @since 0.2.0
		 * @param  string $message the message to write
		 * @return boolean True on success, False on failure
		 */
		public function write_log( $message ) {
			if ( false !== $this->mod && $this->can_write() ) {
				$time = current_time( '[d/M/Y:H:i:s]' );
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- local disk log file.
				fwrite( $this->fp, "$time $message\r\n" );
				return true;
			}
			return false;
		}

		/**
		 * Closes the log file
		 *
		 * @since 0.2.0
		 */
		public function close_log() {
			if ( is_resource( $this->fp ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- local disk log file.
				fclose( $this->fp );
			}
		}
	}
}
