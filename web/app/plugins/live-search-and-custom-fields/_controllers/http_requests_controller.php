<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
require_once LSCF_PLUGIN_PATH . '_controllers/data_custom_fields_controller.php';
require_once LSCF_PLUGIN_PATH . '_models/http_requests_model.php';

/**
 * Class HttpRequestsController Controller used for angular $http requests
 *
 * @category Controller
 * @package  HttpRequests
 * @author   PIXOLETTE
 * @license  http://wp.pixollete.com
 * @link     http://wp.pixollete.com
 **/
class HttpRequestsController {

	/**
	 * The meta name of the price variations cache.
	 *
	 * @access public
	 * @var string
	 */
	public $woo_cache_meta_name = 'lscf_woocommerce_price_variations_cache';

	/**
	 * The meta name of the WooCommerce price. It's used to sort products by WooCommerce price.
	 *
	 * @access public
	 * @var string
	 */
	public $woo_orderby_price_meta_name = '_price';

	/**
	 * The Custom Fields Data Controller.
	 *
	 * @access private
	 * @var Class|Object
	 */
	private $class_data_custom_fields;

	/**
	 * The Http Requests Model.
	 *
	 * @access private
	 * @var Class|Object
	 */
	private $model;

	/**
	 * The Class constructor.
	 * Load the HTTP Requests Model
	 *
	 * @access public
	 * @var function|Class constructor
	 */
	function __construct() {

		$this->model = new HttpRequestsModel();
		$this->class_data_custom_fields = new LscfDataCustomFieldsControoler();

	}

	/**
	 * Get the plugin's general options.
	 *
	 * @access public
	 * @var function
	 */
	public function get_general_settings() {
		$date_format = get_option( 'date_format' );
		return array(
			'date_format' => $date_format,
		);
	}

	/**
	 * Return variations prices for variable products
	 *
	 * @param int $post_id The variations array.
	 * @access public
	 * @var function
	 */
	function return_woo_variation_prices( $post_id, $woo_product ) {

		$cached_variations = get_post_meta( $post_id, $this->woo_cache_meta_name, true );

		if ( isset( $cached_variations['variations_price'] ) ) {

			$cached_variation_prices = $cached_variations['variations_price'];

			$price_min = ( isset( $cached_variation_prices['min'] ) ? $cached_variation_prices['min'] : '' );
			$price_max = ( isset( $cached_variation_prices['max'] ) ? $cached_variation_prices['max'] : '' );

			return array( 'min' => $price_min, 'max' => $price_max );

		}

		$variations = $woo_product->get_available_variations();
		$prices = array();

		foreach ( $variations as $variation ) {

			if ( ! isset( $variation['display_price'] ) || ! isset( $variation['display_regular_price'] ) ) { continue; }

			$prices[] = $variation['display_price'];
			$prices[] = $variation['display_regular_price'];

		}

		if ( 0 === count( $prices ) ) {
			return array(
				'min' => '',
				'max' => '',
			);
		};

		return array(
			'min' => min( $prices ),
			'max' => max( $prices ),
		);

	}

	/**
	 * Retreive the Uniqie ID from field object.
	 *
	 * @param array $field The LSCF field object.
	 * @access public
	 * @var function
	 */
	public static function get_field_unique_id( $field ) {

		if ( isset( $field['ID'] ) ) {
			return $field['ID'];
		};

		if ( isset( $field['fieldID'] ) ) {
			return $field['type'] . '_' . $field['fieldID'];
		};

		if ( isset( $field['search_by'] ) || 'woo-search-default' === $field['type'] || 'search' === $field['type'] ) {
			return 'search';
		}

		return rand( 10, 999 );

	}

	/**
	 * Retrieve subcategories from each term in a hierarchical order.
	 *
	 * @param string $content The WordPress post content.
	 * @param int 	 $post_id The post ID.
	 * @access public
	 * @var function
	 */
	public function build_lscf_shortcodes( $content, $post_id ) {

		$shortcodes_content = '';
		preg_match_all( '/(\[lscf.+?\])/', $content, $lscf_shortcodes_matches );

		if ( 0 === count( $lscf_shortcodes_matches[0] ) ) { return ''; }

		foreach ( $lscf_shortcodes_matches[0] as $shortcode ) {

			$shortcode_post_id = ' ';

			preg_match( '/(.+?)\]$/', $shortcode, $match );

			if ( ! preg_match( '/post_id=[0-9]+/', $shortcode ) ) {
				$shortcode_post_id = ' post_id=' . $post_id . ' ';
			}

			$shortcode = $match[1] . $shortcode_post_id . ' loaded_from="filter"]';

			$shortcodes_content .= $shortcode;
		}

		return do_shortcode( $shortcodes_content );
	}
	/**
	 * Retrieve subcategories from each term in a hierarchical order.
	 *
	 * @param array $taxonomy Filter taxonomy array.
	 * @access public
	 * @var function
	 */
	function retrieve_subcategories_hierarchical( $taxonomy ) {

		$cc = 0;
		$terms_ids = array();
		$cat_data = array();

		foreach ( $taxonomy['terms'] as $term ) {

			if ( ! isset( $term['subcategs'] ) || '' == $term['subcategs'] ) {
				continue;
			}

			$subcategs = get_terms( $taxonomy['slug'], array( 'child_of' => (int) $term['data']['value'], 'hide_empty' => false ) );

			$terms_ids[ $term['data']['value'] ] = 0;

			$cat_data[ $term['data']['value'] ] = $term['data'];

			foreach ( $subcategs as $subcateg ) {

				$cat_data[ $subcateg->term_id ] = $subcateg;

				$terms_ids[ $subcateg->term_id ] = $subcateg->parent;

				if ( 0 == $subcateg->parent ) {
					continue;
				}

				$taxonomy['subcategs'][ $subcateg->parent ]['parent_id'] = (int) $subcateg->parent;
				$taxonomy['subcategs'][ $subcateg->parent ]['data'][ $cc ] = $subcateg;
				$taxonomy['subcategs'][ $subcateg->parent ]['main_parent'] = $term['data']['value'];
				$taxonomy['subcategs'][ $subcateg->parent ]['display_as'] = $term['display_as'];
				$taxonomy['subcategs'][ $subcateg->parent ]['parent_name'] = ( isset( $term['parent_name'] ) ? $term['parent_name'] : '' );

				$cc++;
			}
		}

		$subcategs = array();

		$sc = 0;

		if ( isset( $taxonomy['subcategs'] ) ) {

			foreach ( $taxonomy['subcategs'] as $subcateg ) {

				$subcategs[ $sc ]['data'] = array_values( $subcateg['data'] );
				$subcategs[ $sc ]['parent_id'] = $subcateg['parent_id'];
				$subcategs[ $sc ]['main_parent_id'] = $subcateg['main_parent'];
				$subcategs[ $sc ]['display_as'] = $subcateg['display_as'];

				$sc++;
			}
		}

		$taxonomy['categs'] = $cat_data;
		$taxonomy['subcategs'] = $subcategs;
		$taxonomy['subcategs_hierarchy'] = lscf_group_terms_by_parent( $terms_ids );

		return $taxonomy;
	}


	/**
	 * Retrieve all active terms from current post
	 *
	 * @param array  $post_ids An array of posts IDs.
	 * @param array  $taxonomies An array of all available taxonomies from custom post type.
	 * @param string $post_type The post type.
	 *
	 * @access public
	 * @var function|Class method
	 */
	public function get_posts_active_terms( $post_ids, $taxonomies, $post_type ) {

		$active_posts_terms = array();

		foreach ( $taxonomies as $taxonomy ) {

			for ( $i = 0; $i < count( $post_ids ); $i++ ) {

				$terms = get_the_terms( (int) $post_ids[ $i ], $taxonomy );

				if ( ! is_array( $terms ) ) {
					continue;
				}

				foreach ( $terms as $term ) {
					$active_posts_terms[ $term->term_id ] = $term->term_id;
				};

				$custom_fields = json_decode( lscf_wordpress_add_unicode_slash( get_post_meta( (int) $post_ids[ $i ], 'px-custom_fields', true ) ), true );

				foreach ( $custom_fields as $key => $custom_field ) {

					if ( 'px_date' == $custom_field['field_type'] || 'px_text' == $custom_field['field_type'] || ! isset( $custom_field['value'] ) ) {
						continue;
					}

					if ( is_array( $custom_field['value'] ) ) {

						foreach ( $custom_field['value'] as $value ) {

							$slug = $key . px_sanitize( $value );
							$active_posts_terms['custom_fields'][ $slug ] = $slug;

						}
					} else {

						$slug = $key . px_sanitize( $custom_field['value'] );
						$active_posts_terms['custom_fields'][ $slug ] = $slug;

					}
				}
			}
		}

		$active_posts_terms['length'] = count( $active_posts_terms );

		return $active_posts_terms;
	}

	/**
	 * Format the WooCommerce price currency: on left or right. 
	 *
	 * @access public
	 * @param int $price The product price.
	 * @param string $currency The price currency
	 * @var function
	 */
	public function format_woo_currency_price( $price, $currency ) {

		$currency_pos = get_option('woocommerce_currency_pos');

		if ( 'right' === $currency_pos ) {
			return $price . $currency;
		}
		return $currency . $price;

	}

	/**
	 * Switch to the active WPML language.
	 *
	 * @access public
	 * @param string $lang The WPML active language.
	 * @var function
	 */
	public function switch_wpml_language( $lang ) {
		global $sitepress;
		$sitepress->switch_lang( $lang );
	}

	/**
	 * Merge terms from same taxonomy into single one.
	 *
	 * @access public
	 * @param array $taxonomies An array of taxonomies.
	 * @var function
	 */
	public function combine_same_slug_tax_terms( $taxonomies ) {

		$data = [];

		foreach ( $taxonomies as $tax ) {
			if ( ! isset( $data[ $tax->ID ] ) ) {
				$data[ $tax->ID ] = $tax;
				if ( ! is_array( $tax->value ) ) {
					$data[ $tax->ID ]->value = array( $tax->value );
				}
			} else {
				if ( ! is_array( $tax->value ) ) {
					$data[ $tax->ID ]->value[] = $tax->value;
				} else {
					$data[ $tax->ID ]->value = array_merge( $data[ $tax->ID ]->value, $tax->value );
				}
			}
		}

		return array_values( $data );
	}

	/**
	 * Load filter sidebar.
	 *
	 * @access public
	 * @var function
	 */
	public function load_filter_sidebar() {

		$post_data = json_decode( file_get_contents( 'php://input' ) );

		$filter_id = $post_data->filter_id;

		$filter_data = get_option( PluginMainModel::$meta_name_plugin_settings, true );
		$filter_data = json_decode( $filter_data, true );

		if ( ! isset( $filter_data['filterList'] ) ) {
			exit();
		}

		if ( ! isset( $filter_data['filterList'][ $filter_id ] ) ) {
			exit();
		}

		$custom_template_data = ( isset( $filter_data['custom_templates'] ) && count( $filter_data['custom_templates'] ) > 0 ? $filter_data['custom_templates'] : false  );

		$filter_data = $filter_data['filterList'][ $filter_id ];

		$name = $filter_data['name'];
		$filter_type = ( isset( $filter_data['filter_type'] ) ? $filter_data['filter_type'] : 'custom-posts' );

		$additional_fields = array();
		$post_taxonomies = array();

		$custom_fields_data = get_option( PluginMainModel::$options_custom_fields, true );

		$custom_fields_data = json_decode( $custom_fields_data, true );
		$custom_fields_data = (isset( $custom_fields_data[ $filter_data['post_type'] ] ) ? $custom_fields_data[ $filter_data['post_type'] ] : array() );

		$count = 0;
		$init_instock_woocommerce = true;
		$filter_fields_data = array();
		$filter_fields_object = array();

		if ( isset( $filter_data['shortcode_options'] ) &&
			isset( $filter_data['shortcode_options']['saved_field_options'] ) &&
			1 == $filter_data['shortcode_options']['saved_field_options'] &&
			isset( $filter_data['fields'][0]['type'] ) ) {

			$filter_fields_data = $filter_data['fields'];

		} else {

			foreach ( $filter_data['fields'] as &$field ) {

				switch ( $field['group_type'] ) {

					case 'custom_field':

						if ( 'woocommerce-instock' == $field['ID'] && true === $init_instock_woocommerce ) {

							$init_instock_woocommerce = false;

							$display_as = ( isset( $field['display'] ) && 'default' != $field['display'] ? $field['display'] : 'px_select_box' );

							$filter_fields_data[ $count ]['type'] = 'px_select_box';
							$filter_fields_data[ $count ]['ID'] = 'woocommerce-instock';
							$filter_fields_data[ $count ]['name'] = __( 'Availability', 'lscf' );
							$filter_fields_data[ $count ]['display_as'] = $display_as;
							$filter_fields_data[ $count ]['group_type'] = $field['group_type'];

							$filter_fields_data[ $count ]['options'][0]['opt'] = __( 'In Stock', 'lscf' );
							$filter_fields_data[ $count ]['options'][0]['value'] = 'instock';
							$filter_fields_data[ $count ]['options'][1]['opt'] = __( 'Out of Stock', 'lscf' );
							$filter_fields_data[ $count ]['options'][1]['value'] = 'outofstock';

							$field['options'] = $filter_fields_data[ $count ]['options'];
							$field['type'] = $filter_fields_data[ $count ]['type'];

							$count++;

							continue;
						}

						if ( null !== $custom_fields_data && count( $custom_fields_data ) > 0 ) {

							foreach ( $custom_fields_data as $field_type => $f_data ) {

								foreach ( $f_data as $id => $single_field ) {

									if ( ! isset( $field['ID'] ) ) { continue; }

									if ( $id == $field['ID'] ) {

										if ( 'px_cf_relationship' == $single_field['slug'] ) {

											$cf_fields_data = $custom_fields_data;
											$main_cf_id = $single_field['parent'];
											$child_ids = array();

											if ( isset( $single_field['items'] ) ) {

												foreach ( $single_field['items'] as $item ) {
													$child_ids[] = $item['cf_id'];
												}
											}

											foreach ( $cf_fields_data as $group_id => $cf_field_group ) {

												if ( 'px_cf_relationship' == $group_id ) {
													continue;
												}

												foreach ( $cf_field_group as $cf_id => $cf_field ) {

													if ( in_array( $cf_id, $child_ids, true ) ) {

														$filter_fields_data[ $count ]['items'][] = $cf_field;
														$field['items'][] = $cf_field;

													}

													if ( $cf_id == $main_cf_id ) {

														$filter_fields_data[ $count ]['parent'] = $cf_field;
														$field['parent'] = $cf_field;

													}
												}
											}

											$display_as = ( isset( $field['display'] ) && 'default' != $field['display'] ? $field['display'] : $field_type );

											$filter_fields_data[ $count ]['type'] = $field_type;
											$filter_fields_data[ $count ]['ID'] = $id;
											$filter_fields_data[ $count ]['name'] = __( $single_field['name'], 'lscf' );
											$filter_fields_data[ $count ]['display_as'] = $display_as;
											$filter_fields_data[ $count ]['group_type'] = 'cf_variation';

											$field['display_as'] = $display_as;
											$field['name'] = __( $single_field['name'], 'lscf' );
											$field['options'] = ( isset( $filter_fields_data[ $count ]['options'] ) ? __( $filter_fields_data[ $count ]['options'], 'lscf' ) : array()  );
											$field['type'] = $filter_fields_data[ $count ]['type'];

											$count++;

											continue;
										}

										$display_as = ( isset( $field['display'] ) && 'default' != $field['display'] ? $field['display'] : $field_type );

										$filter_fields_data[ $count ]['type'] = $field_type;
										$filter_fields_data[ $count ]['ID'] = $id;
										$filter_fields_data[ $count ]['name'] = __( $single_field['name'], 'lscf' ) ;
										$filter_fields_data[ $count ]['display_as'] = $display_as;
										$filter_fields_data[ $count ]['group_type'] = $field['group_type'];

										if ( isset( $single_field['options'] ) ) {
											if ( 'px_select_box' == $field_type || 'px_radio_box' == $field_type || 'px_check_box' == $field_type ) {
												foreach( $single_field['options'] as $opt ) {
													$filter_fields_data[ $count ]['options'][]['opt'] = __( $opt, 'lscf' );
												}
											} else {
												foreach ( $single_field['options'] as &$option ) {
													$option['opt'] = __( $option['opt'], 'lscf' );
												}
												$filter_fields_data[ $count ]['options'] = $single_field['options'];
											}
										}

										$field['display_as'] = $display_as;
										$field['name'] = __( $single_field['name'], 'lscf' );
										$field['options'] = @$filter_fields_data[ $count ]['options'];
										$field['type'] = $filter_fields_data[ $count ]['type'];

										$count++;

									}
								}
							}
						}

						break;

					case 'taxonomies':

						$taxonomy = $field;

						if ( isset( $taxonomy['display_all_terms'] ) && 1 === $taxonomy['display_all_terms'] ) {

							$tax_terms = get_terms( $taxonomy['slug'], array( 'hide_empty' => false ) );

							foreach ( $tax_terms as $term ) {
								$taxonomy['terms'][] = array(
									'data' => array(
										'value' => $term->term_id,
										'name'	=> $term->name,
									),
								);
							}
							$taxonomy_data = $taxonomy;
						}

						if ( isset( $taxonomy['display_all_terms'] )
							&& 1 !== $taxonomy['display_all_terms']
							&& ( ! isset( $taxonomy['terms'] ) || empty( $taxonomy['terms'] ) || count( $taxonomy['terms'] ) < 1 ) ) {
								continue;
						}

						if ( 1 == $taxonomy['subcategories_hierarchy_display'] ) {

							$taxonomy_data = $this->retrieve_subcategories_hierarchical( $taxonomy );

						} else {

							$s_count = count( $taxonomy['terms'] );

							foreach ( $taxonomy['terms'] as $term ) {

								if ( ! isset( $term['subcategs'] ) ) {
									continue;
								}

								$subcategs = get_terms( $taxonomy['slug'], array( 'child_of' => (int) $term['data']['value'], 'hide_empty' => false ) );

								foreach ( $subcategs as $subcateg ) {
									$taxonomy['terms'][ $s_count ]['data']['value'] = $subcateg->term_id;
									$taxonomy['terms'][ $s_count ]['data']['name'] = $subcateg->name;
									$s_count++;
								}
							}

							$taxonomy_data = $taxonomy;
						}

						$filter_fields_data[ $count ]['type'] = 'checkbox_post_terms';
						$filter_fields_data[ $count ]['ID'] = $taxonomy_data['slug'];
						$filter_fields_data[ $count ]['tax'] = $taxonomy_data;
						$filter_fields_data[ $count ]['group_type'] = $taxonomy_data['group_type'];

						$post_taxonomies[ $taxonomy['slug'] ] = $filter_fields_data[ $count ];

						$field['ID'] = $taxonomy_data['slug'];
						$field['type'] = 'checkbox_post_terms';
						$field['group_type'] = $taxonomy_data['group_type'];
						$field['tax'] = $taxonomy_data;


						$count++;

						break;

					case 'additional_fields':

						switch ( $field['type'] ) {

							case 'search':

								if ( 'woocommerce' == $filter_data['filter_type'] ) {

									$filter_fields_data[ $count ]['type'] = 'woo-search-' . $field['search_by'];
									$filter_fields_data[ $count ]['name'] = __( wp_unslash( $field['name'] ), 'lscf' );
									$filter_fields_data[ $count ]['group_type'] = $field['group_type'];
									$count++;

								} else {
									$filter_fields_data[ $count ]['type'] = 'search';
									$filter_fields_data[ $count ]['name'] = __( wp_unslash( $field['name'] ), 'lscf' );
									$filter_fields_data[ $count ]['group_type'] = $field['group_type'];
									$count++;
								}


								break;

							case 'date-interval':

									$filter_fields_data[ $count ]['type'] = 'date-interval';
									$filter_fields_data[ $count ]['name'] = __( wp_unslash( $field['name'] ), 'lscf' );
									$filter_fields_data[ $count ]['key'] = $field['key'];
									$filter_fields_data[ $count ]['fieldID'] = $field['fieldID'];
									$filter_fields_data[ $count ]['group_type'] = $field['group_type'];
									$count++;

								break;

							case 'range':

									$filter_fields_data[ $count ]['type'] = 'range';
									$filter_fields_data[ $count ]['name'] = __( wp_unslash( $field['name'] ), 'lscf' );
									$filter_fields_data[ $count ]['key'] = $field['key'];
									$filter_fields_data[ $count ]['min'] = $field['min'];
									$filter_fields_data[ $count ]['max'] = $field['max'];
									$filter_fields_data[ $count ]['label'] = $field['label'];
									$filter_fields_data[ $count ]['label_position'] = ( isset( $field['label_position'] ) ? $field['label_position'] : 'left' );
									$filter_fields_data[ $count ]['fieldID'] = @$field['fieldID'];
									$filter_fields_data[ $count ]['group_type'] = $field['group_type'];
									$count++;

								break;
						}

						break;

				}
			}
		}

		for ( $i = 0; $i < count( $filter_fields_data ); $i++ ) {
			$unique_field_id = $this->get_field_unique_id( $filter_fields_data[ $i ] );
			$filter_fields_object[ $unique_field_id ] = $filter_fields_data[ $i ];
		}

		if ( false !== $custom_template_data ) {
			$filter_data['custom_templates'] = $custom_template_data;
		}

		$general_settings = $this->get_general_settings();
		return
			array(
				'settings' => $general_settings,
				'title' => $name,
				'fields' => $filter_fields_data,
				'fields_object' => $filter_fields_object,
				'filter_type' => $filter_data['filter_type'],
				'post_taxonomies' => $post_taxonomies,
				'default_data' => $filter_data,
			);
	}

	/**
	 * Filter posts.
	 *
	 * @access public
	 * @var function
	 */
	public function filter_posts() {

		$post_data = json_decode( file_get_contents( 'php://input' ) );

		$filter_id = $post_data->filter_id;
		$all_matched_posts = array();

		$filter_data = get_option( PluginMainModel::$meta_name_plugin_settings, true );

		$filter_data = json_decode( $filter_data, true );


		if ( ! isset( $filter_data['filterList'] ) && $filter_id != $post_data->post_type ) {
			echo array( 'error' => 1, 'message' => 'failed to load posts' );
			die();
		}

		if ( ! isset( $filter_data['filterList'][ $filter_id ] ) && $filter_id != $post_data->post_type ) {
			echo array( 'error' => 1, 'message' => 'failed to load posts' );
			die();
		}

		if ( $filter_id == $post_data->post_type ) {
			$filter_data['filter_type'] = 'not-available';
		} else {
			$filter_data = $filter_data['filterList'][ $filter_id ];
		}


		$featured_label_status = 0;
		if ( isset( $filter_data['featuredLabelFieldID'] ) && '' !== $filter_data['featuredLabelFieldID'] ) {
			$featured_label_status = 1;
		}

		if ( 'woocommerce' == $filter_data['filter_type'] ) {
			$woo_price_currency = get_woocommerce_currency_symbol();
		};

		$lscf_custom_fields_order_as = 'meta_value_num';
		if ( isset( $filter_data['options']['lscf_custom_fields_order_as'] ) && 'string' === $filter_data['options']['lscf_custom_fields_order_as'] ) {
			$lscf_custom_fields_order_as = 'meta_value';
		};


		$checkboxes_conditional_logic = ( isset( $filter_data['options']['checkboxes_conditional_logic'] ) && '' !== $filter_data['options']['checkboxes_conditional_logic'] ? $filter_data['options']['checkboxes_conditional_logic'] : 'or' );

		$all_taxonomies_names = get_object_taxonomies( $post_data->post_type, 'names' );

		if ( 1 === $post_data->is_wpml ) {
			$this->switch_wpml_language( $post_data->lang );
		}

		if ( isset( $post_data->q ) && is_array( $post_data->q ) && count( $post_data->q ) > 0 ) {

			$additional_filter_fields = array();
			$post_taxonomies = array();
			$general_search_keyword = null;
			$woo_product_sku_search = null;
			$order_by = array();
			$default_filter = array();

			foreach ( $post_data->q as $field ) {

				if ( ! isset( $field->type ) ) {
					$field->type = '';
				}

				if ( isset( $field->filter_as ) && '' != $field->filter_as ) {
					$f_type = $field->filter_as;
				} else {
					$f_type = $field->type;
				}

				if ( 'date-interval' === $field->type ) {

					$additional_filter_fields[] = $field;

					continue;

				};

				if ( 'checkbox_post_terms' === $f_type  ) {

					if ( preg_match( '/(.+?)_-_([0-9]+)$/', $field->ID, $matches ) ) {
						$field->ID = $matches[1];
					}

					$post_taxonomies[] = $field;

					continue;
				}

				if ( 'main-search' == $field->type ) {

					if ( 'ajax-main-search' == $field->ID ) {
						$general_search_keyword = $field->value;
					}

					continue;
				}

				if ( 'order-posts' == $field->type  ) {

					switch ( $field->value ) {

						case 'post_date':
							$order_by['by'] = 'date';
							$order_by['order'] = strtoupper( $field->order );

							break;

						case 'post_title':

							$order_by['by'] = 'title';
							$order_by['order'] = strtoupper( $field->order );

							break;

						case 'woo_price':

							$order_by['meta_key'] = $this->woo_orderby_price_meta_name;
							$order_by['by'] = 'meta_value_num';
							$order_by['order'] = ( isset( $field->order ) ? strtoupper( $field->order ) : 'ASC' );

							break;

						default :
							$order_by['meta_key'] = $field->value;
							$order_by['by'] = $lscf_custom_fields_order_as;
							$order_by['order'] = strtoupper( $field->order );
							break;
					}
					continue;
				}

				if ( 'default_filter' == $field->type ) {

					$default_filter['px_default_filter'] = $field->default_filter;
					$default_filter['tax_query']['relation'] = 'OR';

					if ( isset( $field->default_filter->post_taxonomies ) ) {

						$default_post_taxs = $field->default_filter->post_taxonomies;
						$tax_count = 0;

						foreach ( $default_post_taxs as $tax_data ) {

							if ( ! isset( $tax_data->tax->terms ) ) {
								continue;
							}

							$terms = array();
							foreach ( $tax_data->tax->terms as $term ) {
								$terms[] = $term->data->value;
							}

							if ( count( $terms ) > 0 ) {

								$default_filter['tax_query'][ $tax_count ]['taxonomy'] = $tax_data->ID;
								$default_filter['tax_query'][ $tax_count ]['field'] = 'term_id';
								$default_filter['tax_query'][ $tax_count ]['terms'] = $terms;
								$default_filter['tax_query'][ $tax_count ]['operator'] = 'IN';
								$default_filter['tax_query'][ $tax_count ]['include_children'] = false;

								$tax_count++;
							}
						}
					}
					continue;
				}
			}

			$limit = ( count( $additional_filter_fields ) > 0 ? 500 : (int) $post_data->limit );

			$args = array(
				'post_type' => $post_data->post_type,
				'post_status' => 'publish',
				'posts_per_page' => $limit,
				'paged' => (int) $post_data->page,
				'checkboxes_conditional_logic' => $checkboxes_conditional_logic,
				'lscf-available-custom-fields' => $this->class_data_custom_fields->list_custom_fields_by_post_type( $filter_data['post_type'] ),
			);

			if ( count( $order_by ) > 0 ) {

				if ( isset( $order_by['meta_key'] ) ) {
					$args['meta_key'] = $order_by['meta_key'];
				}

				$args['orderby'] = $order_by['by'];
				$args['order'] = $order_by['order'];

			} elseif ( isset( $filter_data['options'] ) && isset( $filter_data['options']['default_order_by'] ) && isset( $filter_data['options']['default_order_by']['value'] ) ) {

				switch ( $filter_data['options']['default_order_by']['value'] ) {

					case 'post_title':

						$args['order'] = ( isset( $filter_data['options']['default_order_by']['order_as'] ) ? strtoupper( $filter_data['options']['default_order_by']['order_as'] ) : 'ASC' );
						$args['orderby'] = 'title';

						break;

					case 'post_date':

						$args['order'] = ( isset( $filter_data['options']['default_order_by']['order_as'] ) ? strtoupper( $filter_data['options']['default_order_by']['order_as'] ) : 'ASC' );
						$args['orderby'] = 'date';

						break;

					case 'id':

						$args['order'] = ( isset( $filter_data['options']['default_order_by']['order_as'] ) ? strtoupper( $filter_data['options']['default_order_by']['order_as'] ) : 'ASC' );
						$args['orderby'] = 'ID';

						break;

					case 'menu_order':

						$args['order'] = ( isset( $filter_data['options']['default_order_by']['order_as'] ) ? strtoupper( $filter_data['options']['default_order_by']['order_as'] ) : 'ASC' );
						$args['orderby'] = 'menu_order';

						break;

					case 'woo_price':

						$args['meta_key'] = $this->woo_orderby_price_meta_name;
						$args['orderby'] = 'meta_value_num';
						$args['order'] = ( isset( $filter_data['options']['default_order_by']['order_as'] ) ? strtoupper( $filter_data['options']['default_order_by']['order_as'] ) : 'ASC' );

						break;

					default:

						$args['meta_key'] = $filter_data['options']['default_order_by']['value'];
						$args['orderby'] = $lscf_custom_fields_order_as;
						$args['order'] = ( isset( $filter_data['options']['default_order_by']['order_as'] ) ? strtoupper( $filter_data['options']['default_order_by']['order_as'] ) : 'ASC' );

						break;
				}
			}

			if ( count( $default_filter ) > 0 ) {

				$args['px_default_filter'] = $default_filter['px_default_filter'];
				$args['tax_query']['relation'] = 'OR';
				$args['tax_query'] = $default_filter['tax_query'];

			}

			if ( isset( $post_data->q->default_filter ) ) {

				$args['px_default_filter'] = $post_data->q->default_filter;
				$args['tax_query']['relation'] = 'OR';
				$conditional_operator = ( 'or' === $filter_data['options']['checkboxes_conditional_logic'] ? 'IN' : 'AND' );

				if ( isset( $post_data->q->default_filter->post_taxonomies ) ) {

					$default_post_taxs = $post_data->q->default_filter->post_taxonomies;
					$tax_count = 0;

					foreach ( $default_post_taxs as $tax_data ) {

						if ( ! isset( $tax_data->tax->terms ) ) {
							continue;
						}

						$terms = array();

						foreach ( $tax_data->tax->terms as $term ) {
							$terms[] = $term->data->value;
						}

						if ( count( $terms ) > 0 ) {

							$args['tax_query'][ $tax_count ]['taxonomy'] = $tax_data->ID;
							$args['tax_query'][ $tax_count ]['field'] = 'term_id';
							$args['tax_query'][ $tax_count ]['terms'] = $terms;
							$args['tax_query'][ $tax_count ]['operator'] = $conditional_operator;
							$args['tax_query'][ $tax_count ]['include_children'] = false;

							$tax_count++;
						}
					}
				}
			} else {
				$args['px_custom_fields'] = $post_data->q;
			};

			if ( isset( $additional_filter_fields ) && count( $additional_filter_fields ) > 0 ) {

				foreach ( $additional_filter_fields as $field ) {

					if ( 'px-woocommerce-price' == $field->ID && 'woocommerce' == $filter_data['filter_type'] ) {

						if ( ! isset( $args['meta_query'] ) ) {
							$args['posts_per_page'] = (int) $post_data->limit;
						}

						$range_max_value = (int) $field->value->max;
						$range_min_value = (int) $field->value->min;

					} elseif ( 'px-woocommerce-inventory' == $field->ID && 'woocommerce' == $filter_data['filter_type'] ) {

						if ( ! isset( $args['meta_query'] ) ) {
							$args['posts_per_page'] = (int) $post_data->limit;
						}

						$range_max_value = (int) $field->value->max;
						$range_min_value = (int) $field->value->min;

					}
				}
			}

			if ( null != $general_search_keyword ) {

				$args['s'] = sanitize_text_field( $general_search_keyword );
				$args['general-search'] = sanitize_text_field( $general_search_keyword );

				if ( isset( $filter_data['options'] ) && isset( $filter_data['options']['general_search_by'] ) ) {

					$args['search_by_lscf_custom_fields'] = $filter_data['options']['general_search_by'];
					$args['search_by_cf_conditional_logic'] = $filter_data['options']['general_search_conditional_logic'];
					$args['general_search_algorithm'] = $filter_data['options']['general_search_algorithm'];
				}

			}

			if ( isset( $filter_data['options'] ) && isset( $filter_data['options']['range_filtering_type'] ) ) {
				$args['range_filtering_type'] = $filter_data['options']['range_filtering_type'];
			};

			if ( isset( $post_taxonomies ) && count( $post_taxonomies ) > 0 ) {

				$conditional_operator = ( 'or' === $filter_data['options']['checkboxes_conditional_logic'] ? 'IN' : 'AND' );

				$post_taxonomies = $this->combine_same_slug_tax_terms( $post_taxonomies );
				$taxonomy_count = 0;
				foreach ( $post_taxonomies as $taxonomy ) {

					if ( ( is_array( $taxonomy->value ) && 0 == count( $taxonomy->value ) ) || '' == $taxonomy->value ) { continue; }

					$args['tax_query'][ $taxonomy_count ] = 'AND';

					$args['tax_query'][ $taxonomy_count ] = array(
						'taxonomy' 			=> $taxonomy->ID,
						'field' 			=> 'term_id',
						'terms' 			=> $taxonomy->value,
						'operator'			=> $conditional_operator,
						'include_children' 	=> false,
					);

					$taxonomy_count++;
				}
			};

			$posts_q = new PxWpQuery( $args );

		} else {

			$args = array(
				'post_type'			=> $post_data->post_type,
				'post_status'		=> 'publish',
				'posts_per_page'	=> (int) $post_data->limit,
				'paged'				=> (int) $post_data->page,
			);

			if ( isset( $filter_data['options'] ) && isset( $filter_data['options']['default_order_by'] ) && isset( $filter_data['options']['default_order_by']['value'] ) ) {

				switch ( $filter_data['options']['default_order_by']['value'] ) {

					case 'post_title':

						$args['order'] = ( isset( $filter_data['options']['default_order_by']['order_as'] ) ? strtoupper( $filter_data['options']['default_order_by']['order_as'] ) : 'ASC' );
						$args['orderby'] = 'title';

						break;

					case 'post_date':

						$args['order'] = ( isset( $filter_data['options']['default_order_by']['order_as'] ) ? strtoupper( $filter_data['options']['default_order_by']['order_as'] ) : 'ASC' );
						$args['orderby'] = 'date';

						break;

					case 'id':

						$args['order'] = ( isset( $filter_data['options']['default_order_by']['order_as'] ) ? strtoupper( $filter_data['options']['default_order_by']['order_as'] ) : 'ASC' );
						$args['orderby'] = 'ID';

						break;

					case 'menu_order':

						$args['order'] = ( isset( $filter_data['options']['default_order_by']['order_as'] ) ? strtoupper( $filter_data['options']['default_order_by']['order_as'] ) : 'ASC' );
						$args['orderby'] = 'menu_order';

						break;

					case 'woo_price':

						$args['meta_key'] = $this->woo_orderby_price_meta_name;
						$args['orderby'] = 'meta_value_num';
						$args['order'] = ( isset( $filter_data['options']['default_order_by']['order_as'] ) ? strtoupper( $filter_data['options']['default_order_by']['order_as'] ) : 'ASC' );

						break;

					default:

						$args['meta_key'] = $filter_data['options']['default_order_by']['value'];
						$args['orderby'] = $lscf_custom_fields_order_as;
						$args['order'] = ( isset( $filter_data['options']['default_order_by']['order_as'] ) ? strtoupper( $filter_data['options']['default_order_by']['order_as'] ) : 'ASC' );

						break;
				}
			}

			$posts_q = new WP_QUERY( $args );

		}

		$data_posts = array();

		$posts_count = $posts_q->found_posts;

		$total_pages = $posts_q->max_num_pages;

		$count = 0;

		$active_posts_terms = array();

		foreach ( $posts_q->posts as $post ) {

			if ( 'woocommerce' == $filter_data['filter_type'] ) {

				$woo_product = wc_get_product( $post->ID );
				$variation_price = array( 'min' => '', 'max' => '' );

				if ( $woo_product->is_type( 'variable' ) ) {

					$price_variations = $this->return_woo_variation_prices( $post->ID, $woo_product );
					$woo_sale_price = '';
					$variation_price['min'] = $price_variations['min'];
					$price_min = $price_variations['min'];
					$price_max = $price_variations['max'];
					$woo_price = $price_max;

					if ( $price_min != $price_max && '' !== $price_min && '' !== $price_max ) {
						$woo_price = $price_min . ' - ' . html_entity_decode( $woo_price_currency ) . $price_max;
						$variation_price['min'] = $price_min;
						$variation_price['max'] = $price_max;
					}
				} else {
					$woo_sale_price = $woo_product->get_sale_price();
					$woo_price = get_post_meta( $post->ID, '_regular_price', true );
					$variation_price['min'] = $woo_price;
				}
			}

			$matches = 1;

			$custom_fields_data = lscf_esc_json_object( get_post_meta( $post->ID, 'px-custom_fields', true ) );
			$custom_fields = json_decode( lscf_wordpress_add_unicode_slash( $custom_fields_data ), true );

			if ( isset( $additional_filter_fields ) && count( $additional_filter_fields ) > 0 ) {

				foreach ( $additional_filter_fields as $field ) {

					switch ( $field->type ) {

						case 'date-interval':

							if ( isset( $custom_fields[ $field->ID ] ) ) {

								$post_date = strtotime( $custom_fields[ $field->ID ]['value'] );
								$from = strtotime( $field->fields->from->value );
								$to = strtotime( $field->fields->to->value );

								if ( $post_date < $from || $post_date > $to ) {

									$matches = 0;
									break;
								}
							}

						break;

					}
				}
			}

			if ( 0 == $matches ) {
				continue;
			}


			$featured_img = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), array( 420, 300 ), false, '' );
			$featured_img_full = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), null, false, '' );
			$featured_img_portrait = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), array( 320, 480 ), false, '' );

			$featured_img_thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'thumbnail', false, '' );
			$featured_img_medium = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium', false, '' );
			$featured_img_large = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large', false, '' );

			$full_content = lscf_sanitize_post_content( $post->post_content );
			$short_content = html_entity_decode( wp_trim_words( htmlentities( $full_content ), 20, null ) );
			// $content = wp_filter_nohtml_kses( wp_trim_words( $full_content, 55, '' ) );
			$content = html_entity_decode( wp_trim_words( htmlentities( $full_content ), 55, '' ) );

			if ( 'post' === $post->post_type ) {
				$categories = get_the_category( $post->ID );
			} else {
				$categories = get_the_terms( (int) $post->ID, $post->post_type . '-categories' );
			}

			if ( is_array( $categories ) ) {
				foreach ( $categories as $categ ) {
					$data_posts[ $count ]['categories'][] = $categ->name;
				}
			}

			$data_posts[ $count ]['title']['short'] = wp_trim_words( $post->post_title, 6, null );
			$data_posts[ $count ]['title']['long']  = $post->post_title;
			$data_posts[ $count ]['date'] = $post->post_date;
			$data_posts[ $count ]['custom_date'] = array(
				'd' => get_the_date( 'l', $post->ID ),
				'dd' => get_the_date( 'D', $post->ID ),
				'ddd'	=> get_the_date( 'j', $post->ID ),
				'm' => get_the_date( 'F', $post->ID ),
				'mm'	=> get_the_date( 'm', $post->ID ),
				'mmm'	=> get_the_date( 'n', $post->ID ),
				'y' => get_the_date( 'Y', $post->ID ),
			);
			$data_posts[ $count ]['author'] = $post->post_author;
			$data_posts[ $count ]['ID'] = $post->ID;
			$data_posts[ $count ]['content'] = $content;
			$data_posts[ $count ]['short_content'] = $short_content;
			$data_posts[ $count ]['full_content'] = $full_content;
			$data_posts[ $count ]['lscf_shortcodes'] = $this->build_lscf_shortcodes( $post->post_content, (int) $post->ID );
			$data_posts[ $count ]['excerpt'] = $post->post_excerpt;
			$data_posts[ $count ]['featuredImageFull'] = $featured_img_full[0];
			$data_posts[ $count ]['featuredImage'] = $featured_img[0];
			$data_posts[ $count ]['featured_portrait'] = $featured_img_portrait[0];
			$data_posts[ $count ]['featured_thumbnail'] = $featured_img_thumbnail[0];
			$data_posts[ $count ]['featured_medium'] = $featured_img_medium[0];
			$data_posts[ $count ]['featured_large'] = $featured_img_large[0];

			$data_posts[ $count ]['permalink'] = get_permalink( $post->ID );

			$data_posts[ $count ]['customFields'] = $custom_fields;

			if ( 'woocommerce' == $filter_data['filter_type'] ) {

				$featured_price = ( '' != $woo_sale_price ? $woo_sale_price : $woo_price );

				$data_posts[ $count ]['woocommerce']['regular_price'] = lscf_woo_filter_price( $woo_product->get_regular_price(), $filter_data );
				$data_posts[ $count ]['woocommerce']['sale_price'] = lscf_woo_filter_price( $woo_sale_price, $filter_data );
				$data_posts[ $count ]['woocommerce']['price'] = lscf_woo_filter_price( $featured_price, $filter_data );
				$data_posts[ $count ]['woocommerce']['price_currency'] = html_entity_decode( $woo_price_currency );
				$data_posts[ $count ]['woocommerce']['full_sale_price'] = $this->format_woo_currency_price( $data_posts[ $count ]['woocommerce']['sale_price'], html_entity_decode( $woo_price_currency ) );
				$data_posts[ $count ]['woocommerce']['full_regular_price'] = $this->format_woo_currency_price( $data_posts[ $count ]['woocommerce']['regular_price'], html_entity_decode( $woo_price_currency ) );
				$data_posts[ $count ]['woocommerce']['variation_min_price'] = lscf_woo_price( $variation_price['min'] );
				$data_posts[ $count ]['woocommerce']['variation_max_price'] = lscf_woo_price( $variation_price['max'] );
				$data_posts[ $count ]['woocommerce']['sku'] = $woo_product->get_sku();
				$data_posts[ $count ]['woocommerce']['stock'] = $woo_product->get_stock_quantity();
				$data_posts[ $count ]['woocommerce']['add_to_cart_link'] = '?add-to-cart=' . $post->ID;
        // aron edit add quantity function
        $data_posts[ $count ]['woocommerce']['add_to_cart_link_quantity'] = '?add-to-cart=' . $post->ID . '&quantity=';
				$data_posts[ $count ]['woocommerce']['gallery'] = array();

				$woo_categories = get_the_terms( $post->ID, 'product_cat' );
				if ( is_array( $woo_categories ) ) {
					foreach ( $woo_categories as $woo_categ ) {
						$data_posts[ $count ]['categories'][] = $woo_categ->name;
					}
				}

				$woo_attachments = $woo_product->get_gallery_attachment_ids();
				if ( is_array( $woo_attachments ) ) {
					foreach ( $woo_attachments as $attachment_id ) {
						$data_posts[ $count ]['woocommerce']['gallery'][] = wp_get_attachment_url( $attachment_id );
					}
				}

				if ( '' !== $woo_sale_price && '' !== $woo_product->get_regular_price() ) {
					$data_posts[ $count ]['woocommerce']['sale_percentage'] = 100 - ( round( $woo_sale_price * 100 / $woo_product->get_regular_price() ) );
				} else {
					$data_posts[ $count ]['woocommerce']['sale_percentage'] = 0;
				}

				if ( 1 == $featured_label_status ) {

					if ( 'woocommerce-featured-price' == $filter_data['featuredLabelFieldID'] ) {

						$featured_label = array();
						$featured_label['ID'] = 'woocommerce-featured-price';
						$featured_label['value'] = ( '' != $featured_price ? $this->format_woo_currency_price( $featured_price, html_entity_decode( $woo_price_currency ) ): '' );
						$data_posts[ $count ]['featured_label'] = $featured_label;

					};

				}
			}

			if ( 1 == $featured_label_status ) {
				$featured_label = '';
				if ( isset( $custom_fields[ $filter_data['featuredLabelFieldID'] ] ) ) {
					$featured_label = $custom_fields[ $filter_data['featuredLabelFieldID'] ];
					if ( 'px_date' === $featured_label['field_type'] ) {
						$featured_label['value'] = $featured_label['dlabel'];
					}
				}

				if ( isset( $featured_label['field_type'] ) && 'px_cf_relationship' == $featured_label['field_type'] ) {

					foreach ( $featured_label['data'] as &$option ) {

						if ( isset( $option['fields'] ) ) {
							$option['fields'] = array_values( $option['fields'] );
						}
					}
				}
				$data_posts[ $count ]['featured_label'] = $featured_label;
			}

			$count++;

		}

		if ( isset( $additional_filter_fields ) && count( $additional_filter_fields ) > 0 && 'woocommerce' != $filter_data['filter_type']  ) {

			$offset = $post_data->limit * ( $post_data->page - 1 );
			$posts_limit = ( ( $offset + $post_data->limit ) > count( $data_posts ) ? count( $data_posts ) : $offset + $post_data->limit );

			$temp_data = array();

			for ( $i = $offset; $i < $posts_limit; $i++ ) {

				$temp_data[] = $data_posts[ $i ];

			}

			$total_pages = ceil( count( $data_posts ) / $post_data->limit );

			$posts_count = count( $data_posts );

			$all_matched_posts = $data_posts;

			$data_posts = $temp_data;
		}

		if ( isset( $args['px_custom_fields'] ) ) {

			if ( isset( $filter_data['options']['disable_empty_option_on_filtering'] ) && 1 === (int) $filter_data['options']['disable_empty_option_on_filtering'] ) {

				unset( $args['px_postmeta'] );
				$args['fields'] = 'ids';
				$args['posts_per_page'] = 300;
				$all_posts_ids = new PxWpQuery( $args, true );

				$active_posts_terms = $this->get_posts_active_terms( $all_posts_ids->posts, $all_taxonomies_names, $post_data->post_type );

			}
		}


		return
			array(
				'posts'			 => $data_posts,
				'matched_posts'  => $all_matched_posts,
				'pages'			 => $total_pages,
				'postsCount'	 => $posts_count,
				'featuredLabel'	 => $featured_label_status,
				'active_terms'	 => $active_posts_terms,
				'filter_type'	 => $filter_data['filter_type'],
			);
	}

	/**
	 * Init angular $http requests handler.
	 * Loaded via add_action hook
	 *
	 * @access public
	 * @var function|Class method
	 */
	public function init_http_requests() {

		$post_data = json_decode( file_get_contents( 'php://input' ) );

		$section = $post_data->section;

		switch ( $section ) {

			case 'getSidebar':

				if ( is_active_sidebar( 'lscf_custom_sidebar' ) ) {
					dynamic_sidebar( 'lscf_custom_sidebar' );
				}
				die();
				break;
		}

		die();
	}

}
