<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * Class CustomFieldsModel Model for post's custom fields  DB quries
 *
 * @category Model
 * @package  CustomFieldsMoldel
 * @author   PIXOLETTE
 * @license  http://www.pixollete.com
 * @link     http://www.pixollete.com
 **/
class CustomFieldsModel {

	/**
	 * Post meta_key name for post's custom fields data
	 *
	 * @var string
	 */
	public static $meta_post_custom_fields = 'px-custom_fields';

	/**
	 * Post meta_key name for post's custom fields data
	 *
	 * @param int $post_id The POST ID, used to get post meta data.
	 * @access public
	 * @var function|method
	 */
	public function get_post_custom_fields( $post_id ) {

		$post_custom_fields = get_post_meta( (int) $post_id, self::$meta_post_custom_fields, true );

		// avoid broken json. The new inserted data is sanitized. Leave this line for old versions.
		$post_custom_fields = str_replace( '\'', '"', lscf_wordpress_add_unicode_slash( $post_custom_fields ) );

		$data = json_decode( $post_custom_fields, true );


		if ( ! empty( $data ) ) {

			$grouped_data = array();

			if ( is_array( $data ) ) {

				foreach ( $data as $id => $field ) :

					if ( ! isset( $field['field_type'] ) ) { continue; }

					switch ( $field['field_type'] ) {

						case 'px_icon_check_box':
						case 'px_check_box':
						case 'px_cf_relationship':

							$grouped_data['multiple_values'][] = lscf_esc_json_custom_field_values( $field );

						break;

						case 'px_text':
						case 'px_date':
						case 'px_radio_box':
						case 'px_select_box':


							$grouped_data['single_value'][] = lscf_esc_json_custom_field_values( $field );

						break;
					}

				endforeach;

			}

			if (  ( isset( $grouped_data['multiple_values'] ) && count( $grouped_data['multiple_values'] ) > 0 ) || ( isset( $grouped_data['single_value'] ) && count( $grouped_data['single_value'] ) > 0 ) ) {

				$single_values_group = ( isset( $grouped_data['single_value'] ) ? $grouped_data['single_value'] : '' );
				$multiples_values_group = ( isset( $grouped_data['multiple_values'] ) ? $grouped_data['multiple_values'] : '' );

				return array( 'single_value' => $single_values_group, 'multiple_values' => $multiples_values_group );
			}
		}

		return false;
	}

	/**
	 * Update the POST's custom fields values. Create post meta data for each custom field. Used by Sort By functionality.
	 *
	 * @param int 	$post_id The POST ID, used to update post meta data.
	 * @param array $data The Post's custom fields values.
	 * @access public
	 * @var function|method
	 */
	public function update_posts_custom_fields( $post_id, $data ) {

		$custom_fields = json_decode( lscf_wordpress_add_unicode_slash( $data ) );

		foreach ( $custom_fields as $key => $custom_field ) {

			if ( is_array( $custom_field->value ) ) {

				$values = array();
				foreach ( $custom_field->value as $value ) {
					$values[] = $value;
				}
				$values = implode( ';',  $values );

				update_post_meta( (int) $post_id, $key,  $values );
			} else {
				if ( 'px_date' === $custom_field->field_type) {
					update_post_meta( (int) $post_id, $key . '_timestamp', strtotime( $custom_field->value ) );
				}
				update_post_meta( (int) $post_id, $key, $custom_field->value );
			}
		}

		if ( update_post_meta( (int) $post_id, self::$meta_post_custom_fields, $data ) ) {
			return true;
		}

		return false;

	}


	/**
	 * Update all post's data - LSCF custom fields options values. Used when the LSCF custom field option is edited.
	 *
	 * @param array $lscf_cf_options_to_update An array with options of lscf custom fields that needs to be updated.
	 * @access public
	 */
	public static function update_lscf_custom_fields_options_by_post_type( $lscf_cf_options_to_update ) {

		global $wpdb;

		foreach ( $lscf_cf_options_to_update as $cpt_fields_options_to_update ) {

			foreach ( $cpt_fields_options_to_update as $cpt_options_to_update ) {

				$posts_type_ids = new WP_QUERY( array(
					'post_type' 	 => $cpt_options_to_update['post_type'],
					'posts_per_page' => -1,
					'fields'		 => 'ids',
					'post_status'    => 'any',
				) );

				foreach ( $posts_type_ids->posts as $post_id ) {

					$post_custom_fields_data = get_post_meta( (int) $post_id, self::$meta_post_custom_fields, true );

					$pattern_data = array();
					$replace_data = array();
					$field_id = lscf_convert_unicode_character( $cpt_options_to_update['field_id'] );

					foreach ( $cpt_options_to_update['options_to_update'] as $option_data  ) {

						$old_val = $option_data['old'];
						$new_val = ( isset( $option_data['new']['opt'] ) ?  $option_data['new']['opt'] : $option_data['new'] );
						$old_slug = px_sanitize( $old_val );
						$new_slug = px_sanitize( $new_val );

						$old_val = lscf_regex_sanitize( addslashes( lscf_convert_unicode_character( $old_val ) ) );
						$new_val = lscf_regex_sanitize( lscf_convert_unicode_character( $new_val ) );

						$pattern_data[] =  "/(\"$field_id\":.*?\"value\":.*?)(\"$old_val\")(.*?\"field_type\")/";
						$replace_data[] = "$1\"$new_val\"$3";

						$pattern_data[] =  "/(\"data\":\{.+?)(\"$old_slug\")(:\{\"option\":)(\"$old_val\"|\"$new_val\")(.*?\"fields\":\{)/";
						$replace_data[] = "$1\"$new_slug\"$3\"$new_val\"$5";

					}

					$updated_data = preg_replace( $pattern_data, $replace_data, $post_custom_fields_data );
					$updated_data = lscf_regex_escape( $updated_data );

					$updated_data = str_replace( '\'', '"', lscf_wordpress_add_unicode_slash( $updated_data ) );

					if ( $updated_data = json_decode( $updated_data, true ) ) {

						$updated_data = wp_json_encode( wp_slash( $updated_data ) );

						update_post_meta( (int) $post_id, self::$meta_post_custom_fields, lscf_wordpress_escape_unicode_slash( $updated_data ) );
					};

				}
			}
		}
	}

}
