<?php


if ( ! function_exists( 'px_set_as_active' ) ) {

	/**
	 * Set Menu Nav as Active
	 *
	 * @param string $active_nav The current nav that should be active.
	 * @param string $nav_name The nav name.
	 * @param string $active_class_name The name of active class.
	 * @var function
	 */
	function px_set_as_active( $active_nav, $nav_name, $active_class_name ) {

		$active_nav_names = explode( '|',  $nav_name );

		if ( in_array( $active_nav, $active_nav_names ) ) {
			echo esc_attr( $active_class_name );
		}

	}
}

/**
 * Check if value is in array non-case sensitive
 *
 * @param string $string String that needs to be checked.
 * @param string $array Array of strings.
 * @var function
 */
function lscf_in_array( $string, $array ) {

	foreach ( $array as $value ) {

		if ( mb_strtolower( $value ) === mb_strtolower( $string ) ) {
			return true;
		}
	}

	return false;
}

function lscf_regex_sanitize( $string ) {
	return preg_replace( '/([\!\@\#\$\%\^\&\*\(\)\+\=\[\]\\\;\,\.\/\{\}\|\"\:\<\>\?\~\_\'\-])/', '\\\$1', $string );
}
function lscf_regex_escape( $string ) {
	return preg_replace( '/\\\([\!\@\#\$\%\^\&\*\(\)\+\=\[\]\\\;\,\.\/\{\}\|\:\<\>\?\~\_\'\-])/', '$1', $string );
}


/**
 * Add double slashes to Mysql regex special characters
 *
 * @param string $string Regex values that needs to be escaped.
 * @var function
 */
function lscf_sql_regex_escape( $string ) {

	$string = wp_slash( $string );

	$string = preg_replace( '/([\!\@\#\$\%\^\&\*\(\)\+\=\[\]\\\;\,\.\/\{\}\|\"\:\<\>\?\~\_\'\-])/', '\\_|_\\\$1', $string );
	return $string = str_replace( '_|_', '', $string );
}

/**
 * Generate a random string by given length
 *
 * @param int $length The length of generated string.
 * @var function
 */
function get_random_string( $length ) {

	$letters = array(
		'a',
		'b',
		'c',
		'd',
		'e',
		'f',
		'g',
		'h',
		'i',
		'j',
		'k',
		'l',
		'm',
		'n',
		'o',
		'p',
		'r',
		's',
		't',
		'u',
		'v',
		'w',
		'q',
		'y',
		'x',
		'z',
	);

	$word = '';

	for ( $i = 0; $i < $length; $i++ ) {

		$random = rand( 0, 25 );

		$word = $word . $letters[ $random ];
	};

	return $word;

}

/**
 * Return an array with all parents IDs hierarchical
 *
 * @param array $categories_object The wordpress categories list.
 * @var function
 */
function lscf_wp_return_parent_cat_ids( $categories_object, $current_category_parent_id ) {

	$parent_id = $current_category_parent_id;

	$parent_ids = array();

	$hierachical_ordered = array();

	if ( 0 != $parent_id ) {

		$parent_ids[] = $parent_id;

	}


	while ( 0 != $parent_id ) {

		$parent_id = $categories_object[ $parent_id ]->parent;

		$parent_ids[] = $parent_id;

	}

	for (  $i = ( count( $parent_ids ) - 2 ); $i >= 0; $i-- ) {

		$hierachical_ordered[] = $parent_ids[ $i ];

	}

	return $hierachical_ordered;

}

/**
 * Change caregories array key to category ID
 *
 * @param string $categories Categories array.
 * @var function
 */
function lscf_wp_reset_cat_key_to_id( $categories ) {

	$data = array();

	foreach ( $categories as $category ) {
		$data[ $category->term_id ] = $category;
	}

	return $data;

}

/**
 * Sanitize the custom field slug
 *
 * @param string $string The string that needs to be sanitized.
 * @var function
 */
function px_sanitize( $string ) {
	$string = mb_strtolower( preg_replace( '/([^a-z0-9]+)/', '_', $string ) );
	return preg_replace( "/_$/", '', $string );
}

/**
 * Return filter's viewchanger class
 *
 * @param array $viewchanger_params Viewchange views type.
 * @var function
 */
function lscf_return_viewchanger_class( $viewchanger_params ) {

	$grid = ( isset( $viewchanger_params['grid'] ) ? (int) $viewchanger_params['grid'] : 0 );
	$list = ( isset( $viewchanger_params['list'] ) ? (int) $viewchanger_params['list'] : 0 );

	if ( 1 == $grid && 0 == $list ) {
		return 'pxfilter-grid-only';
	};

	if ( 0 == $grid && 1 == $list ) {
		return 'pxfilter-list-only';
	};

	return;
}

/**
 * Get the hierarchy parents of the term. Starts with imediate parent to main parent(parent=0)
 *
 * @param array $term_ids The terms array("term_id"=>"parent_id").
 * @var function
 */
function lscf_group_terms_by_parent( $term_ids ) {

	$results = array();

	foreach ( $term_ids as $term_id => $parent_id ) {

		if ( 0 == $parent_id ) {
			continue;
		}

		$parent = $parent_id;
		$results[ $term_id ][] = $parent_id;

		while ( 0 != $parent ) {

			if ( ! isset( $term_ids[ $parent ] ) || 0 == $term_ids[ $parent ] ) {
				break;
			}
			$parent = $term_ids[ $parent ];
			$results[ $term_id ][] = $parent;
		}
	}

	return $results;

}

/**
 * Sort create a hierarchy dependency on wp terms
 *
 * @param multidimensional array $terms_ids An array of terms.
 * @var function
 */
function lscf_hierarchy_terms( $terms_ids ) {

	$results = array();

	foreach ( $terms_ids as $term_id => $parent_id  ) {

		if ( 0 == $parent_id ) {
			$results[ $term_id ] = array(
				'subcategs' => array(),
				'parent'	=> 0,
				);
		} else {

			if ( ! in_array( $term_id, $results ) ) {

				$results[ $term_id ] = array(
					'subcategs' => array(),
					'parent'	=> $parent_id,
				);
			} else {
				$results[ $term_id ]['parent'] = $parent_id;
			}


			if ( isset( $results[ $parent_id ] ) ) {

				$results[ $parent_id ]['subcategs'][] = $term_id;

				$parent = $parent_id;

				while ( 0 != (int) $parent ) {

					$parent = $results[ $parent ]['parent'];

					if ( 0 != $parent ) {
						$results[ $parent ]['subcategs'][] = $term_id;
					}
				}
			} else {

				$parent = $terms_ids[ $parent_id ];

				$results[ $parent_id ] = array(
					'subcategs' => array( $term_id ),
					'parent'	=> $parent,
				);

				while ( 0 != $parent ) {

					$results[ $parent ]['subcategs'][] = $term_id;
					$results[ $parent ]['parent'][] = $term_ids[ $parent ];

					$parent = $term_ids[ $parent ];
				}
			}
		}
	}

	return $results;
}

	/**
	 * Escape unicode slash
	 *
	 * @param string $string The string to be escaped.
	 * @var function
	 */
function lscf_wordpress_escape_unicode_slash( $string ) {
	$string = preg_replace( '/\\\\*(u[0-9a-fA-F]{4})/i', '#lscf-slash#$1', $string );
	return lscf_sanitize_json_quotes( $string );
};

	/**
	 * Sanitize the json quotes.
	 *
	 * @param string $string The string to be sanitized.
	 * @var function
	 */
function lscf_sanitize_json_quotes( $string ) {
	return str_replace( '\'', '"', $string );
}

	/**
	 * Escape the magic quotes.
	 *
	 * @param string $string The string to be escaped.
	 * @var function
	 */
function lscf_esc_json_magic_quotes( $string ) {
	return str_replace( '"', '\'', $string );
}

function lscf_convert_unicode_character( $string ) {

	$string = wp_unslash( wp_json_encode( $string ) );
	return lscf_wordpress_escape_unicode_slash( preg_replace( '/"(.+?)"/', '$1', $string ) );

}

	/**
	 * Escape the custom fields values from json object
	 *
	 * @param array $custom_field The custom field data.
	 * @var function
	 */
function lscf_esc_json_custom_field_values( $custom_field ) {

	if ( isset( $custom_field['value'] ) ) {
		$custom_field['value'] = lscf_esc_json_magic_quotes( $custom_field['value'] );
	}

	if ( isset( $custom_field['values'] ) ) {
		foreach ( $custom_field['values'] as &$value ) {
			$value = lscf_esc_json_magic_quotes( $value );
		};

		unset( $value );
	}
	return $custom_field;
}

	/**
	 * Add unicode slash to the string.
	 *
	 * @param string $string String.
	 * @var function
	 */
function lscf_wordpress_add_unicode_slash( $string ) {
	$string = preg_replace( '/#lscf-slash#(u[0-9a-fA-F]{4})/i', '\\\$1',  $string );
	return $string;
}

	/**
	 * Escape the JSON pbject of extra slashes.
	 *
	 * @param string $string String.
	 * @var function
	 */
function lscf_esc_json_object( $string ) {
	return str_replace( '\\\'', '\'', wp_unslash( $string ) );
}

	/**
	 * Add unicode slash to the string.
	 *
	 * @param string $lscf_custom_field_id The LSCF custom field ID.
	 * @var function
	 */
function lscf_get_custom_fields( $lscf_custom_field_id ) {

	(int) $post_id = get_the_ID();

	if ( 0 === $post_id || null === $lscf_custom_field_id ) {
		return false;
	}

	$custom_fields_model = new CustomFieldsModel();
	$data = $custom_fields_model->get_post_custom_fields( $post_id );

	if ( '' === $data || ! is_array( $data ) ) { return; }

	foreach ( $data as $type => $single_field ) {

		if ( ! is_array( $single_field ) ) { continue; }

		switch ( $type ) {

			case 'single_value':

				foreach ( $single_field as $field  ) {

					if ( $lscf_custom_field_id ===  $field['ID'] ) {
						return $field['value'];
					}
				}
				break;

			case 'multiple_values':

				foreach ( $single_field as $field  ) {

					if ( $lscf_custom_field_id == $field['ID'] ) {

						if ( 'px_icon_check_box' == $field['field_type'] ) {
							return $field['ivalue'];
						} else {
							return $field['value'];
						}
					}
				}

				break;

		}
	}

}

function lscf_woo_price( $price ){

	if ( ! function_exists ( 'wc_get_price_decimals' ) || ! function_exists ( 'wc_get_price_decimal_separator' ) || ! function_exists ( 'wc_get_price_thousand_separator' ) ) {
		return $price;
	}

	$decimals_number = wc_get_price_decimals();
	$decimals_separator = wc_get_price_decimal_separator();
	$thousand_separator = wc_get_price_thousand_separator();

	if ( 0 == (float) $price ) {  return $price; }

	return number_format( (float) $price, $decimals_number, $decimals_separator, $thousand_separator );
}


function lscf_woo_filter_price( $price, $filter_settings = null ) {
	if ( null === $filter_settings || ! isset( $filter_settings['options'] ) ) {
		return $price;
	}

	if ( ! isset( $filter_settings['options']['woo_price_format'] ) || 1 !== $filter_settings['options']['woo_price_format'] ) {
		return $price;
	}

	return lscf_woo_price( $price );
}

function lscf_return_theme_languages_path() {

	$theme_directory = get_template_directory_uri();
	$theme_directory = $theme_directory[1];

	return $theme_directory . '/languages';

}

function lscf_sanitize_post_content( $html ) {
	$content = preg_replace( '/\[(.+?)\]/', '', $html );
	return $content;
	// $conent = preg_replace( '/\<.*?img.+?\>/', '', $content );
	// $content = preg_replace( '/(?!src=(\"|\')).+?\.(jpeg|png|jpg)/', '', $content );
	return $content;
}

function lscf_permute( $str, $l, $r, &$regex = null ) {

	if ( $l === $r ) {
		$regex .= '(' . str_replace( ' ', '[^>]*', $str ) . ')|';
	} else {
		for ( $i = $l; $i <= $r; $i++ ) {
			$str = lscf_swap( $str, $l, $i );
			lscf_permute( $str, $l + 1, $r, $regex );
			$str = lscf_swap( $str, $l, $i );
		}
	}
}

/**
* Swap Characters at position
* @param a string value
* @param i position 1
* @param j position 2
* @return swapped string
*/
function lscf_swap( $a, $i, $j ) {
	$temp;
	$charArray = explode( ' ', $a );
	$temp = $charArray[ $i ];
	$charArray[ $i ] = $charArray[ $j ];
	$charArray[ $j ] = $temp;
	return implode( ' ', $charArray );
}

