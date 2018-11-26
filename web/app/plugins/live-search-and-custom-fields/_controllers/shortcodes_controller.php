<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include_once LSCF_PLUGIN_PATH . '_controllers/custom_fields_controller.php';

class LscfShortcodesController {

	/**
	 * The Custom Fields Controller
	 *
	 * @var Class
	 */
	private $custom_fields_controller;

	/**
	 * Custom fields shortcode.
	 *
	 * @param array $atts Injected by add_shortcode action hook.
	 * @var function
	 */
	public function init_shortcode_custom_fields( $atts ) {

		wp_enqueue_style( 'px_base' );

		$display_shortcode_in = array( 'all', 'post', 'filter' );

		$shortcode_attributes = shortcode_atts(
			array(
				'post_id'			=> '',
				'custom_field_id'	=> '',
				'icons_only'		=> 0,
				'display'			=> 'block',
				'title'				=> 0,
				'display_in'		=> 'post',
				'loaded_from'		=> 'post',
				),
			$atts
		);
		if ( ! in_array( $shortcode_attributes['display_in'], $display_shortcode_in, true ) ) {
			$shortcode_attributes['display_in'] = 'post';
		}

		if ( 'post' !== $shortcode_attributes['display_in'] && $shortcode_attributes['display_in'] !== $shortcode_attributes['loaded_from'] ) {
			return;
		}

		if ( isset( $shortcode_attributes['post_id'] ) && '' !== $shortcode_attributes['post_id'] ) {
			$post_id = (int) $shortcode_attributes['post_id'];
		} else {
			$post_id = get_the_ID();
		}

		if ( '' === $post_id || null === $post_id ) { return; }

		$post_custom_fields = $this->custom_fields_controller->get_post_custom_fields( $post_id );

		$active_custom_field_id = sanitize_text_field( $shortcode_attributes['custom_field_id'] );

		$icons_only = ( isset( $shortcode_attributes['icons_only'] ) ? (int) $shortcode_attributes['icons_only'] : 0 );
		$display_type = sanitize_text_field( $shortcode_attributes['display'] );
		$display_title = ( isset( $shortcode_attributes['title'] ) ? (int) $shortcode_attributes['title'] : 0 );
		$display_in = $shortcode_attributes['display_in'];

		if ( ! isset( $post_custom_fields[ $active_custom_field_id ] ) ) {
			return;
		}

		$custom_field = $post_custom_fields[ $active_custom_field_id ];

		ob_start();
		include LSCF_PLUGIN_PATH . '_views/frontend/custom-field-template.php';

		return ob_get_clean();
	}

	/**
	 * Class constructor
	 *
	 * @var function
	 */
	function __construct() {
		$this->custom_fields_controller = new CustomFieldsController();
	}

}
