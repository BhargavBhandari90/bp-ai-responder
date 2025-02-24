/* global  */

/**
 * Custom JS
 */

( function ( $ ) {

	"use strict";

	window.BPAI_Script = {

		init: function () {
			console.log( 'The BPAI Custom Script Loaded.' );
			this.bpai_generate_response();
		},

		bpai_generate_response : function() {
			$(document).on('click', '.ai-audio-responder-button', function() {
				var commentId = $(this).data('comment-id');
				console.log(commentId);
				
				var commentText = $('#activity-comment-' + commentId).text();
		
				$.ajax({
					url: ajaxurl, // WordPress AJAX URL
					type: 'POST',
					data: {
						action: 'generate_audio_response',
						comment_text: commentText,
						comment_id: commentId
					},
					success: function(response) {
						if (response.success) {
							var audioUrl = response.data.audio_url;
							var audioElement = '<audio controls><source src="' + audioUrl + '" type="audio/mpeg"></audio>';
							$('#activity-comment-' + commentId).append(audioElement);
						}
					}
				});
			});
		}
	}

	$( document ).on( 'ready', function () {
		BPAI_Script.init();
	});

} )( jQuery );
