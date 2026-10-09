<?php
/**
 * Plugin Name:       Client Logos
 * Description:       "Proud members" logo carousel sections. Create a section under Client Logos, then place it with [client_logos id="123"].
 * Version:           1.2.1
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Jatinder Singh
 * Author URI:        https://www.linkedin.com/in/jatindersingh0009
 * License:           GPL-2.0-or-later
 * Text Domain:       client-logos
 */

defined( 'ABSPATH' ) || exit;

define( 'CL_VERSION', '1.2.1' );
define( 'CL_FILE', __FILE__ );
define( 'CL_PATH', plugin_dir_path( __FILE__ ) );
define( 'CL_URL', plugin_dir_url( __FILE__ ) );
define( 'CL_POST_TYPE', 'clientlogos' );

require_once CL_PATH . 'includes/class-cl-fields.php';
require_once CL_PATH . 'includes/class-cl-post-type.php';
require_once CL_PATH . 'includes/class-cl-metabox.php';
require_once CL_PATH . 'includes/class-cl-ajax.php';
require_once CL_PATH . 'includes/class-cl-shortcode.php';

CL_Post_Type::init();
CL_Metabox::init();
CL_Ajax::init();
CL_Shortcode::init();

register_activation_hook( __FILE__, function () {
	CL_Post_Type::register();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
