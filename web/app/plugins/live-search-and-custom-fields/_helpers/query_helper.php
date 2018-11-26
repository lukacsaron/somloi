<?php 

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * Class PxWpQuery A Helper Class that extends WP_QUERY
 *
 * @category Helper
 * @package  PxWpQuery
 * @author   PIXOLETTE
 * @license  http://www.pixollete.com
 * @link     http://www.pixollete.com
 **/
class PxWpQuery extends WP_Query {

	/**
	 * Custom fields meta key name.
	 *
	 * @access public
	 * @var string
	 */
	public $lscf_postmeta = 'pxpostmeta';

	/**
	 * The meta name of custom field that is used by range.
	 * Used into left join.
	 *
	 * @access public
	 * @var string
	 */
	public $lscf_range_field_meta_name = array();

	/**
	 * WooCommerce price postmeta alias.
	 *
	 * @access public
	 * @var string
	 */
	public $woo_price_postmeta = 'woo_price';

	/**
	 * WooCommerce stock inventory postmeta alias.
	 *
	 * @access public
	 * @var string
	 */
	public $woo_stock_inventory_postmeta = 'woo_stock_inventory';

	/**
	 * WooCommerce SKU inventory postmeta alias.
	 *
	 * @access public
	 * @var string
	 */
	public $woo_sku_postmeta = 'woo_sku';

	/**
	 * LSCF custom fields list.
	 *
	 * @access public
	 * @var array
	 */
	public $custom_fields_list = array();


	/**
	 * The PxWpQuery Args data.
	 *
	 * @access public
	 * @var array
	 */
	public $lscf_query_args = array();

	/**
	 * Ajax Search. LSCF custom fields to search into.
	 *
	 * @access public
	 * @var array
	 */
	public $general_search_custom_fields = array();


	/**
	 * The post meta data by which the search will be make
	 *
	 * @access public
	 * @var array
	 */
	public $px_custom_post_meta = array();

	/**
	 * Array of custom fields by which the search would be made
	 *
	 * @param array $args The args data for PxWpQuery and WP_QUERY.
	 * @var function|Class constructor
	 */
	function __construct( $args, $called = false ) {

		$this->lscf_query_args = $args;

		if ( isset( $args['lscf-available-custom-fields'] ) && 0 < count( $args['lscf-available-custom-fields'] ) ) {
			$this->custom_fields_list = $args['lscf-available-custom-fields'];
		}

		if ( isset( $args['search_by_lscf_custom_fields'] ) ) {
			$this->general_search_custom_fields = $args['search_by_lscf_custom_fields'];
		}

		$this->init_general_search_wordpress_filters();

		if ( isset( $args['px_postmeta'] ) ) {
			$this->px_custom_post_meta = $args['px_postmeta'];
			add_action( 'posts_join', array( $this, 'px_join_postmeta' ) );

		}

		if ( isset( $args['px_custom_fields'] ) && false === $called ) {

			$this->px_custom_fields = $args['px_custom_fields'];

			$custom_fields = $args['px_custom_fields'];

			foreach ( $custom_fields as $custom_field ) {

				if ( 'woocommerce-instock' == $custom_field->ID ) {

					add_action( 'posts_join', array( $this, 'woo_postmeta_join' ) );
					continue;

				} elseif ( 'px-woocommerce-price' == $custom_field->ID ) {

					add_action( 'posts_join', array( $this, 'woo_price_postmeta_join' ) );
					continue;

				} elseif ( 'px-woocommerce-inventory' == $custom_field->ID ) {

					add_action( 'posts_join', array( $this, 'woo_stock_inventory_postmeta_join' ) );
					continue;

				} elseif ( 'ajax-product-sku-search' == $custom_field->ID ) {

					add_action( 'posts_join', array( $this, 'woo_sku_postmeta_join' ) );

				} elseif ( 'range' === $custom_field->type ) {
					$this->lscf_range_field_meta_name[] = $custom_field->ID;
				}
			}

			if ( 1 <= count ( $this->lscf_range_field_meta_name ) ) {
				add_action( 'posts_join', array( $this, 'range_field_left_join' ) );
			}
			add_action( 'posts_where', array( $this, 'px_cap_search_fields' ) );
			add_action( 'posts_join', array( $this, 'px_join_postmeta' ) );

		}

		parent::__construct( $this->lscf_query_args );

		if ( isset( $args['px_postmeta'] ) ) {
			$this->px_get_posts();
			return $this->posts;

		}
	}

	/**
	 * Makes a Join on post_meta and posts
	 * Loaded via add_action 'posts_join'
	 *
	 * @param string $join A param passed by add_action 'posts_join' hook.
	 * @access public
	 * @var function|Class method
	 */
	public function px_join_postmeta( $join ) {

		global $wpdb;

		$join .= " LEFT JOIN $wpdb->postmeta as $this->lscf_postmeta ON $wpdb->posts.ID = $this->lscf_postmeta.post_id";

		return $join;
	}

	/**
	 * Makes a left join for range field that uses LSCF custom field as filter.
	 * Loaded via add_action 'posts_join'
	 *
	 * @param string $join A param passed by add_action 'posts_join' hook.
	 * @access public
	 * @var function|Class method
	 */
	public function range_field_left_join( $join ) {

		global $wpdb;
		foreach ( $this->lscf_range_field_meta_name as $lscf_range_cf_id ) {
			$join .= " LEFT JOIN $wpdb->postmeta as $lscf_range_cf_id ON $wpdb->posts.ID = $lscf_range_cf_id.post_id";
		}

		return $join;
	}

	/**
	 * Makes a Join on post_meta and posts
	 * Loaded via add_action 'posts_join'
	 *
	 * @param string $join A param passed by add_action 'posts_join' hook.
	 * @access public
	 * @var function|Class method
	 */
	public function woo_price_postmeta_join( $join ) {

		global $wpdb;

		$join .= " LEFT JOIN $wpdb->postmeta as $this->woo_price_postmeta ON $wpdb->posts.ID = $this->woo_price_postmeta.post_id";

		return $join;
	}

	/**
	 * Makes a Join on post_meta and posts
	 * Loaded via add_action 'posts_join'
	 *
	 * @param string $join A param passed by add_action 'posts_join' hook.
	 * @access public
	 * @var function|Class method
	 */
	public function woo_stock_inventory_postmeta_join( $join ) {

		global $wpdb;

		$join .= " LEFT JOIN $wpdb->postmeta as $this->woo_stock_inventory_postmeta ON $wpdb->posts.ID = $this->woo_stock_inventory_postmeta.post_id";

		return $join;
	}

	/**
	 * Makes a Join on post_meta and posts
	 * Loaded via add_action 'posts_join'
	 *
	 * @param string $join A param passed by add_action 'posts_join' hook.
	 * @access public
	 * @var function|Class method
	 */
	public function woo_sku_postmeta_join( $join ) {

		global $wpdb;

		$join .= " LEFT JOIN $wpdb->postmeta as $this->woo_sku_postmeta ON $wpdb->posts.ID = $this->woo_sku_postmeta.post_id";

		return $join;
	}


	/**
	 * Makes a Join on post_meta and posts for WooCommerce
	 * Loaded via add_action 'posts_join'
	 *
	 * @param string $join A param passed by add_action 'posts_join' hook.
	 * @access public
	 * @var function|Class method
	 */
	public function woo_postmeta_join( $join ) {

		global $wpdb;

		$join .= " LEFT JOIN $wpdb->postmeta woo_postmeta ON $wpdb->posts.ID = woo_postmeta.post_id ";

		return $join;
	}

	/**
	 * Search and Filter the custom DB query.
	 * Loaded via add_action 'posts_where'
	 * Search by custom Fields
	 *
	 * @param string $where A param passed by add_action 'posts_where' hook.
	 * @access public
	 * @var function|Class method
	 */
	public function px_cap_search_fields( $where ) {

		global $wpdb;

		$has_custom_fields = false;
		$has_range_fields = false;
		$range_where = '';
		$has_di_fields = false;
		$has_taxonomies = false;
		$sort_by_meta_value = false;
		$order_by_meta_key = ( isset( $this->query_vars['meta_key'] ) && '' !== $this->query_vars['meta_key'] ? true : false );

		$px_where = '';
		$regex = array();

		$regex_or_operator = array();

		$custom_fields = $this->query_vars['px_custom_fields'];

		$search_lscf_custom_fields_regex = $this->search_keyword_regex_lscf_custom_fields();

		foreach ( $custom_fields as $custom_field ) {

			$type = ( isset( $custom_field->filter_as ) && '' !== $custom_field->filter_as ? $custom_field->filter_as : $custom_field->type );

			switch ( $type ) {

				case 'date-interval':
					$has_di_fields = true;
					break;

				case 'main-search':

					if ( 'ajax-product-sku-search' == $custom_field->ID && '' != $custom_field->value ) {
						$where .= " AND ( $this->woo_sku_postmeta.meta_key='_sku' AND $this->woo_sku_postmeta.meta_value LIKE '%" . sanitize_text_field( wp_unslash( $custom_field->value ) ) . "%' )";
					}
					break;

				case 'range':

					$has_range_fields = true;
					if ( 'px-woocommerce-price' == $custom_field->ID ) {
						$px_where .= " AND ( $this->woo_price_postmeta.meta_key='_price' AND ( $this->woo_price_postmeta.meta_value >= " . (int) $custom_field->value->min . " AND $this->woo_price_postmeta.meta_value <= " . (int) $custom_field->value->max . ' ) )';
					} elseif ( 'px-woocommerce-inventory' == $custom_field->ID ) {
						$px_where .= "AND ( $this->woo_stock_inventory_postmeta.meta_key='_stock' AND ( $this->woo_stock_inventory_postmeta.meta_value >= " . (int) $custom_field->value->min . " AND $this->woo_stock_inventory_postmeta.meta_value <= " . (int) $custom_field->value->max . ' ) )';
					} else {

						$range_min = (int) $custom_field->value->min;
						$range_max = (int) $custom_field->value->max;

						if ( isset( $this->lscf_query_args['range_filtering_type'] ) && 'default' === $this->lscf_query_args['range_filtering_type'] ) {
							$px_where .= "AND ( $custom_field->ID.meta_key='$custom_field->ID' AND $custom_field->ID.meta_value+0 >= $range_min AND $custom_field->ID.meta_value+0 <= $range_max )";
						} else{
							$px_where .= "AND ( $custom_field->ID.meta_key='$custom_field->ID' AND lscf_number_only($custom_field->ID.meta_value) >= $range_min AND lscf_number_only($custom_field->ID.meta_value) <= $range_max )";
						}
					}
					break;

				case 'checkbox_post_terms':

					if ( 'checkbox_post_terms' == $type &&
						( is_array( $custom_field->value ) && ( 0 < count( $custom_field->value ) ) ||
						( ! is_array( $custom_field->value ) && '' != $custom_field->value ) ) ) {
						$has_taxonomies = true;
					}
					break;
			}

			if ( 'range' == $type || 'date-interval' == $type || 'checkbox_post_terms' == $type || 'main-search' == $type || 'order-posts' == $type || 'default_filter' == $type ) {

				if ( 'checkbox_post_terms' == $type && ( 0 == count( $custom_field->value ) || ' ' == $custom_field->value ) ) { continue; }

				if ( 'order-posts' == $type && 
					( 'woo_price' === $custom_field->value || preg_match( '/(.+?)__pxid_(.+?)_([0-9]+)/', $custom_field->value ) ) ) 
				{
					$sort_by_meta_value = true;
				}

				if ( 'default_filter' == $type ) {
					$has_taxonomies = true;
				}

				continue;
			}

			// if we are here, then set up the custom fields where clause.
			$id = esc_sql( lscf_wordpress_escape_unicode_slash( preg_replace( '/^"|"$/', '',  json_encode( $custom_field->ID ) ) ) );

			if ( 'woocommerce-instock' == $id ) {

				if ( ! is_array( $custom_field->value ) ) {
					$value = sanitize_text_field( wp_unslash( $custom_field->value ) );
				} else {
					$value = sanitize_text_field( wp_unslash( $custom_field->value[0] ) );
				}
				
				// $has_custom_fields = true;
				$where .= " AND woo_postmeta.meta_key = '_stock_status' AND woo_postmeta.meta_value='$value'";

				continue;
			}

			if ( ! isset( $custom_field->value ) ) { continue; }

			if ( ! is_array( $custom_field->value ) ) {

				$value = sanitize_text_field( wp_unslash( preg_replace( '/^"|"$/', '',  json_encode( $custom_field->value ) ) ) );

				$regex_value = lscf_sql_regex_escape( lscf_wordpress_escape_unicode_slash( $value ) );

				$regex[] = "\"$id\s*\":\s*\{[^>]*\s*\"value\"\s*:\s*\"$regex_value\s*\"\s*[^>]*\"ID\":\"$id\"";

				$has_custom_fields = true;

			} else {

				$value = $custom_field->value;

				if ( isset( $this->query_vars['checkboxes_conditional_logic'] ) && 'and' === $this->query_vars['checkboxes_conditional_logic'] ) {

					foreach ( $value as $val ) {

						$regex_val = lscf_sql_regex_escape( lscf_wordpress_escape_unicode_slash( sanitize_text_field( wp_unslash( preg_replace( '/^"|"$/', '',  json_encode( $val ) ) ) ) ) );

						if ( 'px_icon_check_box' == $type ) {
							$regex[] = "\"$id\s*\":\s*\{[^>]*\s*\"value\"\s*:\s*[^>]*\"\s*($regex_val)\s*\"\s*[^>]*,\"ivalue";
						} else {
							$regex[] = "\"$id\s*\":\s*\{[^>]*\s*\"value\"\s*:\s*[^>]*\"\s*($regex_val)\s*\"\s*[^>]*\"ID\":\"$id\"";
						}
					}
				} else {

					$regex_val = '';
					$c = 0;

					foreach ( $value as $val ) {

						if ( ( count( $value ) - 1 ) == $c ) {
							$regex_val .= lscf_sql_regex_escape( lscf_wordpress_escape_unicode_slash( sanitize_text_field( wp_unslash( preg_replace( '/^"|"$/', '',  json_encode( $val ) ) ) ) ) );
						} else {
							$regex_val .= lscf_sql_regex_escape( lscf_wordpress_escape_unicode_slash( sanitize_text_field( wp_unslash( preg_replace( '/^"|"$/', '',  json_encode( $val ) ) ) ) ) ) . '|';
						}
						$c++;
					}

					if ( 'px_icon_check_box' == $type  ) {
						$regex[] = "\"$id\s*\":\s*\{[^>]*\s*\"value\"\s*:\s*[^>]*\"\s*($regex_val)\s*\"\s*[^>]*,\"ivalue";
					} else {
						$regex[] = "\"$id\s*\":\s*\{[^>]*\s*\"value\"\s*:\s*[^>]*\"\s*($regex_val)\s*\"\s*[^>]*\"ID\":\"$id\"";
					}
				}

				$has_custom_fields = true;
			}
		}

		if ( isset( $this->lscf_query_args['search_into_custom_fields'] ) && true === $this->lscf_query_args['search_into_custom_fields'] ) {

			if ( isset( $this->lscf_query_args['general_search_algorithm'] ) && 'algorithm-2' === $this->lscf_query_args['general_search_algorithm'] ) {
				$general_search_where_clause = $this->search_build_meta_cf_conditional_logic();
			} else {
				$general_search_where_clause = $this->search_build_cf_regex_conditional_logic();
			}

			if ( '' !== $general_search_where_clause ) {
				$where .= preg_replace( '/^\s*(OR|or)/', 'AND ', $general_search_where_clause );
			}
		}

		if ( true === $has_taxonomies && false === $has_range_fields && false === $has_custom_fields ) {
			return $where;
		} elseif ( true === $has_range_fields  && false === $has_taxonomies && false === $has_custom_fields && false === $order_by_meta_key ) {
			return $where . $px_where . " GROUP BY $wpdb->posts.ID";
		}

		if ( true === $has_custom_fields ) {
			$px_where .= " AND $this->lscf_postmeta.meta_key = 'px-custom_fields' ";
			foreach ( $regex as $reg_f ) {
				$px_where .= " AND $this->lscf_postmeta.meta_value REGEXP '$reg_f'";
			}
		}

		if ( false === $has_taxonomies && false === $sort_by_meta_value && false === $order_by_meta_key ) {
			return $where . $px_where . " GROUP BY $wpdb->posts.ID";
			
		} else {
			return $where . $px_where;

		}

	}

	/**
	 * Search any keyword into provide LSCF custom fields.
	 *
	 * @access public
	 * @var return a mysql regex for LSCF custom fields.
	 */
	public function search_keyword_meta_regex_lscf_custom_fields() {


		$regex = array();

		if ( ! isset( $this->query_vars['general-search'] ) || '' === $this->query_vars['general-search']  ) {
			return $regex;
		}

		if ( 0 >= count( $this->general_search_custom_fields ) ) { return $regex; }
		if ( ! isset( $this->general_search_custom_fields['items'] ) || 0 >= count( $this->general_search_custom_fields['items'] ) ) {
			return $regex;
		}

		$keyword = $this->query_vars['general-search'];

		foreach ( $this->general_search_custom_fields['items'] as $custom_field ) {

			if ( 'post_title' === $custom_field['id'] || 'post_content' === $custom_field['id'] ) {
				continue;
			}

			$custom_field_type = ( isset( $this->custom_fields_list[ $custom_field['id'] ] ) ? $this->custom_fields_list[ $custom_field['id'] ]['slug'] : 'px_select_box' );

			$id = $custom_field['id'];
			$data_value = sanitize_text_field( wp_unslash( preg_replace( '/^"|"$/', '',  json_encode( $keyword ) ) ) );
			$values = explode( ' ', $data_value );
			$regex_value = '';

			switch ( $custom_field_type ) {

				default:
				case 'px_select_box':
				case 'px_radio_box':
				case 'px_text':

					foreach ( $values as $value ) {
						$value =  lscf_sql_regex_escape( lscf_wordpress_escape_unicode_slash( $value ) );
						$regex[] = "\"$id\s*\":\s*\{[^>]*\s*\"value\"\s*:\s*\"[^>]*($value)\s*[^>]*\"\s*[^>]*\"ID\":\"$id\"";
					}

					break;

				case 'px_check_box':

					foreach ( $values as $value ) {
						$value =  lscf_sql_regex_escape( lscf_wordpress_escape_unicode_slash( $value ) );
						$regex[] = "\"$id\s*\":\s*\{[^>]*\s*\"value\"\s*:\s*[^>]*\"[^>]*$value\s*[^>]*\"\][^>]*\"ID\":\"$id\"";
					}

					break;

				case 'px_icon_check_box':

					foreach ( $values as $value ) {
						$value =  lscf_sql_regex_escape( lscf_wordpress_escape_unicode_slash( $value ) );
						$regex[] = "\"$id\s*\":\s*\{[^>]*\s*\"value\"\s*:\s*\[\"[^>]*($value)\s*[^>]*\"\s*[^>]*,\"ivalue";
					}
					break;
			}
		}

		return $regex;
	}

	/**
	 * Search any keyword into provide LSCF custom fields.
	 *
	 * @access public
	 * @var return a mysql regex for LSCF custom fields.
	 */
	public function search_keyword_regex_lscf_custom_fields() {

		$regex = array();

		if ( ! isset( $this->query_vars['general-search'] ) || '' === $this->query_vars['general-search']  ) {
			return $regex;
		}

		if ( 0 >= count( $this->general_search_custom_fields ) ) { return $regex; }
		if ( ! isset( $this->general_search_custom_fields['items'] ) || 0 >= count( $this->general_search_custom_fields['items'] ) ) {
			return $regex;
		}

		$keyword = $this->query_vars['general-search'];

		foreach ( $this->general_search_custom_fields['items'] as $custom_field ) {

			if ( 'post_title' === $custom_field['id'] || 'post_content' === $custom_field['id'] ) {
				continue;
			}

			$custom_field_type = ( isset( $this->custom_fields_list[ $custom_field['id'] ] ) ? $this->custom_fields_list[ $custom_field['id'] ]['slug'] : 'px_select_box' );
			$logic_operator = ( '' !== $this->query_vars['search_by_cf_conditional_logic'] ? strtoupper( $this->query_vars['search_by_cf_conditional_logic'] ) : 'AND' );

			$id = $custom_field['id'];
			$data_value = sanitize_text_field( wp_unslash( preg_replace( '/^"|"$/', '',  json_encode( $keyword ) ) ) );
			$values = explode( ' ', $data_value );
			$regex_value = '';

			if ( count( $values ) > 1 ) {
				$n = count( $values );
				$value;
				$data_value = lscf_sql_regex_escape( lscf_wordpress_escape_unicode_slash( $data_value ) );
				lscf_permute( $data_value, 0, $n - 1, $value );
				$value = preg_replace( '/(.*?)\|$/', '$1', $value );
			} else {
				$value = lscf_sql_regex_escape( lscf_wordpress_escape_unicode_slash( $values[0] ) );
			}

			switch ( $custom_field_type ) {

				default:
				case 'px_select_box':
				case 'px_radio_box':
				case 'px_text':
					$regex[] = "\"$id\s*\":\s*\{[^>]*\s*\"value\"\s*:\s*\"[^>]*$value\s*[^>]*\"\s*[^>]*\"ID\":\"$id\"";
					break;

				case 'px_check_box':
					$regex[] = "\"$id\s*\":\s*\{[^>]*\s*\"value\"\s*:\s*[^>]*\"[^>]*$value\s*[^>]*\"\][^>]*\"ID\":\"$id\"";
					break;

				case 'px_icon_check_box':
					$regex[] = "\"$id\s*\":\s*\{[^>]*\s*\"value\"\s*:\s*\[\"[^>]*$value\s*[^>]*\"\s*[^>]*,\"ivalue";
					break;
			}
		}

		return $regex;
	}

	/**
	 * Customize the general WordPress search. Search by post title only or by post content only.
	 *
	 * @access public
	 * @var return 
	 */
	public function init_general_search_wordpress_filters() {

		if ( ! isset( $this->lscf_query_args['s'] ) || '' === $this->lscf_query_args['s']  ) {
			return;
		}

		if ( ! isset( $this->general_search_custom_fields['items'] ) ) {
			return;
		}

		$search_by_title = false; 
		$search_by_content = false;


		foreach ( $this->general_search_custom_fields['items'] as $custom_field ) {
			if ( 'post_title' === $custom_field['id'] ) {
				$search_by_title = true;
				continue;
			}
			if ( 'post_content' === $custom_field['id'] ) {
				$search_by_content = true;
				continue;
			}
		}

		if ( true === $search_by_title && true === $search_by_content ) {
			add_filter( 'posts_search', array( $this, 'lscf_wp_general_search' ), 500, 2 );
			return;
		}

		if ( true === $search_by_title ) {
			add_filter( 'posts_search', array( $this, 'search_by_post_title_only' ), 500, 2 );
			return;
		}

		if ( true === $search_by_content ) {
			add_filter( 'posts_search', array( $this, 'search_by_post_content_only' ), 500, 2 );
			return;
		}

		// if we are here, then  both options are deactivated (post_title, post_content). 
		//We should remove the general WordPress search from sql query and search only into LSCF custom fields.
		unset( $this->lscf_query_args['s'] );
		$this->lscf_query_args['search_into_custom_fields'] = true;

		return;

	}

	/**
	 * WordPress general search by post title and post content and LSCF custom fields.
	 *
	 * @access public
	 * @var return 
	 */
	public function lscf_wp_general_search( $search, $wp_query ) {

		global $wpdb;
		if ( empty( $search ) ) {
			return $search; // skip processing - no search term in query
		}

		$logic_operator = ( '' !== $this->query_vars['search_by_cf_conditional_logic'] ? strtoupper( $this->query_vars['search_by_cf_conditional_logic'] ) : 'AND' );
		$q = $wp_query->query_vars;
		$n = !empty($q['exact']) ? '' : '%';
		$search =
		$searchand = '';

		foreach ( (array)$q['search_terms'] as $term ) {
			$term = esc_sql( $wpdb->esc_like($term) );
			$search .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
			$searchand = ' AND ';
		}

		$searchand = $logic_operator;
		$post_content_search = '';
		foreach ( (array)$q['search_terms'] as $term ) {
			$term = esc_sql( $wpdb->esc_like($term) );
			$post_content_search .= "{$searchand}($wpdb->posts.post_content LIKE '{$n}{$term}{$n}')";
			$searchand = ' AND ';
		}
		$search .= $post_content_search;

		$general_search_where_clause = $this->search_build_cf_regex_conditional_logic();

		if ( '' !== $general_search_where_clause ) {
			$search .= $general_search_where_clause;
		}

		if ( ! empty( $search ) ) {

			$search = " AND ({$search}) ";
			if ( ! is_user_logged_in() ) {
				$search .= " AND ($wpdb->posts.post_password = '') ";
			}
		}

		return $search;
	}

	/**
	 * WordPress general search. Search only into a post title
	 *
	 * @access public
	 * @var return 
	 */
	public function search_by_post_title_only( $search, $wp_query ) {

		global $wpdb;
		if ( empty( $search ) ) {
			return $search; // skip processing - no search term in query
		}

		$q = $wp_query->query_vars;
		$n = !empty($q['exact']) ? '' : '%';
		$search =
		$searchand = '';

		foreach ( (array)$q['search_terms'] as $term ) {
			$term = esc_sql( $wpdb->esc_like($term) );
			$search .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
			$searchand = ' AND ';
		}

		$general_search_where_clause = $this->search_build_cf_regex_conditional_logic();

		if ( '' !== $general_search_where_clause ) {
			$search .= $general_search_where_clause;
		}

		if ( ! empty( $search ) ) {

			$search = " AND ({$search}) ";
			if ( ! is_user_logged_in() ) {
				$search .= " AND ($wpdb->posts.post_password = '') ";
			}
		}

		return $search;

	}


	/**
	 * WordPress search post content only
	 *
	 * @access public
	 * @var return 
	 */
	public function search_by_post_content_only( $search, $wp_query ) {

		global $wpdb;
		if ( empty( $search ) ) {
			return $search; // skip processing - no search term in query
		}

		$q = $wp_query->query_vars;
		$n = !empty($q['exact']) ? '' : '%';
		$search =
		$searchand = '';

		foreach ( (array)$q['search_terms'] as $term ) {
			$term = esc_sql( $wpdb->esc_like($term) );
			$search .= "{$searchand}($wpdb->posts.post_content LIKE '{$n}{$term}{$n}')";
			$searchand = ' AND ';
		}

		$general_search_where_clause = $this->search_build_cf_regex_conditional_logic();

		if ( '' !== $general_search_where_clause ) {
			$search .= $general_search_where_clause;
		}

		if ( ! empty( $search ) ) {

			$search = " AND ({$search}) ";
			if ( ! is_user_logged_in() ) {
				$search .= " AND ($wpdb->posts.post_password = '') ";
			}
		}

		return $search;

	}

	/**
	 * Build the MuySql WHERE conditional logic and regex for general search using custom fields.
	 *
	 * @access public
	 * @var return a MySql Where clause of regex for custom fields. Build the conditional logic for WHERE clause as well.
	 */
	public function search_build_cf_regex_conditional_logic() {

		$where = '';

		if ( ! isset( $this->query_vars['search_by_cf_conditional_logic'] ) || ! isset( $this->query_vars['general-search'] ) ) {
			return '';
		}

		$logic_operator = ( '' !== $this->query_vars['search_by_cf_conditional_logic'] ? strtoupper( $this->query_vars['search_by_cf_conditional_logic'] ) : 'AND' );

		$regex_array = $this->search_keyword_regex_lscf_custom_fields();

		if ( 0 >= count( $regex_array ) ) {
			return '';
		}

		$count = 0 ;

		foreach ( $regex_array as $regex ) {
			if ( $count + 1 < count( $regex_array ) ) {
				$where .= " $this->lscf_postmeta.meta_value REGEXP '$regex' $logic_operator";
			} else {
				$where .= " $this->lscf_postmeta.meta_value REGEXP '$regex'";
			}

			$count++;
		}

		return  " $logic_operator ( $this->lscf_postmeta.meta_key = 'px-custom_fields' AND  ( $where ) )";
	}

	/**
	 * Build the MuySql WHERE conditional logic and general search using META custom fields.
	 *
	 * @access public
	 * @var return a MySql Where clause of Postmeta for custom fields. Build the conditional logic for WHERE clause as well.
	 */
	function search_build_meta_cf_conditional_logic() {
		$where = '';

		if ( ! isset( $this->query_vars['search_by_cf_conditional_logic'] ) || ! isset( $this->query_vars['general-search'] ) ) {
			return '';
		}

		$logic_operator = ( '' !== $this->query_vars['search_by_cf_conditional_logic'] ? strtoupper( $this->query_vars['search_by_cf_conditional_logic'] ) : 'AND' );

		$keywords = explode( ' ',  $this->query_vars['general-search'] );
		$search_query = array();

		$count = 0 ;
		foreach ( $keywords as $keyword ) {
			if ( $count + 1 < count( $keywords ) ) {
				$where .= " $this->lscf_postmeta.meta_value LIKE '%$keyword%' $logic_operator";
			} else {
				$where .= " $this->lscf_postmeta.meta_value LIKE '%$keyword%' ";
			}

			$count++;
		}

		foreach ( $this->general_search_custom_fields['items'] as $custom_field ) {

			if ( 'post_title' === $custom_field['id'] || 'post_content' === $custom_field['id'] ) {
				continue;
			}
			$custom_field_type = ( isset( $this->custom_fields_list[ $custom_field['id'] ] ) ? $this->custom_fields_list[ $custom_field['id'] ]['slug'] : 'px_select_box' );

			$search_query[] = "$this->lscf_postmeta.meta_key = '{$custom_field['id']}' AND ( $where ) ";
		}

		$search = '';
		$count = 0;
		foreach( $search_query as $q ) {
			if ( $count + 1 < count( $search_query ) ) {
				$search .= $q . ' AND ';
			} else {
				$search .= $q;
			}
			$count++;
		}
		return " $logic_operator ( $search )";
	}


	/**
	 * Search by custom post meta.
	 * Loaded via add_action 'posts_where'
	 *
	 * @param string $where A param passed by add_action 'posts_where' hook.
	 * @access public
	 * @var function|Class method
	 */
	public function px_custom_postmeta( $where ) {

		global $wpdb;

		$in_clause = '';
		$count = 0;

		foreach ( $this->px_custom_post_meta as $meta_data ) {

			if ( count( $this->px_custom_post_meta ) !== ( $count + 1 ) ) {

				$in_clause .= "'" . esc_sql( $meta_data['key'] ) . "', ";

			} else {
				$in_clause .= "'" . esc_sql( $meta_data['key'] ) . "'";
			}

			$count++;
		}

		$where .= " AND $wpdb->postmeta.meta_key IN ($in_clause)";

		return $where;

	}

	/**
	 * Get posts and show all post_meta data into results
	 * Return array of posts and post_meta values for each post
	 *
	 * @access public
	 * @var function|Class method
	 */
	public function px_get_posts() {

		global $wpdb;

		$request = preg_replace( '/^select/i', "SELECT $wpdb->postmeta.meta_key, $wpdb->postmeta.meta_value, ", $this->request );

		$data_posts = array();

		$posts = wp_cache_get( md5( $request ), 'px_custom_query' );

		if ( false === $posts ) {

			$posts = $wpdb->get_results( $request );
			wp_cache_add( md5( $request ), 'px_custom_query' );
		}

		foreach ( $posts as $post ) {

			$data_posts[ $post->ID ]['postmeta'][ $post->meta_key ] = $post->meta_value;
			$data_posts[ $post->ID ]['data'] = $post;
		}

		$this->posts = array_values( $data_posts );

	}
}
