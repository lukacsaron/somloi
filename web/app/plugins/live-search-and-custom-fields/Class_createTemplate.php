<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * Class PageTemplater Creates custom page template
 *
 * @category Helper
 * @package  1.0.0
 * 
 **/
class lscfCustomTemplate {

	/**
	 * A Unique Identifier
	 *
	 * @var string
	 */
	protected $plugin_slug;

	/**
	 * A reference to an instance of this class.
	 *
	 * @var string
	 */
	private static $instance;

	/**
	 * The array of templates that this plugin tracks.
	 *
	 * @var array
	 */
	protected $templates;

	/**
	 * Plugin path
	 *
	 * @var string
	 */
	public $plugin_dir;

	/**
	 * Returns an instance of this class.
	 *
	 * @var object
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
				self::$instance = new lscfCustomTemplate();
		}

		return self::$instance;
	}

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 *
	 * @var function
	 */
	private function __construct() {

		$this->plugin_dir = plugin_dir_path( __FILE__ );
		$this->templates = array();


		// Add a filter to the attributes metabox to inject template into the cache.
		add_filter(
			'page_attributes_dropdown_pages_args',
			array( $this, 'register_project_templates' )
		);


		// Add a filter to the save post to inject out template into the page cache.
		add_filter(
			'wp_insert_post_data',
			array( $this, 'register_project_templates' )
		);


		// Add a filter to the template include to determine if the page has our.
		// template assigned and return it's path.
		add_filter(
			'template_include',
			array( $this, 'view_project_template' ) 
		);


		// Add your templates to this array.
		$this->templates = array(
			'page-capf-custom-template.php'     => 'LS&CF - Blank template',
		);

	}

	/**
	 * Adds our template to the pages cache in order to trick WordPress.
	 * into thinking the template file exists where it doens't really exist.
	 *
	 * @param array $atts injected via add_filter hook.
	 * @var function
	 */
	public function register_project_templates( $atts ) {

		// Create the key used for the themes cache.
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list.
		// If it doesn't exist, or it's empty prepare an array.
		$templates = wp_get_theme()->get_page_templates();

		if ( empty( $templates ) ) {
			$templates = array();
		}

		// New cache, therefore remove the old one.
		wp_cache_delete( $cache_key , 'themes' );

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow WordPress to pick it up for listing.
		// available templates.
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;

	}


	/**
	 * Checks if the template is assigned to the page.
	 *
	 * @param array $template injected via add_filter hook.
	 * @var function
	 */
	public function view_project_template( $template ) {

		global $post;

		if ( ! isset( $post->ID ) ) {
			return $template;
		}
		if ( ! isset( $this->templates[ get_post_meta(
			$post->ID, '_wp_page_template', true
		) ] ) ) {

			return $template;

		}

		$file = plugin_dir_path( __FILE__ ) . get_post_meta(
			$post->ID, '_wp_page_template', true
		);

		// Just to be safe, we check if the file exist first.
		if ( file_exists( $file ) ) {
			return $file;
		} else { echo $file; }

		return $template;

	}

}

add_action( 'plugins_loaded', array( 'lscfCustomTemplate', 'get_instance' ) );

