<?php

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Smart_Manager_Pro_Shop_Order' ) ) {
	class Smart_Manager_Pro_Shop_Order extends Smart_Manager_Pro_Base {
		public $dashboard_key = '',
				$req_params = array();

		private $shop_order = '';

		function __construct($dashboard_key) {

			$this->req_params  	= (!empty($_REQUEST)) ? $_REQUEST : array();

			if ( class_exists( 'Smart_Manager_Shop_Order' ) ) {
				$this->shop_order = new Smart_Manager_Shop_Order( $dashboard_key );
			}

			parent::__construct($dashboard_key);
			
		}

		public function __call( $function_name, $arguments = array() ) {

			if( empty( $this->shop_order ) ) {
				return;
			}

			if ( ! is_callable( array( $this->shop_order, $function_name ) ) ) {
				return;
			}

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( array( $this->shop_order, $function_name ), $arguments );
			} else {
				return call_user_func( array( $this->shop_order, $function_name ) );
			}
		}


	}

}