<?php

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Smart_Manager_Shop_Coupon' ) ) {
	class Smart_Manager_Shop_Coupon extends Smart_Manager_Base {

		public $dashboard_key = '',
			$default_store_model = array();

		function __construct($dashboard_key) {
			parent::__construct($dashboard_key);

			$this->dashboard_key = $dashboard_key;
			$this->post_type = $dashboard_key;
			$this->req_params  	= (!empty($_REQUEST)) ? $_REQUEST : array();
			
			add_filter( 'sm_dashboard_model',array( &$this,'coupons_dashboard_model' ), 10, 1 );
			// add_filter( 'sm_data_model', array( &$this, 'coupons_data_model' ), 10, 3 );

		}

		public function coupons_dashboard_model ($dashboard_model) {
			global $wpdb, $current_user;

			$dashboard_model_saved[$this->dashboard_key] = get_transient( 'sm_beta_'.$current_user->user_email.'_'.$this->dashboard_key );


			$visible_columns = array('ID', 'post_title', 'discount_type', 'coupon_amount', 'post_excerpt', 'product_ids', 'product_categories', 'customer_email', 'usage_count', 'usage_limit', 'expiry_date', 'free_shipping', 'individual_use', 'exclude_sale_items', 'usage_limit_per_user');

			$post_type_col_index = sm_multidimesional_array_search('postmeta_meta_key_discount_type_meta_value_discount_type', 'index', $dashboard_model[$this->dashboard_key]['columns']);
			
			$coupon_statuses = ( function_exists('wc_get_coupon_types') ) ? wc_get_coupon_types() : array();

			$dashboard_model[$this->dashboard_key]['columns'][$post_type_col_index]['type'] = 'select';
			$dashboard_model[$this->dashboard_key]['columns'][$post_type_col_index]['values'] = $coupon_statuses;
			$dashboard_model[$this->dashboard_key]['columns'][$post_type_col_index]['save_state'] = true;

			$dashboard_model[$this->dashboard_key]['columns'][$post_type_col_index]['search_values'] = array();
			foreach ($coupon_statuses as $key => $value) {
				$dashboard_model[$this->dashboard_key]['columns'][$post_type_col_index]['search_values'][] = array('key' => $key, 'value' => $value);
			}

			$column_model = &$dashboard_model[$this->dashboard_key]['columns'];

			foreach( $column_model as &$column ) {
				
				if (empty($column['src'])) continue;

				$src_exploded = explode("/",$column['src']);

				if (empty($src_exploded)) {
					$src = $column['src'];
				}

				if ( sizeof($src_exploded) > 2) {
					$col_table = $src_exploded[0];
					$cond = explode("=",$src_exploded[1]);

					if (sizeof($cond) == 2) {
						$src = $cond[1];
					}
				} else {
					$src = $src_exploded[1];
					$col_table = $src_exploded[0];
				}


				if( empty($dashboard_model_saved[$this->dashboard_key]) ) {
					if (!empty($column['position'])) {
						unset($column['position']);
					}

					$position = array_search($src, $visible_columns);

					if ($position !== false) {
						$column['position'] = $position + 1;
						$column['hidden'] = false;
					} else {
						$column['hidden'] = true;
					}
				}

				if ($src == 'post_title') {
					$column ['name_display'] = $column ['key'] = 'Coupon Code';
				} else if( $src == 'post_excerpt' ) {
					$column ['name_display'] = $column ['key'] = 'Description';
				} else if( $src == 'customer_email' ) {
					$column ['name_display'] = $column ['key'] = 'Allowed Emails';
				} else if( $src == 'usage_limit' ) {
					$column ['name_display'] = $column ['key'] = 'Usage Limit Per Coupon';
				} else if( $src == 'free_shipping' ) {
					$column ['name_display'] = $column ['key'] = 'Allow Free Shipping';
				}
			}

			if (!empty($dashboard_model_saved[$this->dashboard_key])) {
				$col_model_diff = sm_array_recursive_diff($dashboard_model_saved,$dashboard_model);	
			}

			//clearing the transients before return
			if (!empty($col_model_diff)) {
				delete_transient( 'sm_beta_'.$current_user->user_email.'_'.$this->dashboard_key );	
			}		

			return $dashboard_model;

		}

	}
}
