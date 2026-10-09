<?php
/**
 * Field definitions, reading and sanitising post meta.
 * Content fields are stored in their own meta keys (_cl_title, _cl_logos, …);
 * slider settings are stored together in _cl_slider.
 */

defined( 'ABSPATH' ) || exit;

class CL_Fields {

	const PREFIX = '_cl_';

	/** Text fields: key => sanitiser. */
	public static function text_keys() {
		return array(
			'title'             => 'sanitize_text_field',
			'subtitle'          => 'sanitize_text_field',
			'description'       => 'sanitize_textarea_field',
			'additional_field1' => 'sanitize_text_field',
			'additional_field2' => 'sanitize_text_field',
			'additional_field3' => 'sanitize_text_field',
		);
	}

	/* ---------------- Slider settings ---------------- */

	public static function slider_defaults() {
		return array(
			'autoplay'    => 1,
			'delay'       => 3,    // seconds between moves
			'speed'       => 700,  // ms for one move
			'pause_hover' => 1,
			'per_desktop' => 4,
			'per_tablet'  => 2,
			'per_mobile'  => 1,
			'arrows'      => 1,
			'dots'        => 1,
		);
	}

	/** Numeric settings: key => [ min, max, step ]. */
	public static function slider_limits() {
		return array(
			'delay'       => array( 1, 20, 0.5 ),
			'speed'       => array( 200, 3000, 50 ),
			'per_desktop' => array( 1, 6, 1 ),
			'per_tablet'  => array( 1, 4, 1 ),
			'per_mobile'  => array( 1, 3, 1 ),
		);
	}

	public static function slider_toggles() {
		return array( 'autoplay', 'pause_hover', 'arrows', 'dots' );
	}

	public static function sanitize_slider( $raw ) {
		$raw = is_array( $raw ) ? $raw : array();
		$out = self::slider_defaults();

		foreach ( self::slider_toggles() as $key ) {
			$out[ $key ] = ! empty( $raw[ $key ] ) ? 1 : 0;
		}
		foreach ( self::slider_limits() as $key => $lim ) {
			if ( ! isset( $raw[ $key ] ) || '' === $raw[ $key ] ) {
				continue; // keep default
			}
			$val = (float) $raw[ $key ];
			$val = round( $val / $lim[2] ) * $lim[2];
			$val = max( $lim[0], min( $lim[1], $val ) );
			$out[ $key ] = ( 1 == $lim[2] || 50 == $lim[2] ) ? (int) $val : $val; // phpcs:ignore
		}
		return $out;
	}

	/* ---------------- Read / save ---------------- */

	public static function defaults() {
		$d = array_fill_keys( array_keys( self::text_keys() ), '' );
		$d['bg_image'] = 0;
		$d['logos']    = array();
		$d['slider']   = self::slider_defaults();
		return $d;
	}

	/**
	 * All section data as a PHP array.
	 *
	 * logos  => [ [ 'id' => 12, 'alt' => 'Name', 'link' => 'https://…' ], … ]
	 * slider => see slider_defaults()
	 */
	public static function get( $post_id ) {
		$data = self::defaults();

		foreach ( array_keys( self::text_keys() ) as $key ) {
			$data[ $key ] = (string) get_post_meta( $post_id, self::PREFIX . $key, true );
		}

		$data['bg_image'] = absint( get_post_meta( $post_id, self::PREFIX . 'bg_image', true ) );

		$logos         = get_post_meta( $post_id, self::PREFIX . 'logos', true );
		$data['logos'] = is_array( $logos ) ? array_values( $logos ) : array();

		$slider = get_post_meta( $post_id, self::PREFIX . 'slider', true );
		if ( ! is_array( $slider ) ) {
			// Sections saved with v1.1 kept autoplay in separate keys.
			$slider   = array();
			$autoplay = get_post_meta( $post_id, self::PREFIX . 'autoplay', true );
			$delay    = get_post_meta( $post_id, self::PREFIX . 'autoplay_delay', true );
			if ( '' !== $autoplay ) {
				$slider['autoplay'] = (int) $autoplay;
			}
			if ( '' !== $delay ) {
				$slider['delay'] = $delay;
			}
		}
		$data['slider'] = wp_parse_args( $slider, self::slider_defaults() );

		return apply_filters( 'client_logos_data', $data, $post_id );
	}

	/**
	 * Sanitise raw (already unslashed) input from the form.
	 */
	public static function sanitize( $raw ) {
		$raw = is_array( $raw ) ? $raw : array();
		$out = self::defaults();

		foreach ( self::text_keys() as $key => $cb ) {
			$out[ $key ] = isset( $raw[ $key ] ) ? call_user_func( $cb, $raw[ $key ] ) : '';
		}

		$bg              = isset( $raw['bg_image'] ) ? absint( $raw['bg_image'] ) : 0;
		$out['bg_image'] = ( $bg && wp_attachment_is_image( $bg ) ) ? $bg : 0;

		if ( ! empty( $raw['logos'] ) && is_array( $raw['logos'] ) ) {
			foreach ( $raw['logos'] as $row ) {
				$id = isset( $row['id'] ) ? absint( $row['id'] ) : 0;
				if ( ! $id || ! wp_attachment_is_image( $id ) ) {
					continue; // Rows without an image are skipped.
				}
				$out['logos'][] = array(
					'id'   => $id,
					'alt'  => isset( $row['alt'] ) ? sanitize_text_field( $row['alt'] ) : '',
					'link' => isset( $row['link'] ) ? esc_url_raw( $row['link'] ) : '',
				);
			}
		}

		$out['slider'] = self::sanitize_slider( isset( $raw['slider'] ) ? $raw['slider'] : array() );

		return $out;
	}

	/**
	 * Sanitise and save. Returns the saved data.
	 */
	public static function save( $post_id, $raw ) {
		$data = self::sanitize( $raw );

		foreach ( $data as $key => $value ) {
			$meta_key = self::PREFIX . $key;
			if ( '' === $value || 0 === $value || array() === $value ) {
				delete_post_meta( $post_id, $meta_key );
			} else {
				update_post_meta( $post_id, $meta_key, $value );
			}
		}

		// Remove v1.1 keys now that settings live in _cl_slider.
		delete_post_meta( $post_id, self::PREFIX . 'autoplay' );
		delete_post_meta( $post_id, self::PREFIX . 'autoplay_delay' );

		do_action( 'client_logos_saved', $post_id, $data );
		return $data;
	}
}
