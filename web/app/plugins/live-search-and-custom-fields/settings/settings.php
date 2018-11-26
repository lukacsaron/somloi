<?php 

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! class_exists( 'PxWpQuery' ) ) {
	include_once LSCF_PLUGIN_PATH . '_helpers/query_helper.php';
}

require_once LSCF_PLUGIN_PATH . '_controllers/http_requests_controller.php';
require_once LSCF_PLUGIN_PATH . '_controllers/api_routes.php';
require_once LSCF_PLUGIN_PATH . '_controllers/cache_controller.php';

include_once LSCF_PLUGIN_PATH . '_controllers/main_controller.php';
include_once LSCF_PLUGIN_PATH . '_controllers/import_export_controller.php';
include_once LSCF_PLUGIN_PATH . '_controllers/custom_fields_controller.php';
include_once LSCF_PLUGIN_PATH . 'settings/functions.php';

use \Lscf\Controller\LscfPluginActivationController;

$lscf_main_controller = new PluginMainController();
$lscf_export_controller = new LSCFexportImportController();
$lscf_custom_fields_controller = new CustomFieldsController();

$lscf_rest_api = new LscfApiRoutes();
$lscf_rest_api->init_api_routes();

$lscf_cache_controller = new LscfCacheController();
$lscf_cache_controller->init_hooks();

add_filter( 'wp_head', 'lscf_cpt_rss_feed' );
function lscf_cpt_rss_feed( $request ) {

	global $lscf_main_controller;

	if ( isset( $lscf_main_controller::$plugin_settings['generate_the_custom_posts_list'] ) ) {

		$post_types = $lscf_main_controller::$plugin_settings['generate_the_custom_posts_list'];
		foreach ( $post_types as $post_type => $label ) {
			$feed = get_post_type_archive_feed_link( $post_type );
			if ( $feed === '' || ! is_string( $feed ) ) {
				$feed = get_bloginfo( 'rss2_url' ) . '?post_type=' . $post_type;
			}
			printf( __( '<link rel="%1$s" type="%2$s" href="%3$s" />' ), 'alternate', 'application/rss+xml', $feed );
		}
	}
}

add_action( 'edit_form_after_editor', array( $lscf_custom_fields_controller, 'display_custom_fields_meta_box' ) );
$lscf_custom_fields_controller->init_hooks();

add_action( 'admin_menu', 'add_px_lf_plugin_page' );

/**
 * Init plugin admin page
 *
 * @var function
 */
function add_px_lf_plugin_page() {

	global $lscf_icon_url, $lscf_main_controller, $lscf_export_controller;

	add_menu_page( 'LSCF', 'LSCF', 'manage_options', 'pxLF_plugin', array( $lscf_main_controller, 'plugin_frontend_init' ), $lscf_icon_url );
	add_submenu_page( 'pxLF_plugin', 'Export/Import', 'Export/Import', 'manage_options', 'lscf_export', array( $lscf_export_controller, 'init' ) );
}

add_action( 'init', array( $lscf_main_controller, 'generate_custom_post_type' ) );
add_action( 'init', array( $lscf_main_controller, 'plugin_backend_init' ) );
add_action( 'admin_init', array( $lscf_export_controller, 'export_custom_fields' ) );
add_action( 'admin_init', array( $lscf_export_controller, 'export_custom_posts' ) );
add_action( 'admin_init', array( $lscf_export_controller, 'import_custom_posts' ) );
add_action( 'admin_init', array( $lscf_export_controller, 'import_custom_fields' ) );





/**
 * Enqueue styles and script - backend only
 *
 * @var function
 */
function px_enqueue_plugin_scripts_snd_styles_backend() {

	 wp_enqueue_script( 'jquery-ui-core' );

	 wp_enqueue_script( 'jquery-ui-widget' );
	 wp_enqueue_script( 'jquery-ui-datepicker' );
	 wp_enqueue_script( 'jquery-ui-draggable' );
	 wp_enqueue_script( 'jquery-ui-droppable' );
	 wp_enqueue_script( 'jquery-ui-mouse' );
	 wp_enqueue_script( 'jquery-ui-sortable' );


	wp_register_script( 'wp_custom_functions', LSCF_PLUGIN_URL . 'assets/js/wp_functions.js', '', '', true );
	wp_enqueue_script( 'wp_custom_functions' );

	wp_register_script( 'px_custom_select_box', LSCF_PLUGIN_URL . 'assets/js/lib/custom-select-box.js', false, 1.0 );
	wp_enqueue_script( 'px_custom_select_box', array( 'jquery' ), '1.0', true );

	wp_register_script( 'px_source', LSCF_PLUGIN_URL . 'assets/source.js', false, 1.1 );
	wp_enqueue_script( 'px_source',  LSCF_PLUGIN_URL . 'assets/source.js', array( 'jquery' ), '1.0', true );
	wp_localize_script( 'px_source', 'adminData', array( 'lscf_url' => LSCF_PLUGIN_URL, 'ajaxURL' => admin_url( 'admin-ajax.php' ) ) );

	wp_register_script( 'px_vendor', LSCF_PLUGIN_URL . 'assets/vendor.js', false, 1.0 );
	wp_enqueue_script( 'px_vendor' );


	wp_register_style( 'px_plugin_style', LSCF_PLUGIN_URL . 'assets/css/wpbackend.css', false, 1.0 );
	wp_enqueue_style( 'px_plugin_style' );
	wp_register_style( 'slick', LSCF_PLUGIN_URL . 'assets/vendor/slick/slick.css', false, 1.0 );
	wp_enqueue_style( 'slick' );
	wp_register_style( 'jquery-ui-style', LSCF_PLUGIN_URL . 'assets/css/jquery-ui.min.css', false, 1.0 );
	wp_enqueue_style( 'jquery-ui-style' );

	wp_enqueue_media();

}

add_action( 'wp_enqueue_scripts', array( $lscf_main_controller, 'px_register_plugin_scripts_and_styles_frontend' ), 11 );
add_action( 'admin_enqueue_scripts', 'px_enqueue_plugin_scripts_snd_styles_backend' );

// setting ajax request POST action type.
add_action( 'wp_ajax_nopriv_px-plugin-ajax',  array( $lscf_main_controller, 'px_plugin_lf_ajax_request' ) );
add_action( 'wp_ajax_px-plugin-ajax', array( $lscf_main_controller, 'px_plugin_lf_ajax_request' ) );

// setting ajax request POST action type.
add_action( 'wp_ajax_nopriv_lscf-administrator-ajax',  array( $lscf_main_controller, 'administrator_ajax_requests' ) );
add_action( 'wp_ajax_lscf-administrator-ajax', array( $lscf_main_controller, 'administrator_ajax_requests' ) );


// setting http angular requests.
add_action( 'wp_ajax_nopriv_px-ang-http',  array( new HttpRequestsController, 'init_http_requests' ) );
add_action( 'wp_ajax_px-ang-http', array( new HttpRequestsController, 'init_http_requests' ) );


/**
 * Create custom blank page template
 *
 * @param string $template Template data.
 * @var function
 */
function px_custom_page_template( $template ) {

	$template = LSCF_PLUGIN_PATH . 'page-capf-custom-template.php';

	return $template;

}

add_action( 'after_setup_theme', array( $lscf_main_controller, 'theme_supports' ) );


// Single page - fields view.
add_filter( 'the_content', 'load_custom_fields_view' );

/**
 * Display custom fields values inside Post or Page.
 *
 * @param string $content The Post's content injected by add_filter hook.
 * @var function
 */
function load_custom_fields_view( $content ) {

	if ( ! is_single() || is_feed() || ! in_the_loop() ) {
		return $content;
	}

	global $post;

	$custom_fields_model = new CustomFieldsModel();

	$data = $custom_fields_model->get_post_custom_fields( $post->ID );

	ob_start();

	include_once LSCF_PLUGIN_PATH . '_views/frontend/custom-fields-single-page.php';

	return $content . ob_get_clean();

}


$lscf_sidebar_args = array(
	'name'          => __( 'LSCF Sidebar', 'lscf' ),
	'id'            => 'lscf_custom_sidebar',
	'description'   => '',
	'class'         => 'lscf-custom-sidebar',
	'before_widget' => '<li id="%1$s" class="widget %2$s">',
	'after_widget'  => '</li>',
	'before_title'  => '<h2 class="widgettitle">',
	'after_title'   => '</h2>',
);

register_sidebar( $lscf_sidebar_args );

/**
 * Add the LSCF ang app to body.
 *
 * @var function
 */
function lscf_add_angular_app_attr( $classes ) {

	$classes[] = '" ng-app="lscf-app';
	return $classes;
}
add_filter( 'body_class', 'lscf_add_angular_app_attr', 9999999 );


