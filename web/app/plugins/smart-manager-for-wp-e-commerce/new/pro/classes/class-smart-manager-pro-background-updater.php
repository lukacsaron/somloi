<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Async_Request', false ) ) {
	include_once( SM_PLUGIN_DIR_PATH . '/new/pro/libraries/wp-async-request.php' );
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
	include_once( SM_PLUGIN_DIR_PATH . '/new/pro/libraries/wp-background-process.php' );
}

/**
 * WC_Background_Emailer Class.
 */
class Smart_Manager_Pro_Background_Updater extends WP_Background_Process {

	/**
	 * @var string
	 */

	public static $_action = 'sm_beta_background_update';

	protected $action = '';

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Initiate new background process
	 */
	public function __construct() {
		$this->action = self::$_action;
		parent::__construct();
	}


	/**
	 * Schedule fallback event.
	 */
	protected function schedule_event() {
		if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
			wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
		}
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param array $callback Update callback function
	 * @return mixed
	 */
	protected function task( $callback ) {

		// sleep(2);
		if ( !empty($callback['filter']) && !empty($callback['args']) ) {
			try {
				include_once dirname( __FILE__ ) .'/class-smart-manager-pro-utils.php';
				include_once( SM_PLUGIN_DIR_PATH . '/new/classes/class-smart-manager-base.php' );
				include_once dirname( __FILE__ ) .'/class-smart-manager-pro-base.php';
				include_once dirname( __FILE__ ) .'/'. $callback['filter']['class_path'];

				if( !empty($callback['args']) && is_array($callback['args']) ) {
					foreach( $callback['args'] as $args ) {
						if( !empty($args['dashboard_key']) && file_exists(dirname( __FILE__ ) . '/class-smart-manager-pro-'.$args['dashboard_key'].'.php')) {
								include_once dirname( __FILE__ ) . '/class-smart-manager-pro-'.$args['dashboard_key'].'.php';
								$class_name = 'Smart_Manager_Pro_'.ucfirst($args['dashboard_key']);
								$obj = $class_name::instance($args['dashboard_key']);
							}
							call_user_func(array($callback['filter']['func'][0],'actions'), $args);
							call_user_func($callback['filter']['func'],$args);
					}	
				}
				// sleep(3);
			} catch ( Exception $e ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					trigger_error( 'Transactional email triggered fatal error for callback ' . $callback['filter'], E_USER_WARNING );
				}
			}
		}
		return false;
	}

	/**
	 * Save and run queue.
	 */
	public function dispatch_queue() {
		if ( ! empty( $this->data ) ) {
			$resp = $this->save()->dispatch();
		}
	}


	/**
	 * Is the updater running?
	 * @return boolean
	 */
	public function is_updating() {
		return false === $this->is_queue_empty();
	}

	/**
	 * Handle
	 *
	 * Pass each queue item to the task handler, while remaining
	 * within server memory and time limit constraints.
	 */
	protected function handle() {

		$this->lock_process();

		do {
			$batch = $this->get_batch();

			//for progressbar
			$tot_batch_count = get_site_option('wp_'.$this->action.'_tot', false);

			if( empty($tot_batch_count) ) {
				$tot_batch_count = count($batch->data);				
				update_site_option( 'wp_'.$this->action.'_tot', $tot_batch_count);
			}

			if ( empty( $batch->data ) ) {
				break;
			}

			foreach ( $batch->data as $key => $value ) {
				$task = $this->task( $value );

				if ( false !== $task ) {
					$batch->data[ $key ] = $task;
				} else {
					unset( $batch->data[ $key ] );
				}

				// Update batch before sending more to prevent duplicate email possibility.
				$this->update( $batch->key, $batch->data );

				$updated_batch_per = (($tot_batch_count - count($batch->data)) / $tot_batch_count) * 100;

				update_site_option('wp_'.$this->action.'_per', $updated_batch_per);

				if ( $this->time_exceeded() || $this->memory_exceeded() ) {
					// Batch limits reached.
					break;
				}
			}
			if ( empty( $batch->data ) ) {
				$this->delete( $batch->key );
			}
		} while ( ! $this->time_exceeded() && ! $this->memory_exceeded() && ! $this->is_queue_empty() );

		$this->unlock_process();

		// Start next batch or complete process.
		if ( ! $this->is_queue_empty() ) {
			$this->dispatch();
		} else {
			$this->complete();
		}
	}

	protected function complete() {
		Smart_Manager_Pro_Base::batch_process_complete();
	}
}
