<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
require_once LSCF_PLUGIN_PATH . '_controllers/http_requests_controller.php';

class LscfApiRoutes {

	/**
	 * The plugin API custom path.
	 *
	 * @access public
	 * @var string
	 */
	public $rest_api_path = 'lscf_rest';

	/**
	 * The http reqests controller class.
	 *
	 * @access public
	 * @var Class
	 */
	public $http_request_controller;

	/**
	 * Register the plugin rest API path. Set up the routes.
	 *
	 * @access public
	 */
	public function init_api_routes() {
		add_action( 'rest_api_init', array( $this, 'register_plugin_routes' ) );
	}

	/**
	 * Add all plugin API methods.
	 *
	 * @access public
	 */
	public function register_plugin_routes() {

		register_rest_route( $this->rest_api_path, '/filter_posts', array(
			'methods'	=> 'POST',
			'callback'	=> array( $this->http_request_controller, 'filter_posts' ),
		) );

		register_rest_route( $this->rest_api_path, '/get_sidebar', array(
			'methods'	=> 'POST',
			'callback'	=> array( $this->http_request_controller, 'load_filter_sidebar' ),
		) );

	}

	/**
	 * Init the HttpRequestController Class.
	 *
	 * @access public
	 */
	function __construct() {
		$this->http_request_controller = new HttpRequestsController();
	}

}

