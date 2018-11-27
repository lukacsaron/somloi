<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
require_once LSCF_PLUGIN_PATH . '_controllers/shortcodes_controller.php';

$lscf_shortcode_controller = new LscfShortcodesController();

//count how many times the shortcode has loaded into same page.
$lscf_main_shortcode_counter = 0;
/**
 * The plugin shorcode function.
 *
 * @param array $atts Injected by add_shorcode hook.
 * @var function
 */
function px_filter_shortcode( $atts ) {
	global $lscf_main_shortcode_counter;
	$lscf_main_shortcode_counter++;

	$active_user = wp_get_current_user();
	$is_administrator = false;

	$lscf_main_controller = new PluginMainController();
	$lscf_main_controller->init_administrator_frontend_editor();

	$is_wpml = 0;
	$lang = null;

	if ( $lang = lscf_init_wpml_lang() ) {
		$is_wpml = 1;
	}

	if ( in_array( 'administrator', $active_user->roles, true ) ) {
		$is_administrator = true;
	}

	if ( isset( PluginMainController::$plugin_settings['options'] ) ) {
		$lscf_settings = PluginMainController::$plugin_settings['options'];
	} else {
		$lscf_settings = null;
	}

	$shortcode_attributes = shortcode_atts(
		array(
			'fields_ids' 	  				  => '',
			'id'			  				  => '',
			'post_type'		  				  => '',
			'featured_label'  				  => '',
			'only_posts_show' 				  => '',
			'filter_type' 	  				  => '',
			'view_type'		  				  => '',
			'lscf-demo-frontend-editor'		  => '',
			'display'						  => '',
			'redirect_page'					  => '',
			),
		$atts
	);

	$fields_ids = explode( ',', $shortcode_attributes['fields_ids'] );

	$filter_id = $shortcode_attributes['id'];

	$filter_data = get_option( PluginMainModel::$meta_name_plugin_settings, true );

	$filter_data = json_decode( $filter_data, true );

	$options = array();


	$writing = array(
		'load_more' 	=> __( 'Load more', 'lscf' ),
		'view'			=> __( 'View', 'lscf' ),
		'any'			=> __( 'Any', 'lscf' ),
		'select'		=> __( 'Select', 'lscf' ),
		'filter'		=> __( 'Filter', 'lscf' ),
		'add_to_cart' 	=> __( 'Add to Cart', 'lscf' ),
		'see_more'		=> __( 'See More', 'lscf' ),
		'see_less'		=> __( 'See Less', 'lscf' ),
		'no_results'	=> __( 'No Results', 'lscf' ),
		'sort_by'		=> __( 'Sort By', 'lscf' ),
		'sort_asc'		=> __( 'ASC', 'lscf' ),
		'sort_desc'		=> __( 'DESC', 'lscf' ),
		'date'			=> __( 'Date', 'lscf' ),
		'title'			=> __( 'Title', 'lscf' ),
		'close'			=> __( 'Close Filter', 'lscf' ),
		'search'		=> __( 'Search', 'lscf' ),
		'price'			=> __( 'Price', 'lscf' ),
		'grid'			=> __( 'Grid', 'lscf' ),
		'reset_filters' => __( 'Reset Filters', 'lscf' ),
	);

	if ( isset( $lscf_settings['writing'] ) ) {

		foreach ( $lscf_settings['writing'] as $key => $word ) {
			$writing[ $key ] = __( $word, 'lscf' );
		}
	}

	$options['writing'] = $writing;
	$general_search_by_default_data = array(
		'items' => array(
			array(
				'id' => 'post_title',
				'name' => 'Title',
			),
			array(
				'id' => 'post_content',
				'name' => 'Post Content',
			),
		)
	);

	if ( isset( $shortcode_attributes['only_posts_show'] ) && '1' === $shortcode_attributes['only_posts_show'] ) {

		$filter_data = array();

		$filter_data['only_posts_show'] = 1;
		$filter_data['view_type'] = $shortcode_attributes['view_type'];

		$posts_per_page = ( isset( $lscf_settings['posts_per_page']['posts_only'] ) ? (int) $lscf_settings['posts_per_page']['posts_only'] : 16 );

		$theme_settings = ( isset( $filter_data['settings'] ) ? $filter_data['settings'] : array() );

	} else {

		if ( ! isset( $filter_data['filterList'] ) ) {
			return;
		}
		if ( ! isset( $filter_data['filterList'][ $filter_id ] ) ) {
			return;
		}

		$lscf_main_controller->enqueue_styles_and_scripts_fronend();

		$filter_data = $filter_data['filterList'][ $filter_id ];
		if ( isset( $filter_data['options'] ) && isset( $filter_data['options']['order_by'] ) && isset( $filter_data['options']['order_by']['items'] ) ) {
			if ( is_array( $filter_data['options']['order_by']['items'] ) ) {
				foreach ( $filter_data['options']['order_by']['items'] as &$item ) {
					$item['name'] = __( $item['name'], 'lscf' );
				}
			}
		};

		$posts_per_page = ( isset( $lscf_settings['posts_per_page']['posts_only'] ) ? (int) $lscf_settings['posts_per_page']['filter'] : 15 );

		$options['reset_button'] = ( isset( $lscf_settings['reset_button'] ) ?  $lscf_settings['reset_button'] : 0 );
		$options['grid_view'] = ( isset( $lscf_settings['block_view'] ) ? (int) $lscf_settings['block_view'] : 0 );
		$options['see_more'] = ( ! isset( $lscf_settings['see_more']['writing'] ) ? 'See More' : sanitize_text_field( $lscf_settings['see_more']['writing'] ) );

		$theme_settings = ( isset( $filter_data['settings'] ) ? $filter_data['settings'] : array() );

		$posts_per_page = ( isset( $theme_settings['posts-per-page'] ) ? (int) $theme_settings['posts-per-page'] : $posts_per_page );

		$theme_settings['is_administrator'] = ( true === $is_administrator || 1 == ( int ) $shortcode_attributes['lscf-demo-frontend-editor'] ? 1 : 0 );

	};

	$theme_settings['redirect_page'] = $shortcode_attributes['redirect_page'];


	$options['filter_type'] = ( isset( $filter_data['filter_type'] ) ? $filter_data['filter_type'] : 'custom-posts' );
	$options['run_shortcodes'] = ( isset( $filter_data['options']['run_shortcodes'] ) ? (int) $filter_data['options']['run_shortcodes']  : 0 );
	$options['disable_empty_option_on_filtering'] = ( isset( $filter_data['options']['disable_empty_option_on_filtering'] ) ? (int) $filter_data['options']['disable_empty_option_on_filtering'] : 0 );

	$options['infinite_scrolling'] = ( isset( $filter_data['options']['infinite_scrolling'] ) ? (int) $filter_data['options']['infinite_scrolling'] : 0 );

	$options['checkboxes_conditional_logic'] = ( isset( $filter_data['options']['checkboxes_conditional_logic'] ) ? $filter_data['options']['checkboxes_conditional_logic'] : 'or' );

	$options['lscf_custom_fields_order_as'] = ( isset( $filter_data['options']['lscf_custom_fields_order_as'] ) ? $filter_data['options']['lscf_custom_fields_order_as'] : 'number' );

	$options['order_by'] = ( isset( $filter_data['options']['order_by'] ) ?  $filter_data['options']['order_by']  : array( 'items' => '' ) );
	$options['default_order_by'] = ( isset( $filter_data['options']['default_order_by'] ) ?  $filter_data['options']['default_order_by']  : 'post_date' );

	$options['url_history'] = ( isset( $filter_data['options']['url_history'] ) ?  $filter_data['options']['url_history']  : 0 );
	$options['instant_search'] = ( isset( $filter_data['options']['instant_search'] ) ?  $filter_data['options']['instant_search'] : 1 );
	$options['general_search_by'] = ( isset( $filter_data['options']['general_search_by'] ) ? $filter_data['options']['general_search_by'] : $general_search_by_default_data );

	$options['general_search_conditional_logic'] = ( isset( $filter_data['options']['general_search_conditional_logic'] ) ? $filter_data['options']['general_search_conditional_logic'] : 'and' );

	$options['general_search_algorithm'] = ( isset( $filter_data['options']['general_search_algorithm'] ) ? $filter_data['options']['general_search_algorithm'] : 'algorithm-1' );

	$options['range_filtering_type'] = ( isset( $filter_data['options']['range_filtering_type'] ) ? $filter_data['options']['range_filtering_type'] : 'lscf_number' );

	$options['hide_see_more_on_checkboxes_list'] = ( isset( $filter_data['options']['hide_see_more_on_checkboxes_list'] ) ? $filter_data['options']['hide_see_more_on_checkboxes_list'] : 0 );

	$options['woo_price_format'] = ( isset( $filter_data['options']['woo_price_format'] ) ? $filter_data['options']['woo_price_format'] : 0 );
	$options['woo_instock'] = ( isset( $filter_data['options']['woo_instock'] ) ? $filter_data['options']['woo_instock'] : 0 );

	if ( isset( $theme_settings['reset_button'] ) && isset( $theme_settings['reset_button']['name'] ) ) {
		$theme_settings['reset_button']['name'] = __( $theme_settings['reset_button']['name'], 'lscf' );
	}

	ob_start();

	?>
	<script type='text/javascript'>

		if ( 'undefined' === typeof lscfShortcodeData ) {
			var lscfShortcodeData = [];
		}

		// split wp-content string to avoid the url to be rewriten by some CDN providers
		lscfShortcodeData.push({
			'ID':'<?php echo esc_attr( $filter_id ); ?>',
			'wpml': '<?php echo $is_wpml; ?>',
			'lang': '<?php echo esc_attr( $lang );?>',
			'postType':'<?php echo esc_attr( $shortcode_attributes['post_type'] );?>',
			'post_per_page':'<?php echo (int) $posts_per_page; ?>',
      'plugin_url':'https://n-somloi.local/app/plugins/live-search-and-custom-fields/',
			'site_url':'https://n-somloi.local/wp',
      // bedrock issue spotted by aron 
			// 'plugin_url':'<?php echo esc_url( site_url() )?>/wp' + '-content/plugins/live-search-and-custom-fields/',
			// 'site_url':'<?php echo esc_url( site_url() )?>',
			'ajax_url':'<?php echo esc_url( admin_url( 'admin-ajax.php' ) ) ?>',
			'rest_api_uri': '<?php echo esc_url( get_rest_url() ) ?>',
			'options':<?php echo wp_json_encode( $options );?>,
			'settings':<?php echo wp_json_encode( $theme_settings ); ?>
		});

		document.getElementsByTagName("body")[0].setAttribute("ng-app", "lscf-app");

	</script>

	<?php
	include LSCF_PLUGIN_PATH . '_views/frontend/filter.php';

	return ob_get_clean();
}
add_shortcode( 'px_filter', 'px_filter_shortcode' );
add_shortcode( 'lscf_customfield', array( $lscf_shortcode_controller, 'init_shortcode_custom_fields' ) );

add_action( 'wp', 'lscf_init_shortcode_styles' );
/**
 * Init the dynamic style after the shortcode is loaded.
 *
 * @var function
 */
function lscf_init_shortcode_styles() {

	global $post, $lscf_main_controller;

	if ( ! isset( $post->post_content ) ) {
		return;
	}

	if ( preg_match( '/\[px_filter\s*id=\"(.+?)\"(.+?)\]/', $post->post_content, $matches ) ) {

		$filter_id = $matches[1];
		$dynamic_css = $lscf_main_controller->generate_style_dynamic_color_css( $filter_id );

		wp_enqueue_style( 'px_base', LSCF_PLUGIN_URL . 'assets/css/base.css', false );
		wp_add_inline_style( 'px_base', $dynamic_css );

	} else {
		return;
	}
}

/**
 * Checks if WPML is installed and active. If active returns the active language.
 *
 * @var function
 */
function lscf_init_wpml_lang() {

	if ( ! function_exists( 'icl_object_id' ) ) { 
		return false;
	}

	global $sitepress;
	$current_lang = $sitepress->get_current_language();
	return $current_lang;
}
