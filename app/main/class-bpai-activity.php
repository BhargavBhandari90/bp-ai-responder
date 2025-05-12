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
if ( ! class_exists( 'BPAI_Activity' ) ) {

	/**
	 * Class for fofc core.
	 */
	class BPAI_Activity {

		/**
		 * Constructor for class.
		 */
		public function __construct() {

			add_filter( 'bp_activity_comment_content', array( $this, 'display_ai_response_in_comment' ), 10 );

			add_action( 'bp_groups_posted_update', array( $this, 'bpl_bpai_generate_group_comment' ), 10, 4 );

			add_action( 'wp_ajax_blp_bpai_get_ai_response', array( $this, 'blp_bpai_get_ai_response' ) );
			add_action( 'wp_ajax_nopriv_blp_bpai_get_ai_response', array( $this, 'blp_bpai_get_ai_response' ) );
		}

		/**
		 * Generates an AI response for a given activity comment and converts it to audio.
		 *
		 * This function retrieves the content of an activity, generates an AI response based on that content,
		 * converts the response to audio, and updates the activity meta with the audio URL.
		 *
		 * @since 1.0.0
		 *
		 * @uses filter_input()
		 * @uses blp_bpair_get_activity_content()
		 * @uses blp_bpai_generate_ai_response()
		 * @uses blp_bpai_convert_text_to_speech()
		 * @uses bp_activity_update_meta()
		 * @uses bp_activity_delete_meta()
		 * @uses wp_send_json_success()
		 * @uses wp_send_json_error()
		 *
		 * @return void This function doesn't return a value. It sends a JSON response instead.
		 */
		public function blp_bpai_get_ai_response() {

			// Security check.
			check_ajax_referer( 'blp-bpai-nounce', 'security' );

			$comment_activity_id = filter_input( INPUT_GET, 'comment_activity_id', FILTER_SANITIZE_NUMBER_INT );

			$main_activity_content = trim( blp_bpair_get_activity_content( $comment_activity_id ) );

			if ( empty( $main_activity_content ) ) {
				wp_send_json_error( 'No content' );
			}

			// Generate an AI response (text).
			$ai_response_text = blp_bpai_generate_ai_response( $main_activity_content );

			if ( empty( $ai_response_text ) ) {
				wp_send_json_error( 'No response received.' );
			}

			bp_activity_delete_meta( $comment_activity_id, 'needs_response', 'yes' );
			bp_activity_update_meta( $comment_activity_id, 'bpai_response', $ai_response_text );

			wp_send_json_success( array( 'text' => $ai_response_text ) );

			wp_send_json_error();
		}

		/**
		 * Generates a comment for a group activity and marks it as needing an AI response.
		 *
		 * This function creates a new comment on a group activity and sets a meta flag
		 * indicating that the comment needs an AI-generated audio response.
		 *
		 * @since 1.0.0
		 *
		 * @param string $content     The content of the group activity update.
		 * @param int    $user_id     The ID of the user who posted the update.
		 * @param int    $group_id    The ID of the group where the update was posted.
		 * @param int    $activity_id The ID of the activity item.
		 *
		 * @return void This function doesn't return a value.
		 */
		public function bpl_bpai_generate_group_comment( $content, $user_id, $group_id, $activity_id ) {

			// Bail, if anything goes wrong.
			if ( ! function_exists( 'bp_activity_new_comment' ) ||
				! function_exists( 'bp_activity_get_specific' ) ||
				! function_exists( 'bp_activity_update_meta' ) ) {

				return;
			}

			$activiy_posted = bpai_has_user_posted_group_activity( $user_id, $group_id );

			if ( 1 !== $activiy_posted ) {
				return;
			}

			// Get the activity content.
			$auto_responder = bpai_get_auto_responder();

			// Post the audio as a comment on the activity.
			$comment_activity_id = bp_activity_new_comment(
				array(
					'activity_id' => $activity_id,
					'content'     => '&nbsp;',
					'user_id'     => $auto_responder,
				)
			);

			// Set meta to indicate that it requires auto response.
			if ( $comment_activity_id ) {
				bp_activity_update_meta( $comment_activity_id, 'needs_response', 'yes' );
			}
		}

		/**
		 * Displays AI response in the comment.
		 *
		 * This function checks if the comment requires an AI response, fetches the AI response
		 * and displays it in the comment. If the response is audio, it generates an audio player.
		 *
		 * @since 1.0.0
		 *
		 * @param string $comment_text The original comment text.
		 *
		 * @return string The modified comment text with AI response.
		 */
		public function display_ai_response_in_comment( $comment_text ) {

			$needs_response = bp_activity_get_meta( bp_get_activity_comment_id(), 'needs_response', true );
			$response_text  = bp_activity_get_meta( bp_get_activity_comment_id(), 'bpai_response', true );

			/**
			 * TODO: Audio response i'll do it later.
			 */
			$audio_url = bp_activity_get_meta( bp_get_activity_comment_id(), 'ai_audio_response', true );

			if ( $needs_response ) {

					$comment_text .= wp_sprintf(
						'<audio controls id="audio-player-%1$s"></audio><div id="loader-%1$s" class="typing-loader"><span></span><span></span><span></span></div>',
						esc_attr( bp_get_activity_comment_id() )
					);
					ob_start();
				?>
				<script>
				jQuery(document).ready(function($) {
					function typeText(element, text, speed = 50) {
						let index = 0;
						function type() {
							if (index < text.length) {
								$(element).append(text.charAt(index));
								index++;
								setTimeout(type, speed);
							}
						}
						type();
					}
					var activity_id = '<?php echo esc_attr( bp_get_activity_comment_id() ); ?>';
					var nounce = '<?php echo esc_attr( wp_create_nonce( 'blp-bpai-nounce' ) ); ?>';
					var comment_ele = jQuery( '#acomment-' + activity_id + ' .acomment-content' );

					jQuery('#audio-player-'+activity_id).hide();

					$.ajax({
						url: ajaxurl, // WordPress AJAX URL
						type: 'GET',
						data: {
							action              : 'blp_bpai_get_ai_response',
							comment_activity_id : activity_id,
							security            : nounce,
						},
						success: function(response) {
							jQuery('#loader').hide(); // Hide loader

							if (response.success) {
								// jQuery('#audio-player-'+activity_id).attr('src', response.data.text).show();
								jQuery('#loader-'+activity_id).hide();
								typeText(comment_ele, response.data.text);
							} else {
								// alert(response.data.message);
							}
						},
						error: function() {
							jQuery('#loader-'+activity_id).hide();
							alert('An error occurred. Please try again.');
						}
					});
				});
				</script>
				<?php
					$comment_text .= ob_get_clean();
			} elseif ( ! empty( $audio_url ) ) {

					$comment_text .= wp_sprintf(
						'<br><audio controls><source id="audio-player-%1$s" src="%2$s" type="audio/mpeg"></audio>',
						esc_attr( bp_get_activity_comment_id() ),
						esc_url( $audio_url )
					);
			} elseif ( ! empty( $response_text ) ) {
				$comment_text .= wp_sprintf(
					'<p>%s</p>',
					esc_html( $response_text )
				);
			}

			return $comment_text;
		}
	}

	new BPAI_Activity();
}
