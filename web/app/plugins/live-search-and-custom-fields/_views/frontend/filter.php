<?php

$active_user = wp_get_current_user();

$is_administrator = false;

if ( in_array( 'administrator', $active_user->roles, true ) ) {
	$is_administrator = true;
}

$filter_settings = ( isset( $filter_data['settings'] ) ? $filter_data['settings'] : array() );

$sidebar_position = ( isset( $filter_settings['theme']['sidebar']['position'] ) ? $filter_settings['theme']['sidebar']['position'] : 'left' );

$view_changer = array();
$view_changer['grid'] = ( isset( $filter_settings['theme']['viewchanger']['grid'] ) ? (int) $filter_settings['theme']['viewchanger']['grid'] : 0 );
$view_changer['list'] = ( isset( $filter_settings['theme']['viewchanger']['list'] ) ? (int) $filter_settings['theme']['viewchanger']['list'] : 0 );

$container_class = lscf_return_viewchanger_class( $view_changer );


if ( isset( $filter_data['only_posts_show'] ) && '1' == $filter_data['only_posts_show'] ) {

	$sidebar_position = null;
	$filter_sidebar = 0;
	$post_container_class = 'col-xs-12 col-sm-12 col-md-12 col-lg-12 only-posts-show';

	$wrapper_class = 'wide';

} else {

	$filter_sidebar = 1;

	if ( isset( $filter_settings['theme']['columns'] )  && 4 === $filter_settings['theme']['columns'] && ( 'default' == $filter_settings['theme']['display'] || 'portrait' == $filter_settings['theme']['display']  ) ) {

		$wrapper_class = 'wide';
		$post_container_class = 'col-xs-12 col-sm-9 col-md-10 col-lg-10';
		$sidebar_class = 'col-xs-12 col-sm-3 col-md-2 col-lg-2 px-filter-fields';

	} else {

		$wrapper_class = '';
		$post_container_class = 'col-xs-12 col-sm-9 col-md-9 col-lg-9';
		$sidebar_class = 'col-xs-12 col-sm-3 col-md-3 col-lg-3 px-filter-fields';
	}
}

if ( true === $is_administrator ) {
	$wrapper_class .= ' lscf-admin-wrapper';
}

if ( 'filters_only' === $shortcode_attributes['display'] ) {
	$wrapper_class .= ' lscf-wrapper-independent-filters ';
}
$wrapper_class .= ' lscf-theme-' . $filter_settings['theme']['display'];
?>


<div 
	class="px-capf-wrapper row <?php echo esc_attr( $wrapper_class ); ?>" >


	<?php if ( 'filters_only' === $shortcode_attributes['display'] ): ?>
	
		<div 
			class="lscf-container lscf-filters-only" 
			data-index="<?php echo (int) ($lscf_main_shortcode_counter - 1);?>" 
			ng-controller="lscfFiltersOnlyController">

				<div
					class="col-xs-12 px-filter-fields col-md-12 col-lg-12 lscf-horizontal-sidebar" 
					ng-include="pluginSettings.pluginPath + 'app/views/filterFields.html'">
				</div>
		</div>

	<?php else : ?>
		<div 
			class="col-sm-12 col-md-12 col-lg-12 lscf-container <?php echo esc_attr( $container_class )?>" 
			data-index="<?php echo (int) ( $lscf_main_shortcode_counter - 1 );?>" 
			ng-controller="pxfilterController" >

			<?php

			if ( true === $is_administrator || 1 == ( int ) $shortcode_attributes['lscf-demo-frontend-editor'] ) {
			?>
				<div class="lscf-sidebar-live-customizer" sidebar-live-customizer></div>
			<?php
			}
			?>

			<div 
				class="row" 
				id="lscf-posts-wrapper" 
				ng-if="pluginSettings.existsPosts" 
				ng-class="{'lscf-administrator' : pluginSettings.filterSettings.is_administrator == 1 }">

				<div
					ng-if="pluginSettings.filterSettings.theme.sidebar.position == 'left' || pluginSettings.filterSettings.theme.sidebar.position == 'top' " 
					class="{{ 'col-xs-12 ' +pluginSettings.className.sidebar+' px-filter-fields' }}" 
					ng-include="pluginSettings.pluginPath + 'app/views/filterFields.html'">
				</div>


				<div class="{{ 'col-xs-12 ' + pluginSettings.className.posts_theme + 'lscf-posts' }} ">

					<div 
						class="px-posts-overlay-loading px-hide"
						ng-class="{'ang_ready' : loadMoreBtn.ready}"
						ng-if="loadMoreBtn.postsLoading" >

						<img src="<?php echo esc_url( LSCF_PLUGIN_URL . 'assets/images/loading_light.gif' ); ?>">
					</div>
					
					<div 
						class="lscf-no-results-error px-hide" 
						ng-class="{'ang_ready' : loadMoreBtn.ready}"
						ng-if="loadMoreBtn.noResults">
							{{pluginSettings.generalSettings.writing.no_results}}
					</div>

					<div 
						ng-switch="pluginSettings.filterSettings.theme.display" 
						ng-class=" {'lscf-template-orderby-right-margin': 'woocommerce-grid-2' == pluginSettings.filterSettings.theme.display}" >
						
						<div class="lscf-order-by" sort-by></div>

						<div ng-switch-when="default">
							
							<div class="row filter-headline">
								
								<div class="viewModeBlock col-sm-12 col-md-12 col-lg-12 ">
						
									<div class="viewMode">
										<div id="blockView" class="glyphicon glyphicon-th"></div>
										<div id="listView" class="active glyphicon glyphicon-th-list"></div>
									</div>
								</div>
							</div>
							
							<div id="lscf-posts-container-defaultTheme" class="view lscf-posts-block lscf-grid-view" viewmode-default></div>

						</div>

						<div ng-switch-when="accordion" class="view lscf-posts-accordion lscf-posts-wrapper">
							<div viewmode-accordion></div>
						</div>

						<div ng-switch-when="portrait" class="view lscf-posts-portrait lscf-posts-wrapper lscf-grid-view">
							<div viewmode-portrait></div>
						</div>

						<div ng-switch-when="basic-grid" class="view lscf-posts-basic-grid lscf-posts-wrapper lscf-grid-view">
							<div viewmode-basic-grid></div>
						</div>

						<div ng-switch-when="masonry-grid" class="view lscf-posts-basic-grid lscf-posts-wrapper lscf-grid-view">
							<div viewmode-masonry-grid></div>
						</div>

						<div ng-switch-when="woocommerce-grid" class="view lscf-posts-basic-grid lscf-posts-wrapper lscf-grid-view">
							<div viewmode-woocommerce></div>
						</div>

						<div ng-switch-when="woocommerce-grid-2" class="lscf-posts-basic-grid lscf-posts-wrapper lscf-grid-view">
							<div viewmode-woocommerce-grid></div>
						</div>

						<div ng-switch-when="custom-theme" class="lscf-posts-wrapper lscf-custom-template-wrapper lscf-grid-view">
							<div viewmode-custom ></div>
						</div>

					</div>
					<div class="clear"></div>
					
					<div 
						class="capf_loading px-hide" 
						ng-class="{'ang_ready' : loadMoreBtn.ready}" ng-if="loadMoreBtn.morePostsAvailable">
							<img ng-if="loadMoreBtn.loading" src="<?php echo esc_url( LSCF_PLUGIN_URL . 'assets/images/loading_light.gif' ); ?>">
							<br/>

							<label 
								class="loadMore" 
								ng-if="!loadMoreBtn.loading" 
								ng-click="load_more()">

								<?php echo esc_attr( $options['writing']['load_more'] )?>
							</label>
					</div>
				</div>
		
				<div 
					ng-if="pluginSettings.filterSettings.theme.sidebar.position == 'right' " 
					class="{{ 'col-xs-12 ' +pluginSettings.className.sidebar+' px-filter-fields' }}" 
					ng-include="pluginSettings.pluginPath + 'app/views/filterFields.html'">
				</div>

			</div>

		</div>

	<?php endif; ?>
</div>
