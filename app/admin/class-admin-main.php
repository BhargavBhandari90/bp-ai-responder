<?php
/**
 * Class for custom work.
 *
 * @package BPAI_Admin_Core
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// If class is exist, then don't execute this.
if ( ! class_exists( 'BPAI_Admin_Core' ) ) {

	/**
	 * Class for fofc core.
	 */
	class BPAI_Admin_Core {

		/**
		 * Constructor for class.
		 */
		public function __construct() {

			// Plugin's setting page.
			add_action( 'admin_menu', array( $this, 'blp_bpai_settings_page' ) );

			// Register settings fields.
			add_action( 'admin_init', array( $this, 'blp_bpai_register_settings' ) );

			// Generate autoresponder user.
			add_action( 'admin_init', array( $this, 'blp_bpai_generate_autoresponder' ) );
		}

		/**
		 * Add settings page.
		 *
		 * @return void
		 */
		public function blp_bpai_settings_page() {
			add_menu_page(
				esc_html__( 'AI Auto Responder Settings', 'bpai-core' ),
				esc_html__( 'AI Auto Responder', 'bpai-core' ),
				'manage_options',
				'ai-auto-responder',
				array( $this, 'blp_bpai_settings' ),
				'dashicons-controls-volumeon',
				80
			);
		}

		/**
		 * Settings fields.
		 *
		 * @return void
		 */
		public function blp_bpai_settings() {
			?>
			<div class="wrap">
				<h1><?php echo __( 'AI Audio Responder Settings', 'bpai-core' ); ?></h1>
				<form method="post" action="options.php" novalidate="novalidate">
					<?php settings_fields( 'bpai_settings' ); ?>
					<table class="form-table" role="presentation">
					<?php do_settings_sections( 'ai-auto-responder' ); ?>
					</table>
					<?php submit_button(); ?>
				</form>
			</div>
			<?php
		}

		/**
		 * Register setting fields.
		 *
		 * @return void
		 */
		public function blp_bpai_register_settings() {

			register_setting( 'bpai_settings', 'bpai_setting' );

			// register a new section in the "reading" page
			add_settings_section(
				'bpai_settings_section',
				esc_html__( 'API Keys', 'bpai-core' ),
				array( $this, 'bwp_bpai_setting_section_cb' ),
				'ai-auto-responder',
				array(
					'description' => __( 'API keys for Gemini and Voice RSS. <kbd>Gemini</kbd>: It is used to get response in a text form. <kbd>Voice RSS</kbd>: It is used to convert text response from Gemini to audio.', 'bpai-core' ),
				)
			);

			add_settings_field(
				'bpai_voice_rss_key',
				esc_html__( 'Voice RSS API key ( COMING SOON )', 'bpai-core' ),
				array( $this, 'bwp_setting_field_callback' ),
				'ai-auto-responder',
				'bpai_settings_section',
				array(
					'name'        => 'bpai_voice_rss_key',
					'class'       => 'regular-text',
					'type'        => 'text',
					'description' => 'Not functional yet. Get Voice RSS key from <a href="https://www.voicerss.org/personel/" target="_blank">here</a>',
				)
			);

			add_settings_field(
				'bpai_gemini_key',
				esc_html__( 'Gemini API key', 'bpai-core' ),
				array( $this, 'bwp_setting_field_callback' ),
				'ai-auto-responder',
				'bpai_settings_section',
				array(
					'name'        => 'bpai_gemini_key',
					'class'       => 'regular-text',
					'type'        => 'text',
					'description' => 'Get Gemini key from <a href="https://aistudio.google.com/app/apikey" target="_blank">here</a>',
				)
			);

			// Add a new section for Prompt Settings.
			add_settings_section(
				'prompt_settings_section',
				esc_html__( 'Prompt Settings', 'bpai-core' ),
				array( $this, 'bwp_bpai_setting_section_cb' ),
				'ai-auto-responder',
				array(
					'description' => esc_html__( 'Set your prompt for AIs.', 'bpai-core' ),
				),
			);

			// Add the text area field for the prompt.
			add_settings_field(
				'gemini_ai_prompt',
				esc_html__( 'Prompt for Gemini', 'bpai-core' ),
				array( $this, 'bwp_setting_field_callback' ),
				'ai-auto-responder',
				'prompt_settings_section',
				array(
					'name'        => 'bpai_gemini_prompt',
					'class'       => 'large-text code',
					'type'        => 'textarea',
					'description' => esc_html__( 'Set prompt for Gemini.', 'bpai-core' ),
				)
			);

			// Add a new section for Auto Responder Settings.
			add_settings_section(
				'auto_responder_settings_section',
				esc_html__( 'Auto Responder', 'bpai-core' ),
				array( $this, 'bwp_bpai_setting_section_cb' ),
				'ai-auto-responder',
				array(
					'description' => esc_html__( 'Set autoresponder user.', 'bpai-core' ),
				),
			);

			// Add the text area field for the prompt.
			add_settings_field(
				'ai_auto_reponder',
				esc_html__( 'Auto-responder', 'bpai-core' ),
				array( $this, 'bwp_setting_auto_responder_field_callback' ),
				'ai-auto-responder',
				'auto_responder_settings_section',
				array(
					'name'        => 'bpai_auto_responder',
					'type'        => 'select',
					'description' => esc_html__( 'Set autoresponder user.', 'bpai-core' ),
				)
			);
		}

		/**
		 * Settings description.
		 *
		 * @param  array $args array of settings parameters.
		 * @return void
		 */
		public function bwp_bpai_setting_section_cb( $args ) {
			echo wp_sprintf(
				'<p>%1$s</p>',
				wp_kses_post( $args['description'] )
			);
		}

		/**
		 * Display fields.
		 *
		 * @param array $args array of settings.
		 * @return void
		 */
		public function bwp_setting_field_callback( $args ) {

			$field_name = $args['name'];
			$settings   = get_option( 'bpai_setting' );
			$value      = isset( $settings[ $field_name ] ) ? $settings[ $field_name ] : '';
			$class      = $args['class'];

			switch ( $args['type'] ) {

				case 'text':
					echo wp_sprintf(
						'<input type="text" name="bpai_setting[%s]" value="%s" class="%s" /><p class="description">%s</p>',
						esc_attr( $field_name ),
						esc_attr( $value ),
						esc_attr( $class ),
						wp_kses_post( $args['description'] )
					);
					break;

				case 'textarea':
					echo wp_sprintf(
						'<textarea name="bpai_setting[%s]" class="%s" rows="5" cols="50">%s</textarea><p class="description">%s</p>',
						esc_attr( $field_name ),
						esc_attr( $class ),
						esc_textarea( $value ),
						wp_kses_post( $args['description'] )
					);
					break;
			}
		}

		/**
		 * Generate Responder Settings.
		 *
		 * @param array $args Array of parameters.
		 * @return void
		 */
		public function bwp_setting_auto_responder_field_callback( $args ) {
			$field_name = $args['name'];
			$value      = get_option( 'bpai_auto_responder' );
			$class      = $args['class'] ?? '';

			if ( empty( $value ) ) {
				echo wp_sprintf(
					'<button type="submit" name="generate-autoresponder" value="%d">%s</button>',
					1,
					esc_html__( 'Generate Responder', 'bpai-core' )
				);
			} else {

				$ai_responder = get_user_by( 'ID', $value );

				echo wp_sprintf(
					'<input type="hidden" name="bpai_auto_responder" value="%d"/><a href="%s">%s</a>',
					esc_attr( $value ),
					esc_url( admin_url( 'user-edit.php?user_id=' . $value ) ),
					esc_html( $ai_responder->display_name )
				);
			}
		}

		/**
		 * Generate auto responder user.
		 *
		 * @return void
		 */
		public function blp_bpai_generate_autoresponder() {

			if ( isset( $_POST['generate-autoresponder'] ) ) {

				$username = 'ai_responder';
				$email    = 'ai_responder@example.com';
				$password = wp_generate_password();

				// Check if user already exists.
				if ( username_exists( $username ) || email_exists( $email ) ) {
					return;
				}

				// Create a user.
				$user_id = wp_create_user( $username, $password, $email );

				if ( is_wp_error( $user_id ) ) {
					return;
				}

				update_option( 'bpai_auto_responder', $user_id );

				wp_update_user(
					array(
						'ID'           => $user_id,
						'display_name' => 'AI Auto Responder',
					)
				);

				$user = new WP_User( $user_id );
				$user->set_role( 'subscriber' );

				update_user_meta( $user_id, 'ai_avatar_url', 'https://www.gravatar.com/avatar/' . md5( strtolower( trim( $email ) ) ) . '?s=200' );
			}
		}
	}

	new BPAI_Admin_Core();
}
