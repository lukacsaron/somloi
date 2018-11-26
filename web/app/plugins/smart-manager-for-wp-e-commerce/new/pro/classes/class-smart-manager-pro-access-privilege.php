<?php

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Smart_Manager_Pro_Access_Privilege' ) ) {
	class Smart_Manager_Pro_Access_Privilege {

		protected static $_instance = null;

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		function __construct() {
			add_action( 'admin_menu', array( $this, 'sm_beta_add_menu_access' ), 50, 1 );
			add_filter( 'sm_active_dashboards', array( $this, 'sm_beta_get_accessible_dashboards' ) );
		}

		public function sm_beta_add_menu_access() {

			// if ( ( defined('SM_BETA_ACCESS') && SM_BETA_ACCESS === true ) ) {
				$page = add_menu_page( 'Smart Manager', 'Smart Manager','read', 'smart-manager', 'sm_admin_page' );
				add_action( 'admin_print_scripts-' . $page, 'smart_admin_scripts' );
				add_action( 'admin_print_styles-' . $page, 'smart_admin_styles' );
			// }
		}

		//function to get current user wp_role object
		public static function getRoles( $role ) {
	        global $wp_roles;

	        $current_user_role_obj = array();
	        
	        if (function_exists('wp_roles')) {
	            $roles = wp_roles();
	        } elseif(isset($wp_roles)) {
	            $roles = $wp_roles;
	        } else {
	            $roles = new WP_Roles();
	        }

	        if( !empty( $roles->roles ) ) {
	        	$current_user_role_obj = ( !empty( $roles->roles[$role] ) ) ? $roles->roles[$role] : array();
	        }
	        
	        return $current_user_role_obj;
	    }

	    public static function is_dashboard_valid( $role, $dashboard ) {

	    	$singular_cap = array('edit_', 'read_', 'delete_');
        	$plural_cap = array('edit_','edit_others_','publish_','read_private_','delete_','delete_private_','delete_published_','delete_others_','edit_private_','edit_published_');

        	$current_user_role_obj = self::getRoles( $role );
	        $current_user_role_caps = ( !empty( $current_user_role_obj['capabilities'] ) ) ? $current_user_role_obj['capabilities'] : array();

        	$valid = array( 'custom_cap_isset' => false,
        					'dashboard_valid' => false );

        	if( $dashboard != 'post' && $dashboard != 'page' ) {
        		foreach( $singular_cap as $singular ) {

	        		$cap = $singular.''.$dashboard;

	        		if( isset( $current_user_role_caps[$cap] ) ) {

	        			$valid['custom_cap_isset'] = true;
	        			$valid['dashboard_valid'] = true;

	        			if( empty( $current_user_role_caps[$cap] ) ) {
	        				$valid['dashboard_valid'] = false;
	        				break;
	        			}
	        		}
	        	}
        	}

        	foreach( $plural_cap as $plural ) {

        		$cap = $plural.''.$dashboard.'s';

        		if( isset( $current_user_role_caps[$cap] ) ) {

        			$valid['custom_cap_isset'] = true;
	        		$valid['dashboard_valid'] = true;

        			if( empty( $current_user_role_caps[$cap] ) ) {
        				$valid['dashboard_valid'] = false;
        				break;
        			}
        		}
        	}

        	return $valid;
	    }

		public function sm_beta_get_accessible_dashboards( $dashboards ) {

			global $current_user;

			if (!function_exists('wp_get_current_user')) {
				require_once (ABSPATH . 'wp-includes/pluggable.php'); // Sometimes conflict with SB-Welcome Email Editor
			}

			$current_user = wp_get_current_user(); // Sometimes conflict with SB-Welcome Email Editor

	        if ( !isset( $current_user->roles[0] ) ) {
	            $roles = array_values( $current_user->roles );
	        } else {
	            $roles = $current_user->roles;
	        }

	        //Fix for the client
			if ( !empty( $current_user->caps ) ) {
	        	$caps = array_keys($current_user->caps);
	        	$current_user_caps = $roles[0] = (!empty($caps)) ? $caps[0] : '';
	        }

	        if( !( ( !empty( $current_user->roles[0] ) && $current_user->roles[0] == 'administrator' ) || (!empty($current_user_caps) && $current_user_caps == 'administrator') ) ) {

	        	$role = ( !empty( $roles[0] ) ) ? $roles[0] : $current_user_caps;

	        	foreach( $dashboards as $key => $dashboard ) {

	        		if( $key != 'user' ) {
	        			$custom_cap = self::is_dashboard_valid( $role, $key );

			        	if( empty( $custom_cap['custom_cap_isset'] ) ) {
							$custom_cap = self::is_dashboard_valid( $role, 'post' );
			        	}

			        	if( empty( $custom_cap['dashboard_valid'] ) ) {
			        		unset( $dashboards[$key] );
			        	}

	        		} else {
	        			if( !current_user_can('edit_users') ) {
	        				unset( $dashboards[$key] );
	        			}
	        		}
	        		
	        	}

	        }


	        if( empty($dashboards) && !defined('SM_BETA_ACCESS') ){
	        	define('SM_BETA_ACCESS', false);
	        } else if( !empty($dashboards) && !defined('SM_BETA_ACCESS') ){
	        	define('SM_BETA_ACCESS', true);
	        }

			return $dashboards;

		}

	}

}

$GLOBALS['smart_manager_pro_access_privilege'] = Smart_Manager_Pro_Access_Privilege::instance();
