<?php 
/**
	Plugin Name: Live Search & Custom Fields
	Plugin URI: https://wp.pixolette.com/plugins/live-search-custom-filter/
	Text Domain: lscf
	Description: Make your own live filter for custom posts. Add add custom fields for your filter and custom posts.
	Author: Pixolette
	Version: 2.4.2
	Author URI: https://pixolette.com
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define( 'LSCF', 1 );
define( 'LSCF_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'LSCF_PLUGIN_URL', plugins_url() . '/live-search-and-custom-fields/' );

$lscf_icon_url = LSCF_PLUGIN_URL . 'assets/images/icons/panda-white-16x16.png';

$main_path = explode( 'wp-content', LSCF_PLUGIN_URL );
$main_path = array_shift( $main_path );


define( 'LSCF_MAIN_PATH', $main_path );

add_action( 'plugins_loaded', 'lscf_load_textdomain' );

/**
 * Load LSCF textdomain.
 *
 */
function lscf_load_textdomain() {
  load_plugin_textdomain( 'lscf', false, '../themes/' . lscf_return_theme_languages_path() ); 
}

include LSCF_PLUGIN_PATH . 'settings/settings.php';
include LSCF_PLUGIN_PATH . 'Class_createTemplate.php';
include LSCF_PLUGIN_PATH . 'shortcode.php';
