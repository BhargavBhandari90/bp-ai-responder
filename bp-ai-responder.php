<?php
/**
 * Plugin Name:     BuddyPress AI Responder
 * Description:     Adds AI-generated responses to BuddyPress activity.
 * Author:          Bili Plugins
 * Author URI:      https://biliplugins.com/
 * Text Domain:     bpai-core
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         BPAI_Core
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Limit plugin execution if main plugin is not available.
 */
function blp_bpai_load_plugin() {

	if ( ! class_exists( 'BuddyPress' ) ) {
		add_action( 'admin_notices', 'blp_bpai_admin_notice' );
		return;
	}
}

add_action( 'plugins_loaded', 'blp_bpai_load_plugin' );

/**
 * Plugin notice.
 *
 * @return void
 */
function blp_bpai_admin_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<strong>BuddyPress AI Audio Responder</strong> requires the <strong>BuddyPress</strong> plugin to be installed and activated. Please install and activate BuddyPress to use this plugin.
		</p>
	</div>
	<?php
}


/**
 * Main file, contains the plugin metadata and activation processes
 *
 * @package    BPAI_Core
 */
if ( ! defined( 'BPAI_VERSION' ) ) {
	/**
	 * The version of the plugin.
	 */
	define( 'BPAI_VERSION', '1.0.0' );
}

if ( ! defined( 'BPAI_PATH' ) ) {
	/**
	 *  The server file system path to the plugin directory.
	 */
	define( 'BPAI_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'BPAI_URL' ) ) {
	/**
	 * The url to the plugin directory.
	 */
	define( 'BPAI_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'BPAI_BASE_NAME' ) ) {
	/**
	 * The url to the plugin directory.
	 */
	define( 'BPAI_BASE_NAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'BPAI_MAIN_FILE' ) ) {
	/**
	 * The url to the plugin directory.
	 */
	define( 'BPAI_MAIN_FILE', __FILE__ );
}

/**
 * Include files.
 */
$files = array(
	'app/includes/common-functions',
	'app/main/class-main',
	'app/admin/class-admin-main',
);

if ( ! empty( $files ) ) {

	foreach ( $files as $file ) {

		// Include functions file.
		if ( file_exists( BPAI_PATH . $file . '.php' ) ) {
			require BPAI_PATH . $file . '.php';
		}
	}
}

/**
 * Plugin Setting page.
 *
 * @param array $links Array of plugin links.
 * @return array Array of plugin links.
 */
function blp_bpai_settings_link( $links ) {

	$settings_link = sprintf(
		'<a href="%1$s">%2$s</a>',
		'admin.php?page=ai-audio-responder',
		esc_html__( 'Settings', 'bpai-core' )
	);

	array_unshift( $links, $settings_link );

	return $links;
}

add_filter( 'plugin_action_links_' . BPAI_BASE_NAME, 'blp_bpai_settings_link' );

/**
 * Apply translation file as per WP language.
 */
function blp_tus_text_domain_loader() {

	// Get mo file as per current locale.
	$mofile = BPAI_PATH . 'languages/bp-ai-' . get_locale() . '.mo';

	// If file does not exists, then apply default mo.
	if ( ! file_exists( $mofile ) ) {
		$mofile = BPAI_PATH . 'languages/default.mo';
	}

	if ( file_exists( $mofile ) ) {
		load_textdomain( 'bpai-core', $mofile );
	}
}

add_action( 'plugins_loaded', 'blp_tus_text_domain_loader' );
