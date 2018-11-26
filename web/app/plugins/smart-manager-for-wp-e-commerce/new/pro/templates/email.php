<?php if ( ! defined( 'ABSPATH' ) ) { exit;
} ?>

<?php

$heading = str_replace('( ', '(', ucwords(str_replace('(', '( ', $records_str))) .' '.( ( $background_process_params['process_name'] == 'Duplicate Records' ) ? __('Duplicated', SM_TEXT_DOMAIN) : __('Updated', SM_TEXT_DOMAIN) );

if( !empty($batch_update_params['SM_IS_WOO30']) && $batch_update_params['SM_IS_WOO30'] == "true" ) {
	wc_get_template('emails/email-header.php', array( 'email_heading' => $heading ));	
} else {
	woocommerce_get_template('emails/email-header.php', array( 'email_heading' => $heading ));
}

add_filter( 'wp_mail_content_type','sm_beta_pro_batch_set_content_type' );

function sm_beta_pro_batch_set_content_type(){
    return "text/html";
}

?>
	<style type="text/css">
		.sm_code {
			padding: 3px 5px 2px;
			margin: 0 1px;
			background: rgba(0,0,0,.07);
		}
		#template_header {
			background-color: #7748AA !important;
			text-align: center !important;
		}
</style>
<?php

$msg_body = '<p>'. __('Hi there!', SM_TEXT_DOMAIN) .'</p>
			<p>'. __('Smart Manager successfully completed', SM_TEXT_DOMAIN) .' \''. $background_process_params['process_name'] .'\' process on <span class="sm_code">'. get_bloginfo() .'</span>. </p>';

			if( !empty( $actions ) ) {
				$msg_body .= '<p>'. __('Below are the lists of updates done.',SM_TEXT_DOMAIN) .'</p>
							<p> <table cellspacing="0" cellpadding="6" border="1" style="text-align:center;color:'.$email_text_color.' !important;margin-bottom: 25px;border: 1px solid #e5e5e5;">
							  <tr style="font-weight:bold;color:'.$email_heading_color.' !important;">
							    <th style="border: 1px solid #e5e5e5;">'. __('Field', SM_TEXT_DOMAIN) .'</th>
							    <th style="border: 1px solid #e5e5e5;">'. __('Action', SM_TEXT_DOMAIN) .'</th>
							    <th style="border: 1px solid #e5e5e5;">'. __('Value', SM_TEXT_DOMAIN) .'</th>
							    <th style="border: 1px solid #e5e5e5;">'. __('Records Updated', SM_TEXT_DOMAIN) .'</th>
							  </tr>';

				foreach ($actions as $action) {
				  	$msg_body .= '<tr style="font-size: 14px;">
								    <td style="border: 1px solid #e5e5e5;">'. (!empty($action['field_display_text']) ? $action['field_display_text'] : $action['col_nm']) .'</td>
								    <td style="border: 1px solid #e5e5e5;">'. (!empty($action['action_display_text']) ? $action['action_display_text'] : $action['action']) .'</td>
								    <td style="border: 1px solid #e5e5e5;">'. (!empty($action['value_display_text']) ? $action['value_display_text'] : $action['value']) .'</td>
								    <td style="border: 1px solid #e5e5e5;">'. $records_str .'</td>
								  </tr>';
				}

				$msg_body .= '</table> </p>';
			}
			
			$msg_body .= '<br/>
							<p>
							<div style="color:#9e9b9b;font-size:0.95em;text-align: center;"> <div> '. __('If you like', SM_TEXT_DOMAIN) .' <strong>'. __('Smart Manager', SM_TEXT_DOMAIN) .'</strong> '. __('please leave us a', SM_TEXT_DOMAIN) .' <a href="https://wordpress.org/support/view/plugin-reviews/smart-manager-for-wp-e-commerce?filter=5#postform" target="_blank" data-rated="Thanks :)">★★★★★</a> '.__('rating', SM_TEXT_DOMAIN).'.</div> <div>'. __('A huge thank you from StoreApps in advance!', SM_TEXT_DOMAIN) .'</div></div>';


echo $msg_body;

?>