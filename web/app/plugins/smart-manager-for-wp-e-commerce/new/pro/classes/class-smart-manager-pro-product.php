<?php

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Smart_Manager_Pro_Product' ) ) {
	class Smart_Manager_Pro_Product extends Smart_Manager_Pro_Base {
		public $dashboard_key = '',
				$variation_product_old_title = '';

		protected static $_instance = null;

		private $product = '';

		public static function instance($dashboard_key) {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self($dashboard_key);
			}
			return self::$_instance;
		}

		function __construct($dashboard_key) {
			parent::__construct($dashboard_key);
			self::actions();

			if ( class_exists( 'Smart_Manager_Product' ) ) {
				$this->product = new Smart_Manager_Product( $dashboard_key );
			}
		}

		public function __call( $function_name, $arguments = array() ) {

			if( empty( $this->product ) ) {
				return;
			}

			if ( ! is_callable( array( $this->product, $function_name ) ) ) {
				return;
			}

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( array( $this->product, $function_name ), $arguments );
			} else {
				return call_user_func( array( $this->product, $function_name ) );
			}
		}

		public static function actions() {
			add_filter('sm_beta_batch_update_entire_store_ids_query', __CLASS__. '::products_batch_update_entire_store_ids_query', 10, 1);
			add_filter('sm_pre_batch_update_db_updates', __CLASS__. '::products_pre_batch_update_db_updates', 10, 2);
			add_filter('sm_post_batch_update_db_updates', __CLASS__. '::products_post_batch_update_db_updates', 10, 2);
		}
		

		public static function products_batch_update_entire_store_ids_query( $query ) {

			global $wpdb;

			$query = $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE 1=%d AND post_type IN ('product', 'product_variation')", 1 );
			return $query;
		}

		public static function products_pre_batch_update_db_updates($args) {

			if( !empty( $args['id'] ) && ( !empty( $args['table_nm'] ) && $args['table_nm'] == 'posts' ) && ( !empty( $args['col_nm'] ) && $args['col_nm'] == 'post_title' ) && !empty( Smart_Manager::$sm_is_woo30 ) ) {
				$results = sm_get_current_variation_title( array($args['id']) );

	            if( count( $results ) > 0 ) {
	                foreach( $results as $result ) {
	                    self::$_instance->variation_product_old_title[ $result['id'] ] = $result['post_title'];
	                }
	            }
			}
		}

		public static function products_post_batch_update_db_updates($update_flag = false, $args) {

			//code for handling updation of price & sales pice in woocommerce
			if( !empty($args['table_nm']) && $args['table_nm'] == 'postmeta' && ( (!empty($args['col_nm']) && $args['col_nm'] == '_regular_price') || (!empty($args['col_nm']) && $args['col_nm'] == '_sale_price') )) {
				sm_update_price_meta(array($args['id']));
				$update_flag = true;
			}

			//code to sync the variations title if the variation parent title has been updated
			if( !empty( Smart_Manager::$sm_is_woo30 ) && ( !empty( $args['table_nm'] ) && $args['table_nm'] == 'posts' ) && ( !empty( $args['col_nm'] ) && $args['col_nm'] == 'post_title' ) ) {

				$new_title = ( !empty( $args['value'] ) ) ? $args['value'] : '';

				if( !empty( self::$_instance->variation_product_old_title[ $args['id'] ] ) && self::$_instance->variation_product_old_title[ $args['id'] ] != $new_title ) {
                    $new_title_update_case = 'WHEN post_parent='. $args['id'] .' THEN REPLACE(post_title, \''. self::$_instance->variation_product_old_title[ $args['id'] ] .'\', \''. $new_title .'\')';
                    sm_sync_variation_title( array($new_title_update_case), array($args['id']) );
                }
			}


			//Code for updating product attributes
			if( !empty($args['table_nm']) && $args['table_nm'] == 'custom' && ( (!empty($args['col_nm']) && $args['col_nm'] == 'product_attributes_add') || (!empty($args['col_nm']) && $args['col_nm'] == 'product_attributes_remove') )) {
				
				$action = ( !empty($args['action']) ) ? $args['action'] : '';
				$current_term_ids = array();

				if( !empty($action) ) {
					delete_transient( 'wc_layered_nav_counts_' . $args['action'] );
				}

				$product_attributes = get_post_meta( $args['id'], '_product_attributes', true );

				$all_terms_ids = array();

				if( !empty($action) && $action != 'custom' ) {
					$current_term_ids = wp_get_object_terms( $args['id'], $action, 'orderby=none&fields=ids' );

					if( !empty($args['value']) && $args['value'] == 'all' ) { //creating array of all values for the attribute
						$taxonomy_terms = get_terms($action, array('hide_empty'=> 0,'orderby'=> 'id'));

						if (!empty($taxonomy_terms)) {
							foreach ($taxonomy_terms as $term_obj) {
								$all_terms_ids[] = $term_obj->term_id;
							}
						}
					}
				}

				if( $args['col_nm'] == 'product_attributes_add' ) {

					if( !empty($action) && $action != 'custom' ) {

						if( !in_array($args['value'], $current_term_ids) ) {

							if( $args['value'] != 'all' ) {
								$current_term_ids[] = (int) $args['value'];
							} else {
								$current_term_ids = $all_terms_ids;
							}

							$update_flag = wp_set_object_terms( $args['id'], $current_term_ids, $action );
						}


						if( empty($product_attributes[$action]) ) {
							$product_attributes[$action] = array( 'name' => $action,
															            'value' => '',
															            'position' => 1,
															            'is_visible' => 1,
															            'is_variation' => 0,
															            'is_taxonomy' => 1 );
						}

					} else if( !empty($action) && $action == 'custom' ) {

						$value = ( (!empty($args['value2'])) ? $args['value2'] : '' );

						if( !empty($product_attributes[$args['value']]) ) {
							$product_attributes[$args['value']]['value'] = $value;
						} else {
							$product_attributes[$args['value']] = array( 'name' => $args['value'],
															            'value' => $value,
															            'position' => 1,
															            'is_visible' => 1,
															            'is_variation' => 0,
															            'is_taxonomy' => 0 );
						}

						$update_flag = true;

					}

				} else if( $args['col_nm'] == 'product_attributes_remove' ) {
					if( !empty($action) && $action != 'custom' ) {

						$all = ( !empty($args['value']) && $args['value'] == 'all') ? true : false;

						$key = array_search($args['value'], $current_term_ids);

						if( $key !== false ) {
							unset($current_term_ids[$key]);
							$update_flag = wp_set_object_terms( $args['id'], $current_term_ids, $action );
						} else if( $all === true ) {
							$update_flag = wp_set_object_terms( $args['id'], array(), $action );
						}

						if( (count($current_term_ids) == 0 || $all === true) && !empty($product_attributes[$action]) ) {
							unset($product_attributes[$action]);
						}
					}
				}

				update_post_meta( $args['id'], '_product_attributes', $product_attributes );
			}

			//Code for updating product categories
			if( !empty($args['table_nm']) && $args['table_nm'] == 'custom' && (!empty($args['col_nm']) && strpos($args['col_nm'], 'product_cat') !== false ) ) {

				$action = ( !empty($args['action']) ) ? $args['action'] : '';
				$value = ( !empty($args['value']) ) ? (int) $args['value'] : 0;
				$taxonomy_nm = 'product_cat';
				$current_term_ids = array();

				if( !empty($action) && $action != 'set_to' ) {
					$current_term_ids = wp_get_object_terms( $args['id'], $taxonomy_nm, 'orderby=none&fields=ids' );

					if( $action == 'add_to' ) {
						$current_term_ids[] = $value;						
					} else if( $action == 'remove_from' ) {
						$key = array_search($value, $current_term_ids);
						if( $key !== false ) {
							unset($current_term_ids[$key]);
						}
					}
					
				} else if( !empty($action) && $action == 'set_to' ) {
					$current_term_ids = array( $value );
				}

				$update_flag = wp_set_object_terms( $args['id'], $current_term_ids, $taxonomy_nm );

			}

			clean_post_cache( $args['id'] );

			//product clear_caches
			wc_delete_product_transients( $args['id'] );
			if ( class_exists( 'WC_Cache_Helper' ) ) {
				WC_Cache_Helper::incr_cache_prefix( 'product_' . $args['id'] );
			}

			do_action( 'woocommerce_update_product', $args['id'] );

			return $update_flag;

		}

		//function to process duplicate products logic
		public static function process_duplicate_record( $original_id ) {

			do_action('sm_beta_pre_process_duplicate_products', $original_id );

			//code for processing logic for duplicate products
			if( empty( $original_id ) ) {
				return false;
			}

			$product = wc_get_product( $original_id );

			$background_process_params = get_site_option('sm_beta_background_process_params');

            $parent_id = 0;
            $woo_dup_obj = '';
            if( !empty( $background_process_params ) && (!empty( $background_process_params['SM_IS_WOO30'] ) || !empty( $background_process_params['SM_IS_WOO22'] ) || !empty( $background_process_params['SM_IS_WOO21'] ) ) ) {
                $parent_id = wp_get_post_parent_id($original_id);

                $file = WP_PLUGIN_DIR . '/woocommerce/includes/admin/class-wc-admin-duplicate-product.php';
                if( file_exists( $file ) ) {
                	include_once ( $file ); // for handling the duplicate product functionality
                }

                if ( class_exists( 'WC_Admin_Duplicate_Product' ) ) {
                	$woo_dup_obj = new WC_Admin_Duplicate_Product();
                }
                
            } else {

            	$file = WP_PLUGIN_DIR . '/woocommerce/admin/includes/duplicate_product.php';
                if( file_exists( $file ) ) {
                	include_once ( $file ); // for handling the duplicate product functionality
                }

                $post = get_post( $original_id );
                $parent_id = $post->post_parent;    
            }

            if ($parent_id == 0) {
                
                if ($woo_dup_obj instanceof WC_Admin_Duplicate_Product) {
                    if( !empty( $background_process_params ) && !empty( $background_process_params['SM_IS_WOO30'] ) ) {
                        $product = wc_get_product( $original_id );
                        $dup_prod = $woo_dup_obj->product_duplicate( $product );

                        if( !is_wp_error($dup_prod) ) {
                        	$dup_prod_id = $dup_prod->get_id();
                        }
                        

                    } else {
                        $dup_prod_id = $woo_dup_obj -> duplicate_product($post,0,$post->post_status);
                    }
                } else {
                    $dup_prod_id = woocommerce_create_duplicate_from_product($post,0,$post->post_status);
                }

                //Code for updating the post name
                if( !empty( $background_process_params ) && empty( $background_process_params['SM_IS_WOO30'] ) ) {
                    $new_slug = sanitize_title( get_the_title($dup_prod_id) );
                    wp_update_post(
                                        array (
                                            'ID'        => $dup_prod_id,
                                            'post_name' => $new_slug
                                        )
                                    );
                }

            }

            if( is_wp_error($dup_prod_id) ) {
				return false;
			} else {
				return true;
			}
		}

	} //End of Class
}