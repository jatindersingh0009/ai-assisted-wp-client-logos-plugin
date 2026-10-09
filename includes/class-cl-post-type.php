<?php
defined( 'ABSPATH' ) || exit;

class CL_Post_Type {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_filter( 'manage_' . CL_POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . CL_POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
	}

	public static function register() {
		register_post_type( CL_POST_TYPE, array(
			'labels'              => array(
				'name'               => __( 'Client Logos', 'client-logos' ),
				'singular_name'      => __( 'Logo Section', 'client-logos' ),
				'add_new'            => __( 'Add New', 'client-logos' ),
				'add_new_item'       => __( 'Add New Logo Section', 'client-logos' ),
				'edit_item'          => __( 'Edit Logo Section', 'client-logos' ),
				'all_items'          => __( 'All Logo Sections', 'client-logos' ),
				'search_items'       => __( 'Search Logo Sections', 'client-logos' ),
				'not_found'          => __( 'No logo sections yet.', 'client-logos' ),
				'menu_name'          => __( 'Client Logos', 'client-logos' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'menu_icon'           => 'dashicons-images-alt2',
			'menu_position'       => 25,
			'supports'            => array( 'title' ),
		) );
	}

	public static function columns( $cols ) {
		$new = array();
		foreach ( $cols as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['cl_logos']     = __( 'Logos', 'client-logos' );
				$new['cl_shortcode'] = __( 'Shortcode', 'client-logos' );
			}
		}
		return $new;
	}

	public static function column_content( $col, $post_id ) {
		if ( 'cl_shortcode' === $col ) {
			printf( '<code>[client_logos id="%d"]</code>', (int) $post_id );
		} elseif ( 'cl_logos' === $col ) {
			$logos = get_post_meta( $post_id, CL_Fields::PREFIX . 'logos', true );
			echo (int) ( is_array( $logos ) ? count( $logos ) : 0 );
		}
	}
}
