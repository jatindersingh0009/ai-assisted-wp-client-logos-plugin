<?php
/**
 * [client_logos id="123"]
 */

defined( 'ABSPATH' ) || exit;

class CL_Shortcode {

	public static function init() {
		add_shortcode( 'client_logos', array( __CLASS__, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
	}

	public static function register_assets() {
		if ( apply_filters( 'client_logos_load_font', true ) ) {
			wp_register_style( 'cl-font', 'https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;700&display=swap', array(), null );
			$deps = array( 'cl-font' );
		} else {
			$deps = array();
		}
		wp_register_style( 'cl-frontend', CL_URL . 'assets/css/frontend.css', $deps, CL_VERSION );
		wp_register_script( 'cl-frontend', CL_URL . 'assets/js/frontend.js', array(), CL_VERSION, true );
	}

	public static function render( $atts ) {
		$atts    = shortcode_atts( array( 'id' => 0, 'class' => '' ), $atts, 'client_logos' );
		$post_id = absint( $atts['id'] );
		$post    = $post_id ? get_post( $post_id ) : null;

		if ( ! $post || CL_POST_TYPE !== $post->post_type ) {
			return current_user_can( 'edit_posts' )
				? '<p><em>' . esc_html( sprintf( __( 'Client Logos: no section found with ID %d.', 'client-logos' ), $post_id ) ) . '</em></p>'
				: '';
		}
		if ( 'publish' !== $post->post_status && ! current_user_can( 'edit_post', $post_id ) ) {
			return '';
		}

		$data = CL_Fields::get( $post_id );

		if ( ! wp_style_is( 'cl-frontend', 'registered' ) ) {
			self::register_assets();
		}
		wp_enqueue_style( 'cl-frontend' );
		wp_enqueue_script( 'cl-frontend' );

		// Theme override: yourtheme/client-logos/section.php
		$template = locate_template( 'client-logos/section.php' );
		if ( ! $template ) {
			$template = CL_PATH . 'templates/section.php';
		}

		$uid       = 'cl-' . $post_id . '-' . wp_unique_id();
		$css_class = sanitize_html_class( $atts['class'] );

		ob_start();
		include $template;
		return ob_get_clean();
	}
}
