
<div class="lfcs-wrapper lscf-edit-shortcode">

	<h1>Edit the shortcode</h1>

	<div>

		<form id="generateshortcode-form">

			<div class="f-name">

				<label>Filter Name:</label><br/>

					<div class="inline">
						<input id="px_filter-name-shorcode-generator" type="text" name="px_filter_name" value="<?php echo esc_attr( $filter_data['name'] ) ?>"/>
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
										<option <?php echo ( $key == $filter_data['post_type'] ? 'selected' :'' );?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $post_type ); ?>(WooCommerce)</option>
									<?php
									else :
									?>
										<option <?php echo ( $key == $filter_data['post_type'] ? 'selected' :'' );?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $post_type ); ?></option>
									<?php
									endif;

									$count++;

								endforeach;

							} else {

								foreach ( $post_types_list as $key => $post_type ) :

									$checked = ( in_array( $key, $active_post_types ) ? 'checked="checked"' : '' );
									?>
										<option <?php echo ( $key == $filter_data['post_type'] ? 'selected' :'' );?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $post_type ); ?></option>
										<?php

										$count++;

								endforeach;
							}
						endif;
						?>    

					</select>

				</div><!-- END step1 -->

				<div class="step2 step-shape">

					<div class="post-categories-group">

						<strong class="expandable-container-headline always-active actives">Categories</strong>
						

						<div class="expandable-container active">
							<div id="px_post_categories" class="data-container"></div>
						</div>

					</div>

					<br/>
					<br/>
					<br/>

					<div class="post-custom-fields-group">

						<strong class="expandable-container-headline always-active active">Custom Fields</strong>
						<hr class="silver"/>

						<div class="expandable-container active">

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

					<div class="expandable-container active">

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

					<div class="expandable-container filter-display-settings active">

						<?php include_once LSCF_PLUGIN_PATH . '_views/backend/filter-style.php'; ?>

					</div>

				</div>

				<div class="step5 last-step">

					<strong>Save settings</strong>
					<hr class="silver"/>
					<label>Save the filter shortcode settings</label>

				</div><!-- END step4 -->
				<br/>
				<button type="button" class="generate-shortcode" data-type="edit" data-filter-id="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['edit_filter'] ) ) );?>" id="pxcf_generate-shortcode">Edit Shortcode</button>
				<span id="lscf-saving-shorcode-message">Saving...</span>
				<span id="lscf-shortcode-generated-message">Saved</span>

				<!-- Append Shorcodes -->
				<ul id="pxGenerateShortcodesContainer" class="generatedShorcodes"></ul>

			</div>

		</form>
	</div>
</div>
