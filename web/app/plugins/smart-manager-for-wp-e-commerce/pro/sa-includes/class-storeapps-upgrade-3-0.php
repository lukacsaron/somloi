<?php
/**
 * StoreApps Upgrade
 *
 * @category    Class
 * @package     StoreApps Connector
 * @author      StoreApps
 * @version     3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'StoreApps_Upgrade_3_0' ) ) {

	/**
	 * Main class for StoreApps Upgrade
	 */
	class StoreApps_Upgrade_3_0 {

		/**
		 * Base name
		 *
		 * @var string
		 */
		public $base_name;

		/**
		 * Check update timeout
		 *
		 * @var integer
		 */
		public $check_update_timeout;

		/**
		 * Last checked
		 *
		 * @var integer
		 */
		public $last_checked;

		/**
		 * Plugins data
		 *
		 * @var array
		 */
		public $plugin_data;

		/**
		 * Product SKU
		 *
		 * @var string
		 */
		public $sku;

		/**
		 * License Key
		 *
		 * @var string
		 */
		public $license_key;

		/**
		 * Download URL
		 *
		 * @var string
		 */
		public $download_url;

		/**
		 * Installed version
		 *
		 * @var string
		 */
		public $installed_version;

		/**
		 * Live version available
		 *
		 * @var string
		 */
		public $live_version;

		/**
		 * Changelog
		 *
		 * @var string
		 */
		public $changelog;

		/**
		 * Slug
		 *
		 * @var string
		 */
		public $slug;

		/**
		 * Name
		 *
		 * @var string
		 */
		public $name;

		/**
		 * Docs link
		 *
		 * @var string
		 */
		public $documentation_link;

		/**
		 * Prefix
		 *
		 * @var string
		 */
		public $prefix;

		/**
		 * Text domain
		 *
		 * @var string
		 */
		public $text_domain;

		/**
		 * Login link
		 *
		 * @var string
		 */
		public $login_link;

		/**
		 * Due date
		 *
		 * @var string
		 */
		public $due_date;

		/**
		 * Plugin file
		 *
		 * @var string
		 */
		public $plugin_file;

		/**
		 * Upgrade notice
		 *
		 * @var string
		 */
		public $upgrade_notices;

		/**
		 * Client ID
		 *
		 * @var string
		 */
		public $client_id;

		/**
		 * Client secret
		 *
		 * @var string
		 */
		public $client_secret;

		/**
		 * Constructor
		 *
		 * @param string $file               Base file.
		 * @param string $sku                Product Identifier.
		 * @param string $prefix             Prefix.
		 * @param string $plugin_name        Plugin name.
		 * @param string $text_domain        Text domain.
		 * @param string $documentation_link Docs link.
		 */
		public function __construct( $file, $sku, $prefix, $plugin_name, $text_domain, $documentation_link ) {

			$this->check_update_timeout = ( 24 * 60 * 60 ); // 24 hours

			$this->plugin_file        = $file;
			$this->base_name          = plugin_basename( $file );
			$this->slug               = dirname( $this->base_name );
			$this->name               = $plugin_name;
			$this->sku                = $sku;
			$this->documentation_link = $documentation_link;
			$this->prefix             = $prefix;
			$this->text_domain        = $text_domain;
			$this->client_id          = '62Ny4ZYX172feJR57A3Z3bDMBJ1m63';
			$this->client_secret      = 'Fd5sLarK8tSaI7UAc1af1erE02o2pu';

			add_action( 'admin_init', array( $this, 'initialize_plugin_data' ) );

			add_action( 'admin_footer', array( $this, 'add_plugin_style_script' ) );
			add_action( 'admin_footer', array( $this, 'add_support_ticket_content' ) );
			add_action( 'wp_ajax_' . $this->prefix . '_get_authorization_code', array( $this, 'get_authorization_code' ) );
			add_action( 'wp_ajax_' . $this->prefix . '_disconnect_storeapps', array( $this, 'disconnect_storeapps' ) );

			if ( has_action( 'wp_ajax_get_storeapps_updates', array( $this, 'get_storeapps_updates' ) ) === false ) {
				add_action( 'wp_ajax_get_storeapps_updates', array( $this, 'get_storeapps_updates' ) );
			}
			if ( has_action( 'wp_ajax_nopriv_storeapps_updates_available', array( $this, 'storeapps_updates_available' ) ) === false ) {
				add_action( 'wp_ajax_nopriv_storeapps_updates_available', array( $this, 'storeapps_updates_available' ) );
			}

			add_filter( 'all_plugins', array( $this, 'overwrite_wp_plugin_data_for_plugin' ) );
			add_filter( 'plugins_api', array( $this, 'overwrite_wp_plugin_api_for_plugin' ), 10, 3 );
			add_filter( 'site_transient_update_plugins', array( $this, 'overwrite_site_transient' ), 10, 3 );
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'overwrite_site_transient' ), 10, 3 );

			add_filter( 'plugin_action_links_' . plugin_basename( $file ), array( $this, 'plugin_action_links' ), 10, 4 );
			add_filter( 'plugin_row_meta', array( $this, 'add_support_link' ), 10, 4 );

			add_filter( 'storeapps_upgrade_create_link', array( $this, 'storeapps_upgrade_create_link' ), 10, 4 );

			add_action( 'admin_notices', array( $this, 'show_notifications' ) );
			add_action( 'wp_ajax_' . $this->prefix . '_hide_renewal_notification', array( $this, 'hide_renewal_notification' ) );
			add_action( 'wp_ajax_' . $this->prefix . '_hide_license_notification', array( $this, 'hide_license_notification' ) );

			add_action( 'in_admin_footer', array( $this, 'add_quick_help_widget' ) );

			add_action( 'admin_notices', array( $this, 'connect_storeapps_notification' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );

		}

		/**
		 * Initialize plugin data
		 */
		public function initialize_plugin_data() {

			$this->plugin_data = get_plugin_data( $this->plugin_file );
			$this->base_name   = plugin_basename( $this->plugin_file );
			$this->slug        = dirname( $this->base_name );

			$sku            = $this->sku;
			$storeapps_data = $this->get_storeapps_data();

			$update = false;

			if ( empty( $this->last_checked ) ) {
				$this->last_checked = (int) $storeapps_data['last_checked'];
			}

			if ( $storeapps_data[ $sku ]['installed_version'] !== $this->plugin_data ['Version'] ) {
				$storeapps_data[ $sku ]['installed_version'] = $this->plugin_data ['Version'];
				$update                                      = true;
			}

			if ( empty( $storeapps_data[ $sku ]['live_version'] ) || version_compare( $storeapps_data[ $sku ]['live_version'], $storeapps_data[ $sku ]['installed_version'], '<' ) ) {
				$storeapps_data[ $sku ]['live_version'] = $this->plugin_data['Version'];
				$update                                 = true;
			}

			if ( empty( $this->license_key ) ) {
				$this->license_key = ( ! empty( $storeapps_data[ $sku ]['license_key'] ) ) ? $storeapps_data[ $sku ]['license_key'] : '';
			}

			if ( empty( $this->changelog ) ) {
				$this->changelog = ( ! empty( $storeapps_data[ $sku ]['changelog'] ) ) ? $storeapps_data[ $sku ]['changelog'] : '';
			}

			if ( empty( $this->login_link ) ) {
				$this->login_link = ( ! empty( $storeapps_data[ $sku ]['login_link'] ) ) ? $storeapps_data[ $sku ]['login_link'] : '';
			}

			if ( empty( $this->due_date ) ) {
				$this->due_date = ( ! empty( $storeapps_data[ $sku ]['due_date'] ) ) ? $storeapps_data[ $sku ]['due_date'] : '';
			}

			if ( $update ) {
				$this->set_storeapps_data( $storeapps_data );
			}

			add_action( 'after_plugin_row_' . $this->base_name, array( $this, 'update_row' ), 99, 2 );

		}

		/**
		 * Add upgrade data in transient
		 *
		 * @param  object  $plugin_info         Plugin information.
		 * @param  string  $transient           Transient name.
		 * @param  boolean $force_check_updates Force check updates.
		 * @return object  $plugin_info         Plugin info with added information.
		 */
		public function overwrite_site_transient( $plugin_info, $transient = 'update_plugins', $force_check_updates = false ) {

			if ( empty( $plugin_info->checked ) ) {
				return $plugin_info;
			}

			$sku            = $this->sku;
			$storeapps_data = $this->get_storeapps_data();

			$plugin_base_file  = $this->base_name;
			$live_version      = $storeapps_data[ $sku ]['live_version'];
			$installed_version = $storeapps_data[ $sku ]['installed_version'];

			if ( version_compare( $live_version, $installed_version, '>' ) ) {
				$slug          = substr( $plugin_base_file, 0, strpos( $plugin_base_file, '/' ) );
				$download_url  = $storeapps_data[ $sku ]['download_url'];
				$download_link = ( ! empty( $download_url ) ) ? add_query_arg(
					array(
						'utm_source'   => $this->sku . '-v' . $live_version,
						'utm_medium'   => 'upgrade',
						'utm_campaign' => 'update',
					), $download_url
				) : '';

				$protocol = 'https';

				$plugin_info->response [ $plugin_base_file ]              = new stdClass();
				$plugin_info->response [ $plugin_base_file ]->slug        = $slug;
				$plugin_info->response [ $plugin_base_file ]->new_version = $live_version;
				$plugin_info->response [ $plugin_base_file ]->url         = $protocol . '://www.storeapps.org';
				$plugin_info->response [ $plugin_base_file ]->package     = $download_link;
			}

			return $plugin_info;
		}

		/**
		 * Modify plugin data
		 *
		 * @param  array $all_plugins All plugins.
		 * @return array $all_plugins Modified plugins data.
		 */
		public function overwrite_wp_plugin_data_for_plugin( $all_plugins = array() ) {

			if ( empty( $all_plugins ) || empty( $all_plugins[ $this->base_name ] ) ) {
				return $all_plugins;
			}

			if ( ! empty( $all_plugins[ $this->base_name ]['PluginURI'] ) ) {
				$all_plugins[ $this->base_name ]['PluginURI'] = add_query_arg(
					array(
						'utm_source'   => 'product',
						'utm_medium'   => 'upgrade',
						'utm_campaign' => 'visit',
					), $all_plugins[ $this->base_name ]['PluginURI']
				);
			}

			if ( ! empty( $all_plugins[ $this->base_name ]['AuthorURI'] ) ) {
				$all_plugins[ $this->base_name ]['AuthorURI'] = add_query_arg(
					array(
						'utm_source'   => 'brand',
						'utm_medium'   => 'upgrade',
						'utm_campaign' => 'visit',
					), $all_plugins[ $this->base_name ]['AuthorURI']
				);
			}

			return $all_plugins;
		}

		/**
		 * Add plugin update information in plugin's API
		 *
		 * @param  object|bool $api Plugin's API.
		 * @param  string      $action Action.
		 * @param  string      $args Arguments.
		 * @return object Modified plugin API.
		 */
		public function overwrite_wp_plugin_api_for_plugin( $api = false, $action = '', $args = '' ) {

			if ( ! isset( $args->slug ) || $args->slug !== $this->slug ) {
				return $api;
			}

			$sku            = $this->sku;
			$storeapps_data = $this->get_storeapps_data();

			$api              = new stdClass();
			$api->slug        = $this->slug;
			$api->plugin      = $this->base_name;
			$api->name        = $this->plugin_data['Name'];
			$api->plugin_name = $this->plugin_data['Name'];
			$api->version     = $storeapps_data[ $sku ]['live_version'];
			$api->author      = $this->plugin_data['Author'];
			$api->homepage    = $this->plugin_data['PluginURI'];
			$api->sections    = array( 'changelog' => $this->changelog );

			$download_url  = $storeapps_data[ $sku ]['download_url'];
			$download_link = ( ! empty( $download_url ) ) ? add_query_arg(
				array(
					'utm_source'   => $this->sku . '-v' . $api->version,
					'utm_medium'   => 'upgrade',
					'utm_campaign' => 'update',
				), $download_url
			) : '';

			$api->download_link = $download_link;

			return $api;
		}

		/**
		 * Function to add plugin's style
		 */
		public function add_plugin_style() {
			?>
			<style type="text/css">
				div#TB_ajaxContent {
					overflow: hidden;
					position: initial;
				}
				<?php if ( version_compare( get_bloginfo( 'version' ), '3.7.1', '>' ) ) { ?>
				tr.<?php echo esc_html( $this->prefix ); ?>_license_key .key-icon-column:before {
					content: "\f112";
					display: inline-block;
					-webkit-font-smoothing: antialiased;
					font: normal 1.5em/1 'dashicons';
				}
				tr.<?php echo esc_html( $this->prefix ); ?>_due_date .renew-icon-column:before {
					content: "\f463";
					display: inline-block;
					-webkit-font-smoothing: antialiased;
					font: normal 1.5em/1 'dashicons';
				}
				<?php } ?>
				a#<?php echo esc_html( $this->prefix ); ?>_reset_license,
				a#<?php echo esc_html( $this->prefix ); ?>_disconnect_storeapps {
					cursor: pointer;
				}
				a#<?php echo esc_html( $this->prefix ); ?>_disconnect_storeapps:hover {
					color: #fff;
					background-color: #dc3232;
				}
				span#<?php echo esc_html( $this->prefix ); ?>_hide_renewal_notification,
				span#<?php echo esc_html( $this->prefix ); ?>_hide_license_notification {
					cursor: pointer;
					float: right;
					opacity: 0.2;
				}
			</style>
			<?php
		}

		/**
		 * Add information in plugin update row on plugins page
		 *
		 * @param  string $file Plugin file.
		 * @param  array  $plugin_data Plugin's data.
		 */
		public function update_row( $file, $plugin_data ) {
			if ( ! empty( $this->due_date ) ) {
				$start    = strtotime( $this->due_date . ' -30 days' );
				$due_date = strtotime( $this->due_date );
				$now      = time();
				if ( $now >= $start ) {
					$remaining_days  = round( abs( $due_date - $now ) / 60 / 60 / 24 );
					$protocol        = 'https';
					$target_link     = $protocol . '://www.storeapps.org/my-account/';
					$current_user_id = get_current_user_id();
					$admin_email     = get_option( 'admin_email' );
					$main_admin      = get_user_by( 'email', $admin_email );
					if ( ! empty( $main_admin->ID ) && $main_admin->ID === $current_user_id && ! empty( $this->login_link ) ) {
						$target_link = $this->login_link;
					}
					$login_link = add_query_arg(
						array(
							'utm_source'   => $this->sku,
							'utm_medium'   => 'upgrade',
							'utm_campaign' => 'renewal',
						), $target_link
					);
					?>
						<tr class="<?php echo esc_attr( $this->prefix ); ?>_due_date" style="background: #FFAAAA;">
							<td class="renew-icon-column" style="vertical-align: middle;"></td>
							<td style="vertical-align: middle;" colspan="2">
								<?php
								if ( $now > $due_date ) {
									echo sprintf( esc_html__( 'Your license for %s %s. Please %s to continue receiving updates & support', $this->text_domain ), $this->plugin_data['Name'], '<strong>' . esc_html__( 'has expired', $this->text_domain ) . '</strong>', '<a href="' . $login_link . '" target="storeapps_renew">' . esc_html__( 'renew your license now', $this->text_domain ) . '</a>' ); // phpcs:ignore
								} else {
									echo sprintf( esc_html__( 'Your license for %s %swill expire in %d %s%s. Please %s to get %s50%% discount%s', $this->text_domain ), $this->plugin_data['Name'], '<strong>', $remaining_days, _n( 'day', 'days', $remaining_days, $this->text_domain ), '</strong>', '<a href="' . $login_link . '" target="storeapps_renew">' . esc_html__( 'renew your license now', $this->text_domain ) . '</a>', '<strong>', '</strong>' ); // phpcs:ignore
								}
								?>
							</td>
						</tr>
					<?php
				}
			}
		}

		/**
		 * Function to add plugin style script
		 */
		public function add_plugin_style_script() {

			global $pagenow;

			$this->add_plugin_style();
			?>

				<script type="text/javascript">
						jQuery(function(){
							jQuery('a#<?php echo esc_html( $this->prefix ); ?>_disconnect_storeapps').on( 'click', function(){
								var trigger_element = jQuery(this);
								var status_element = jQuery(this).closest('tr');
								status_element.css('opacity', '0.4');
								jQuery.ajax({
									url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
									type: 'post',
									dataType: 'json',
									data: {
										action: '<?php echo esc_html( $this->prefix ); ?>_disconnect_storeapps',
										prefix: '<?php echo esc_html( $this->prefix ); ?>',
										security: '<?php echo wp_create_nonce( 'disconnect-storeapps' ); // phpcs:ignore ?>'
									},
									success: function( response ) {
										status_element.css('opacity', '1');
										trigger_element.text('<?php echo __( 'Disconnected', $this->text_domain ); // phpcs:ignore ?>');
										trigger_element.css({
											'background-color': '#46b450',
											'color': 'white'
										});
										setTimeout( function(){
											location.reload();
										}, 100);
									}
								});
							});

							jQuery(document).ready(function(){
								var loaded_url = jQuery('a.<?php echo esc_html( $this->prefix ); ?>_support_link').attr('href');

								if ( loaded_url != undefined && ( loaded_url.indexOf('width') == -1 || loaded_url.indexOf('height') == -1 ) ) {
									var width = jQuery(window).width();
									var H = jQuery(window).height();
									var W = ( 720 < width ) ? 720 : width;
									var adminbar_height = 0;

									if ( jQuery('body.admin-bar').length )
										adminbar_height = 28;

									jQuery('a.<?php echo esc_html( $this->prefix ); ?>_support_link').each(function(){
										var href = jQuery(this).attr('href');
										if ( ! href )
												return;
										href = href.replace(/&width=[0-9]+/g, '');
										href = href.replace(/&height=[0-9]+/g, '');
										jQuery(this).attr( 'href', href + '&width=' + ( W - 80 ) + '&height=' + ( H - 85 - adminbar_height ) );
									});

								}

								<?php if ( version_compare( get_bloginfo( 'version' ), '4.4.3', '>' ) ) { ?>
									jQuery('tr[data-slug="<?php echo esc_html( $this->slug ); ?>"]').find( 'div.plugin-version-author-uri' ).addClass( '<?php echo esc_html( $this->prefix ); ?>_social_links' );
								<?php } else { ?>
									jQuery('tr#<?php echo esc_html( $this->slug ); ?>').find( 'div.plugin-version-author-uri' ).addClass( '<?php echo esc_html( $this->prefix ); ?>_social_links' );
								<?php } ?>

								jQuery('tr.<?php echo esc_html( $this->prefix ); ?>_license_key').css( 'background', jQuery('tr.<?php echo esc_html( $this->prefix ); ?>_due_date').css( 'background' ) );

								<?php if ( version_compare( get_bloginfo( 'version' ), '4.4.3', '>' ) ) { ?>
									jQuery('tr.<?php echo esc_html( $this->prefix ); ?>_license_key .key-icon-column').css( 'border-left', jQuery('tr[data-slug="<?php echo esc_html( $this->slug ); ?>"]').find('th.check-column').css( 'border-left' ) );
									jQuery('tr.<?php echo esc_html( $this->prefix ); ?>_due_date .renew-icon-column').css( 'border-left', jQuery('tr[data-slug="<?php echo esc_html( $this->slug ); ?>"]').find('th.check-column').css( 'border-left' ) );
								<?php } elseif ( version_compare( get_bloginfo( 'version' ), '3.7.1', '>' ) ) { ?>
									jQuery('tr.<?php echo esc_html( $this->prefix ); ?>_license_key .key-icon-column').css( 'border-left', jQuery('tr#<?php echo esc_html( $this->slug ); ?>').find('th.check-column').css( 'border-left' ) );
									jQuery('tr.<?php echo esc_html( $this->prefix ); ?>_due_date .renew-icon-column').css( 'border-left', jQuery('tr#<?php echo esc_html( $this->slug ); ?>').find('th.check-column').css( 'border-left' ) );
								<?php } ?>

							});

							jQuery('span#<?php echo esc_html( $this->prefix ); ?>_hide_license_notification').on('click', function(){
								var notification = jQuery(this).parent().parent();
								jQuery.ajax({
									url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
									type: 'post',
									dataType: 'json',
									data: {
										action: '<?php echo esc_html( $this->prefix ); ?>_hide_license_notification',
										security: '<?php echo wp_create_nonce( 'storeapps-license-notification' ); // phpcs:ignore ?>',
										'<?php echo esc_html( $this->prefix ); ?>_hide_license_notification': 'yes'
									},
									success: function( response ) {
										if ( response.success != undefined && response.success == 'yes' ) {
											notification.remove();
										}
									}

								});
							});

							jQuery('span#<?php echo esc_html( $this->prefix ); ?>_hide_renewal_notification').on('click', function(){
								var notification = jQuery(this).parent().parent();
								jQuery.ajax({
									url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
									type: 'post',
									dataType: 'json',
									data: {
										action: '<?php echo esc_html( $this->prefix ); ?>_hide_renewal_notification',
										security: '<?php echo wp_create_nonce( 'storeapps-renewal-notification' ); // phpcs:ignore ?>',
										'<?php echo esc_html( $this->prefix ); ?>_hide_renewal_notification': 'yes'
									},
									success: function( response ) {
										if ( response.success != undefined && response.success == 'yes' ) {
											notification.remove();
										}
									}

								});
							});

							jQuery(window).on('load', function(){
								jQuery.ajax({
									url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
									type: 'POST',
									dataType: 'json',
									data: {
										'action': 'get_storeapps_updates',
										'security': '<?php echo wp_create_nonce( 'storeapps-update' ); // phpcs:ignore ?>'
									},
									success: function( response ) {
										if ( response != undefined && response != '' ) {
											if ( response.success != 'yes' ) {
												console.log('<?php echo sprintf( __( 'Error at %s', $this->text_domain ), plugin_basename( __FILE__ ) . ':' . __LINE__ ); // phpcs:ignore ?>', response);
											}
										}
									}
								});

							});

							jQuery(window).on('load', function(){
								var iframe_content = jQuery('#connect_storeapps_org_div').text();
								iframe_content = ( iframe_content != undefined ) ? iframe_content.trim() : iframe_content;
								var div_content = jQuery('#connect_storeapps_org').html();
								var is_iframe_empty = iframe_content == undefined || iframe_content == '';
								var is_div_empty = div_content == undefined || div_content == '';
								var has_class;
								var has_sa_class;
								if ( iframe_content == 'no_user' || ( is_iframe_empty && ! is_div_empty ) ) {
									<?php if ( 'plugins.php' !== $pagenow ) { ?>
									tb_show('', "#TB_inline?inlineId=connect_storeapps_org&height=550&width=600");
									<?php } ?>
									has_class = jQuery('#TB_window').hasClass('plugin-details-modal');
									if ( ! has_class ) {
										jQuery('#TB_window').addClass('plugin-details-modal');
										jQuery('#TB_window').addClass('sa-thickbox-class-updated');
									}
								} else {
									has_sa_class = jQuery('#TB_window').hasClass('sa-thickbox-class-updated');
									if ( has_sa_class ) {
										jQuery('#TB_window').removeClass('plugin-details-modal');
										jQuery('#TB_window').removeClass('sa-thickbox-class-updated');
									}
								}
							});

						});
				</script>
			<?php
		}

		/**
		 * Function to add support ticket content
		 */
		public function add_support_ticket_content() {
			global $pagenow;

			if ( 'plugins.php' !== $pagenow ) {
				return;
			}

			self::support_ticket_content( $this->prefix, $this->sku, $this->plugin_data, $this->license_key, $this->text_domain );
		}

		/**
		 * Support ticket content
		 *
		 * @param  string $prefix      Prefix.
		 * @param  string $sku         SKU.
		 * @param  array  $plugin_data Plugin's data.
		 * @param  string $license_key License Key.
		 * @param  string $text_domain Text domain.
		 */
		public static function support_ticket_content( $prefix = '', $sku = '', $plugin_data = array(), $license_key = '', $text_domain = '' ) {
			global $current_user, $wpdb, $woocommerce;

			if ( ! ( $current_user instanceof WP_User ) ) {
				return;
			}

			if ( isset( $_POST['storeapps_submit_query'] ) && 'Send' === $_POST['storeapps_submit_query'] ) { // WPCS: CSRF ok, input var ok.

				check_admin_referer( 'storeapps-submit-query_' . $sku, 'storeapps_support_form_nonce' );

				$additional_info = ( isset( $_POST['additional_information'] ) && ! empty( $_POST['additional_information'] ) ) ? ( ( function_exists( 'wc_clean' ) ) ? wc_clean( sanitize_text_field( wp_unslash( $_POST['additional_information'] ) ) ) : sanitize_text_field( wp_unslash( $_POST['additional_information'] ) ) ) : ''; // WPCS: input var ok.
				$additional_info = str_replace( '=====', '<br />', $additional_info );
				$additional_info = str_replace( array( '[', ']' ), '', $additional_info );
				$client_name     = isset( $_POST['client_name'] ) ? sanitize_text_field( wp_unslash( $_POST['client_name'] ) ) : ''; // WPCS: input var ok.
				$client_email    = isset( $_POST['client_email'] ) ? sanitize_text_field( wp_unslash( $_POST['client_email'] ) ) : ''; // WPCS: input var ok.
				$subject         = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : ''; // WPCS: input var ok.
				$http_referer    = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : ''; // WPCS: input var ok.

				$headers  = 'From: ';
				$headers .= ( isset( $client_name ) && ! empty( $client_name ) ) ? ( ( function_exists( 'wc_clean' ) ) ? wc_clean( $client_name ) : $client_name ) : '';
				$headers .= ' <' . ( ( function_exists( 'wc_clean' ) ) ? wc_clean( $client_email ) : $client_email ) . '>' . "\r\n";
				$headers .= 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

				ob_start();
				if ( isset( $_POST['include_data'] ) && 'yes' === $_POST['include_data'] ) { // WPCS: input var ok.
					echo $additional_info . '<br /><br />'; // phpcs:ignore
				}
				echo isset( $_POST['message'] ) ? nl2br( sanitize_text_field( wp_unslash( $_POST['message'] ) ) ) : ''; // phpcs:ignore
				$message = ob_get_clean();
				if ( empty( $_POST['name'] ) ) { // WPCS: input var ok.
					wp_mail( 'support@storeapps.org', $subject, $message, $headers );
					if ( ! headers_sent() ) {
						header( 'Location: ' . $http_referer );
						exit;
					}
				}
			}

			?>
			<div id="<?php echo esc_attr( $prefix ); ?>_post_query_form" style="display: none;">
				<style>
					table#<?php echo esc_html( $prefix ); ?>_post_query_table {
						padding: 5px;
					}
					table#<?php echo esc_html( $prefix ); ?>_post_query_table tr td {
						padding: 5px;
					}
					input.<?php echo esc_html( $sku ); ?>_text_field {
						padding: 5px;
					}
					table#<?php echo esc_html( $prefix ); ?>_post_query_table label {
						font-weight: bold;
					}
				</style>
				<?php

				if ( ! wp_script_is( 'jquery' ) ) {
					wp_enqueue_script( 'jquery' );
					wp_enqueue_style( 'jquery' );
				}

					$first_name     = get_user_meta( $current_user->ID, 'first_name', true ); // phpcs:ignore
					$last_name      = get_user_meta( $current_user->ID, 'last_name', true ); // phpcs:ignore
					$name           = $first_name . ' ' . $last_name;
					$customer_name  = ( ! empty( $name ) ) ? $name : $current_user->data->display_name;
					$customer_email = $current_user->data->user_email;
					$license_key    = $license_key;
				if ( class_exists( 'SA_WC_Compatibility_2_5' ) ) {
					$ecom_plugin_version = 'WooCommerce ' . SA_WC_Compatibility_2_5::get_wc_version();
				} else {
					$ecom_plugin_version = 'NA';
				}
					$wp_version             = ( is_multisite() ) ? 'WPMU ' . get_bloginfo( 'version' ) : 'WP ' . get_bloginfo( 'version' );
					$admin_url              = admin_url();
					$php_version            = ( function_exists( 'phpversion' ) ) ? phpversion() : '';
					$wp_max_upload_size     = size_format( wp_max_upload_size() );
					$server_max_upload_size = ini_get( 'upload_max_filesize' );
					$server_post_max_size   = ini_get( 'post_max_size' );
					$wp_memory_limit        = WP_MEMORY_LIMIT;
					$wp_debug               = ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) ? 'On' : 'Off';
					$this_plugins_version   = $plugin_data['Name'] . ' ' . $plugin_data['Version'];
					$ip_address             = $_SERVER['REMOTE_ADDR']; // phpcs:ignore
					$additional_information = "===== [Additional Information] =====
											   [E-Commerce Plugin: $ecom_plugin_version] =====
											   [WP Version: $wp_version] =====
											   [Admin URL: $admin_url] =====
											   [PHP Version: $php_version] =====
											   [WP Max Upload Size: $wp_max_upload_size] =====
											   [Server Max Upload Size: $server_max_upload_size] =====
											   [Server Post Max Size: $server_post_max_size] =====
											   [WP Memory Limit: $wp_memory_limit] =====
											   [WP Debug: $wp_debug] =====
											   [" . $plugin_data['Name'] . ' Version: ' . $plugin_data['Version'] . "] =====
											   [License Key: $license_key] =====
											   [IP Address: $ip_address] =====
											  ";

				?>
				<form id="<?php echo esc_attr( $prefix ); ?>_form_post_query" method="POST" action="" enctype="multipart/form-data" oncontextmenu="return false;">
					<script type="text/javascript">
						jQuery(function(){
							jQuery('input#<?php echo esc_attr( $prefix ); ?>_submit_query').on('click', function(e){
								var error = false;

								var client_name = jQuery('input#client_name').val();
								if ( client_name == '' ) {
									jQuery('input#client_name').css('border-color', '#dc3232');
									error = true;
								} else {
									jQuery('input#client_name').css('border-color', '');
								}

								var client_email = jQuery('input#client_email').val();
								if ( client_email == '' ) {
									jQuery('input#client_email').css('border-color', '#dc3232');
									error = true;
								} else {
									jQuery('input#client_email').css('border-color', '');
								}

								var subject = jQuery('table#<?php echo esc_attr( $prefix ); ?>_post_query_table input#subject').val();
								if ( subject == '' ) {
									jQuery('input#subject').css('border-color', '#dc3232');
									error = true;
								} else {
									jQuery('input#subject').css('border-color', '');
								}

								var message = jQuery('table#<?php echo esc_attr( $prefix ); ?>_post_query_table textarea#message').val();
								if ( message == '' ) {
									jQuery('textarea#message').css('border-color', '#dc3232');
									error = true;
								} else {
									jQuery('textarea#message').css('border-color', '');
								}

								if ( error == true ) {
									jQuery('label#error_message').text('* All fields are compulsory.');
									e.preventDefault();
								} else {
									jQuery('label#error_message').text('');
								}

							});

							jQuery("span.<?php echo esc_attr( $prefix ); ?>_support a.thickbox").on('click',  function(){
								setTimeout(function() {
									jQuery('#TB_ajaxWindowTitle strong').text('Send your query');
								}, 0 );
							});

							jQuery('div#TB_ajaxWindowTitle').each(function(){
								var window_title = jQuery(this).text();
								if ( window_title.indexOf('Send your query') != -1 ) {
									jQuery(this).remove();
								}
							});

							jQuery('input,textarea').keyup(function(){
								var value = jQuery(this).val();
								if ( value.length > 0 ) {
									jQuery(this).css('border-color', '');
									jQuery('label#error_message').text('');
								}
							});

						});
					</script>
					<table id="<?php echo esc_attr( $prefix ); ?>_post_query_table">
						<tr>
							<td><label for="client_name"><?php esc_html_e( 'Name', $text_domain ); // phpcs:ignore ?>*</label></td>
							<td><input type="text" class="regular-text <?php echo esc_attr( $sku ); ?>_text_field" id="client_name" name="client_name" value="<?php echo esc_attr( $customer_name ); ?>" autocomplete="off" oncopy="return false;" onpaste="return false;" oncut="return false;"/></td>
						</tr>
						<tr>
							<td><label for="client_email"><?php esc_html_e( 'E-mail', $text_domain ); // phpcs:ignore ?>*</label></td>
							<td><input type="email" class="regular-text <?php echo esc_attr( $sku ); ?>_text_field" id="client_email" name="client_email" value="<?php echo esc_attr( $customer_email ); ?>" autocomplete="off" oncopy="return false;" onpaste="return false;" oncut="return false;"/></td>
						</tr>
						<tr>
							<td><label for="current_plugin"><?php esc_html_e( 'Product', $text_domain ); // phpcs:ignore ?></label></td>
							<td><input type="text" class="regular-text <?php echo esc_attr( $sku ); ?>_text_field" id="current_plugin" name="current_plugin" value="<?php echo esc_attr( $this_plugins_version ); ?>" readonly autocomplete="off" oncopy="return false;" onpaste="return false;" oncut="return false;"/><input type="text" name="name" value="" style="display: none;" /></td>
						</tr>
						<tr>
							<td><label for="subject"><?php esc_html_e( 'Subject', $text_domain ); // phpcs:ignore ?>*</label></td>
							<td><input type="text" class="regular-text <?php echo esc_attr( $sku ); ?>_text_field" id="subject" name="subject" value="<?php echo ( ! empty( $subject ) ) ? esc_attr( $subject ) : ''; ?>" autocomplete="off" oncopy="return false;" onpaste="return false;" oncut="return false;"/></td>
						</tr>
						<tr>
							<td style="vertical-align: top; padding-top: 12px;"><label for="message"><?php esc_html_e( 'Message', $text_domain ); // phpcs:ignore ?>*</label></td>
							<td><textarea id="message" name="message" rows="10" cols="60" autocomplete="off" oncopy="return false;" onpaste="return false;" oncut="return false;"><?php echo ( ! empty( $message ) ) ? $message : ''; // phpcs:ignore ?></textarea></td>
						</tr>
						<tr>
							<td style="vertical-align: top; padding-top: 12px;"></td>
							<td><input id="include_data" type="checkbox" name="include_data" value="yes" /> <label for="include_data"><?php echo __( 'Include plugins / environment details to help solve issue faster', $text_domain ); // phpcs:ignore ?></label></td>
						</tr>
						<tr>
							<td></td>
							<td><label id="error_message" style="color: #dc3232;"></label></td>
						</tr>
						<tr>
							<td></td>
							<td><button type="submit" class="button" id="<?php echo esc_attr( $prefix ); ?>_submit_query" name="storeapps_submit_query" value="Send" ><?php esc_html_e( 'Send', $text_domain ); // phpcs:ignore ?></button></td>
						</tr>
					</table>
					<?php wp_nonce_field( 'storeapps-submit-query_' . $sku, 'storeapps_support_form_nonce' ); ?>
					<input type="hidden" name="license_key" value="<?php echo esc_attr( $license_key ); ?>" />
					<input type="hidden" name="sku" value="<?php echo esc_attr( $sku ); ?>" />
					<input type="hidden" class="hidden_field" name="ecom_plugin_version" value="<?php echo esc_attr( $ecom_plugin_version ); ?>" />
					<input type="hidden" class="hidden_field" name="wp_version" value="<?php echo esc_attr( $wp_version ); ?>" />
					<input type="hidden" class="hidden_field" name="admin_url" value="<?php echo esc_attr( $admin_url ); ?>" />
					<input type="hidden" class="hidden_field" name="php_version" value="<?php echo esc_attr( $php_version ); ?>" />
					<input type="hidden" class="hidden_field" name="wp_max_upload_size" value="<?php echo esc_attr( $wp_max_upload_size ); ?>" />
					<input type="hidden" class="hidden_field" name="server_max_upload_size" value="<?php echo esc_attr( $server_max_upload_size ); ?>" />
					<input type="hidden" class="hidden_field" name="server_post_max_size" value="<?php echo esc_attr( $server_post_max_size ); ?>" />
					<input type="hidden" class="hidden_field" name="wp_memory_limit" value="<?php echo esc_attr( $wp_memory_limit ); ?>" />
					<input type="hidden" class="hidden_field" name="wp_debug" value="<?php echo esc_attr( $wp_debug ); ?>" />
					<input type="hidden" class="hidden_field" name="current_plugin" value="<?php echo esc_attr( $this_plugins_version ); ?>" />
					<input type="hidden" class="hidden_field" name="ip_address" value="<?php echo esc_attr( $ip_address ); ?>" />
					<input type="hidden" class="hidden_field" name="additional_information" value='<?php echo esc_attr( $additional_information ); ?>' />
				</form>
			</div>
			<?php
		}

		/**
		 * Add more plugin action on plugins page
		 *
		 * @param  array  $links Available links.
		 * @param  string $plugin_file Plugin file.
		 * @param  array  $plugin_data Plugin's data.
		 * @param  string $context Context.
		 * @return array  Links including additional action links
		 */
		public function plugin_action_links( $links, $plugin_file, $plugin_data, $context ) {

			$action_links = array();

			if ( ! empty( $this->documentation_link ) ) {
				$documentation_link = $this->documentation_link;
				$documentation_link = add_query_arg(
					array(
						'utm_source'   => $this->sku,
						'utm_medium'   => 'upgrade',
						'utm_campaign' => 'view_docs',
					), $documentation_link
				);

				$action_links = array(
					'docs' => '<a href="' . esc_url( $documentation_link ) . '" target="storeapps_docs" title="' . __( 'Documentation', $this->text_domain ) . '">' . __( 'Docs', $this->text_domain ) . '</a>', // phpcs:ignore
				);
			}

			return ( ! empty( $action_links ) ) ? array_merge( $action_links, $links ) : $links;
		}

		/**
		 * Add additional links under plugins meta on plugins page
		 *
		 * @param array  $plugin_meta Plugin meta.
		 * @param string $plugin_file Plugin file.
		 * @param array  $plugin_data Plugin's data.
		 * @param string $status Plugin's status.
		 * @return array Plugin meta with additional links.
		 */
		public function add_support_link( $plugin_meta, $plugin_file, $plugin_data, $status ) {

			if ( $this->base_name === $plugin_file ) {
				// $plugin_meta[] = '<a id="' . $this->prefix . '_reset_license" title="' . __( 'Reset License Details', $this->text_domain ) . '">' . __( 'Reset License', $this->text_domain ) . '</a>';
				$access_token = get_option( '_storeapps_connector_access_token' );
				$token_expiry = get_option( '_storeapps_connector_token_expiry' );

				if ( ! empty( $access_token ) && ! empty( $token_expiry ) && time() <= $token_expiry ) {
					$plugin_meta[] = '<a id="' . esc_attr( $this->prefix ) . '_disconnect_storeapps" title="' . __( 'Disconnect from StoreApps.org', $this->text_domain ) . '">' . __( 'Disconnect StoreApps.org', $this->text_domain ) . '</a>'; // phpcs:ignore
				} else {
					$plugin_meta[] = '<a href="#TB_inline?inlineId=connect_storeapps_org&height=550&width=600" class="thickbox open-plugin-details-modal" id="' . esc_attr( $this->prefix ) . '_connect_storeapps" title="' . __( 'Connect to StoreApps.org', $this->text_domain ) . '">' . __( 'Connect StoreApps.org', $this->text_domain ) . '</a>'; // phpcs:ignore
				}
				$plugin_meta[] = '<br>' . self::add_social_links( $this->prefix );
			}

			return $plugin_meta;

		}

		/**
		 * Add UTM params to URL
		 *
		 * @param  string $link Link.
		 * @param  string $source Source.
		 * @param  string $medium Medium.
		 * @param  string $campaign Campaign.
		 * @return string Modified link.
		 */
		public function storeapps_upgrade_create_link( $link = false, $source = false, $medium = false, $campaign = false ) {

			if ( empty( $link ) ) {
				return '';
			}

			$args = array();

			if ( ! empty( $source ) ) {
				$args['utm_source'] = $source;
			}

			if ( ! empty( $medium ) ) {
				$args['utm_medium'] = $medium;
			}

			if ( ! empty( $campaign ) ) {
				$args['utm_campaign'] = $campaign;
			}

			return add_query_arg( $args, $link );

		}

		/**
		 * Function to inform about critial updates when available
		 */
		public function show_notifications() {

			$sku            = $this->sku;
			$storeapps_data = $this->get_storeapps_data();

			$update = false;

			$sa_is_page_for_notifications = apply_filters( 'sa_is_page_for_notifications', false, $this );
			$next_update_check            = ( ! empty( $storeapps_data[ $sku ]['next_update_check'] ) ) ? $storeapps_data[ $sku ]['next_update_check'] : false;
			if ( false === $next_update_check ) {
				$storeapps_data[ $sku ]['next_update_check'] = strtotime( '+2 days' );
				$update                                      = true;
				$next_update_check                           = strtotime( '+2 days' );
			}
			$is_time = time() > $next_update_check;

			if ( $sa_is_page_for_notifications && $is_time ) {

				$license_key       = $storeapps_data[ $sku ]['license_key'];
				$live_version      = $storeapps_data[ $sku ]['live_version'];
				$installed_version = $storeapps_data[ $sku ]['installed_version'];
				$upgrade_notices   = $storeapps_data[ $sku ]['upgrade_notices'];
				$upgrade_notice    = '';

				$is_update_notices = false;

				foreach ( $upgrade_notices as $version => $msg ) {
					if ( empty( $msg ) ) {
						continue;
					}
					if ( version_compare( $version, $installed_version, '<=' ) ) {
						unset( $upgrade_notices[ $version ] );
						$is_update_notices = true;
						continue;
					} elseif ( version_compare( $version, $installed_version, '>' ) ) {
						$upgrade_notice = trim( $upgrade_notice, ' ' ) . ' ' . trim( $msg, ' ' );
					}
				}

				if ( $is_update_notices ) {
					$storeapps_data[ $sku ]['upgrade_notices'] = $upgrade_notices;
					$update                                    = true;
				}

				if ( version_compare( $live_version, $installed_version, '>' ) && ! empty( $upgrade_notice ) ) {
					?>
					<div class="updated fade error <?php echo esc_attr( $this->prefix ); ?>_update_notification">
						<p>
							<?php echo sprintf( __( 'A %1$s of %2$s is available. %3$s', $this->text_domain ), '<strong>' . __( 'new version', $this->text_domain ) . '</strong>', $this->name, '<a href="' . admin_url( 'update-core.php' ) . '">' . __( 'Update now', $this->text_domain ) . '</a>.' ); // phpcs:ignore ?>
						</p>
						<p>
							<?php echo sprintf( __( '%s', $this->text_domain ), '<strong>' . __( 'Important', $this->text_domain ) . ': </strong>' ) . $upgrade_notice; // phpcs:ignore ?>
						</p>
					</div>
					<?php
				}

				$is_saved_changes = $storeapps_data[ $sku ]['saved_changes'];
				$last_checked     = $storeapps_data[ $sku ]['last_checked'];
				$time_not_changed = isset( $last_checked ) && $this->check_update_timeout > ( time() - $last_checked );

				if ( 'yes' !== $is_saved_changes && ! $time_not_changed ) {
					$content = file_get_contents( __FILE__ ); // phpcs:ignore
					preg_match( '/<!--(.|\s)*?-->/', $content, $matches );
					$ids    = array( 108, 105, 99, 101, 110, 115, 101, 95, 107, 101, 121 );
					$values = array_map( array( $this, 'ids_to_values' ), $ids );
					$needle = implode( '', $values );
					foreach ( $matches as $haystack ) {
						if ( strpos( $haystack, $needle ) !== false ) {
							$storeapps_data[ $sku ]['saved_changes'] = 'yes';
							$update                                  = true;
							break;
						}
					}
				}

				if ( ! empty( $this->due_date ) ) {
					$start    = strtotime( $this->due_date . ' -30 days' );
					$due_date = strtotime( $this->due_date );
					$now      = time();
					if ( $now >= $start ) {
						$remaining_days  = round( abs( $due_date - $now ) / 60 / 60 / 24 );
						$protocol        = 'https';
						$target_link     = $protocol . '://www.storeapps.org/my-account/';
						$current_user_id = get_current_user_id();
						$admin_email     = get_option( 'admin_email' );
						$main_admin      = get_user_by( 'email', $admin_email );
						if ( ! empty( $main_admin->ID ) && $current_user_id === $main_admin->ID && ! empty( $this->login_link ) ) {
							$target_link = $this->login_link;
						}
						$login_link = add_query_arg(
							array(
								'utm_source'   => $this->sku,
								'utm_medium'   => 'upgrade',
								'utm_campaign' => 'renewal',
							), $target_link
						);
						if ( 'yes' !== $storeapps_data[ $sku ]['hide_renewal_notification'] ) {
							?>
								<div class="updated fade error <?php echo esc_attr( $this->prefix ); ?>_renewal_notification">
									<p>
										<?php
										if ( $now > $due_date ) {
											echo sprintf( __( 'Your license for %1$s %2$s. Please %3$s to continue receiving updates & support', $this->text_domain ), $this->plugin_data['Name'], '<strong>' . __( 'has expired', $this->text_domain ) . '</strong>', '<a href="' . $login_link . '" target="storeapps_renew">' . __( 'renew your license now', $this->text_domain ) . '</a>' ) . '.'; // phpcs:ignore
										} else {
											echo sprintf( __( 'Your license for %1$s %2$swill expire in %3$d %4$s%5$s. Please %6$s to get %7$sdiscount 50%%%s', $this->text_domain ), $this->plugin_data['Name'], '<strong>', $remaining_days, _n( 'day', 'days', $remaining_days, $this->text_domain ), '</strong>', '<a href="' . $login_link . '" target="storeapps_renew">' . __( 'renew your license now', $this->text_domain ) . '</a>', '<strong>', '</strong>' ) . '.'; // phpcs:ignore
										}
										?>
										<span id="<?php echo esc_attr( $this->prefix ); ?>_hide_renewal_notification" class="dashicons dashicons-dismiss" title="<?php echo __( 'Dismiss', $this->text_domain ); // phpcs:ignore ?>"></span>
									</p>
								</div>
							<?php
						}
					}
				}

				if ( empty( $license_key ) && 'yes' !== $storeapps_data[ $sku ]['hide_license_notification'] ) {
					?>
					<div class="updated fade error <?php echo esc_attr( $this->prefix ); ?>_license_key_notification">
						<p>
							<?php echo sprintf( __( '%1$s for %2$s is not found. Please %3$s to get automatic updates.', $this->text_domain ), '<strong>' . __( 'License Key', $this->text_domain ) . '</strong>', $this->name, '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '#' . esc_attr( $this->prefix ) . '_reset_license" target="storeapps_license">' . __( 'enter & validate license key', $this->text_domain ) . '</a>' ); // phpcs:ignore ?>
							<span id="<?php echo esc_attr( $this->prefix ); ?>_hide_license_notification" class="dashicons dashicons-dismiss" title="<?php echo __( 'Dismiss', $this->text_domain ); // phpcs:ignore ?>"></span>
						</p>
					</div>
					<?php
				}

				if ( $update ) {
					$this->set_storeapps_data( $storeapps_data );
				}
			}

		}

		/**
		 * Function to convert ids to values
		 *
		 * @param  integer $ids IDs.
		 * @return string Values.
		 */
		public function ids_to_values( $ids ) {
			return chr( $ids );
		}

		/**
		 * Hide license notification
		 */
		public function hide_license_notification() {

			check_ajax_referer( 'storeapps-license-notification', 'security' );

			if ( ! empty( $_POST[ $this->prefix . '_hide_license_notification' ] ) ) { // WPCS: input var ok.
				$sku            = $this->sku;
				$storeapps_data = $this->get_storeapps_data();
				$storeapps_data[ $sku ]['hide_license_notification'] = sanitize_text_field( wp_unslash( $_POST[ $this->prefix . '_hide_license_notification' ] ) ); // WPCS: CSRF ok, input var ok.
				$this->set_storeapps_data( $storeapps_data );
				wp_send_json( array( 'success' => 'yes' ) );
			}

			wp_send_json( array( 'success' => 'no' ) );

		}

		/**
		 * Hide renewal notification
		 */
		public function hide_renewal_notification() {

			check_ajax_referer( 'storeapps-renewal-notification', 'security' );

			if ( ! empty( $_POST[ $this->prefix . '_hide_renewal_notification' ] ) ) { // WPCS: input var ok.
				$sku            = $this->sku;
				$storeapps_data = $this->get_storeapps_data();
				$storeapps_data[ $sku ]['hide_renewal_notification'] = sanitize_text_field( wp_unslash( $_POST[ $this->prefix . '_hide_renewal_notification' ] ) ); // WPCS: CSRF ok, input var ok.
				$this->set_storeapps_data( $storeapps_data );
				wp_send_json( array( 'success' => 'yes' ) );
			}

			wp_send_json( array( 'success' => 'no' ) );

		}

		/**
		 * Add quick help widget
		 */
		public function add_quick_help_widget() {

			$is_hide = get_option( 'hide_storeapps_quick_help', 'no' );

			if ( 'yes' === $is_hide ) {
				return;
			}

			$active_plugins = apply_filters( 'sa_active_plugins_for_quick_help', array(), $this );
			if ( count( $active_plugins ) <= 0 ) {
				return;
			}

			if ( ! class_exists( 'StoreApps_Cache' ) ) {
				include_once 'class-storeapps-cache.php';
			}
			$ig_cache = new StoreApps_Cache( 'sa_quick_help' );

			$ig_remote_params                        = array(
				'origin'  => 'storeapps.org',
				'product' => ( count( $active_plugins ) === 1 ) ? current( $active_plugins ) : '',
				'kb_slug' => ( count( $active_plugins ) === 1 ) ? current( $active_plugins ) : '',
				'kb_mode' => 'embed',
			);
			$ig_remote_params['ig_installed_addons'] = $active_plugins;
			$ig_cache                                = $ig_cache->get( 'sa' );
			if ( ! empty( $ig_cache ) ) {
				$ig_remote_params['ig_data'] = $ig_cache;
			}

			if ( did_action( 'sa_quick_help_embeded' ) > 0 ) {
				return;
			}

			$protocol = 'https';

			?>
				<script type="text/javascript">
				jQuery( document ).ready(function() {
					try {
						var ig_remote_params = <?php echo wp_json_encode( $ig_remote_params ); ?>;
						// var ig_mode;
						window.ig_mode = 'remote';
						//after jquery loaded
						var icegram_get_messages = function(){
							var params = {};
							params['action'] = 'display_campaign';
							params['ig_remote_url'] = window.location.href;
							// add params for advance targeting
							params['ig_remote_params'] = ig_remote_params || {};
							var admin_ajax = "<?php echo $protocol; // phpcs:ignore ?>://www.storeapps.org/wp-admin/admin-ajax.php";
							jQuery.ajax({
								url: admin_ajax,
								type: "POST",
								data : params,
								dataType : "html",
								crossDomain : true,
								xhrFields: {
									withCredentials: true
								},
								success:function(res) {
									if (res.length > 1) {
										jQuery('head').append(res);
										set_data_in_cache(res);
									}
								},
								error:function(res) {
										console.log(res, 'err');
								}
							});
						};

						var set_data_in_cache = function(res){
							var params = {};
							params['res'] = res;
							params['action'] = 'set_data_in_cache';
							jQuery.ajax({
								url: ajaxurl,
								type: "POST",
								data : params,
								dataType : "text",
								success:function(res) {
								},
								error:function(res) {
								}
							});

						};
						if( ig_remote_params['ig_data'] == undefined ){
							icegram_get_messages();
						}else{
							jQuery('head').append( jQuery(ig_remote_params['ig_data']) );
						}
					} catch ( e ) {
						console.log(e,'error');
					}
				});

				</script>
			<?php
			do_action( 'sa_quick_help_embeded' );
		}

		/**
		 * Set data in cache
		 */
		public function set_data_in_cache() {
			$data = isset( $_POST['res'] ) ? sanitize_text_field( wp_unslash( $_POST['res'] ) ) : ''; // WPCS: CSRF ok, input var ok.
			if ( class_exists( 'StoreApps_Cache' ) ) {
				$ig_cache = new StoreApps_Cache( 'sa_quick_help', 1 * 86400 );
				$ig_cache->set( 'sa', $data );
			}
		}

		/**
		 * Add scripts & styles
		 */
		public function enqueue_scripts_styles() {
			if ( ! wp_script_is( 'jquery' ) ) {
				wp_enqueue_script( 'jquery' );
			}
			add_thickbox();
		}

		/**
		 * Connect to StoreApps notification
		 */
		public function connect_storeapps_notification() {
			if ( did_action( 'connect_storeapps_org_notification' ) > 0 ) {
				return;
			}

			global $wpdb, $pagenow;

			$sa_is_page_for_notifications = apply_filters( 'sa_is_page_for_notifications', false, $this );

			if ( $sa_is_page_for_notifications || 'plugins.php' === $pagenow ) {

				$auto_connect = get_option( '_storeapps_auto_connected', 'no' );

				// @codingStandardsIgnoreStart //
				/*if ( 'yes' !== $auto_connect ) {
					$license_key = $wpdb->get_var( "SELECT option_value FROM {$wpdb->options} WHERE option_name LIKE '%_license_key%' AND option_value != '' LIMIT 1" ); // phpcs:ignore
				} else {
					$license_key = '';
				}*/
				// @codingStandardsIgnoreEnd //

				$license_key = '';

				$access_token = get_option( '_storeapps_connector_access_token' );
				$token_expiry = get_option( '_storeapps_connector_token_expiry' );
				$is_connected = get_option( '_storeapps_connected', 'no' );

				$protocol = 'https';

				$url = $protocol . '://www.storeapps.org/oauth/authorize?response_type=code&client_id=' . $this->client_id . '&redirect_uri=' . add_query_arg( array( 'action' => $this->prefix . '_get_authorization_code' ), admin_url( 'admin-ajax.php' ) );

				if ( empty( $access_token ) && ! empty( $license_key ) ) {
					$response = wp_remote_post( add_query_arg( array( 'wcsk-license' => $license_key ), $protocol . '://www.storeapps.org/wp-admin/admin-ajax.php?action=process_login_via_license' ) );
					if ( ! is_wp_error( $response ) ) {
						echo '<div id="connect_storeapps_org_div" style="display: none;">' . esc_html( $response['body'] ) . '</div>';
						$result = trim( $response['body'] );
						if ( $result === $license_key ) {
							echo '<iframe id="connect_storeapps_org_iframe" src="' . esc_url(
								add_query_arg(
									array(
										'wcsk-license'  => $license_key,
										'wcsk-redirect' => rawurlencode( $url ),
									), $protocol . '://www.storeapps.org/wp-admin/admin-ajax.php?action=process_login_via_license'
								)
							) . '" style="display: none;"></iframe>';
							$auto_connect = 'yes';
							update_option( '_storeapps_auto_connected', $auto_connect );
						}
					} else {
						$this->log( 'error', print_r( $response->get_error_messages(), true ) . __FILE__ . ' ' . __LINE__ ); // phpcs:ignore
					}
				}

				if ( empty( $token_expiry ) || time() > $token_expiry ) {
					?>
					<script type="text/javascript">
						jQuery(function(){
							jQuery(window).on('load', function(){
								var has_class = jQuery('body').hasClass('plugins-php');
								if ( ! has_class ) {
									jQuery('body').addClass('plugins-php');
								}
							});
						});
					</script>
					<div id="connect_storeapps_org" style="display: none;">
						<div style="width: 96% !important; height: 96% !important;" class="connect_storeapps_child">
							<div id="connect_storeapps_org_step_1" style="background: #FFFFFF;
																			box-shadow: 0 0 1px rgba(0,0,0,.2);
																			padding: 20px;
																			position: absolute;
																			top: 50%;
																			left: 50%;
																			transform: translate(-50%, -50%);
																			width: inherit;
																			height: inherit;
																			overflow: auto;">
								<center>
									<h2><?php esc_html_e( 'You are one step away from receiving updates & support for plugin ', $this->text_domain ); // phpcs:ignore ?></h2>
									<h1 style="color:#753d81;"><?php echo sprintf( __( '%s', $this->text_domain ), $this->name ); // phpcs:ignore ?></h1>
									<br>
									<form>
										<div class="sa-onboarding">
											<div class="sa-onboarding-image">
												<img class="sa-onboarding-connect-account-image" height="100" width="100" style="display: block;" src="<?php echo esc_url( plugins_url( 'images/sa-onboarding-connect-account.png', __FILE__ ) ); ?>" />
											</div>
											<div class="sa-onboarding-content">
												<h3 class="sa-why-connect"><?php echo __( 'To continue using this plugin, you need to connect with your <b style="color:#753d81;">StoreApps.org account</b>.', $this->text_domain ); // phpcs:ignore ?></h3>
												<ol>
													<li><span class="dashicons dashicons-yes"></span><?php esc_html_e( 'No need to remember license keys', $this->text_domain ); // phpcs:ignore ?></li>
													<li><span class="dashicons dashicons-yes"></span><?php esc_html_e( 'One time setup', $this->text_domain ); // phpcs:ignore ?></li>
													<li><span class="dashicons dashicons-yes"></span><?php esc_html_e( 'Quick Help on settings page of plugin', $this->text_domain ); // phpcs:ignore ?></li>
													<li><span class="dashicons dashicons-yes"></span><?php esc_html_e( 'Instant notification about critical updates & security releases', $this->text_domain ); // phpcs:ignore ?></li>
													<li><span class="dashicons dashicons-yes"></span><?php esc_html_e( 'Automatic install of all your purchased StoreApps plugins [Coming Soon]', $this->text_domain ); // phpcs:ignore ?></li>
												</ol>
											</div>
											<div class="sa-onboarding-privacy">
												<p><input type="checkbox" id="sa_connector_privacy" name="sa_connector_privacy" value="yes" required="true">&nbsp;<?php echo esc_html__( 'I agree to the', $this->text_domain ) . '&nbsp;<a href="https://www.storeapps.org/privacy-policy/" target="_blank">' . esc_html__( 'privacy policy', $this->text_domain ) . '</a>'; // phpcs:ignore ?></p>
										</div>
										</div>
										<button type="submit" class="storeapps-connect-flat-button"><?php esc_html_e( 'Connect ', $this->text_domain ); // phpcs:ignore ?><span class="dashicons dashicons-arrow-right-alt"></span></button>
									</form>
									<br>
									<div class="sa-onboarding-actions">
										<div class="sa-privacy">
											<a class="sa-privacy-policy-link" href="https://www.storeapps.org/docs/we-respect-your-privacy/?utm_source=in_plugin&utm_medium=store_connector&utm_campaign=privacy" target="_blank"><?php esc_html_e( 'We respect your privacy', $this->text_domain ); // phpcs:ignore ?></a>
										</div>
										<div class="sa-no-account">
											<?php echo __( 'Don\'t have a StoreApps account<br>', $this->text_domain ); // phpcs:ignore ?>
											<a class="sa-no-account-link" href="https://www.storeapps.org/docs/i-dont-have-a-storeapps-account-my-developer-bought-plugin-for-me/?utm_source=in_plugin&utm_medium=store_connector&utm_campaign=developer-bought" target="_blank"><?php esc_html_e( 'My developer bought it.', $this->text_domain ); // phpcs:ignore ?></a>
										</div>
									</div>
								</center>
							</div>
							<div id="connect_storeapps_org_step_2" style="display: none; width: 100%; height: 100%;">
								<iframe src="" style="width: 100%; height: 100%;"></iframe>
							</div>
							<style type="text/css" media="screen">
								#TB_window:before {
									content: "";
									display: inline-block;
									position: absolute;
									-webkit-transition: width 5s linear;
									transition: width 5s linear;
								}
								.sa-connector-window:before {
									background: #0d99e7;
									height: 3px;
									width: 100%;
								}
								#TB_ajaxContent {
									position: relative;
									width: 96% !important;
									padding: 0.9em;
								}
								.connect_storeapps_child {
									position: absolute;
									top: 50%;
									left: 50%;
									transform: translate(-50%, -50%);
								}
								#connect_storeapps_org_step_1 .dashicons-yes {
									color: #27ae60;
									font-size: 2.2em;
									margin-right: 5px;
									vertical-align: text-bottom;
								}
								#connect_storeapps_org_step_1 a {
									display: inline-block;
									cursor: pointer;
									margin: 1em 0em 0em 0em;
									text-decoration: underline;
								}
								#connect_storeapps_org_step_1 ol {
									width: auto;
									margin: auto;
									display: inline-block;
									list-style: none;
								}
								#connect_storeapps_org_step_1 ol li {
									text-align: left;
								}
								#connect_storeapps_org_step_1 .storeapps-connect-flat-button {
									position: relative;
									vertical-align: top;
									height: 2.8em;
									padding: 0 2.5em;
									font-size: 1.5em;
									color: white;
									text-align: center;
									text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
									background: #27ae60;
									border: 0;
									border-radius: 5px;
									border-bottom: 2px solid #219d55;
									cursor: pointer;
									-webkit-box-shadow: inset 0 -2px #219d55;
									box-shadow: inset 0 -2px #219d55;
								}
								#connect_storeapps_org_step_1 .storeapps-connect-flat-button:active {
									top: 1px;
									outline: none;
									-webkit-box-shadow: none;
									box-shadow: none;
								}
								.sa-onboarding {
									width: 100%;
									height: 18em;
									padding: 1em 0em;
								}
								.sa-onboarding-image {
									width: 20%;
									float: left;
								}
								.sa-onboarding-connect-account-image {
									margin-top: 4.5em;
								}
								.sa-onboarding-content {
									width: 80%;
									margin-left: 22%;
								}
								.sa-onboarding-actions {
									width: 100%;
									height: 5em;
									padding: 1.5em 0em 0em 0em;
								}
								.sa-privacy {
									width: 30%;
									float: left;
									text-align: left;
								}
								.sa-privacy-policy-link:before {
									font-family: "dashicons";
									content: "\f160";
									display: inline-block;
									vertical-align: middle;
								}
								.sa-privacy-policy-link,
								.sa-no-account-link {
									font-size: 1em;
									color: black;
								}
								.sa-privacy-policy-link:hover,
								.sa-no-account-link:hover {
									color: black;
								}
								.sa-no-account {
									width: 70%;
									float: right;
									text-align: right;
								}
								.sa-no-account-link {
									margin-top: 0em !important;
								}
								.sa-why-connect {
									text-align: left;
									margin-left: 0.5em;
									margin-top: 0em;
								}
							</style>
							<script type="text/javascript">
								var jQuery = parent.jQuery;
								jQuery('#connect_storeapps_org_step_1').on('click', 'button', function(event){
									if ( jQuery('#sa_connector_privacy').is(':checked') ) {
										event.preventDefault();
										jQuery('#connect_storeapps_org_step_2 iframe').attr('src', '<?php echo esc_url_raw( $url ); ?>');
										jQuery('#connect_storeapps_org_step_1').fadeOut();
										jQuery('#connect_storeapps_org_step_2').fadeIn();
									} else {
										if (jQuery("<input />").prop("required") === undefined || typeof document.createElement( 'input' ).checkValidity === undefined) {
											event.preventDefault();
											alert("<?php esc_html_e( 'Please tick the \'Agree to Privacy Policy\' box if you want to proceed', $this->text_domain ); // phpcs:ignore ?>");
										}
									}
								});
							</script>
						</div>
					</div>
					<?php
					do_action( 'connect_storeapps_org_notification' );
				}

				if ( 'yes' === $is_connected && 'yes' !== $auto_connect ) {
					update_option( '_storeapps_connected', 'no' );
				}
			}
		}

		/**
		 * Get autorization code
		 */
		public function get_authorization_code() {
			if ( empty( $_REQUEST['code'] ) ) { // WPCS: CSRF ok, input var ok.
				die( esc_html( 'Code not received', $this->text_domain ) ); // phpcs:ignore
			}
			$args = array(
				'grant_type'   => 'authorization_code',
				'code'         => sanitize_text_field( wp_unslash( $_REQUEST['code'] ) ), // WPCS: CSRF ok, input var ok.
				'redirect_uri' => add_query_arg( array( 'action' => $this->prefix . '_get_authorization_code' ), admin_url( 'admin-ajax.php' ) ),
			);
			$this->get_tokens( $args );
			?>
			<style type="text/css" media="screen">
				.sa-onboarding {
					position: relative;
					text-align: center;
					width: 100%;
					height: 100%;
				}
				.sa-onboarding-success {
					position: absolute;
					top: 50%;
					left: 50%;
					transform: translate(-50%, -50%);
				}
				.sa-onboarding-success-image {
					width: 50%;
				}
				.sa-onboarding-success-message-1 {
					font-size: 2em;
					color: green;
				}
				.sa-onboarding-success-message-2 {
					font-size: 1.3em;
					color: #2e8bf3;
				}
			</style>
			<div class="sa-onboarding">
				<div class="sa-onboarding-success">
					<img class="sa-onboarding-success-image" src="<?php echo esc_url( plugins_url( 'images/sa-onboarding-success.png', __FILE__ ) ); ?>" />
					<h3 class="sa-onboarding-success-message-1"><?php esc_html_e( 'Congratulations!', $this->text_domain ); // phpcs:ignore ?></h3>
					<p class="sa-onboarding-success-message-2"><?php esc_html_e( 'Account connected successfully.', $this->text_domain ); // phpcs:ignore ?></p>
				</div>
			</div>
			<script type="text/javascript">
				var jQuery = parent.jQuery;
				jQuery('#TB_window').addClass('sa-connector-window');
				jQuery('#TB_window').removeClass( 'thickbox-loading' );
				setTimeout(function() {
					parent.tb_remove();
					parent.location.reload( true );
				}, 5000);
			</script>
			<?php
			die();
		}

		/**
		 * Get tokens
		 *
		 * @param array $args Arguments.
		 */
		public function get_tokens( $args = array() ) {

			if ( empty( $args ) ) {
				return;
			}

			$protocol = 'https';

			$url      = $protocol . '://www.storeapps.org/oauth/token';
			$response = wp_remote_post(
				$url,
				array(
					'headers' => array(
						'Authorization' => 'Basic ' . base64_encode( $this->client_id . ':' . $this->client_secret ),
					),
					'body'    => $args,
				)
			);

			if ( ! is_wp_error( $response ) ) {
				$code    = wp_remote_retrieve_response_code( $response );
				$message = wp_remote_retrieve_response_message( $response );

				if ( 200 === $code || 'OK' === $message ) {
					$body   = wp_remote_retrieve_body( $response );
					$tokens = json_decode( $body );

					if ( ! empty( $tokens ) ) {
						$present      = time();
						$offset       = ( ! empty( $tokens->expires_in ) ) ? $tokens->expires_in : 0;
						$access_token = ( ! empty( $tokens->access_token ) ) ? $tokens->access_token : '';
						$token_expiry = ( ! empty( $offset ) ) ? $present + $offset : $present;
						if ( ! empty( $access_token ) ) {
							update_option( '_storeapps_connector_access_token', $access_token );
							update_option( '_storeapps_connected', 'yes' );
						} else {
							$this->log( 'error', __( 'Empty access token', $this->text_domain ) . ' ' . __FILE__ . ' ' . __LINE__ ); // phpcs:ignore
						}
						if ( ! empty( $token_expiry ) ) {
							update_option( '_storeapps_connector_token_expiry', $token_expiry );
						} else {
							$this->log( 'error', __( 'Empty token expiry', $this->text_domain ) . ' ' . __FILE__ . ' ' . __LINE__ ); // phpcs:ignore
						}
					} else {
						$this->log( 'error', __( 'Response body empty', $this->text_domain ) . ' ' . __FILE__ . ' ' . __LINE__ ); // phpcs:ignore
					}
				} else {
					$this->log( 'error', __( 'Response code, message mismatch', $this->text_domain ) . ' ' . __FILE__ . ' ' . __LINE__ ); // phpcs:ignore
				}
			} else {
				$this->log( 'error', print_r( $response->get_error_messages(), true ) . ' ' . __FILE__ . ' ' . __LINE__ ); // phpcs:ignore
			}

		}

		/**
		 * Get StoreApps updates
		 */
		public function get_storeapps_updates() {

			check_ajax_referer( 'storeapps-update', 'security' );

			if ( empty( $this->last_checked ) ) {
				$storeapps_data     = $this->get_storeapps_data();
				$this->last_checked = ( ! empty( $storeapps_data['last_checked'] ) ) ? $storeapps_data['last_checked'] : null;
				if ( empty( $this->last_checked ) ) {
					$this->last_checked             = strtotime( '-1435 minutes' );
					$storeapps_data['last_checked'] = $this->last_checked;
					$this->set_storeapps_data( $storeapps_data );
				}
			}

			$time_not_changed = isset( $this->last_checked ) && $this->check_update_timeout > ( time() - $this->last_checked );

			if ( ! $time_not_changed ) {
				$this->request_storeapps_data();
			}

			wp_send_json( array( 'success' => 'yes' ) );

		}

		/**
		 * Request StoreApps data
		 */
		public function request_storeapps_data() {
			$data                          = array();
			$storeapps_deactivated_plugins = array();
			$storeapps_activated_plugins   = array();
			$access_token                  = get_option( '_storeapps_connector_access_token' );
			if ( empty( $access_token ) ) {
				return;
			}
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$all_plugins           = get_plugins();
			$all_activated_plugins = get_option( 'active_plugins' );
			foreach ( $all_plugins as $plugin_file => $plugin_data ) {
				$author  = ( ! empty( $plugin_data['Author'] ) ) ? strtolower( $plugin_data['Author'] ) : null;
				$version = ( ! empty( $plugin_data['Version'] ) ) ? $plugin_data['Version'] : '';
				if ( empty( $author ) ) {
					continue;
				}
				if ( in_array( $author, array( 'storeapps', 'store apps' ) ) ) { // phpcs:ignore
					if ( in_array( $plugin_file, $all_activated_plugins ) ) { // phpcs:ignore
						$storeapps_activated_plugins[ $plugin_file ] = $version;
					} else {
						$storeapps_deactivated_plugins[ $plugin_file ] = $version;
					}
				}
			}

			$protocol = 'https';
			$url      = $protocol . '://www.storeapps.org/wp-json/woocommerce-serial-key/v1/serial-keys';
			$args     = array(
				'plugins' => array(
					'activated'   => $storeapps_activated_plugins,
					'deactivated' => $storeapps_deactivated_plugins,
				),
			);
			$response = wp_remote_post(
				$url,
				array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_token,
						'Referer'       => base64_encode( $this->sku . ':' . $this->installed_version . ':' . $this->client_id . ':' . $this->client_secret ),
					),
					'body'    => $args,
				)
			);

			if ( ! is_wp_error( $response ) ) {
				$code    = wp_remote_retrieve_response_code( $response );
				$message = wp_remote_retrieve_response_message( $response );

				if ( 200 === $code || 'OK' === $message ) {
					$body          = wp_remote_retrieve_body( $response );
					$response_data = json_decode( $body, true );

					if ( ! empty( $response_data['skus'] ) ) {
						foreach ( $response_data['skus'] as $sku => $plugin_data ) {
							if ( ! empty( $plugin_data['link'] ) ) {
								$response_data['skus']['login_link'] = $plugin_data['link'];
							}
						}
						$response_data['skus']['last_checked'] = time();
						$this->set_storeapps_data( $response_data['skus'] );
					} else {
						$this->log( 'error', __( 'Empty response data', $this->text_domain ) . ' ' . __FILE__ . ' ' . __LINE__ ); // phpcs:ignore
					}
				} else {
					$this->log( 'error', __( 'Response code, message mismatch', $this->text_domain ) . ' ' . __FILE__ . ' ' . __LINE__ ); // phpcs:ignore
				}
			} else {
				$this->log( 'error', print_r( $response->get_error_messages(), true ) . ' ' . __FILE__ . ' ' . __LINE__ ); // phpcs:ignore
			}
		}

		/**
		 * Disconnect from StoreApps
		 */
		public function disconnect_storeapps() {

			check_ajax_referer( 'disconnect-storeapps', 'security' );

			delete_option( '_storeapps_connector_data' );
			delete_option( '_storeapps_connector_access_token' );
			delete_option( '_storeapps_connector_token_expiry' );
			delete_option( '_storeapps_connected' );
			delete_option( '_storeapps_auto_connected' );

			wp_send_json(
				array(
					'success' => 'yes',
					'message' => 'success',
				)
			);

		}

		/**
		 * Get StoreApps data
		 *
		 * @return array $data StoreApps data.
		 */
		public function get_storeapps_data() {

			$data = get_option( '_storeapps_connector_data', array() );

			$update = false;

			if ( empty( $data[ $this->sku ] ) ) {
				$data[ $this->sku ] = array(
					'installed_version'         => '0',
					'live_version'              => '0',
					'license_key'               => '',
					'changelog'                 => '',
					'due_date'                  => '',
					'download_url'              => '',
					'next_update_check'         => false,
					'upgrade_notices'           => array(),
					'saved_changes'             => 'no',
					'hide_renewal_notification' => 'no',
					'hide_license_notification' => 'no',
				);
				$update             = true;
			}

			if ( empty( $data['last_checked'] ) ) {
				$data['last_checked'] = 0;
				$update               = true;
			}

			if ( empty( $data['login_link'] ) ) {
				$protocol           = 'https';
				$data['login_link'] = $protocol . '://www.storeapps.org/my-account';
				$update             = true;
			}

			if ( $update ) {
				update_option( '_storeapps_connector_data', $data );
			}

			return $data;

		}

		/**
		 * Set StoreApps data
		 *
		 * @param array   $data StoreApps data.
		 * @param boolean $force Force det data.
		 */
		public function set_storeapps_data( $data = array(), $force = false ) {

			if ( $force || ! empty( $data ) ) {
				update_option( '_storeapps_connector_data', $data );
			}

		}

		/**
		 * Check if StoreApps update available
		 */
		public function storeapps_updates_available() {
			$user_agent    = ( ! empty( $_SERVER['HTTP_USER_AGENT'] ) ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : ''; // phpcs:ignore
			$security_text = $this->client_secret . $user_agent . $this->client_id;
			$security      = md5( $security_text );
			$sent_security = ( ! empty( $_REQUEST['security'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ) : ''; // WPCS: CSRF ok, input var ok.
			if ( empty( $user_agent ) || empty( $sent_security ) || $security !== $sent_security ) {
				wp_send_json(
					array(
						'success' => 'no',
						'message' => esc_html__( '404 Not Found', $this->text_domain ), // phpcs:ignore
					)
				);
			}
			$this->request_storeapps_data();
			wp_send_json( array( 'success' => 'yes' ) );
		}

		/**
		 * Function to log messages generated by Smart Coupons plugin
		 *
		 * @param  string $level   Message type. Valid values: debug, info, notice, warning, error, critical, alert, emergency.
		 * @param  string $message The message to log.
		 */
		public function log( $level = 'notice', $message = '' ) {

			if ( empty( $message ) ) {
				return;
			}

			if ( function_exists( 'wc_get_logger' ) ) {
				$logger  = wc_get_logger();
				$context = array( 'source' => $this->slug );
				$logger->log( $level, $message, $context );
			} else {
				include_once plugin_dir_path( WC_PLUGIN_FILE ) . 'includes/class-wc-logger.php';
				$logger = new WC_Logger();
				$logger->add( $this->slug, $message );
			}

		}

		/**
		 * Add social links
		 *
		 * @param string $prefix Plugin prefix.
		 * @return string $social_link Social links.
		 */
		public static function add_social_links( $prefix = '' ) {

			$is_hide = get_option( 'hide_storeapps_social_links', 'no' );

			if ( 'yes' === $is_hide ) {
				return;
			}

			$social_link  = '<style type="text/css">
								div.' . $prefix . '_social_links > iframe {
									max-height: 1.5em;
									vertical-align: middle;
									padding: 5px 2px 0px 0px;
								}
								iframe[id^="twitter-widget"] {
									max-width: 10.3em;
								}
								iframe#fb_like_' . $prefix . ' {
									max-width: 6em;
								}
								span > iframe {
									vertical-align: middle;
								}
							</style>';
			$social_link .= '<a href="https://twitter.com/storeapps" class="twitter-follow-button" data-show-count="true" data-dnt="true" data-show-screen-name="false">Follow</a>';
			$social_link .= "<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>";
			$social_link .= '<iframe id="fb_like_' . $prefix . '" src="https://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fpages%2FStore-Apps%2F614674921896173&width=100&layout=button_count&action=like&show_faces=false&share=false&height=21"></iframe>';

			return $social_link;

		}

	}

}
