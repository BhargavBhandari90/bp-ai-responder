<?php
/**
 * Common functions.
 *
 * @package BPAI_Core
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves a specific setting value from the plugin's settings.
 *
 * This function fetches the entire 'bpai_setting' option from the WordPress
 * options table and returns the value associated with the specified key.
 * If the key doesn't exist, it returns an empty string.
 *
 * @param string $key The specific setting key to retrieve.
 *
 * @return string The value of the specified setting, or an empty string if not found.
 */
function blp_bpai_get_setting( $key ) {

	$settings = get_option( 'bpai_setting' );
	return isset( $settings[ $key ] ) ? $settings[ $key ] : '';
}















/**
 * Generates an AI response using the Gemini model from Google's Generative Language API.
 *
 * This function sends a text input to the Google Generative Language API, specifically the Gemini model,
 * and retrieves an AI-generated response based on the provided prompt.
 *
 * @param string $text The text input to be used as a prompt for the AI response.
 *
 * @return string|void The AI-generated response, or void if an error occurs during the API request.
 */
function blp_bpai_generate_ai_response( $text ) {
	$api_key  = blp_bpai_get_setting( 'bpai_gemini_key' );
	$endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $api_key;
	$prompt   = blp_bpai_get_setting( 'bpai_gemini_prompt' );

	$data = array(
		'system_instruction' => array(
			'parts' => array(
				'text' => $prompt,
			),
		),
		'contents'           => array(
			array(
				'parts' => array(
					array(
						'text' => trim( $text ),
					),
				),
			),
		),
	);

	$response = wp_remote_post(
		$endpoint,
		array(
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'body'    => json_encode( $data ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return;
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	return $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
}

/**
 * Converts text to speech using the VoiceRSS API and saves the audio file.
 *
 * This function takes a text input, sends it to the VoiceRSS API for text-to-speech
 * conversion, and saves the resulting audio as an MP3 file in the WordPress uploads directory.
 *
 * @param string $text The text to be converted to speech.
 *
 * @return string|void The URL of the saved audio file, or void if an error occurs.
 */
function blp_bpai_convert_text_to_speech( $text ) {
	$api_key  = blp_bpai_get_setting( 'bpai_voice_rss_key' );
	$endpoint = 'http://api.voicerss.org/';

	$params = array(
		'key' => $api_key,
		'hl'  => 'en-us',
		'v'   => 'Mary',
		'src' => $text,
		'c'   => 'MP3',
		'f'   => '44khz_16bit_stereo',
	);

	$response = wp_remote_get( add_query_arg( $params, $endpoint ) );

	if ( is_wp_error( $response ) ) {
		return;
	}

	$audio_data = wp_remote_retrieve_body( $response );

	// Save the audio file.
	$file_name  = 'ai_audio_' . time() . '.mp3';
	$upload_dir = wp_upload_dir();
	$file_path  = $upload_dir['path'] . '/' . $file_name;
	file_put_contents( $file_path, $audio_data );

	return $upload_dir['url'] . '/' . $file_name;
}

/**
 * Retrieves the content of the main activity associated with a comment activity.
 *
 * This function takes a comment activity ID, finds its parent activity,
 * and returns the content of that parent activity.
 *
 * @param int $comment_activity_id The ID of the comment activity. Default is 0.
 *
 * @return string The content of the main activity associated with the comment,
 *                or an empty string if no activity is found.
 */
function blp_bpair_get_activity_content( $comment_activity_id = 0 ) {

	if ( ! function_exists( 'bp_activity_get_specific' ) ) {
		return;
	}

	$content = '';

	$activity = bp_activity_get_specific(
		array(
			'activity_ids'     => array( $comment_activity_id ),
			'display_comments' => true,
		)
	);

	if ( ! empty( $activity ) ) {
		$main_activity_id = $activity['activities'][0]->item_id;
		$main_activity    = bp_activity_get_specific(
			array(
				'activity_ids'     => array( $main_activity_id ),
				'display_comments' => true,
			)
		);

		if ( ! empty( $main_activity ) ) {
			$content = $main_activity['activities'][0]->content;
		}
	}

	return $content;
}

/**
 * Checks if a user has posted in a specific group before.
 *
 * This function uses the BuddyPress Activity API to count the number of activity updates
 * made by a user in a specific group. It filters the activity updates based on the user ID
 * and the group component.
 *
 * @param int $user_id The ID of the user to check.
 * @param int $group_id The ID of the group to check.
 *
 * @return int The total number of activity updates made by the user in the group.
 *             Returns 0 if the BuddyPress Activity API is not available.
 */
function bpai_has_user_posted_group_activity( $user_id, $group_id ) {

	if ( ! function_exists( 'bp_activity_get' ) ) {
		return 0;
	}

	// Check if this user has posted in this group before.
	$user_group_activity = bp_activity_get(
		array(
			'user_id__in'      => array( $user_id ),
			'count_total_only' => true,
			'filter_query'     => array(
				'relation' => 'AND',
				array(
					'column' => 'type',
					'value'  => 'activity_update',
				),
				array(
					'column' => 'component',
					'value'  => 'groups',
				),
				array(
					'column' => 'item_id',
					'value'  => $group_id,
				),
			),
		)
	);

	return isset( $user_group_activity['total'] ) ? intval( $user_group_activity['total'] ) : 0;
}

/**
 * Retrieves the auto responder settings from the WordPress options table.
 *
 * This function retrieves the 'bpai_auto_responder' option from the WordPress options table
 * and returns its value.
 *
 * @return int The auto responder user id.
 */
function bpai_get_auto_responder() {
	return get_option( 'bpai_auto_responder' );
}
