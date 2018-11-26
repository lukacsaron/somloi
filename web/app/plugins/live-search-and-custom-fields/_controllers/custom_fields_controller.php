<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once LSCF_PLUGIN_PATH . '_models/main_model.php';
include LSCF_PLUGIN_PATH . '_models/custom_fields_model.php';

/**
 * Class CustomFieldsController controller for post's custom fields
 *
 * @category Controller
 * @package  CustomFieldsController
 * @author   PIXOLETTE
 * @license  http://www.pixollete.com
 * @link     http://www.pixollete.com
 **/
class CustomFieldsController {

	/**
	 * Model of custom fields db queries
	 *
	 * Methods:
	 *		get_post_custom_fields(postid = int),
	 *		update_post_custom_fields(postid, data)
	 *
	 * @var Class|Object
	 */
	public $model;


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
	 * @var Class|Object
	 */
	public $main_model;

	/**
	 * The Post ID. Used in Models to get postmeta data
	 *
	 * @access protected
	 * @var Integer
	 */
	protected $post_id;

	/**
	 * Post's processed custom fields data.
	 *
	 * @access public
	 * @var array
	 */
	public $post_custom_fields_data = array();

	/**
	 * Store each requested post custom fields to local storage.
	 *
	 * @var array
	 */
	public $local_storage_post_custom_fields = array();

	/**
	 * Constructor function. Load the CustomFieldsModel and init the Wordpress action hooks
	 *
	 * @param int $id post id.
	 * @var function constructor
	 */
	function __construct( $id = null ) {

		$this->post_id = $id;

		$this->model = new customFieldsModel();
	}

	/**
	 * Initialize the Wordpress action hooks.
	 *
	 * Hooks:
	 *		save_post
	 *
	 * @access public
	 * @var function
	 */
	public function init_hooks() {

		add_action( 'save_post', array( __CLASS__, 'save_custom_fields' ) );
	}

	/**
	 * Load the custom fields to page or post section if available.
	 *
	 * @access public
	 * @var function
	 */
	public function display_custom_fields_meta_box() {

		global $pagenow;

		$model = $this->model;

		$main_model = new pluginMainModel();

		$this->main_model = $main_model;

		$data_fields = $main_model->data_option;

		if ( false === $data_fields || ! is_array( $data_fields ) ) {
			return;
		};

		if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {

			$active_posts_type = array();

			foreach ( $data_fields as $post_type => $data ) {

				$active_posts_type[] = $post_type;

			}

			switch ( $pagenow ) {

				case 'post.php':

					$post_id = ( isset( $_GET['post'] ) ? (int) $_GET['post'] : 0 );

					$this->post_id = $post_id;

					$post_type = get_post_type( $post_id );

					break;

				case 'post-new.php':

					if ( isset( $_GET['post_type'] ) ) {
						$post_type = sanitize_text_field( wp_unslash( @$_GET['post_type'] ) );
					}

				break;

			}

			if ( isset( $post_type ) && in_array( $post_type, $active_posts_type, true ) ) {

				$post_fields_data = $model->get_post_custom_fields( $this->post_id );

				$data_fields = $data_fields[ $post_type ];

				if ( false !== $post_fields_data ) {
					foreach ( $data_fields as $key => $fields ) {

						foreach ( $fields as $array_key => $field ) {

							foreach ( $post_fields_data as $group_fields ) {

								if ( ! is_array( $group_fields ) ) {
									continue;
								}
								foreach ( $group_fields as $single_field ) {
									if ( $single_field['ID'] === $array_key ) {

										if ( isset( $single_field['value'] ) ) {
											$data_fields[ $key ][ $array_key ]['dataValue'] = $single_field['value'];
										};
										if ( isset( $single_field['dlabel'] ) ) {
											$data_fields[ $key ][ $array_key ]['dlabel'] = $single_field['dlabel'];
										}
										if ( isset( $single_field['data'] ) ) {
											$data_fields[ $key ][ $array_key ]['data'] = $single_field['data'];
										}
										if ( isset( $single_field['post-display'] ) ) {
											$data_fields[ $key ][ $array_key ]['post-display'] = (int) $single_field['post-display'];
										}
									}
								}
							}
						}
					}
				}

				$this->post_custom_fields_data = $data_fields;

				add_meta_box( 'lscf-custom-fields', 'LSCF Custom Fields', array( $this, 'display_custom_fields' ), $post_type, 'normal', 'low' );

			}
		}
	}
	/**
	 * Display custom fields inside each post or page from wp-backend, if available...
	 *
	 * @access public
	 * @var function
	 */
	public function display_custom_fields() {

		$model = $this->model;
		$post_id = $this->post_id;
		$fields_data = $this->post_custom_fields_data;

		$post_fields = ( null !== $post_id  ? $model->get_post_custom_fields( $post_id ) : null );

		include LSCF_PLUGIN_PATH . '_views/backend/post-customFields.php' ;

	}

	/**
	 * Save Post's Custom Fields Values into DB
	 *
	 * @access public
	 * @var function
	 */
	public static function save_custom_fields() {

		if ( isset( $_POST['save_px_post_fields'] ) ) {

			$model = new CustomFieldsModel();

			$fields = array();

			$count = 0;

			foreach ( $_POST['lscf_cf'] as $id => $lscf_custom_field ) {

				switch ( $lscf_custom_field['type'] ) {

					default:

						$id = sanitize_text_field( $lscf_custom_field['ID'] );
						$name = sanitize_text_field( $lscf_custom_field['name'] );
						$post_display = ( isset( $lscf_custom_field['display'] ) ? (int) $lscf_custom_field['display'] : 0 );

						if ( isset( $lscf_custom_field['options'] ) && is_array( $lscf_custom_field['options'] ) ) {

							if ( 'px_icon_check_box' == $lscf_custom_field['type'] ) {

								foreach ( $lscf_custom_field['options'] as $icon_checkbox_option ) {

									if ( isset( $icon_checkbox_option['opt'] ) ) {

										if ( is_array( $icon_checkbox_option['opt'] ) ) {
											$value = array_map( 'sanitize_text_field', $icon_checkbox_option['opt'] );
										} else {
											$value = sanitize_text_field( $icon_checkbox_option['opt'] );
										}

										$fields[ $id ]['value'][] = $value;
										$fields[ $id ]['ivalue'][] = $icon_checkbox_option;
									}
								}
							} else {
								if ( is_array( $lscf_custom_field['options'] ) ) {
									$value = array_map( 'sanitize_text_field', $lscf_custom_field['options'] );
								} else {
									$value = sanitize_text_field( $lscf_custom_field['options'] );
								}
								$fields[ $id ]['value'] = $value;
							}
						} else {

							if ( ! isset( $lscf_custom_field['value'] ) ) {
								$value = '';
							} else {
								$value = ( is_array( $lscf_custom_field['value'] ) ? array_map( 'sanitize_text_field', $lscf_custom_field['value'] ) : sanitize_text_field( $lscf_custom_field['value'] ) );
							}

							$fields[ $id ]['value'] = $value;

							if ( 'px_date' === $lscf_custom_field['type'] ) {
								$fields[ $id ]['dlabel'] = ( isset( $lscf_custom_field['dlabel'] ) ? sanitize_text_field( $lscf_custom_field['dlabel'] ) : '' );

							}
						}

						$fields[ $id ]['name'] = sanitize_text_field( $lscf_custom_field['name'] );
						$fields[ $id ]['ID'] = sanitize_text_field( $id );
						$fields[ $id ]['field_type'] = sanitize_text_field( $lscf_custom_field['type'] );
						$fields[ $id ]['post-display'] = $post_display;

						break;

					case 'px_cf_relationship':

						$id = sanitize_text_field( $lscf_custom_field['ID'] );
						$name = sanitize_text_field( $lscf_custom_field['name'] );
						$type = 'px_cf_relationship';

						$data_items = array();

						foreach ( $lscf_custom_field['items'] as $i_key => $item ) {

							$data_items[ $i_key ]['option'] = $item['option_name'];

							foreach ( $item['values'] as $value ) {
								$data_items[ $i_key ]['fields'][ $value['ID'] ] = $value;
							}
						}

						$fields[ $id ]['name'] = $name;
						$fields[ $id ]['ID'] = $id;
						$fields[ $id ]['field_type'] = $type;
						$fields[ $id ]['data'] = $data_items;

						break;

				}
			}

			$data = lscf_wordpress_escape_unicode_slash( wp_json_encode( $fields ) );
			$model->update_posts_custom_fields( (int) $_POST['post_ID'], $data );

		}
	}

	/**
	 * Get the post custom fields.
	 *
	 * @param int $post_id The post ID.
	 * @access public
	 * @var function
	 */
	public function get_post_custom_fields( $post_id ) {

		if ( isset( $this->local_storage_post_custom_fields[ $post_id ] ) ) {
			return $this->local_storage_post_custom_fields[ $post_id ];
		}

		$model = $this->model;
		$data = $model->get_post_custom_fields( (int) $post_id );

		$this->local_storage_post_custom_fields[ (int) $post_id ] = $this->prepare_custom_fields_object( $data );

		return $this->local_storage_post_custom_fields[ (int) $post_id ];

	}

	/**
	 * Prepare the post custom fields object format.
	 *
	 * @param array $data The custom fields object.
	 * @access public
	 * @var function
	 */
	public function prepare_custom_fields_object( $data ) {

		if ( ! is_array( $data ) || 0 === count( $data ) ) { return null; }

		$custom_fields = array();

		foreach ( $data as $group_type ) {

			if ( ! is_array( $group_type ) ) { continue; }

			foreach ( $group_type as $custom_field ) {
				$custom_fields[ $custom_field['ID'] ] = $custom_field;
			}
		}

		return $custom_fields;
	}
}
