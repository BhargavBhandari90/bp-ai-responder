<?php

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
if ( ! class_exists( 'BPAI_Custom_Actions_Filters' ) ) {

	/**
	 * Class for custom actions and filters.
	 */
	class BPAI_Custom_Actions_Filters {

		/**
		 * Constructor for class.
		 */
		public function __construct() {
			// Hook goes here.
		}
	}

	new BPAI_Custom_Actions_Filters();
}
