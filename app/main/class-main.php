<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Class for custom work.
 *
 * @package BPAI_Core
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// If class is exist, then don't execute this.
if ( ! class_exists( 'BPAI_Core' ) ) {

	/**
	 * Class for fofc core.
	 */
	class BPAI_Core {

		/**
		 * Constructor for class.
		 */
		public function __construct() {

			$files = array(
				'app/main/class-custom-actions-filters',
				'app/main/class-bpai-activity',
			);

			foreach ( $files as $file ) {
				// Include functions file.
				if ( file_exists( BPAI_PATH . $file . '.php' ) ) {
					require BPAI_PATH . $file . '.php';
				}
			}

			// Add custom style and script.
			add_action( 'wp_enqueue_scripts', array( $this, 'blp_bpai_enqueue_style_script' ) );
		}

		/**
		 * Script and styling for plugin.
		 *
		 * @return void
		 */
		public function blp_bpai_enqueue_style_script() {

			// Custom plugin script.
			wp_enqueue_style(
				'bpai-style',
				BPAI_URL . 'assets/css/custom-style.min.css',
				'',
				BPAI_VERSION
			);

			// Custom plugin script.
			wp_enqueue_script(
				'bpai-script',
				BPAI_URL . 'assets/js/custom-script.min.js',
				array( 'jquery' ),
				BPAI_VERSION,
				true
			);
		}
	}

	new BPAI_Core();
}
