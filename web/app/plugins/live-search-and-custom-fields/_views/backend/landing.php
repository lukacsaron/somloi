<?php 
	$plugin_url = LSCF_PLUGIN_URL; 
	// get all the CUSTOM POSTS TYPE List event if the custom posts weren't created by current plugin.
	$post_types_list = PluginMainController::$post_types_list;
	// custom post types that already are activated into filter.
	$active_post_types = PluginMainController::$filter_custom_posts_type_list;
	$custom_post_types_list_plugin_only = PluginMainController::$plugin_settings['generate_the_custom_posts_list'];
	$custom_fields_data = PluginMainController::$custom_fields_data;

?>

<div class="px-plugin-wrapper">

	<div class="px-plugin-sidebar">

		<div class="block-section">
			<img src="<?php echo esc_url( LSCF_PLUGIN_URL . 'assets/images/icons/pixolette-logo.png' ); ?>"/>
		</div>

		<hr class="silver"/>

		<div class="block-section">
			<h2>Need Help?</h2>
			<a class="underline" target="_blank" href="https://wp.pixolette.com/docs/lscf">Documentation</a><br/>
			<a class="underline" target="_blank" href="https://wp.pixolette.com/docs/general/faq/">FAQ</a><br/>
		</div>

		<div class="block-section">
			<h2>Custom Template</h2>
			<a class="underline" target="_blank" href="https://wp.pixolette.com/docs/lscf/custom-template/data-tags-list/" target="_blank">Data Tag List</a><br/>
			<a class="underline" target="_blank" href="https://wp.pixolette.com/docs/lscf/custom-template/displaying-lscf-custom-fields-data/" target="_blank">Display LSCF custom fields into template</a><br/><br/>
			<h2>Shortcodes</h2>
			<a href="https://wp.pixolette.com/docs/lscf/shortcodes/">Custom Fields Shortcode</a>
		</div>

		<hr/>

		<div class="block-section lscf-rate-on-codecanyon">
			<a class="lscf-rate-link" href="https://codecanyon.net/item/live-search-and-custom-fields/reviews/18598007" target="_blank"></a>
			<p>Do you like how LSCF serves you? Give it a rating <a href="https://codecanyon.net/item/live-search-and-custom-fields/reviews/18598007" target="_blank">here</a></p>

		</div>

	</div>

	<div class="px-left-col">

		<div class="px-lfcf-logo">
			<img src="<?php echo esc_url( LSCF_PLUGIN_URL . 'assets/css/images/logo.jpg' ) ?>"/>
		</div>

		<div class="nav-tab-wrapper">
			<a href="<?php echo esc_url( $screen . '&plugin-tab=settings' ); ?>" class="nav-tab <?php px_set_as_active( $active_tab, 'settings', 'nav-tab-active' )?>">Settings</a>
			<a href="<?php echo esc_url( $screen . '&plugin-tab=general-opt' ); ?>" class="nav-tab <?php px_set_as_active( $active_tab, 'general-opt', 'nav-tab-active' )?>">Custom Posts</a>
			<a href="<?php echo esc_url( $screen . '&plugin-tab=post-fields' ) ?>" class="nav-tab <?php px_set_as_active( $active_tab, 'post-fields', 'nav-tab-active' )?>">Custom Fields</a>
			<a href="<?php echo esc_url( $screen . '&plugin-tab=filter-generator' )?>" class="nav-tab <?php px_set_as_active( $active_tab, 'filter-generator|edit-filter-shortcode', 'nav-tab-active' ) ?>">Custom Filter</a>
		</div>

		<div class="clear"></div>

		<div class="px-plugin-container lscf-wrapper">

		<?php
		switch ( $active_tab ) {

			case 'settings':

				?>
				<div class="px-plugin-secction">
				<?php include LSCF_PLUGIN_PATH . '_views/backend/settings-tab.php'; ?>
				</div>
				<?php

				break;

			case 'general-opt':
		?>
					<div class="px-plugin-section">

						<div>
							<br/>
							<br/>

							<h3 class="plugin-section-headline">Add new Custom Post</h3>
							<label class="headline-label">Create new custom post types for your website</label>               

							<div class="create-custom-post-wrapper">

								<div class="create-custom-post-box">

									<label>Custom Post Name:</label><br/>
									<input id="customPostName" type="text" name="post_type_name" value=""/>
									<button id="create-new-custom-post" type="button" class="button">Create new Custom Post</button>

								</div>

								<div class="custom-posts-list">
									<h2> Custom Posts List</h2>
									<?php if ( count( $custom_post_types_list_plugin_only ) > 0 ) : ?>
										<ul>
										<?php foreach ( $custom_post_types_list_plugin_only as $key => $custom_post_type ) : ?>
											<li data-key="<?php echo esc_attr( $key ); ?>"><strong><?php echo esc_attr( $custom_post_type ); ?></strong>
											<span class="remove-custom-post">Remove</span></li>
										<?php endforeach;?>
										</ul>
									<?php endif;?>
								</div>

							</div>

						</div>

					</div>
					
				<?php
				break;

			case 'post-fields':

				include_once LSCF_PLUGIN_PATH . '_views/backend/create-custom-fields.php';

				break;

			case 'edit-filter-shortcode':

				include_once LSCF_PLUGIN_PATH . '_views/backend/edit-filter-shortcode.php';

				break;

			case 'filter-generator':

			?>

				<div id="pxcf_createFilter" class="lfcs-wrapper">

					<h1>Create your Custom Filter</h1>

					<div id="px_post-customFields">

						<form id="generateshortcode-form">

							<div class="f-name">

								<label>Filter Name:</label><br/>

									<div class="inline">
										<input id="px_filter-name-shorcode-generator" type="text" name="px_filter_name" value=""/>
										<button id="goToFilterFields" type="button" class="px-button">Next</button>
									</div>

									<span class="px_error" id="px_filter_name_error">Filter name is empty</span>

							</div>

							<div class="lscf-step-2">


								<div class="px_lf_post-type step1 step-shape">

									<label>Choose the custom post type</label>
									
									<input type="hidden" id="px-filter-for" name="filter-for" value="custom-posts"/>

									<select id="px-filter-selected-post-type" name="px_lf_post-type" class="px-custom-select">

										<option value="0">Select Post Type</option>

										<?php
										if ( count( $post_types_list ) > 0 ) :

											$count = 0;
											if ( class_exists( 'WooCommerce' ) ) {

												foreach ( $post_types_list as $key => $post_type ) :

													if ( 'wooframework' == $key ) {
														continue;
													}

													$checked = ( in_array( $key, $active_post_types ) ? 'checked="checked"' : '' );

													if ( 'product' == $key ) :
													?>
														<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $post_type ); ?>(WooCommerce)</option>
													<?php
													else :
													?>
														<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $post_type ); ?></option>
													<?php
													endif;

													$count++;

												endforeach;

											} else {

												foreach ( $post_types_list as $key => $post_type ) :

													$checked = ( in_array( $key, $active_post_types ) ? 'checked="checked"' : '' );
													?>
														<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $post_type ); ?></option>
														<?php

														$count++;

												endforeach;
											}
										endif;
										?>    

									</select>

								</div><!-- END step1 -->

								<div class="step2 step-shape">

									<label>Select the custom fields for filter sidebar</label>

									<div class="post-categories-group">

										<strong class="expandable-container-headline inactive">Categories</strong>
										

										<div class="expandable-container">
											<div id="px_post_categories" class="data-container"></div>
										</div>

									</div>

									<br/>
									<br/>
									<br/>

									<div class="post-custom-fields-group">

										<strong class="expandable-container-headline always-active">Custom Fields</strong>
										<hr class="silver"/>

										<div class="expandable-container">

											<div class="data-container custom-fields-block">

												<div id="px_post_fields" class="px_dynamic-field"></div>

												<hr class="silver"/>

												<div class="px_additional-fields">

													<h4>Special fields for results filtering</h4>
													<select id="px_additional-fields" class="px-custom-select">
														<option value="">Select Field</option>
														<option value="search">Search</option>
														<option value="range">Range</option>
														<option value="date-interval">Date Interval</option>
													</select>

													<span class="px_add-additional-field" id="px_add-additional-field">Add</span>

													<div class="clear"></div>

													<!-- ************* Custom Post Type Fields List ***********************-->
													<div id="px_additional-fields-container"></div>

												</div><!-- END px_additional-fields-->

											</div><!-- END data-container-->

										</div><!-- END expandable-continer-->

									</div><!-- END post-custom-fields-group -->

								</div><!-- END step2 -->


								<div class="featured-fields-group step3 step-shape ">                                    

									<label>Some extra stuff for you... Nice label on featured pictues(grid), on right side (list) </label>

									<strong class="expandable-container-headline inactive">Features</strong>
									<hr class="silver"/>

									<div class="expandable-container">

										<div class="data-container">

											<div class="px_featured-field">
												<h4>Select a featured field that would show on each post from list</h4>
												<div id="setAsFeaturedField"></div>
											</div>

										</div>

									</div><!-- END expandable-container --> 

								</div><!-- END featured-fields-group && step3  -->

								<div class="step4 step-shape ">
									
									<strong class="expandable-container-headline always-active">Styles</strong>

									<div class="expandable-container filter-display-settings">

										<?php include_once LSCF_PLUGIN_PATH . '_views/backend/filter-style.php'; ?>

									</div>

								</div>

								<div class="step5 step-shape last-step">

									<strong>Shortcode</strong>
									<hr class="silver"/>
									<label>Generate shortcode. Copy and pase the generated shortcode into desired page for filter to show up.</label>

								</div><!-- END step4 -->

								<button type="button" class="generate-shortcode" data-type="new" id="pxcf_generate-shortcode">Generate Shortcode</button>
								<span id="lscf-saving-shorcode-message">Saving...</span>
								<span id="lscf-shortcode-generated-message">Saved</span>

								<!-- Append Shorcodes -->
								<ul id="pxGenerateShortcodesContainer" class="generatedShorcodes"></ul>

							</div>

						</form>

						<div id="active-shortcodes-list" class="lscf-shortcodes-list">

							<h2><span>or</span> Copy/Paste your filters to Page editor</h2>

							<div class="shortcodes-list">

								<h2>Shortcodes:</h2>
								<hr/>

								<ul class="px-active-shorcodes">
								<?php

								$f_data = array();
								$count = 0;

								if ( isset( PluginMainController::$plugin_settings['filterList'] ) ) :

									foreach ( PluginMainController::$plugin_settings['filterList'] as $filter_id => $ch_data ) {

										$f_data[ $count ]['filterID'] = $filter_id;
										$f_data[ $count ]['data'] = $ch_data;

										$count++;

									}

								endif;

								for ( $i = ( count( $f_data ) - 1 ); $i >= 0; $i-- ) :

									if ( ! isset( $f_data[ $i ]['data']['post_type'] ) || ! isset( $f_data[ $i ]['filterID'] ) ) {
										continue;
									}
								?>
									<li class="single-shortcode">
										<ul>
											<li class="filter-headline">

												<span>Name:</span><strong><?php echo esc_attr( $f_data[ $i ]['data']['name'] ); ?></strong>
												<br/>
												<span data-id="<?php echo esc_attr( $f_data[ $i ]['filterID'] ); ?>" data-post="<?php echo esc_attr( $f_data[ $i ]['data']['post_type'] ); ?>" class="px_remove-shortcode px_removeOption">Remove</span>
												<a class="lscf-edit-shortcode"  href="<?php echo esc_url( admin_url() . '?page=pxLF_plugin&plugin-tab=filter-generator&edit_filter='.$f_data[ $i ]['filterID'] )?>">Edit</a>
											</li>

											<li class="shortcode-copy">

												<textarea style="width:100%; text-align:left" rows="4" readonly="readonly"><?php echo esc_html( trim('
												[px_filter id="' . $f_data[ $i ]['filterID'] . '" post_type="' . $f_data[ $i ]['data']['post_type'] . '" featured_label="' . $f_data[ $i ]['data']['featuredLabelFieldID'] . '"]' ) );
												?></textarea>

											</li>

										</ul>
									</li>
								<?php
								endfor;
									?>
								</ul>

							</div>
						</div>

					</div>
				</div>
				<?php

				break;
		}
		?>

		</div>
	</div>
</div>
