<?php 

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
require_once LSCF_PLUGIN_PATH . '_models/main_model.php';

/**
 * Class PluginMainController The main Controller
 *
 * @category Model
 * @package  MainController
 * @author   PIXOLETTE
 * @license  http://www.pixollete.com
 * @link     http://www.pixollete.com
 **/
class PluginMainController {

	/**
	 * Store the plugin general settings.
	 *
	 * @access public
	 * @var array
	 */
	public static $plugin_settings;

	/**
	 * Store all registered Custom Fields Data
	 *
	 * @access public
	 * @var array
	 */
	public static $custom_fields_data;

	/**
	 * The plugin current path.
	 *
	 * @access public
	 * @var string
	 */
	public $pluginpath;

	/**
	 * Will store the Filter's Active custom post types
	 *
	 * @access public
	 * @var array
	 */
	public static $filter_custom_posts_type_list;

	/**
	 * Will store all the public Custom Post Types from Wordpress
	 *
	 * @access public
	 * @var array
	 */
	public static $post_types_list;

	/**
	 * The custom fields list that are added by current plugin, Data stored into an array.
	 *
	 * @access public
	 * @var array
	 */
	public static $custom_fields_opt = array(
		'px_date' => array( 'name' => 'Date' ),
		'px_text' => array( 'name' => 'Text' ),
		'px_select_box' => array( 'name' => 'Select' ),
		'px_radio_box' => array( 'name' => 'Radio' ),
		'px_check_box' => array( 'name' => 'Checkbox' ),
		'px_icon_check_box' => array( 'name' => 'Checkbox /w icons' ),
		'px_cf_relationship' => array( 'name' => 'Variation/Relationship' ),
	);

	/**
	 * Model of main db queries. Plugin's Main Model
	 *
	 * Methods:
	 *		update_plugin_settings,
	 *		get_post_categories,
	 *		get_post_type_custom_fields,
	 *		fetch_posts_type_list,
	 *		update_custom_fields_options
	 *
	 * @access private
	 * @var Class|Object
	 */
	private $model;

	/**
	 * The Class constructor.
	 *
	 * @access public
	 * @var function|Class constructor
	 */
	function __construct() {

		$this->pluginpath = LSCF_PLUGIN_PATH;

		$model = new PluginMainModel();

		$this->model = $model;

		self::$plugin_settings = $model->plugin_settings;

		self::$custom_fields_data = $model->data_option;

	}

	/**
	 * Init the Administrator frontend editor.
	 *
	 * @access public
	 * @var function
	 */
	public function init_administrator_frontend_editor() {

		$active_user = wp_get_current_user();
		if ( ! in_array( 'administrator', $active_user->roles, true ) ) {
			return;
		}
		$this->model->create_custom_mysql_functions();
		$this->init_custom_templates();
	}

	/**
	 * Init all backend functions & actions. Loads before any HTML code loaded.
	 * Send headers, save Forms data
	 * loaded via add_action hook
	 *
	 * @access public
	 * @var function|Class Method
	 */
	public function plugin_backend_init() {

		if ( isset( $_GET['doAction'] ) && 'saveCFP' == $_GET['doAction'] ) {

			$this->save_data();

			wp_redirect( admin_url() . 'admin.php?page=pxLF_plugin&plugin-tab=post-fields' );

			die();
		}

		if ( isset( $_GET['plugin-tab'] ) && ( 'filter-generator' == $_GET['plugin-tab'] || 'general-opt' == $_GET['plugin-tab'] ) ) {

			$this->init_custom_templates();
			$this->model->create_custom_mysql_functions();
		}

	}

	/**
	 * Adds the custom templates to plugin list.
	 *
	 * @access public
	 * @var function|Class Method
	 */
	public function init_custom_templates() {

		$model = $this->model;

		if ( $custom_templates = $this->get_custom_templates_links() ) {

			self::$plugin_settings['custom_templates'] = $custom_templates;

		} else {
			unset( self::$plugin_settings['custom_templates'] );

		}

		$model->plugin_settings = self::$plugin_settings;
		$model->update_plugin_settings();
	}

	/**
	 * Checks if theme has custom templates.
	 * Returns array if child directory has templates. Return false if theme has no templates.
	 *
	 * @access public
	 * @var function|Class Method
	 */
	public function get_custom_templates_links() {

		$plugin_child_theme_path = get_template_directory() . '/lscf-templates/';
		$plugin_child_theme_url = get_template_directory_uri() . '/lscf-templates/';

		$custom_templates = array();

		if ( ! file_exists( $plugin_child_theme_path ) ) {

			$plugin_child_theme_path = get_stylesheet_directory() . '/lscf-templates/';
			$plugin_child_theme_url = get_stylesheet_directory_uri() . '/lscf-templates/';

			if ( ! file_exists( $plugin_child_theme_path ) ) {
				return false;
			}
		}

		$count = 0;
		if ( $templates = scandir( $plugin_child_theme_path ) ) {
			foreach ( $templates as $template ) {

				if ( ! preg_match( '/^lscf\-(.+?)\.html$/', $template ) ) {
					continue;
				}

				preg_match( '/^lscf\-(.+?)\.html$/', $template, $matches );

				$name = str_replace( '-', ' ', $matches[1] );

				$custom_templates[ $count ]['slug'] = str_replace( ' ', '-', strtolower( $name ) );
				$custom_templates[ $count ]['name'] = $name;
				$custom_templates[ $count ]['path'] = $plugin_child_theme_path . $template;
				$custom_templates[ $count ]['url'] 	= $plugin_child_theme_url . $template;

				$count++;
			}

			return $custom_templates;
		}

		return false;
	}

	/**
	 * Init theme/plugins supports
	 * Loaded via after_theme_setup action hook
	 *
	 * @access public
	 * @var function|Class Method
	 */
	public function theme_supports() {

		add_theme_support( 'post-thumbnails' );
		$this->add_post_thumbnails_size();

	}

	/**
	 * Init all frontend functionalities for plugin's settings manager from wp-admin.
	 * Loaded via add_action hook
	 *
	 * @access public
	 * @var function|Class Method
	 */
	public function plugin_frontend_init() {

		$model = $this->model;

		$active_page = ( isset( $_GET['page'] ) ?  sanitize_text_field( wp_unslash( $_GET['page'] ) ) : 'pxLF_plugin' );
		$screen = esc_url( get_admin_url() . 'admin.php?page=' . $active_page );

		$active_tab = ( ( ! isset( $_GET['plugin-tab'] ) || '' === $_GET['plugin-tab']  ) ? 'general-opt': $_GET['plugin-tab']) ;

		if ( isset( $_GET['edit_filter'] ) && '' != $_GET['edit_filter'] && isset( self::$plugin_settings['filterList'] ) ) {

			$the_filters_data = self::$plugin_settings['filterList'];

			if ( isset( $the_filters_data[ $_GET['edit_filter'] ] ) ) {

				$filter_data = $the_filters_data[ $_GET['edit_filter'] ];
				$active_tab = 'edit-filter-shortcode';

			} else {
				$filter_data = null;
			}
		}

		// retrieve custom posts type list.
		self::$filter_custom_posts_type_list = $model->plugin_settings['filter-active_postTypes'];

		self::$post_types_list = $model->fetch_posts_types_list();

		include_once $this->pluginpath . '_views/backend/landing.php';

	}


	/**
	 * Register styles and script - frontend only
	 *
	 * @var function
	 */
	public function px_register_plugin_scripts_and_styles_frontend() {

		global $wp_query;

		wp_register_style( 'lscf-bootstrap', LSCF_PLUGIN_URL . 'assets/css/bootstrap/bootstrap.min.css', false );
		wp_register_style( 'px_base', LSCF_PLUGIN_URL . 'assets/css/base.css', false );
		wp_register_script( 'px_vendor', LSCF_PLUGIN_URL . 'assets/vendor.js', '', '', true );
		wp_register_script( 'angular', LSCF_PLUGIN_URL . 'assets/js/angular/angular.1.7.js', '', '', true );
		wp_register_script( 'angular-ngAnimate', LSCF_PLUGIN_URL . 'assets/js/angular/angular_animate.1.7.0.js', '', '', true );
		wp_register_script( 'jquery-ui-core', LSCF_PLUGIN_URL . '/assets/vendor/jquery-ui/jquery-ui.min.js', false, '1.0.0' );
		wp_register_style( 'jquery-ui-style', LSCF_PLUGIN_URL . 'assets/css/jquery-ui.min.css', false, 1.0 );
		wp_register_style( 'jquery-custom-scroll-bar', LSCF_PLUGIN_URL . 'assets/vendor/jquery-custom-scrollbar-master/jquery.custom-scrollbar.css', false, 1.0 );
		wp_register_script( 'angular_sanitize', LSCF_PLUGIN_URL . 'assets/js/angular/angular_sanitize1.7.0.js', '', '', true );
		wp_register_script( 'px_capf', LSCF_PLUGIN_URL . 'assets/app.js', '', '', true );
		wp_register_script( 'px_main', LSCF_PLUGIN_URL . 'assets/main.js', '', '', true );
		wp_localize_script( 'px_capf', 'pxData', array( 'ajaxURL' => admin_url( 'admin-ajax.php' ) ) );

	}

	/**
	 * Enqueue styles and script - frontend only
	 *
	 * @var function
	 */
	public function enqueue_styles_and_scripts_fronend() {

		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-widget' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-mouse' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-touch-punch' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-custom-scroll-bar' );

		wp_enqueue_style( 'lscf-bootstrap' );
		wp_enqueue_style( 'px_base' );
		wp_enqueue_script( 'px_vendor' );
		wp_enqueue_script( 'angular' );
		wp_enqueue_script( 'angular-ngAnimate' );
		wp_enqueue_script( 'jquery-ui' );
		wp_enqueue_style( 'jquery-ui-style' );
		wp_enqueue_style( 'jquery-custom-scroll-bar' );
		wp_enqueue_script( 'angular_sanitize' );
		wp_enqueue_script( 'px_capf' );
		wp_enqueue_script( 'px_main' );

	}

	/**
	 * Generate the dynamic css color
	 *
	 * @param array $color A color object.
	 * @access public
	 * @var function
	 */
	public function generate_dynamic_css_color( $color ) {

		$dynamic_css_color = '
			.lscf-template2-woocommerce-display .lscf-template2-add-to-cart{
				background: ' . esc_attr( $color['hex'] ) . ';
			}
			.lscf-template3-onsale{
				background: ' . esc_attr( $color['hex'] ) . ';
			}
			.lscf-template3-woocommerce-display .lscf-template3-add-to-cart{
				background: ' . esc_attr( $color['hex'] ) . ';
			}
			.lscf-template1-image::before{
				background:rgba(' . esc_attr( $color['rgb'] ) . ', 0.4 );
			}
			.lscf-li-title::before{
				background:rgba(' . esc_attr( $color['rgb'] ) . ', 0.4 );
			}
			.lscf-masonry-grid-cf{
				background:rgba(' . esc_attr( $color['rgb'] ) . ', 0.4 );
			}
			.lscf-woocommerce-grid-cf{
				background:rgba(' . esc_attr( $color['rgb'] ) . ', 0.2 );
			}
			.lscf-woocommerce-grid-2-cf{
				background:rgba(' . esc_attr( $color['rgb'] ) . ', 0.6);
			}
			.viewMode div.active{
				color:' . esc_attr( $color['hex'] ) . ';
			}

			.lscf-sorting-opt .lscf-sort-up.active .glyphicon, .lscf-sorting-opt .lscf-sort-down.active .glyphicon{
				color: ' . esc_attr( $color['hex'] ) . ';
			}
			.lscf-woo-grid-2-onsale{
				background: ' . esc_attr( $color['hex'] ) . '!important;
			}
			.lscf-woo-grid-2-add-to-cart{
				background:rgba(' . esc_attr( $color['rgb'] ) . ', 0.9 )!important;
			}
			.customRange .range_draggable{
				background:' . esc_attr( $color['hex'] ) . ';
			}	
			.customRange label{
				color:' . esc_attr( $color['hex'] ) . ';
			}
			.px_checkbox:after{
				background:' . esc_attr( $color['hex'] ) . ';
			}
			.post-list .price label, .post-list .block-price label{
				color:' . esc_attr( $color['hex'] ) . ';
			}
			.post-list .post-featuredImage .post-overlay{
				background: rgba(' . esc_attr( $color['rgb'] ) . ', 0.7 );
			}
			.view.block-view .block-price{
				background: ' . esc_attr( $color['hex'] ) . ';
			}
			.view.block-view .block-price:before{
				border-bottom:34px solid ' . esc_attr( $color['hex'] ) . ';
			}
			.view.block-view .block-price:after{
				border-top:34px solid ' . esc_attr( $color['hex'] ) . ';
			}
			.px-select-box.active-val div.select{
				border:1px solid ' . esc_attr( $color['hex'] ) . ';
			}
			.pxSearchField .px-focus{
				border:1px solid ' . esc_attr( $color['hex'] ) . ';
			}
			input[type="radio"]:checked + label.pxRadioLabel:before{
				border:1px solid ' . esc_attr( $color['hex'] ) . ';
				background:' . esc_attr( $color['hex'] ) . ';
			}
			input[type="radio"]:checked + label.pxRadioLabel:after{
				border:1px solid ' . esc_attr( $color['hex'] ) . ';
			}
			.px-select-box.active-val div.select .options{
				border:1px solid ' . esc_attr( $color['hex'] ) . ';
			}
			.px-cross .px-cross-child-1, .px-cross .px-cross-child-2{
				background:' . esc_attr( $color['hex'] ) . ';
			}
			.lscf-portrait .block-featuredImage .block-price {
				background:rgba(' . esc_attr( $color['rgb'] ) . ', 0.6 );
			} 
			.lscf-portrait .block-featuredImage .block-price:after{
				border-left:3px solid ' . esc_attr( $color['hex'] ) . ';
				border-bottom:3px solid ' . esc_attr( $color['hex'] ) . ';
			}
			.lscf-portrait .block-featuredImage .block-price:before{
				border-right:3px solid ' . esc_attr( $color['hex'] ) . ';
				border-top:3px solid ' . esc_attr( $color['hex'] ) . ';
			}
			.lscf-portrait .post-overlay{
				background:rgba(' . esc_attr( $color['rgb'] ) . ', 0.9 );
			}
			.lscf-portrait .post-overlay .eyeglass{
				background:rgba(' . esc_attr( $color['rgb'] ) . ', 0.9 );
			}
			.px_capf-field .lscf-see-more{
				color:' . esc_attr( $color['hex'] ) . ';	
			}
			@media (max-width:770px) {
				.px-capf-wrapper .px-filter-fields .px-fiels-wrapper .px-filter-label-mobile{
					border:1px solid ' . esc_attr( $color['hex'] ) . ';
				}
				.px-capf-wrapper .px-filter-fields .px-fiels-wrapper.active .px-filter-label-mobile{
					border-bottom:0px;
				}
				.px-capf-wrapper .px-filter-fields .px-fiels-wrapper .px-field-wrapper-container{
					border:1px solid ' . esc_attr( $color['hex'] ) . ';
				}
			}
		';

		return $dynamic_css_color;
	}

	/**
	 * Generate the dynamic css style
	 *
	 * @param string $filter_id The filter ID.
	 * @access public
	 * @var function
	 */
	public function generate_style_dynamic_color_css( $filter_id ) {

		if ( isset( self::$plugin_settings['filterList'][ $filter_id ]['settings']['main-color'] ) ) {

			$color = self::$plugin_settings['filterList'][ $filter_id ]['settings']['main-color'];
			$rgb_color = ( isset( self::$plugin_settings['filterList'][ $filter_id ]['settings']['main-color-rgb'] ) ? self::$plugin_settings['filterList'][ $filter_id ]['settings']['main-color-rgb'] : '67, 55, 165' );

		} else {

			$color = ( isset( self::$plugin_settings['options']['main-color']['color'] ) ? self::$plugin_settings['options']['main-color']['color'] : '');
			$rgb_color = ( isset( self::$plugin_settings['options']['main-color']['rgb'] ) ? self::$plugin_settings['options']['main-color']['rgb'] : '67, 55, 165' );

		}

		$dynamic_style = $this->generate_dynamic_css_color( array( 'hex' => $color, 'rgb' => $rgb_color ) );

		return $dynamic_style;
	}

	/**
	 * Add a new thumbnails size for Featured Image for each custom post type
	 *
	 * @access public
	 * @var function|Class Method
	 */
	public function add_post_thumbnails_size() {

		if ( count( self::$plugin_settings['generate_the_custom_posts_list'] ) < 1 ) {
			return;
		}

		$array_of_posts_type = self::$plugin_settings['generate_the_custom_posts_list'];


		foreach ( $array_of_posts_type as $key => $post_type ) {
			add_image_size( $key, 420, 300, true );
			add_image_size( $key, 320, 480, true );
			add_post_type_support( $key, 'excerpt' );
		}
	}

	/**
	 * Register all custom posts type added by user into wordpress
	 *
	 * @access public
	 * @var function|Class Method
	 */
	public static function generate_custom_post_type() {

		global $lscf_icon_url;

		if ( count( self::$plugin_settings['generate_the_custom_posts_list'] ) < 1 ) {
			return;
		}

		$array_of_posts_type = self::$plugin_settings['generate_the_custom_posts_list'];

		$posts_type_keys = array();

		$count = 1;

		foreach ( $array_of_posts_type as $key => $post_type ) {

			$args = array(
				'public' => true,
				'label'  => $post_type,
				'menu_icon'  => $lscf_icon_url,
				'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'comments' ),
			);

			register_post_type( $key, $args );

			register_taxonomy(
				$key . '-categories',
				$key,
				array(
					'label' => 'Categories',
					'hierarchical' => true,
				)
			);

			$posts_type_keys[] = $key;

		};

	}

	/**
	 * Save Forms data
	 * Save Custom Fields Data Option that would be used by specified Custom Post Type.
	 *
	 * @access protected
	 * @var function|Class Method
	 */
	protected function save_data() {

		$model = $this->model;

		$data_options = array();
		$data_options_to_update = array();
		$update_post_cf_options = false;

		$custom_fields_data = array();
		$custom_fields_data_ready = array();

		foreach ( $_POST as $key => $value ) {

			if ( preg_match( '/(^px_check_box|^px_select_box|^px_radio_box|^px_icon_check_box)_([a-zA-Z0-9_-]+?)-name$/', $key, $matches ) ) {

				$field_type = $matches[1];
				$post_type = $matches[2];

				$value_key = preg_replace( '/-name$/' , '', $key );

				foreach ( $_POST[ $key ] as $i => $post_data ) {

					$id = null;
					$r_name = '';

					if ( isset( $_POST[ $field_type . '_' . $post_type . '-name' ][ $i ] ) ) {
						$r_name = sanitize_text_field( wp_unslash( $_POST[ $field_type . '_' . $post_type . '-name' ][ $i ] ) );
					};

					$custom_fields_data[ $post_type ][ $field_type ][ $i ]['name'] = $r_name;
					$custom_fields_data[ $post_type ][ $field_type ][ $i ]['slug'] = sanitize_text_field( $field_type );
					$custom_fields_data[ $post_type ][ $field_type ][ $i ]['value'] = sanitize_text_field( $_POST[ $value_key ][ $i ] );

					if ( isset( $_POST[ $field_type . '_' . $post_type . '_fieldUniqueID' ][ $i ] ) ) {

						$id = sanitize_text_field( $_POST[ $field_type . '_' . $post_type . '_fieldUniqueID' ][ $i ] );

					};


					$custom_fields_data[ $post_type ][ $field_type ][ $i ]['field_form_id'] = $id;
				}
			} elseif ( preg_match( '/(px_options_px_icon_check_box|px_options_px_select_box|px_options_px_check_box|px_options_px_radio_box)_([a-zA-Z0-9_-]+?)_([0-9]{1,2})$/i', $key, $matches ) ) {

				// select/radio/check box fields types.
				$field_type = str_replace( 'px_options_', '', $matches[1] );
				$post_type = $matches[2];
				$field_count = $matches[3];

				$update_options_values = array();

				if ( 'px_icon_check_box' === $field_type ) {

					$icon_key = str_replace( 'px_options', 'px_options_icon', $key );
					$c = 0;

					$old_option_key = $key . '-old-val';

					$custom_fields_data[ $post_type ][ $field_type ][ $field_count ]['old_options']['update_post_options'] = 0;

					foreach ( $_POST[ $key ] as $option ) {

						$img_id = 0 ;
						if ( isset( $_POST[ $icon_key ][ $c ] ) ) {
							$img_id = (int) $_POST[ $icon_key ][ $c ];
						}

						$option_old_value = ( isset( $_POST[ $old_option_key ][ $c ] ) ? sanitize_text_field( wp_unslash( $_POST[ $old_option_key ][ $c ] ) ) : null );
						$option = sanitize_text_field( wp_unslash( $option ) );
						$icon = wp_get_attachment_image_src( $img_id, 'thumbnail', false );

						$custom_fields_data[ $post_type ][ $field_type ][ $field_count ]['options'][ $c ]['opt'] = $option;
						$custom_fields_data[ $post_type ][ $field_type ][ $field_count ]['options'][ $c ]['icon'] = $icon[0];
						$custom_fields_data[ $post_type ][ $field_type ][ $field_count ]['options'][ $c ]['ID'] = $img_id;
						$custom_fields_data[ $post_type ][ $field_type ][ $field_count ]['old_options']['values'][] = $option_old_value;

						if ( $option_old_value != $option ) {
							$custom_fields_data[ $post_type ][ $field_type ][ $field_count ]['old_options']['update_post_options'] = 1;
						}

						$c++;
					}
				} else {

					$opt_count = 0;
					$old_option_key = $key . '-old-val';

					$custom_fields_data[ $post_type ][ $field_type ][ $field_count ]['old_options']['update_post_options'] = 0;

					foreach ( $_POST[ $key ] as $option ) {

						$option = sanitize_text_field( wp_unslash( $option ) );
						$option_old_value = ( isset( $_POST[ $old_option_key ][ $opt_count ] ) ? sanitize_text_field( wp_unslash( $_POST[ $old_option_key ][ $opt_count ] ) ) : null ) ;

						$custom_fields_data[ $post_type ][ $field_type ][ $field_count ]['options'][] = $option;
						$custom_fields_data[ $post_type ][ $field_type ][ $field_count ]['old_options']['values'][] = $option_old_value;

						if ( null !== $option_old_value && $option_old_value != $option ) {
							$custom_fields_data[ $post_type ][ $field_type ][ $field_count ]['old_options']['update_post_options'] = 1;
						}

						$opt_count++;
					}
				};

			} elseif ( preg_match( '/(px_text|px_date)_([a-zA-Z0-9_-]+?)-name$/i', $key, $matches ) ) {

				$field_type = $matches[1];
				$post_type  = $matches[2];

				$value_key = preg_replace( '/-name$/', '', $key );

				foreach ( $_POST[ $key ] as $i => $post_data ) {

					$field = array();
					$f_name = '';
					$f_value = '';
					$id = '';

					if ( isset( $_POST[ $field_type . '_' . $post_type . '-name' ][ $i ] ) ) {
							$f_name = sanitize_text_field( wp_unslash( $_POST[ $field_type . '_' . $post_type . '-name' ][ $i ] ) );
					};

					if ( isset( $_POST[ $value_key ][ $i ] ) ) {
						$f_value = sanitize_text_field( wp_unslash( $_POST[ $value_key ][ $i ] ) );
					};

					if ( isset( $_POST[ $field_type . '_' . $post_type . '_fieldUniqueID' ][ $i ] ) ) {
						$id = sanitize_text_field( wp_unslash( $_POST[ $field_type . '_' . $post_type . '_fieldUniqueID' ][ $i ] ) ) ;
					}

					$field['name'] = $f_name;
					$field['slug'] = sanitize_text_field( $field_type );
					$field['value'] = $f_value;

					$field['field_form_id'] = $id;

					$custom_fields_data[ $post_type ][ $field_type ][ $i ] = $field;

				}
			} elseif( preg_match( '/px_cf_relationship_([a-zA-Z0-9_-]+?)-name$/i', $key, $matches )  ) {

				$field_type = 'px_cf_relationship';
				$post_type  = $matches[1];
				$id = '';

				$value_key = str_replace( '-name', '', $key );

				foreach ( $_POST[ $key ] as $index => $value ) {

					$field['name'] = sanitize_text_field( wp_unslash( $value ) );
					$field['slug'] = sanitize_text_field( $field_type );
					$field['value'] = sanitize_text_field( $_POST[ $value_key ][ $index ] );

					if ( isset( $_POST[ $field_type . '_' . $post_type . '_fieldUniqueID' ][ $index ] ) ) {
						$id = sanitize_text_field( wp_unslash( $_POST[ $field_type . '_' . $post_type . '_fieldUniqueID' ][ $index ] ) );
					}

					$field['field_form_id'] = $id;
					$custom_fields_data[ $post_type ][ $field_type ][ $index ] = $field;
				}
			} elseif( preg_match( '/px_cf_relationship_([a-zA-Z0-9_-]+?)-cf-id/', $key, $matches ) ) {

				$field_type = 'px_cf_relationship';
				$post_type = $matches[1];

				foreach ( $_POST[ $key ] as $index => $parent_item ) {

					$custom_fields_data[ $post_type ][ $field_type ][ $index ]['parent'] = $parent_item;
				}
			} elseif ( preg_match( '/px_cf_relationship_([a-zA-Z0-9_-]+?)-item_([0-9]+)/', $key, $matches ) ) {

				$field_type = 'px_cf_relationship';
				$post_type = $matches[1];
				$field_count = (int) $matches[2];

				foreach ( $_POST[ $key ] as $item_id ) {
					$custom_fields_data[ $post_type ][ $field_type ][ $field_count ]['items'][]['cf_id'] = $item_id;
					$custom_fields_data[ $post_type ][ $field_type ][ $field_count ]['item_ids'][] = $item_id;
				}

			}
		}

		foreach ( $custom_fields_data as $post_type => $post_type_data ) {

			foreach ( $post_type_data as $key => $array ) {

				$count = 0;

				foreach ( $array as $single_field ) {

					$random_string = get_random_string( 15 );

					if ( preg_match( '/__pxid_([a-z]+_[0-9]{1,2})/', $single_field['field_form_id'], $matches) ) {

						$single_field['ID'] = $matches[1];
						$unique_id = $single_field['field_form_id'];
					} else {
						$single_field['ID'] = $random_string . '_' . $count;
						$unique_id = $single_field['value'] . '__pxid_' . $random_string . '_' . $count;
						$single_field['field_form_id'] = $unique_id;
					}

					$single_field['value'] = $unique_id;
					$custom_fields_data_ready[ $post_type ][ $key ][ $unique_id ] = $single_field;

					if ( isset( $single_field['old_options'] ) && 1 === $single_field['old_options']['update_post_options'] ) {

						$options_to_update = array();
						$update_post_cf_options = true;

						foreach ( $single_field['old_options']['values'] as $opt_index => $old_option_val ) {

							if ( $old_option_val !== $single_field['options'][ $opt_index ] ) {
								$options_to_update[] = array(
									'new' => $single_field['options'][ $opt_index ],
									'old' => $old_option_val,
								);
							}
						}

						$data_options_to_update[ $post_type ][] = array(
							'field_id'  => $unique_id,
							'post_type' => $post_type,
							'options_to_update' => $options_to_update,
						);
					}

					$count++;
				}
			}
		}


		foreach ( $custom_fields_data_ready as $post_type => $post_data ) {

			if ( isset( $data_options[ $post_type ] ) ) {

				foreach ( $post_data as $field_key => $fields ) {

					if ( isset( $data_options[ $post_type ][ $field_key ] ) ) {

						foreach ( $fields as $field_id => $field ) {

							$data_options[ $post_type ][ $field_key ][ $field_id ] = $field;

						}
					} else {

						$data_options[ $post_type ][ $field_key ] = $post_data[ $field_key ];

					}
				}
			} else {

				$data_options[ $post_type ] = $custom_fields_data_ready[ $post_type ];

			};

		}

		if ( true === $update_post_cf_options ) {
			CustomFieldsModel::update_lscf_custom_fields_options_by_post_type( $data_options_to_update );
		}

		$model->update_custom_fields_options( $data_options );

	}

	/**
	 * Handle Ajax Requests
	 * Loaded via add_action hook
	 *
	 * @access public
	 * @var function|Class Method
	 */
	public function px_plugin_lf_ajax_request() {

		switch ( $_POST['section'] ) {

			case 'generate-theme-color-style':

				$color = sanitize_text_field( wp_unslash( $_POST['color']['hex'] ) );
				$rgb_color = sanitize_text_field( wp_unslash( $_POST['color']['rgb'] ) );

				echo esc_html( $this->generate_dynamic_css_color( array( 'hex' => $color, 'rgb' => $rgb_color ) ) );

				die();

				break;

			case 'addNewCustomPostType':

				if ( isset( $_POST['name'] ) && '' !== $_POST['name'] ) {

					$model = $this->model;

					$post_name = sanitize_text_field( wp_unslash( $_POST['name'] ) );

					$key = preg_replace( '/[^a-z]+/', '', strtolower( $_POST['name'] ) );
					if ( 20 < strlen( $key ) ) {
						$key = substr( $key, 0, 20 );
					}

					$model->plugin_settings['generate_the_custom_posts_list'][ $key ] = $post_name;

					$model->update_plugin_settings();

				}

				break;

			case 'removeCustomPostType':

				if ( ! isset( $_POST['key'] ) ) {
					die();
				}

				$model = $this->model;

				$custom_post_list = $model->plugin_settings['generate_the_custom_posts_list'];

				$post_key = sanitize_text_field( wp_unslash( $_POST['key'] ) );

				unset( $custom_post_list[ $post_key ] );

				$model->plugin_settings['generate_the_custom_posts_list'] = $custom_post_list;

				$model->update_plugin_settings();

				break;

			case 'getPostCategories':

				if ( ! isset( $_POST['post_type'] ) ) {

					echo wp_json_encode( array() );
					die();

				}

				$model = $this->model;

				$post_type = sanitize_text_field( wp_unslash( $_POST['post_type'] ) );

				$data = $model->get_post_categories( $post_type );

				echo json_encode( $data );

				die();

				break;

			case 'getPostType_customFields':

				if ( ! isset( $_POST['post_type'] ) ) {
					die();
				}

				$model = $this->model;

				$post_type = sanitize_text_field( wp_unslash( $_POST['post_type'] ) );

				if ( $model->get_post_type_custom_fields( $post_type ) ) {

					$data = $model->get_post_type_custom_fields( $post_type );

					$results = array();

					if ( isset( $_POST['fieldType'] ) && is_array( $_POST['fieldType'] ) ) {

						foreach ( $_POST['fieldType'] as $field_type ) {

							if ( 'text' === $field_type || 'number' === $field_type ) {

								foreach ( $data as $childs ) {
									foreach ( $childs as $child ) {
										$results['data']['fields'][] = $child;
									}
								}
							} elseif ( 'date' == $field_type ) {

								foreach ( $data as $childs ) {

									foreach ( $childs as $child ) {

										if ( 'px_date' != $child['slug'] ) {
											continue;
										}

										$results['data']['fields'][] = $child;
									}
								}
							} else {

								foreach ( $data as $childs ) {

									foreach ( $childs as $child ) {

										if ( $field_type != $child['slug'] ) {
											continue;
										}

										$results['data']['fields'][] = $child;
									}
								}
							}
						}
					} elseif( 'all' == $_POST['fieldType'] ) {

						foreach ( $data as $childs ) {

							foreach ( $childs as $child ) {

								$results['data']['fields'][] = $child;
							}
						}
					} else {

						foreach ( $data as $childs ) {

							foreach ( $childs as $child ) {

								if ( 'px_text' == $child['slug'] ) {
									continue;
								}

								$results['data']['fields'][] = $child;
							}
						}
					}

					if ( 0 === count( $results ) ) {

						echo wp_json_encode( array( 'succes' => 0 ) );
						die();
					}

					$results['data']['post_type'] = $post_type;

					echo wp_json_encode( array( 'success' => 1, 'data' => $results ) );

				} else {

					echo wp_json_encode( array( 'success' => 0 ) );
				}

				die();

				break;

			case 'generateShortcode':

				$filter_type = 'custom-posts';

				$model = $this->model;

				$settings = self::$plugin_settings;

				if ( ! isset( $settings['filterList'] ) ) {
					$settings['filterList'] = array();
				}

				$post_type = '';
				if ( isset( $_POST['postType'] ) ) {
					$post_type = sanitize_text_field( wp_unslash( $_POST['postType'] ) );
				}

				if ( true == $_POST['actionData']['editShortcode'] && null != $_POST['actionData']['filterID'] ) {
					$filter_id = sanitize_text_field( wp_unslash( $_POST['actionData']['filterID'] ) );
				} else {
					$filter_id = get_random_string( 15 ) . '_' . count( $settings['filterList'] );
				}

				$filter_fields = array();
				$custom_fields_data = array();
				$post_taxonomies = array();
				$additional_fields = array();
				$filter_style = array();
				$filter_options = array();
				$filter_name = '';
				$featured_label_field_id = '';

				foreach ( $_POST['fieldsData'] as $field ) {

					if ( preg_match( '/^pxfid_([a-z0-9_]+[0-9]{1,3})$/', $field['name'], $matches ) ) {

						$custom_fields_data[ $matches[1] ]['ID'] = sanitize_text_field( $field['value'] );
						continue;

					};

					if ( preg_match( '/^pxfid_([a-z0-9_]+[0-9]{1,3})_display_as/', $field['name'], $matches ) ) {

						$custom_fields_data[ $matches[1] ]['display'] = sanitize_text_field( $field['value'] );
						continue;

					};

					if ( preg_match( '/px_filter_name/', $field['name'] ) ) {

						$filter_name = sanitize_text_field( $field['value'] );
						continue;
					};

					if ( preg_match( '/^px_range-([a-zA-Z_]+)-([0-9]+)$/', $field['name'], $matches ) ) {

						$additional_fields['range'][ $matches[2] ][ $matches[1] ] = sanitize_text_field( $field['value'] );
						continue;
					};

					if ( preg_match( '/^px_date-interval-([a-zA-Z]+)-([0-9]+)$/', $field['name'], $matches ) ) {

						$additional_fields['date-interval'][ $matches[2] ][ $matches[1] ] = sanitize_text_field( $field['value'] );
						continue;
					};

					if ( preg_match( '/^px_search_field_name$/', $field['name'], $matches ) ) {

						$additional_fields['search'][0]['name'] = sanitize_text_field( $field['value'] );
						continue;
					};

					if ( preg_match( '/^px_search_field_name_([0-9]+)/', $field['name'], $matches ) ) {

						$index = (int) $matches[1];
						$additional_fields['search'][ $index ]['name'] = sanitize_text_field( $field['value'] );
						continue;

					};

					if ( preg_match( '/^px_search_by_([0-9]+)/', $field['name'], $matches ) ) {

						$index = (int) $matches[1];
						$additional_fields['search'][ $index ]['search_by'] = sanitize_text_field( $field['value'] );
						continue;
					};

					if ( preg_match( '/(.+?)_taxtitle$/', $field['name'], $matches ) ) {

						$tax_name = sanitize_text_field( $matches[1] );
						$post_taxonomies[ $tax_name ]['name'] = sanitize_text_field( $field['value'] );
						$post_taxonomies[ $tax_name ]['slug'] = sanitize_text_field( $tax_name );
						continue;
					}

					if ( preg_match( '/(.+?)_posttax$/', $field['name'], $matches ) ) {

						$categ_data = array();

						$tax_name = sanitize_text_field( $matches[1] );
						$tax_data = explode( '!#', $field['value'] );

						$ID = (int) $tax_data[0];
						$categ_data['value'] = $ID;
						$categ_data['name'] = sanitize_text_field( $tax_data[1] );

						$post_taxonomies[ $tax_name ]['terms'][ $ID ]['data'] = $categ_data;
						continue;
					};

					if ( preg_match( '/all_from_tax_(.+?)$/', $field['name'], $matches ) ) {
						$tax_name = sanitize_text_field( $matches[1] );
						$post_taxonomies[ $tax_name ]['display_all_terms'] = (int) $field['value'];
					}

					if ( preg_match( '/(.+?)_tax_display_as$/', $field['name'], $matches ) ) {
						$tax_name = sanitize_text_field( $matches[1] );
						$post_taxonomies[ $tax_name ]['display_as'] = sanitize_text_field( $field['value'] );
						continue;
					}

					if ( preg_match( '/(.+?)_pcatid_([0-9]+)$/', $field['name'], $matches ) ) {

						$tax_name = sanitize_text_field( $matches[1] );
						$ID = (int) $field['value'];
						$post_taxonomies[ $tax_name ]['terms'][ $ID ]['subcategs'] = 1;

						continue;
					}

					if ( preg_match( '/(.+?)_pcatid_([0-9]+)_child_display_as$/', $field['name'], $matches ) ) {

						$tax_name = sanitize_text_field( $matches[1] );
						$id = (int) $matches[2];
						$post_taxonomies[ $tax_name ]['terms'][ $id ]['display_as'] = sanitize_text_field( $field['value'] );

						continue;
					}

					if ( preg_match( '/(.+?)_hierarchy_display$/', $field['name'], $matches ) ) {

						$tax_name = sanitize_text_field( $matches[1] );
						$hierarchy_display = ( 1 === (int) $field['value'] ? 1 :0 );
						$post_taxonomies[ $tax_name ]['subcategories_hierarchy_display'] = $hierarchy_display;

						continue;

					}

					if ( preg_match( '/(.+?)_independent_parent_display$/', $field['name'], $matches ) ) {

						$tax_name = sanitize_text_field( $matches[1] );
						$parent_categs_as_filter_field = ( 1 === (int) $field['value'] ? 1 :0 );
						$post_taxonomies[ $tax_name ]['display_parent_categs_as_filters'] = $parent_categs_as_filter_field;

						continue;

					}

					switch ( $field['name'] ) {

						case 'px_filter-featured-posts-label' :

							$featured_label_field_id = sanitize_text_field( $field['value'] );
							continue;

							break;

						case 'px-woocommerce-instock' :

							$custom_fields_data['woocommerce-instock']['ID'] = sanitize_text_field( $field['value'] );
							continue;

							break;

						case 'px-woocommerce-instock_display_as' :

							$id = 'woocommerce-instock';
							$custom_fields_data[ $id ]['display'] = $field['value'];
							continue;

							break;

						case 'filter-for' :

							$filter_type = sanitize_text_field( $field['value'] );
							continue;

							break;

						case 'filter-main-color' :

							$filter_style['main-color'] = sanitize_text_field( wp_unslash( $field['value'] ) );

							break;

						case 'filter-main-color-rgb' :

							$filter_style['main-color-rgb'] = sanitize_text_field( wp_unslash( $field['value'] ) );

							break;

						case 'posts-per-page' :

							$filter_style['posts-per-page'] = (int) $field['value'];

							break;

						case 'filter-reset-button' :

							$filter_style['reset_button']['status'] = (int) $field['value'];

							break;

						case 'rest-button-position' :

							$filter_style['reset_button']['position'] = sanitize_text_field( wp_unslash( $field['value'] ) );

							break;

						case 'reset-button-name' :

							$filter_style['reset_button']['name'] = sanitize_text_field( wp_unslash( $field['value'] ) );

							break;

						case 'post-theme-style' :

							$filter_style['theme']['display'] = sanitize_text_field( wp_unslash( $field['value'] ) );

							break;

						case 'filter-columns-number' :

							$filter_style['theme']['columns'] = (int) $field['value'];

							break;

						case 'sidebar-position' :

							$filter_style['theme']['sidebar']['position'] = sanitize_text_field( wp_unslash( $field['value'] ) );

							break;

						case 'filter-default-view-grid':

							$filter_style['theme']['viewchanger']['grid'] = (int) $field['value'];

							break;

						case 'filter-default-view-list':

							$filter_style['theme']['viewchanger']['list'] = (int) $field['value'];

							break;

						case 'filter-link-type':

							$filter_style['theme']['link_type'] = sanitize_text_field( wp_unslash( $field['value'] ) );

							break;

						case 'lscf_settings_display_all_posts':

							$filter_style['options']['display_all_posts'] = (int) $field['value'];

							break;

						case 'filter-custom-theme-url':

							$filter_style['theme']['custom_template']['url'] = sanitize_text_field( wp_unslash( $field['value'] ) );

							break;

						case 'filter-custom-theme-name':

							$filter_style['theme']['custom_template']['name'] = sanitize_text_field( wp_unslash( $field['value'] ) );
							$filter_style['theme']['custom_template']['slug'] = sanitize_text_field( wp_unslash( str_replace( ' ', '-', strtolower( $field['value'] ) ) ) );

							break;

						case 'lscf_settings_disable_empty_options':

							$filter_options['disable_empty_option_on_filtering'] = (int) $field['value'];

							break;

					}
				}

				$filter_options['checkboxes_conditional_logic'] = 'or';

				if ( isset( $additional_fields['range'] ) ) {
					ksort( $additional_fields['range'] );
				}
				if ( isset( $additional_fields['date-interval'] ) ) {
					ksort( $additional_fields['date-interval'] );
				}

				foreach ( $custom_fields_data as $custom_field ) {

					$custom_field['group_type'] = 'custom_field';
					if ( isset( $custom_field['ID'] ) && '' != $custom_field['ID'] ) {
						$filter_fields[] = $custom_field;
					}
				}

				foreach ( $additional_fields as $type => $additional_fields_type ) {
					foreach ( $additional_fields_type as $field ) {
						$field['group_type'] = 'additional_fields';
						$field['type'] = $type;
						$filter_fields[] = $field;
					}
				}

				$taxonomy = array();

				foreach ( $post_taxonomies as $tax_key => $tax ) {

					if ( ! isset( $tax['subcategories_hierarchy_display'] ) ) {
						$tax['subcategories_hierarchy_display'] = 0;
					}

					if ( isset( $tax['display_parent_categs_as_filters'] ) && 1 === (int) $tax['display_parent_categs_as_filters'] ) {


						$taxonomy[ $tax_key ] = $tax;
						$taxonomy[ $tax_key ]['group_type'] = 'taxonomies';
						$taxonomy[ $tax_key ]['terms'] = array();

						if ( ! isset( $tax['terms'] ) ) {
							continue;
						}
						$count_subcategories_tax = 0;
						foreach ( $tax['terms'] as $key => $term ) {
							if ( isset( $term['data'] ) && isset( $term['data']['value'] ) && '' != $term['data']['value'] ) {

								if ( isset( $term['subcategs'] ) && 1 === (int) $term['subcategs'] ) {
									$subcategs_tax = null;
									$subcategs_tax = $taxonomy[ $tax_key ];
									$subcategs_tax['slug'] = $tax_key . '_-_' .$count_subcategories_tax;
									$subcategs_tax['terms'] = null;
									$subcategs = get_terms( $tax_key, array( 'child_of' => (int) $term['data']['value'], 'hide_empty' => false ) );

									foreach ( $subcategs as $item ) {
										$subcategs_tax['terms'][] = array(
											'data' => array(
												'value' => $item->term_id,
												'name'	=> $item->name,
											),
										);
									}
									$subcategs_tax['display_as'] = $term['display_as'];
									$subcategs_tax['name'] = $term['data']['name'];
									$subcategs_tax['subcategs_parent_id'] = $term['data']['value'];
									$filter_fields[] = $subcategs_tax;

									$count_subcategories_tax++;

								} else {
									$taxonomy[ $tax_key ]['terms'][] = $term;
								}
							}
						}
						if ( count( $taxonomy[ $tax_key ]['terms'] ) > 0 ) {
							$filter_fields[] = $taxonomy[ $tax_key ];
						}
					} else {

						$taxonomy[ $tax_key ] = $tax;
						$taxonomy[ $tax_key ]['group_type'] = 'taxonomies';
						$taxonomy[ $tax_key ]['terms'] = array();

						if ( isset( $tax['display_all_terms'] ) && 1 === $tax['display_all_terms'] ) {
							$filter_fields[] = $taxonomy[ $tax_key ];
							continue;
						}

						if ( ! isset( $tax['terms'] ) ) {
							continue;
						}

						foreach ( $tax['terms'] as $key => $term ) {
							if ( isset( $term['data'] ) && isset( $term['data']['value'] ) && '' != $term['data']['value'] ) {
								$taxonomy[ $tax_key ]['terms'][] = $term;
							}
						}
						if ( count( $taxonomy[ $tax_key ]['terms'] ) > 0 ) {
							$filter_fields[] = $taxonomy[ $tax_key ];
						}
					}

					$taxonomy = null;
				}

				$settings['filterList'][ $filter_id ]['post_type'] = $post_type;
				$settings['filterList'][ $filter_id ]['filter_type'] = $filter_type;
				$settings['filterList'][ $filter_id ]['name'] = $filter_name;
				$settings['filterList'][ $filter_id ]['fields'] = $filter_fields;

				if ( isset( $settings['filterList'][ $filter_id ]['settings'] ) && isset( $settings['filterList'][ $filter_id ]['settings']['theme'] ) 
				&& isset( $settings['filterList'][ $filter_id ]['settings']['theme']['posts_display_from'] ) ) {
					$filter_style['theme']['posts_display_from'] = $settings['filterList'][ $filter_id ]['settings']['theme']['posts_display_from']; 
					$settings['filterList'][ $filter_id ]['settings'] = $filter_style;
				} else {
					$settings['filterList'][ $filter_id ]['settings'] = $filter_style;
				}

				if ( ! isset( $settings['filterList'][$filter_id]['options'] ) ) {
					$settings['filterList'][ $filter_id ]['options'] = $filter_options;
				}

				$settings['filterList'][ $filter_id ]['featuredLabelFieldID'] = $featured_label_field_id;

				if ( isset( $settings['filterList'][ $filter_id ]['shortcode_options'] ) && isset( $settings['filterList'][ $filter_id ]['shortcode_options']['saved_field_options'] ) ) {
					unset( $settings['filterList'][ $filter_id ]['shortcode_options']['saved_field_options'] );
				}

				self::$plugin_settings = $settings;

				$model->plugin_settings = $settings;
				$model->update_plugin_settings();

				echo wp_json_encode(

					array(
						'filterID'   	=> $filter_id,
						'post_type'  	=> $post_type,
						'name'       	=> $filter_name,
						'featuredLabel' => $featured_label_field_id,
					)
				);

				die();

				break;

			case 'get_filter_data':

				$filter_id = sanitize_text_field( wp_unslash( $_POST['filter_id'] ) );

				$filter_data = self::$plugin_settings['filterList'];

				if ( isset( $filter_data[ $filter_id ] ) ) {

					echo  wp_json_encode( $filter_data[ $filter_id ] );
				}

				die();

				break;

			case 'removeShortcode':

				$model = $this->model;

				$settings = self::$plugin_settings;

				if ( ! isset( $_POST['filterID'] ) ) {
					die();
				}

				$filter_id = sanitize_text_field( wp_unslash( $_POST['filterID'] ) );

				if ( isset( $settings['filterList'] ) && isset( $settings['filterList'][ $filter_id ] ) ) {
					unset( $settings['filterList'][ $filter_id ] );
				}

				self::$plugin_settings = $settings;

				$model->plugin_settings = $settings;
				$model->update_plugin_settings();

				echo wp_json_encode(
					array( 'success' => 1 )
				);

				die();

				break;

			case 'save-general-settings':

				$model = $this->model;

				self::$plugin_settings['options']['main-color']['color'] = sanitize_text_field( wp_unslash( $_POST['settings']['color']['color'] ) );
				self::$plugin_settings['options']['main-color']['rgb'] = sanitize_text_field( wp_unslash( $_POST['settings']['color']['rgb'] ) );

				self::$plugin_settings['options']['posts_per_page']['filter'] = (int) $_POST['settings']['posts_per_page']['filter'];
				self::$plugin_settings['options']['posts_per_page']['posts_only'] = (int) $_POST['settings']['posts_per_page']['posts_only'];

				self::$plugin_settings['options']['reset_button'] = array();
				self::$plugin_settings['options']['reset_button']['status'] = (int) $_POST['settings']['reset_button']['status'];
				self::$plugin_settings['options']['reset_button']['name'] = sanitize_text_field( wp_unslash( $_POST['settings']['reset_button']['name'] ) );
				self::$plugin_settings['options']['reset_button']['position'] = sanitize_text_field( wp_unslash( $_POST['settings']['reset_button']['position'] ) );

				self::$plugin_settings['options']['writing']['see_more'] = sanitize_text_field( wp_unslash( $_POST['settings']['see_more_writing'] ) );
				self::$plugin_settings['options']['writing']['see_less'] = sanitize_text_field( wp_unslash( $_POST['settings']['see_less_writing'] ) );
				self::$plugin_settings['options']['writing']['load_more'] = sanitize_text_field( wp_unslash( $_POST['settings']['load_more_writing'] ) );
				self::$plugin_settings['options']['writing']['any'] = sanitize_text_field( wp_unslash( $_POST['settings']['any_writing'] ) );
				self::$plugin_settings['options']['writing']['select'] = sanitize_text_field( wp_unslash( $_POST['settings']['select_writing'] ) );
				self::$plugin_settings['options']['writing']['view'] = sanitize_text_field( wp_unslash( $_POST['settings']['view_writing'] ) );
				self::$plugin_settings['options']['writing']['filter'] = sanitize_text_field( wp_unslash( $_POST['settings']['filter_writing'] ) );
				self::$plugin_settings['options']['writing']['close'] = sanitize_text_field( wp_unslash( $_POST['settings']['close_writing'] ) );
				self::$plugin_settings['options']['writing']['add_to_cart'] = sanitize_text_field( wp_unslash( $_POST['settings']['add_to_cart'] ) );
				self::$plugin_settings['options']['writing']['no_results'] = sanitize_text_field( wp_unslash( $_POST['settings']['no_results'] ) );
				self::$plugin_settings['options']['writing']['sort_by'] = sanitize_text_field( wp_unslash( $_POST['settings']['sort_by'] ) );
				self::$plugin_settings['options']['writing']['sort_asc'] = sanitize_text_field( wp_unslash( $_POST['settings']['sort_asc'] ) );
				self::$plugin_settings['options']['writing']['sort_desc'] = sanitize_text_field( wp_unslash( $_POST['settings']['sort_desc'] ) );
				self::$plugin_settings['options']['writing']['date'] = sanitize_text_field( wp_unslash( $_POST['settings']['date_writing'] ) );
				self::$plugin_settings['options']['writing']['title'] = sanitize_text_field( wp_unslash( $_POST['settings']['title_writing'] ) );
				self::$plugin_settings['options']['writing']['search'] = sanitize_text_field( wp_unslash( $_POST['settings']['search_writing'] ) );
				self::$plugin_settings['options']['writing']['price'] = sanitize_text_field( wp_unslash( $_POST['settings']['price_writing'] ) );

				self::$plugin_settings['options']['block_view'] = (int) $_POST['settings']['block_view'];
				$model->plugin_settings = self::$plugin_settings;
				$model->update_plugin_settings();

				die();
				break;

		}
		die();
	}

	/**
	 * Handle only Administrator Ajax Requests
	 * Loaded via add_action hook
	 *
	 * @access public
	 * @var function|Class Method
	 */
	public function administrator_ajax_requests() {

		$is_administrator = false;
		$active_user = wp_get_current_user();

		if ( in_array( 'administrator', $active_user->roles, true ) ) {
			$is_administrator = true;
		}

		if ( true !== $is_administrator ) {
			return false;
			die();
		}

		switch ( $_POST['section'] ) {

			case 'update-fields-order':

				$filter_data = get_option( PluginMainModel::$meta_name_plugin_settings, true );
				$filter_data = json_decode( $filter_data, true );

				if ( ! isset( $filter_data['filterList'][ $_POST['filter_id'] ] ) ) {
					die();
				}
				$data = json_decode( wp_unslash( $_POST['fields'] ) );

				$filter_data['filterList'][ $_POST['filter_id'] ]['fields'] = json_decode( stripslashes( $_POST['fields'] ), true );
				$filter_data['filterList'][ $_POST['filter_id'] ]['shortcode_options'] = $_POST['options'];

				update_option( PluginMainModel::$meta_name_plugin_settings, wp_json_encode( $filter_data ) );

				die();
				break;

			case 'generate-theme-olor-style':

				$color = sanitize_text_field( wp_unslash( $_POST['color']['hex'] ) );
				$rgb_color = sanitize_text_field( wp_unslash( $_POST['color']['rgb'] ) );

				echo esc_html( $this->generate_dynamic_css_color( array( 'hex' => $color, 'rgb' => $rgb_color ) ) );

				die();

				break;

			case 'save-filter-settings':

				$filter_data = get_option( PluginMainModel::$meta_name_plugin_settings, true );
				$filter_data = json_decode( $filter_data, true );

				$filter_id = sanitize_text_field( wp_unslash( $_POST['filter_id'] ) );

				if ( ! isset( $filter_data['filterList'][ $filter_id ] ) ) {
					die();
				}

				$f_data = json_decode( wp_unslash( $_POST['settings'] ), true );

				$filter_data['filterList'][ $filter_id ]['settings'] = $f_data['filterSettings'];
				$filter_data['filterList'][ $filter_id ]['options'] = $f_data['generalSettings'];

				update_option( PluginMainModel::$meta_name_plugin_settings, wp_json_encode( $filter_data ) );

				die();
				break;

			case 'get_taxonomies_and_terms':

				$post_type = sanitize_text_field( wp_unslash( $_POST['post_type'] ) );
				$model = $this->model;

				$data_categories = $model->get_post_categories( $post_type );

				$taxonomies = array();

				$count = 0;

				foreach ( $data_categories as $category ) {

					if ( isset( $category['categs'] ) && count( $category['categs'] ) > 0 ) {

						$taxonomies[ $count ]['categs'] = $category['categs'];
						$taxonomies[ $count ]['taxonomy'] = $category['taxonomy'];

						$count++;
					}
				}

				echo wp_json_encode( $taxonomies );

				die();
				break;
		}

	}
}
