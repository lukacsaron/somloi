<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
include_once LSCF_PLUGIN_PATH . '_models/main_model.php';

/**
 * Class PluginMainController The main Controller
 *
 * @category Controller
 * @package  LSCF
 * @author   PIXOLETTE
 * @license  http://www.pixollete.com
 * @link     http://www.pixollete.com
 **/
class LSCFexportImportController {
	/**
	 * Store the plugin general settings.
	 *
	 * @access public
	 * @var array
	 */
	public $plugin_settings;

	/**
	 * Store all registered Custom Fields Data
	 *
	 * @access public
	 * @var array
	 */
	public $custom_fields_data;


	/**
	 * The init the plugin main Model Class
	 *
	 * @access public
	 * @var Class
	 */
	public $main_model;

	/**
	 * Class constructor
	 *
	 * @access public
	 * @var Function
	 */
	public function __construct() {

		$this->main_model = new PluginMainModel();

		$this->custom_fields_data = PluginMainController::$custom_fields_data;
		$this->plugin_settings = PluginMainController::$plugin_settings;

	}

	/**
	 * Init submenu page Export/Import
	 *
	 * @access public
	 * @var array
	 */
	public function init() {

		$main_model = $this->main_model;
		$custom_posts_list = $main_model->fetch_posts_types_list();

		include LSCF_PLUGIN_PATH . '_views/backend/import-export.php';

	}

	/**
	 * Invoke the download of the spcified file
	 *
	 * @param string $filename the file.
	 * @param string $extension the file extension.
	 * @access public
	 * @var array
	 */
	public function invoke_download( $filename, $extension ) {

		if ( '' === $filename ) {
			return false;
		}
		if ( file_exists( $filename ) && is_readable( $filename ) && preg_match( '/\.' . $extension . '$/', $filename ) ) {
			preg_match( '/\/(((?!\/).)+?\.' . $extension . ')$/', $filename, $matches );
			header( 'Content-Type: application/' . $extension );
			header( 'Content-type: text/' . $extension );
			header( 'Content-Disposition: attachment; filename=' . $matches[1] );
			readfile( $filename );
		} else {
			return false;
		}

	}

	/**
	 * Export the Custom Fields to a json file
	 *
	 * @access public
	 * @var array
	 */
	public function export_custom_fields() {

		if ( ! isset( $_GET['pxact'] ) || ( 'export-cf' != $_GET['pxact'] ) ) {
			return;
		}

		if ( ! isset( $_POST['export-custom-fields'] ) ||
			! wp_verify_nonce( $_POST['export-custom-fields'], 'lscf-export-cfjson' ) ) {
				die();
		}

		if( ! isset( $_POST['custom-posts'] ) ) {
			wp_redirect( admin_url() . 'admin.php?page=lscf_export' );
			die();
		}

		$url = wp_nonce_url( admin_url() . 'admin.php?page=lscf_export&pxact=export-cf' ,'lscf-export-to-json' );

		if ( false === ($creds = request_filesystem_credentials( $url, '', false, false, null ) ) ) {
			return;
		}
		if ( ! WP_Filesystem( $creds ) ) {
			request_filesystem_credentials( $url, '', true, false, null );
			return;
		}

		$export_data = array();
		$custom_posts_keys = $_POST['custom-posts'];

		$custom_fields = $this->custom_fields_data;

		if ( ! is_array( $custom_fields ) ) {
			wp_redirect( admin_url() . 'admin.php?page=lscf_export&pxerror=1' );
			die();
		}

		foreach ( $custom_posts_keys as $custom_posts_key ) {

			$key = sanitize_text_field( wp_unslash( $custom_posts_key ) );

			if ( isset( $custom_fields[ $key ] ) ) {
				$export_data[ $key ] = $custom_fields[ $key ];
			}
		}

		if ( ! is_array( $export_data ) ) {
			wp_redirect( admin_url() . 'admin.php?page=lscf_export&pxerror=1' );
			die();
		}

		$upload_dir = wp_upload_dir();
		$filename = $upload_dir['path'] . '/custom-fields-export.json';
		$export_data = wp_json_encode( $export_data );

		global $wp_filesystem;

		if ( ! $wp_filesystem->put_contents( $filename, $export_data, FS_CHMOD_FILE ) ) {
			echo 'Error saving the file';
		} else {
			$this->invoke_download( $filename, 'json' );
			exit;
		}

	}


	/**
	 * Exports all active custom posts to json file
	 *
	 * @access public
	 */
	public function export_custom_posts() {

		if ( ! isset( $_GET['pxact'] ) || ( 'export-cp' != $_GET['pxact'] ) ) {
			return;
		}

		$url = wp_nonce_url( admin_url() . 'admin.php?page=lscf_export&pxact=export-cp' ,'lscf-export-cp-to-json' );

		if ( false === ($creds = request_filesystem_credentials( $url, '', false, false, null ) ) ) {
			return;
		}
		if ( ! WP_Filesystem( $creds ) ) {
			request_filesystem_credentials( $url, '', true, false, null );
			return;
		}

		$export_data = array();

		if ( ! isset( $this->plugin_settings['generate_the_custom_posts_list'] ) ) {
			echo 'There are no any Custom Posts added by LS&CF';
			die();
		}

		$export_data = $this->plugin_settings['generate_the_custom_posts_list'];

		if ( ! is_array( $export_data ) ) {
			wp_redirect( admin_url() . 'admin.php?page=lscf_export&pxerror=1' );
			die();
		}

		$upload_dir = wp_upload_dir();
		$filename = $upload_dir['path'] . '/custom-posts-export.json';
		$export_data = wp_json_encode( $export_data );

		global $wp_filesystem;

		if ( ! $wp_filesystem->put_contents( $filename, $export_data, FS_CHMOD_FILE ) ) {
			echo 'Error saving the file';
		} else {
			$this->invoke_download( $filename, 'json' );
			exit;
		}
	}


	/**
	 * Import the active custom posts list
	 *
	 * @access public
	 */
	public function import_custom_posts() {

		if ( ! isset( $_GET['pxact'] ) ) {
			return;
		}

		if ( 'import-cp' !== $_GET['pxact'] ) {
			return;
		}
		if ( ! isset( $_FILES['import-json-custom-posts'] ) ) {
			die();
		}

		if ( preg_match( '/(.+?)\.json$/', $_FILES['import-json-custom-posts']['name'], $matches ) ) {

			$filename = $matches[0];

			$upload_dir = wp_upload_dir();
			$upload_path = trailingslashit( $upload_dir['path'] );

			if ( move_uploaded_file( $_FILES['import-json-custom-posts']['tmp_name'], $upload_path . 'lscf-import-custom-posts.json' ) ) {

				$filename = $upload_path . '/lscf-import-custom-posts.json';

				$file = fopen( $filename, 'r' );
				$data = fread( $file, filesize( $filename ) );
				fclose( $file );

				$json_data = json_decode( $data, true );

				foreach ( $json_data as $key => $post ) {

					if ( ! isset( $this->plugin_settings['generate_the_custom_posts_list'][ $key ] ) ) {
						$this->plugin_settings['generate_the_custom_posts_list'][ $key ] = $post;
					}
				}

				$model = $this->main_model;
				$model->plugin_settings = $this->plugin_settings;
				$model->update_plugin_settings();
				echo '<strong style="color:green">Import Completed</strong>';
				die();

			}
		}

	}
	/**
	 * Import the custom fields from json file
	 *
	 * @access public
	 */
	public function import_custom_fields() {

		if ( ! isset( $_GET['pxact'] ) ) {
			return;
		}

		if ( 'import-cf' !== $_GET['pxact'] ) {

			wp_redirect( admin_url() . 'admin.php?page=lscf_export' );
			die();
		}

		if ( ! isset( $_FILES['import-json-custom-fields'] ) ) {
			die();
		}

		if ( preg_match( '/(.+?)\.json$/', $_FILES['import-json-custom-fields']['name'], $matches ) ) {

			$filename = $matches[0];

			$upload_dir = wp_upload_dir();
			$upload_path = trailingslashit( $upload_dir['path'] );

			$url = wp_nonce_url( admin_url() . 'admin.php?page=lscf_export&pxact=import-cf' ,'lscf-import-to-json' );

			if ( false === ($creds = request_filesystem_credentials( $url, '', false, false, null ) ) ) {
				return;
			}
			if ( ! WP_Filesystem( $creds ) ) {
				request_filesystem_credentials( $url, '', true, false, null );
				return;
			}

			if ( move_uploaded_file( $_FILES['import-json-custom-fields']['tmp_name'], $upload_path . 'lscf-import.json' ) ) {

				$filename = $upload_path . '/lscf-import.json';

				$file = fopen( $filename, 'r' );
				$data = fread( $file, filesize( $filename ) );
				fclose( $file );

				$json_data = json_decode( $data, true );

				global $wp_filesystem;

				if ( is_array( $json_data ) && count( $json_data ) > 0 ) {

					foreach ( $json_data as $post_type => $custom_fields ) {


						foreach ( $custom_fields as $type => $cf ) {

							if ( 'px_icon_check_box' == $type ) {

								foreach ( $cf as $cf_key => $field ) {

									foreach ( $field['options'] as $index => $opt ) {
										$imagename = '';
										if ( ! isset( $opt['icon'] ) ) { continue; }

										preg_match( '/(((?!\/).)+?)\.(jpg|jpeg|gif|png)$/', $opt['icon'], $matches );
										$imagename = $matches[0];

										if ( $image = $wp_filesystem->get_contents( $opt['icon'] ) ) {
											if ( $wp_filesystem->put_contents( $upload_path . $imagename, $image ) ) {
												echo '<b style="color:green; font-size:11px;">' . esc_url( $upload_dir['url'] . '/' . $imagename ) . '</b><span style="color:green; font-size:11px;"> has downloaded</span><br/>';
												$custom_fields[ $type ] [ $cf_key ] ['options'][ $index ]['icon'] = $upload_dir['url'] . '/' . $imagename;

											} else {
												echo '<b style="color:red; font-size:11px;">' . $upload_dir['url'] . '/' . $imagename . '</b><span style="color:red; font-size:11px;"> error on download 	</span>';
												$custom_fields[ $type ] [ $cf_key ] ['options'][ $index ]['icon'] = '';
											}
										}
									}
								}
							} else {
								continue;
							}
						}

						$this->custom_fields_data[ $post_type ] = $custom_fields;
					}
				}

				$model = $this->main_model;
				$model->update_custom_fields_options( $this->custom_fields_data );
				echo '<br/><strong style="color:green">Import completed</strong>';
				die();

			}
		} else {
			echo 'Please upload only <b>json</b> files';
			die();
		}

		die();

	}

}
?>