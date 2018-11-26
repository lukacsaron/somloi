<?php

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Smart_Manager_Pro_Base' ) ) {
	class Smart_Manager_Pro_Base extends Smart_Manager_Base {

		public $dashboard_key = '';

		protected static $sm_beta_background_updater;
		protected static $sm_beta_background_updater_action;

		function __construct($dashboard_key) {
			$this->dashboard_key = $dashboard_key;
			parent::__construct($dashboard_key);
			self::$sm_beta_background_updater = Smart_Manager_Pro_Background_Updater::instance();
		}

		//function to handle batch update request
		public function batch_update() {
			global $wpdb, $current_user;

			$selected_ids = (!empty($this->req_params['selected_ids'])) ? json_decode(stripslashes($this->req_params['selected_ids']), true) : array();
			$batch_update_actions = (!empty($this->req_params['batch_update_actions'])) ? json_decode(stripslashes($this->req_params['batch_update_actions']), true) : array();

			$dashboard_key = $this->dashboard_key; //fix for PHP 5.3 or earlier

			$batch_update_actions = array_map( function( $batch_update_action ) use ($dashboard_key) {
				$batch_update_action['dashboard_key'] = $dashboard_key;
				return $batch_update_action;
			}, $batch_update_actions);

			$entire_store = false;

			if( !empty( $this->req_params['storewide_option']) && $this->req_params['storewide_option'] == 'entire_store' && !empty($this->req_params['active_module']) ) { //code for fetching all the ids in case of entire store batch update

				$query = apply_filters( 'sm_beta_batch_update_entire_store_ids_query', $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = %s",$this->req_params['active_module'] ) );

				$selected_ids = $wpdb->get_col( $query );
				$entire_store = true;
			}

			update_site_option('sm_beta_background_process_params',array('process_name' => 'Batch Update', 'actions' => $batch_update_actions, 'id_count' => count($selected_ids), 'entire_store' => $entire_store, 'SM_IS_WOO30' => $this->req_params['SM_IS_WOO30']));


			if ( is_a( self::$sm_beta_background_updater, 'Smart_Manager_Pro_Background_Updater' ) ) {

				if( !empty($selected_ids) ) {
					
					foreach ($selected_ids as $selected_id) {

						foreach( $batch_update_actions as $key => $value ) {
							$batch_update_actions[$key]['id'] = $selected_id;
						}

						self::$sm_beta_background_updater->push_to_queue( array(
							'filter' => array( 'class_path' => $this->req_params['class_path'], 
												'func' => array($this->req_params['class_nm'], 'process_batch_update')),
							'args'   => $batch_update_actions
						) );
					}

					self::$sm_beta_background_updater->dispatch_queue();
				}	

			}

		}

		//function to process batch update conditions
		public static function process_batch_update($args) {
			do_action('sm_beta_pre_process_batch');

			//code for processing logic for batch update
			if( empty($args['table_nm']) || empty($args['action']) || empty($args['col_nm']) || empty($args['value']) || empty($args['id']) ) {
				return false;
			}

			$prev_val = $new_val = '';

			if( $args['action'] != 'set_to' ) { //code to fetch prev stored values
				if( $args['table_nm'] == 'posts' ) {
					$prev_val = get_post_field($args['col_nm'], $args['id']);
				} else if( $args['table_nm'] == 'postmeta' ) {
					$prev_val = get_post_meta($args['id'], $args['col_nm'], true);
				}

				$prev_val = apply_filters( 'sm_beta_batch_update_prev_value', $prev_val, $args );
			}

			if( $args['type'] == 'number' && empty($prev_val) ) {
				$prev_val = 0;
			}

			if( $args['type'] == 'number' && empty($args['value']) ) {
				$args['value'] = 0;
			}

			//cases to update the value based on the batch update actions

			switch( $args['action'] ) {
				case 'set_to':
					$new_val = $args['value'];
					break;
				case 'prepend':
					$new_val = $args['value'].''.$prev_val;
					break;
				case 'append':
					$new_val = $prev_val.''.$args['value'];
					break;
				case 'increase_by_per':
					$new_val = round( ($prev_val + ($prev_val * ($args['value'] / 100))), apply_filters('sm_beta_pro_num_decimals',get_option( 'woocommerce_price_num_decimals' )) );
					break;
				case 'decrease_by_per':
					$new_val = round( ($prev_val - ($prev_val * ($args['value'] / 100))), apply_filters('sm_beta_pro_num_decimals',get_option( 'woocommerce_price_num_decimals' )) );
					break;
				case 'increase_by_num':
					$new_val = round( ($prev_val + $args['value']), apply_filters('sm_beta_pro_num_decimals',get_option( 'woocommerce_price_num_decimals' )) );
					break;
				case 'decrease_by_num':
					$new_val = round( ($prev_val - $args['value']), apply_filters('sm_beta_pro_num_decimals',get_option( 'woocommerce_price_num_decimals' )) );
					break;
				default:
					$new_val = $args['value'];
					break;
			}

			$args['value'] = $new_val;
			self::process_batch_update_db_updates($args);
		}

		//function to handle the batch update db updates
		public static function process_batch_update_db_updates($args) {

			do_action('sm_pre_batch_update_db_updates',$args);

			$update = false;
			$default_batch_update = true;

			$default_batch_update = apply_filters( 'sm_default_batch_update_db_updates',$default_batch_update );


			if( $default_batch_update ) {			

				if( $args['table_nm'] == 'posts' ) {
					$update = wp_update_post(array('ID' => $args['id'], $args['col_nm'] => $args['value']));
				} else if( $args['table_nm'] == 'postmeta' ) {
					$update = update_post_meta($args['id'], $args['col_nm'], $args['value']);
				} else if( $args['table_nm'] == 'terms' ) {
					$update = wp_set_object_terms( $args['id'], $args['value'], $args['col_nm'] );
				}
			}

			$update = apply_filters('sm_post_batch_update_db_updates',$update ,$args);

			if( is_wp_error($update) ) {
				return false;
			} else {
				return true;
			}
		}

		//function to handle batch process complete
		public static function batch_process_complete() {

			//for background process
			$key = 'sm_beta_background_process_status';
			$batch_background_process = get_site_option($key, false);

			if( empty($batch_background_process) ) {
				return;
			}

			$result = delete_site_option($key);

			$transient_key = 'sm_beta_background_process_params';
			$background_process_params = get_site_option($transient_key);
			
			//preparing email content

			$email = get_option('admin_email');
			$email_heading_color = get_option('woocommerce_email_base_color');
			$email_text_color = get_option('woocommerce_email_text_color');

			$email_heading_color = (empty($email_heading_color)) ? '#96588a' : $email_heading_color; 
			$email_text_color = (empty($email_text_color)) ? '#3c3c3c' : $email_text_color; 

			$actions = ( !empty($background_process_params['actions']) ) ? $background_process_params['actions'] : array();

			$records_str = $background_process_params['id_count'] .' '. (( $background_process_params['id_count'] > 1 ) ? __('records', SM_TEXT_DOMAIN) : __('record', SM_TEXT_DOMAIN));
			$records_str .= ( $background_process_params['entire_store'] ) ? ' ('. __('entire store', SM_TEXT_DOMAIN) .')' : '';

			$title = __('Smart Manager completed your', SM_TEXT_DOMAIN) . ' \''. $background_process_params['process_name'] .'\' '. __('Process', SM_TEXT_DOMAIN) .' !!!';

			ob_start();

			include( apply_filters( 'sm_beta_pro_batch_email_template', SM_BETA_PRO_URL.'templates/email.php' ) );

			$message = ob_get_clean();

			$subject = $title;

			delete_site_option($transient_key);

			wc_mail( $email, $subject, $message );
		}

		//function to update the progress of background update process
		public function background_updater_heartbeat() {

            $response = array('ack' => 'Failure');

            $batch_update_per = get_site_option('wp_'.Smart_Manager_Pro_Background_Updater::$_action.'_per',false);

			if( $batch_update_per !== false ) {

				if( $batch_update_per == 100 ) {
					delete_site_option('wp_'.Smart_Manager_Pro_Background_Updater::$_action.'_per');
					delete_site_option('wp_'.Smart_Manager_Pro_Background_Updater::$_action.'_tot');
				} else {
					//for background_process
					$key = 'sm_beta_background_process_status';
					$batch_background_process = get_site_option($key, false);
		            if( (!empty($this->req_params['background_process']) && $this->req_params['background_process'] != 'false') && empty($batch_background_process) ) {
		            	update_site_option($key, 1);
		            }
				}

				$response = array('ack' => 'Success', 'per' => $batch_update_per );
			}

            ob_clean();
            echo json_encode($response);
            die();
        }

        //Function to generate and export the CSV data
        public function get_export_csv() {

        	global $current_user;

        	ini_set('memory_limit','512M');
    		set_time_limit(0);

    		$this->req_params['sort_params'] = json_decode( stripslashes( $this->req_params['sort_params'] ), true );
    		$this->req_params['table_model'] = json_decode( stripslashes( $this->req_params['table_model'] ), true );

    		$current_store_model[$this->dashboard_key] = get_transient( 'sm_beta_'.$current_user->user_email.'_'.$this->dashboard_key );
			$col_model = (!empty($current_store_model[$this->dashboard_key]['columns'])) ? $current_store_model[$this->dashboard_key]['columns'] : array();

        	$data = $this->get_data_model();

        	$columns_header = $select_cols = array();

	        $getfield = '';

        	foreach( $col_model as $col ) {

        		if( empty( $col['exportable'] ) ) {
        			continue;
        		}

				$columns_header[ $col['index'] ] = $col['key'];

				$getfield .= $col['key'] . ',';

				if( !empty( $col['values'] ) ) {
					$select_cols[ $col['index'] ] = $col['values'];
				}
        	}

        	$fields = substr_replace($getfield, '', -1);
			$each_field = array_keys( $columns_header );

			$csv_file_name = sanitize_title(get_bloginfo( 'name' )) . '_' . $this->dashboard_key . '_' . gmdate('d-M-Y_H:i:s') . ".csv";

			foreach( (array) $data['items'] as $row ){

				for($i = 0; $i < count ( $columns_header ); $i++){

					if( $i == 0 ){
						$fields .= "\n";	
					}

	                if( !empty( $select_cols[ $each_field[$i] ] ) ) {
	                	$row_each_field = !empty( $select_cols[ $each_field[$i] ][ $row[$each_field[$i]] ] ) ? $select_cols[ $each_field[$i] ][ $row[$each_field[$i]] ] : $row[$each_field[$i]];
	            	} else {
	                    $row_each_field = !empty($row[$each_field[$i]]) ? $row[$each_field[$i]] : '';
	                }
	                $array_temp = str_replace(array("\n", "\n\r", "\r\n", "\r"), "\t", $row_each_field);
	                
	                $array = str_replace("<br>", "\n", $array_temp);
					$array = str_replace('"', '""', $array);
					$array = str_getcsv ( $array , ",", "\"" , "\\");
					$str = ( $array && is_array( $array ) ) ? implode( ', ', $array ) : '';
					$fields .= '"'. $str . '",'; 

				}	
				$fields = substr_replace($fields, '', -1); 
			}

			$upload_dir = wp_upload_dir();
			$file_data = array();
			$file_data['wp_upload_dir'] = $upload_dir['path'] . '/';
			$file_data['file_name'] = $csv_file_name;
			$file_data['file_content'] = $fields;

        	header("Content-type: text/x-csv; charset=UTF-8"); 
		    header("Content-Transfer-Encoding: binary");
		    header("Content-Disposition: attachment; filename=".$file_data['file_name']); 
		    header("Pragma: no-cache");
		    header("Expires: 0");

		    while(ob_get_contents()) {
		        ob_clean();
		    }

		    echo $file_data['file_content'];
		    
		    exit;
        }

        //Function to generate the data for print_invoice
        public function get_print_invoice() {

        	global $smart_manager_beta;

        	ini_set('memory_limit','512M');
    		set_time_limit(0);

        	$purchase_id_arr = json_decode( stripslashes( $this->req_params['selected_ids'] ), true );
        	$sm_text_domain = Smart_Manager::$text_domain;
        	$sm_is_woo30 = ( ! empty( Smart_Manager::$sm_is_woo30 ) && 'true' === Smart_Manager::$sm_is_woo30 ) ? true : false;

        	ob_start();
        	include SM_PLUGIN_DIR_PATH . '/new/pro/templates/order-invoice.php';
        	echo ob_get_clean();
		    exit;
        }

        //function to handle duplicate records functionality
        public function duplicate_records() {

        	global $wpdb, $current_user;
	
			$selected_ids = (!empty($this->req_params['selected_ids'])) ? json_decode(stripslashes($this->req_params['selected_ids']), true) : array();

			if( !empty( $this->req_params['storewide_option']) && $this->req_params['storewide_option'] == 'entire_store' && !empty($this->req_params['active_module']) ) { //code for fetching all the ids in case of duplicate entire store

				$query = apply_filters( 'sm_beta_duplicate_records_entire_store_ids_query', $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = %s",$this->req_params['active_module'] ) );

				$selected_ids = $wpdb->get_col( $query );
				$entire_store = true;
			}

			update_site_option('sm_beta_background_process_params',array('process_name' => 'Duplicate Records', 'id_count' => count($selected_ids), 'entire_store' => $entire_store, 'SM_IS_WOO30' => $this->req_params['SM_IS_WOO30'], 'SM_IS_WOO22' => $this->req_params['SM_IS_WOO22'], 'SM_IS_WOO21' => $this->req_params['SM_IS_WOO21']));

			if ( is_a( self::$sm_beta_background_updater, 'Smart_Manager_Pro_Background_Updater' ) ) {

				if( !empty($selected_ids) ) {
					
					foreach ($selected_ids as $selected_id) {

						self::$sm_beta_background_updater->push_to_queue( array(
							'filter' => array( 'class_path' => $this->req_params['class_path'], 
												'func' => array($this->req_params['class_nm'], 'process_duplicate_record')),
							'args'   => array( $selected_id )
						) );
					}

					self::$sm_beta_background_updater->dispatch_queue();
				}	

			}

        }

        public static function get_duplicate_record_settings() {
	
			$defaults = array(
				'status' => 'same',
				'type' => 'same',
				'timestamp' => 'current',
				'title' => '('.__('Copy', SM_TEXT_DOMAIN).')',
				'slug' => 'copy',
				'time_offset' => false,
				'time_offset_days' => 0,
				'time_offset_hours' => 0,
				'time_offset_minutes' => 0,
				'time_offset_seconds' => 0,
				'time_offset_direction' => 'newer'
			);
			
			$settings = apply_filters( 'sm_beta_duplicate_records_settings', $defaults );
			
			return $settings;
		}


        //function to process duplicate records logic
		public static function process_duplicate_record( $original_id ) {

			do_action('sm_beta_pre_process_duplicate_records', $original_id );

			//code for processing logic for duplicate records
			if( empty( $original_id ) ) {
				return false;
			}

			global $wpdb;

			// Get the post as an array
			$duplicate = get_post( $original_id, 'ARRAY_A' );
				
			$settings = self::get_duplicate_record_settings();
			
			// Modify title
			$appended = ( $settings['title'] != '' ) ? ' '.$settings['title'] : '';
			$duplicate['post_title'] = $duplicate['post_title'].' '.$appended;
			$duplicate['post_name'] = sanitize_title($duplicate['post_name'].'-'.$settings['slug']);
			
			// Set the post status
			if( $settings['status'] != 'same' ) {
				$duplicate['post_status'] = $settings['status'];
			}
			
			// Set the post type
			if( $settings['type'] != 'same' ) {
				$duplicate['post_type'] = $settings['type'];
			}
			
			// Set the post date
			$timestamp = ( $settings['timestamp'] == 'duplicate' ) ? strtotime($duplicate['post_date']) : current_time('timestamp',0);
			$timestamp_gmt = ( $settings['timestamp'] == 'duplicate' ) ? strtotime($duplicate['post_date_gmt']) : current_time('timestamp',1);
			
			if( $settings['time_offset'] ) {
				$offset = intval($settings['time_offset_seconds']+$settings['time_offset_minutes']*60+$settings['time_offset_hours']*3600+$settings['time_offset_days']*86400);
				if( $settings['time_offset_direction'] == 'newer' ) {
					$timestamp = intval($timestamp+$offset);
					$timestamp_gmt = intval($timestamp_gmt+$offset);
				} else {
					$timestamp = intval($timestamp-$offset);
					$timestamp_gmt = intval($timestamp_gmt-$offset);
				}
			}
			$duplicate['post_date'] = date('Y-m-d H:i:s', $timestamp);
			$duplicate['post_date_gmt'] = date('Y-m-d H:i:s', $timestamp_gmt);
			$duplicate['post_modified'] = date('Y-m-d H:i:s', current_time('timestamp',0));
			$duplicate['post_modified_gmt'] = date('Y-m-d H:i:s', current_time('timestamp',1));

			// Remove some of the keys
			unset( $duplicate['ID'] );
			unset( $duplicate['guid'] );
			unset( $duplicate['comment_count'] );

			// Insert the post into the database
			$duplicate_id = wp_insert_post( $duplicate );
			
			// Duplicate all the taxonomies/terms
			$taxonomies = get_object_taxonomies( $duplicate['post_type'] );
			foreach( $taxonomies as $taxonomy ) {
				$terms = wp_get_post_terms( $original_id, $taxonomy, array('fields' => 'names') );
				wp_set_object_terms( $duplicate_id, $terms, $taxonomy );
			}
		  
		  	// Duplicate all the custom fields
			$custom_fields = get_post_custom( $original_id );

			$postmeta_data = array();

		  	foreach ( $custom_fields as $key => $value ) {
			  if( is_array($value) && count($value) > 0 ) { //TODO: optimize
					foreach( $value as $i=>$v ) {
						$postmeta_data[] = '('.$duplicate_id.',\''.$key.'\',\''.$v.'\')'; 
					}
				}
		 	}

		 	if( !empty($postmeta_data) ) {

		 		$q = "INSERT INTO {$wpdb->prefix}postmeta(post_id, meta_key, meta_value) VALUES ". implode(",", $postmeta_data);
		 		$query = $wpdb->query("INSERT INTO {$wpdb->prefix}postmeta(post_id, meta_key, meta_value) VALUES ". implode(",", $postmeta_data));
		 	}

		  	do_action( 'sm_beta_post_process_duplicate_records', array( 'original_id' => $original_id, 'duplicate_id' => $duplicate_id, 'settings' => $settings, 'duplicate' => $duplicate ) );
			
		  	if( is_wp_error($duplicate_id) ) {
				return false;
			} else {
				return true;
			}

		}

	}
}

?>
