<?php

if ( !defined( 'ABSPATH' ) ) exit;

global $sm_text_domain;

$sm_text_domain = ( defined('SM_TEXT_DOMAIN') ) ? SM_TEXT_DOMAIN : 'smart-manager-for-wp-e-commerce';

function smart_settings_page() {
	global $sm_download_url, $wpdb, $sm_text_domain;

	$is_pro_updated = smart_is_pro_updated ();	
	$sm_license_key    = smart_get_license_key();
	if (isset ( $_POST ['submit'] )) {
		$latest_version 	= smart_get_latest_version ();
		$sm_license_key		= $wpdb->_real_escape( trim( $_POST['license_key'] ) );
		$sm_post_url 	    = STORE_APPS_URL . 'wp-admin/admin-ajax.php?action=woocommerce_validate_serial_key&serial=' . urlencode($sm_license_key) . '&sku=sm';
		$sm_response_result = smart_get_sm_response ( $sm_post_url );
		if ($sm_license_key != '') {
			if ($sm_response_result->is_valid) {
				if ( is_multisite() ) {
					$delete_query = "DELETE FROM $wpdb->sitemeta WHERE meta_key = 'sm_license_key'";
					$wpdb->query ( $delete_query );
					$query  = "REPLACE INTO $wpdb->sitemeta (`meta_key`,`meta_value`) VALUES('sm_license_key','$sm_license_key')";
				} else {
					$query  = "REPLACE INTO `{$wpdb->prefix}options`(`option_name`,`option_value`) VALUES('sm_license_key','$sm_license_key')";
				}
				$result = $wpdb->query ( $query );
				$msg  = __('Your key is valid. Automatic Upgrades and support are now activated.',$sm_text_domain);
				smart_display_notice ( $msg );
			} else {
				smart_display_err ( $sm_response_result->msg );
			}
		} else {
			$msg = __('Please enter license key',$sm_text_domain);
			smart_display_err ( $msg );
		}
	}
	?>
</br>
<form method="post" action="">
<div class="wrap">
<div id="icon-smart-manager" class="icon32"><br/></div>
<h2><?php _e('Smart Manager Pro Settings',$sm_text_domain);?></h2>
<?php _e( "Your Smart Manager Pro license key is used to verify your support package, enable automatic updates and receive support.", $sm_text_domain ); ?> </div>
<br />
<?php _e('License key:',$sm_text_domain); ?> <input id="license_key" type="text" name="license_key" size="45"
	value="<?php echo $sm_license_key; ?>" /> <input class="button" type="submit" name="submit"
	value="<?php _e( 'Validate', $sm_text_domain ); ?>" /></form>
<div id="notification" name="notification"></div>
<?php
}

function smart_get_license_key() {
	global $wpdb;
	$key = '';

	if ( is_multisite() ) {
		$query = "SELECT meta_value FROM $wpdb->sitemeta WHERE meta_key = 'sm_license_key'";
	} else {
		$query = "SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'sm_license_key'";
	}
	$records = $wpdb->get_results ( $query, ARRAY_A );
	if ( count($records) == 1 ) {
		$key = is_multisite() ? $records [0] ['meta_value'] : $records [0] ['option_value'];
	}
	return $key;
}
