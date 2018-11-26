<?php 

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * Class PluginMainController The main MODEL for DB queries
 *
 * @category Model
 * @package  PluginMainModel
 * @author   PIXOLETTE
 * @license  http://www.pixollete.com
 * @link     http://www.pixollete.com
 **/
class PluginMainModel {

	/**
	 * Post meta_key name for plugin settings
	 *
	 * @access public
	 * @var string
	 */
	public static $meta_name_plugin_settings = 'pxLF_settings';

	/**
	 * Option_name for all custom fields data
	 *
	 * @access public
	 * @var string
	 */
	public static $options_custom_fields  = 'px_custom_fields_options';

	/**
	 * Will store all custom fields data option
	 *
	 * @access public
	 * @var array
	 */
	public $data_option;

	/**
	 * Checks if option_name self::$option_custom_fields exists
	 *
	 * @access public
	 * @var boolean
	 */
	public static $is_set_custom_fields = false;

	/**
	 * Stores Plugins settings
	 *
	 * @access public
	 * @var array
	 */
	public $plugin_settings = array(
		'generate_the_custom_posts_list' => array(),
		'filter-active_postTypes' => array(),
		'configs'	=>	array(),
		);

	/**
	 * Checks if option_name self::$meta_name_plugin_settings exists
	 *
	 * @access public
	 * @var boolean
	 */
	public $is_set_plugin_option = false;

	/**
	 * Class constructior.
	 *		get plugin settings
	 *		get custom fields data option
	 *
	 * @access public
	 * @var function|Class Constructor
	 */
	function __construct() {

		$option = get_option( self::$meta_name_plugin_settings );
		$data_option = get_option( self::$options_custom_fields );

		if ( false !== $option ) {
			$this->plugin_settings = json_decode( $option, true );
			$this->is_set_plugin_option = true;
		}

		// get the custom fields data from database.
		if ( false !== $data_option ) {
			$this->data_option = json_decode( $data_option, true );
			self::$is_set_custom_fields = true;
		}

	}

	/**
	 * Update plugin settings
	 *
	 * @access public
	 * @var function|Class Method
	 */
	public function update_plugin_settings() {

		if ( true === $this->is_set_plugin_option ) {

			update_option( self::$meta_name_plugin_settings, wp_json_encode( $this->plugin_settings ) );

		} else {

			add_option( self::$meta_name_plugin_settings, wp_json_encode( $this->plugin_settings ), '', false );

		}

	}


	/**
	 * Get post taxonomies and categories by custom post type key
	 *
	 * @param string $post_type Custom Post Type Key.
	 * @access public
	 * @var function|Class Method
	 */
	public function get_post_categories( $post_type ) {

		$results = array();
		$taxonomies_list = get_object_taxonomies( $post_type, 'names' );

		$count = 0;

		foreach ( $taxonomies_list as $tax_name ) {

			if ( 'post_format' == $tax_name ) {
				continue;
			}

			$categories = get_terms( $tax_name,
				array( 'hide_empty' => false )
			);

			$categories_object = lscf_wp_reset_cat_key_to_id( $categories );

			$results[ $count ]['taxonomy'] = $tax_name;
			$results[ $count ]['subcategs'] = array();
			$results[ $count ]['unsorted_subcategs'] = array();

			foreach ( $categories as $category ) {

				$results[ $count ]['ids_all'][] = $category->term_id;
				$results[ $count ]['categs'][] = $category;

				if ( 0 === $category->parent ) {

					$results[ $count ]['ids_parent'][] = $category->term_id;
					$results[ $count ]['parent_categs'][ $category->term_id ]['data'] = $category;

					continue;
				};

				if ( 0 !== $category->parent && null !== $category->parent ) {

					$results[ $count ]['subcategs'][ $category->parent ][] = $category;
					$results[ $count ]['unsorted_subcategs'][] = $category;
					$results[ $count ]['parent_categs'][ $category->parent ]['has_subcategories'] = 1;
					$results[ $count ]['has_subcategories'] = 1;

					continue;

				}
			}

			if ( isset( $results[ $count ]['parent_categs'] ) ) {

				$parent_categs = array();

				foreach ( $results[ $count ]['parent_categs'] as $parent_categ ) {
					if ( isset( $parent_categ['data'] ) ) {
						$parent_categs[] = $parent_categ;
					}
				}

				$results[ $count ]['parent_categs'] = $parent_categs;

			}

			$count++;
		}

		return $results;
	}

	/**
	 * Get the custom fields data by custom post type
	 *
	 * @param string $post_type Custom Post Type Key.
	 * @access public
	 * @var function|Class Method
	 */
	public function get_post_type_custom_fields( $post_type ) {

		if ( false === self::$is_set_custom_fields ) {
			return false;
		}

		$data = $this->data_option;

		if ( ! isset( $data[ $post_type ] ) ) {
			return false;
		}

		return $data[ $post_type ];
	}

	/**
	 * Get all Custom Posts Types from Wordpress
	 * Returns array of custom post types keys.
	 *
	 * @access public
	 * @var function|Class Method
	 */
	public function fetch_posts_types_list() {

		$data = array(
			'post' => 'Post',
		);
		$args = array(
			'public'   => true,
			'_builtin' => false,
		);

		$posts_type = get_post_types( $args, 'object' );

		foreach ( $posts_type as $key => $post_type ) {
			$data[ $key ] = $post_type->labels->name;
		}

		if ( count( $data ) < 1 ) {
			return false;
		}

		return $data;
	}

	/**
	 * Update custom fields data into a wp options_name self::$options_custom_fields
	 *
	 * @param array $data An array of fields that will be updated into a wp option.
	 * @access public
	 * @var function|Class Method
	 */
	public static function update_custom_fields_options( $data ) {

		$data = wp_json_encode( $data );

		if ( false !== self::$is_set_custom_fields ) {
				update_option( self::$options_custom_fields, $data );
		} else {
			add_option( self::$options_custom_fields, $data, '', false );
		}
	}

	/**
	 * Create custom MySql functions into Schema.
	 *
	 * @access public
	 * @var function
	 */
	public function create_custom_mysql_functions() {

		global $wpdb;

		$has_mysql_function = $wpdb->query( 'show create function lscf_number_only' );
		if ( $has_mysql_function ) {
			return;
		}

		global $wpdb;
		$sql = "CREATE FUNCTION `lscf_number_only`(in_string VARCHAR(50)) 
			RETURNS INT
			NO SQL
			BEGIN
				DECLARE ctrNumber VARCHAR(50);
				DECLARE finNumber VARCHAR(50) DEFAULT '';
				DECLARE sChar VARCHAR(1);
				DECLARE inti INTEGER DEFAULT 1;
			
				IF LENGTH(in_string) > 0 THEN
					WHILE(inti <= LENGTH(in_string)) DO
						SET sChar = SUBSTRING(in_string, inti, 1);
						SET ctrNumber = FIND_IN_SET(sChar, '0,1,2,3,4,5,6,7,8,9'); 
						IF ctrNumber > 0 THEN
							SET finNumber = CONCAT(finNumber, sChar);
						END IF;
						SET inti = inti + 1;
					END WHILE;
					RETURN CAST(finNumber AS UNSIGNED);
				ELSE
					RETURN 0;
				END IF;
			END";

		$wpdb->query( $sql );
	}
}
