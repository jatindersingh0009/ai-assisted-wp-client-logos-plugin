<?php
/**
 * AJAX save of the section meta: action "cl_save_meta".
 */

defined( 'ABSPATH' ) || exit;

class CL_Ajax {

	public static function init() {
		add_action( 'wp_ajax_cl_save_meta', array( __CLASS__, 'save' ) );
	}

	public static function save() {
		check_ajax_referer( CL_Metabox::NONCE, 'nonce' );

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$post    = $post_id ? get_post( $post_id ) : null;

		if ( ! $post || CL_POST_TYPE !== $post->post_type ) {
			wp_send_json_error( array( 'message' => __( 'This logo section no longer exists.', 'client-logos' ) ), 404 );
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'You don’t have permission to edit this section.', 'client-logos' ) ), 403 );
		}

		$raw  = isset( $_POST['cl'] ) ? wp_unslash( $_POST['cl'] ) : array(); // phpcs:ignore -- sanitised in CL_Fields.
		$sent = ( isset( $raw['logos'] ) && is_array( $raw['logos'] ) ) ? count( $raw['logos'] ) : 0;
		$data = CL_Fields::save( $post_id, $raw );

		wp_send_json_success( array(
			'logos'   => count( $data['logos'] ),
			'skipped' => max( 0, $sent - count( $data['logos'] ) ),
			'time'    => wp_date( get_option( 'time_format' ) ),
		) );
	}
}
