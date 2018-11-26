<?php 

require_once LSCF_PLUGIN_PATH . '_models/main_model.php';

class LscfDataCustomFieldsControoler {

	/**
	 * Model of custom fields db queries
	 *
	 *
	 * @var Class|Object
	 */
	public $class_main_model;

	/**
	 * List all Custom Fields by Post Type
	 *
	 * @param 'object'|'array' $results_format. Return results as array or as object(CF ID).
	 * @param string $post_type. The Post Type.
	 * @access public
	 * @var function
	 */
	public function list_custom_fields_by_post_type( $post_type, $results_format = 'object' ) {

		$results = array();

		if ( $custom_fields_data = $this->class_main_model->get_post_type_custom_fields( $post_type ) ) {

			if ( ! is_array( $custom_fields_data ) || 0 >= count( $custom_fields_data ) ) {
				return $results;
			}

			switch ( $results_format ) {
				default:
				case 'object':

					foreach ( $custom_fields_data as $custom_field_group ) {
						if ( ! is_array( $custom_field_group ) || 0 >= count( $custom_field_group ) ) {
							continue;
						}

						foreach ( $custom_field_group as $custom_field ) {
							if ( ! isset( $custom_field['value'] ) ) {
								continue;
							}
							$results[ $custom_field['value'] ] = $custom_field;
						}
					}

					return $results;
				
				case 'array':

					foreach ( $custom_fields_data as $custom_field_group ) {
						if ( ! is_array( $custom_field_group ) || 0 >= count( $custom_field_group )  ) {
							continue;
						}

						foreach ( $custom_field_group as $custom_field ) {
							if ( ! isset( $custom_field['value'] ) ) {
								continue;
							}
							$results[] = $custom_field;
						}
					}

					return $results;
			}

		}

	}

	public function __construct(){
		$this->class_main_model = new pluginMainModel();
	}
	
}