<?php

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Smart_Manager_Shop_Order' ) ) {
	class Smart_Manager_Shop_Order extends Smart_Manager_Base {
		public $dashboard_key = '',
			$default_store_model = array();

		function __construct($dashboard_key) {
			parent::__construct($dashboard_key);

			$this->dashboard_key = $dashboard_key;
			$this->post_type = $dashboard_key;
			$this->req_params  	= (!empty($_REQUEST)) ? $_REQUEST : array();
			
			add_filter( 'sm_dashboard_model',array( &$this,'orders_dashboard_model' ), 10, 1 );
			add_filter( 'sm_data_model', array( &$this, 'orders_data_model' ), 10, 3 );

			add_filter('posts_join_paged',array(&$this,'sm_query_join'),10,2);
			add_filter('posts_orderby',array(&$this,'sm_query_order_by'),10,2);

		}

		public function orders_dashboard_model ($dashboard_model) {
			global $wpdb, $current_user;

			$dashboard_model[$this->dashboard_key]['tables']['posts']['where']['post_type'] = 'shop_order';

			$dashboard_model_saved[$this->dashboard_key] = get_transient( 'sm_beta_'.$current_user->user_email.'_'.$this->dashboard_key );

			$visible_columns = array('ID', 'post_date', '_billing_first_name', '_billing_last_name', '_billing_email', 'post_status', '_order_total', 'details', '_payment_method_title', 'shipping_method', 'coupons_used', 'line_items');

			$post_type_col_index = sm_multidimesional_array_search('posts_post_status', 'index', $dashboard_model[$this->dashboard_key]['columns']);
			
			$order_statuses = wc_get_order_statuses();

			$dashboard_model[$this->dashboard_key]['columns'][$post_type_col_index]['save_state'] = true;
			
			$dashboard_model[$this->dashboard_key]['columns'][$post_type_col_index]['values'] = array_flip($order_statuses);

			$dashboard_model[$this->dashboard_key]['columns'][$post_type_col_index]['search_values'] = array();
			foreach ($order_statuses as $key => $value) {
				$dashboard_model[$this->dashboard_key]['columns'][$post_type_col_index]['search_values'][] = array('key' => $key, 'value' => $value);
			}


			$column_model = &$dashboard_model[$this->dashboard_key]['columns'];

			//Code for including custom columns for product dashboard
			$custom_columns = array( 'shipping_method', 'coupons_used', 'line_items', 'details' );
			$index = sizeof($column_model);

			foreach( $custom_columns as $col ) {

				$src = 'custom/'.$col;

				$col_index = sm_multidimesional_array_search ($src, 'src', $column_model);

				if( empty( $col_index ) ) {
					$column_model [$index] = array();
					$column_model [$index]['src'] = $src;
					$column_model [$index]['index'] = sanitize_title(str_replace('/', '_', $column_model [$index]['src'])); // generate slug using the wordpress function if not given 
					$column_model [$index]['name'] = __(ucwords(str_replace('_', ' ', $col)), Smart_Manager::$text_domain);
					$column_model [$index]['key'] = $column_model [$index]['name'];
					$column_model [$index]['type'] = 'string';
					$column_model [$index]['hidden']	= false;
					$column_model [$index]['editable']	= false;
					$column_model [$index]['batch_editable'] = false;
					$column_model [$index]['sortable']	= true;
					$column_model [$index]['resizable']	= true;
					$column_model [$index]['allow_showhide'] = true;
					$column_model [$index]['exportable']	= true;
					$column_model [$index]['searchable']	= false;
					$column_model [$index]['save_state'] = true;
					$column_model [$index]['values'] = array();
					$column_model [$index]['search_values'] = array();
					$index++;
				}
			}

			//Code for unsetting the position for hidden columns

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

				if ($src == 'post_date') {
					$column ['name'] = $column ['key'] = 'Order Date';
				} else if ($src == 'post_status') {
					$column ['name'] = $column ['key'] = 'Order Status';
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


		//function to modify the data_model only for Export CSV
		public function orders_data_model( $data_model, $data_cols, $data_cols_serialized ) {

			global $wpdb, $current_user;


			$order_ids = $order_coupons = array();

			foreach( $data_model['items'] as $data ) {

				if( !empty( $data['posts_id'] ) ) {
					$order_ids[] = $data['posts_id'];
				}

			}

			if( count( $order_ids ) > 100 ) {
				$order_ids_imploded = implode(",",$order_ids);
				update_option( 'sm_beta_export_order_ids', $order_ids_imploded );
				$order_id_cond = "SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'sm_beta_export_order_ids'";
			} else {
				$order_id_cond = implode(",",$order_ids);
			}

			$results_order_coupons = $wpdb->get_results( $wpdb->prepare( "SELECT order_id,
								                                        GROUP_CONCAT(order_item_name
								                                                            ORDER BY order_item_id 
								                                                            SEPARATOR ', ' ) AS coupon_used
								                                    FROM {$wpdb->prefix}woocommerce_order_items
								                                    WHERE order_item_type LIKE %s
								                                        AND order_id IN (".$order_id_cond.")
								                                    GROUP BY order_id", 'coupon'), 'ARRAY_A' );

			if( !empty( $results_order_coupons ) ) {
				foreach( $results_order_coupons as $result ) {
                    $order_coupons[$result['order_id']] = $result['coupon_used'];
                } 
			}

			$variation_ids = $wpdb->get_col( $wpdb->prepare( "SELECT order_itemmeta.meta_value 
						                                        FROM {$wpdb->prefix}woocommerce_order_items AS order_items 
						                                           LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_itemmeta 
						                                               ON (order_items.order_item_id = order_itemmeta.order_item_id)
						                                        WHERE order_itemmeta.meta_key LIKE %s
						                                               AND order_itemmeta.meta_value > %d
						                                               AND order_items.order_id IN (".$order_id_cond.")", '_variation_id', 0 ) );
            
            if ( count( $variation_ids ) > 0 ) {

            	if( count( $variation_ids ) > 100 ) {
					$variation_ids_imploded = implode(",",$variation_ids);
					update_option( 'sm_beta_export_variation_ids', $variation_ids_imploded );
					$variation_id_cond = "SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'sm_beta_export_variation_ids'";
				} else {
					$variation_id_cond = implode(",",$variation_ids);
				}

                $results_variation_att = $wpdb->get_results( $wpdb->prepare( "SELECT postmeta.post_id AS post_id,
									                                                    GROUP_CONCAT(postmeta.meta_value
									                                                        ORDER BY postmeta.meta_id 
									                                                        SEPARATOR ',' ) AS meta_value
									                                            FROM {$wpdb->prefix}postmeta AS postmeta
									                                            WHERE postmeta.meta_key LIKE %s
									                                                AND postmeta.post_id IN (". $variation_id_cond .")
									                                            GROUP BY postmeta.post_id", 'attribute_%' ), 'ARRAY_A') ;
            }

			//Code to get the variation Attributes
			$attributes_terms = $wpdb->get_results( $wpdb->prepare( "SELECT terms.slug as slug, terms.name as term_name
										                          FROM {$wpdb->prefix}terms AS terms
										                            JOIN {$wpdb->prefix}postmeta AS postmeta 
										                                ON ( postmeta.meta_value = terms.slug 
										                                        AND postmeta.meta_key LIKE %s )
										                          GROUP BY terms.slug", 'attribute_%' ), 'ARRAY_A' );
            $attributes = array();
            foreach ( $attributes_terms as $attributes_term ) {
                $attributes[$attributes_term['slug']] = $attributes_term['term_name'];
            }
            
            $variation_att_all = array();

            if ( !empty($results_variation_att) && is_array( $results_variation_att ) && count( $results_variation_att ) > 0 ) {
                
                for ($i=0;$i<sizeof($results_variation_att);$i++) {
                    $variation_attributes = explode(", ",$results_variation_att [$i]['meta_value']);
                    
                    $attributes_final = array();
                    foreach ($variation_attributes as $variation_attribute) {
                        $attributes_final[] = (isset($attributes[$variation_attribute]) ? $attributes[$variation_attribute] : ucfirst($variation_attribute) );
                    }
                    
                    $results_variation_att [$i]['meta_value'] = implode(", ",$attributes_final);
                    $variation_att_all [$results_variation_att [$i]['post_id']] = $results_variation_att [$i]['meta_value'];
                }

            }

            //Code for handling search
            $order_id_join = '';
			if( !empty($this->req_params) && !empty($this->req_params['search_query']) && !empty($this->req_params['search_query'][0]) ) {
				$order_id_join = " JOIN {$wpdb->base_prefix}sm_advanced_search_temp as temp ON (temp.product_id = order_items.order_id)";
			}


			$order_items = array();
            $order_shipping_method = array();

            $results = $wpdb->get_results( $wpdb->prepare( "SELECT order_items.order_item_id,
				                            order_items.order_id    ,
				                            order_items.order_item_name AS order_prod,
				                            order_items.order_item_type,
				                            GROUP_CONCAT(order_itemmeta.meta_key
				                                                ORDER BY order_itemmeta.meta_id 
				                                                SEPARATOR '###' ) AS meta_key,
				                            GROUP_CONCAT(order_itemmeta.meta_value
				                                                ORDER BY order_itemmeta.meta_id 
				                                                SEPARATOR '###' ) AS meta_value
				                        FROM {$wpdb->prefix}woocommerce_order_items AS order_items 
				                            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_itemmeta 
				                                ON (order_items.order_item_id = order_itemmeta.order_item_id)
				                            ". $order_id_join ."
				                        WHERE order_items.order_item_type IN ('line_item', 'shipping') 
				                        	AND 1 = %d
				                        GROUP BY order_items.order_item_id", 1 ), 'ARRAY_A' );

            if ( !empty( $results ) ) {

                foreach ( $results as $result ) {

                    if ( !isset($order_items [$result['order_id']]) ) {
                        $order_items [$result['order_id']] = array();
                    }

                    if ($result['order_item_type'] == 'shipping') {
                        $order_shipping_method [$result['order_id']] = $result['order_prod'];
                    } else {
                        $order_items [$result['order_id']] [] = $result;
                    }

                }    
            }


            if( !empty( $data_model['items'] ) ) {
            	foreach( $data_model['items'] as $key => $order_data ) {

            		$order_id = ( !empty( $order_data['posts_id'] ) ) ? $order_data['posts_id'] : 0;

            		if( !empty( $order_items[$order_id] ) ) {

            			foreach( $order_items[$order_id] as $order_item ) {
            				$order_meta_values = explode('###', $order_item ['meta_value'] );
	                        $order_meta_key = explode('###', $order_item ['meta_key'] );

	                        if (count($order_meta_values) != count($order_meta_key)) {
	                            continue;
	                        }

	                        $order_meta_key_values = array_combine($order_meta_key, $order_meta_values);

	                        $data_model['items'][$key]['custom_details'] = (!empty($data_model['items'][$key]['custom_details'])) ? $data_model['items'][$key]['custom_details'] : '';
	                        $data_model['items'][$key]['custom_details'] += $order_meta_key_values['_qty'];

	                        $product_id = ( $order_meta_key_values['_variation_id'] > 0 ) ? $order_meta_key_values['_variation_id'] : $order_meta_key_values['_product_id'];
		                    $sm_sku = get_post_meta( $product_id, '_sku', true );
		                    if ( ! empty( $sm_sku ) ) {
		                            $sku_detail = '[SKU: ' . $sm_sku . ']';
		                    } else {
		                            $sku_detail = '';
		                    }
		                    
		                    $variation_att = ( isset( $variation_att_all [$order_meta_key_values['_variation_id']] ) && !empty( $variation_att_all [$order_meta_key_values['_variation_id']] ) ) ? $variation_att_all [$order_meta_key_values['_variation_id']] : '';

		                    $product_full_name = ( !empty( $variation_att ) ) ? $order_item['order_prod'] . ' (' . $variation_att . ')' : $order_item['order_prod'];

		                    $data_model['items'][$key]['custom_line_items'] = (!empty($updated_data_model[$order_id]['custom_line_items'])) ? $updated_data_model[$order_id]['custom_line_items'] : '';
		                    $data_model['items'][$key]['custom_line_items'] .= $product_full_name.' '.$sku_detail.'['.__('Qty','smart-manager-for-wp-e-commerce').': '.$order_meta_key_values['_qty'].']['.__('Price','smart-manager-for-wp-e-commerce').': '.($order_meta_key_values['_line_total']/$order_meta_key_values['_qty']).'], ';
            			}

            			if( !empty( $data_model['items'][$key]['custom_line_items'] ) ) {
            				$data_model['items'][$key]['custom_line_items'] = substr( $data_model['items'][$key]['custom_line_items'], 0, -2 ); //To remove extra comma ', ' from returned 
            			}

            			$data_model['items'][$key]['custom_details'] = !empty( $data_model['items'][$key]['custom_details'] ) ? ( ( $data_model['items'][$key]['custom_details'] == 1) ? $data_model['items'][$key]['custom_details'] . ' item' : $data_model['items'][$key]['custom_details'] . ' items' ) : ''; 

            		}

                    $data_model['items'][$key]['custom_coupons_used'] = ( !empty( $order_coupons[$order_id] ) ) ? $order_coupons[$order_id] : "";
                    $data_model['items'][$key]['custom_shipping_method'] = ( !empty( $order_shipping_method[$order_id] ) ) ? $order_shipping_method[$order_id] : "";
            	}
            }

			return $data_model;
		}

	}

}