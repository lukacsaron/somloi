<?php

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Smart_Manager_Pro_Post' ) ) {
	class Smart_Manager_Pro_Post extends Smart_Manager_Pro_Base {
		public $dashboard_key = '';

		private $post = '';

		function __construct($dashboard_key) {
			parent::__construct($dashboard_key);

			if ( class_exists( 'Smart_Manager_Post' ) ) {
				$this->post = new Smart_Manager_Post( $dashboard_key );
			}
		}

		public function __call( $function_name, $arguments = array() ) {

			if( empty( $this->post ) ) {
				return;
			}

			if ( ! is_callable( array( $this->post, $function_name ) ) ) {
				return;
			}

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( array( $this->post, $function_name ), $arguments );
			} else {
				return call_user_func( array( $this->post, $function_name ) );
			}
		}


	}

}