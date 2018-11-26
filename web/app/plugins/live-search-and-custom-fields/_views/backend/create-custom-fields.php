<?php
$fields_opt = PluginMainController::$custom_fields_opt;

?>

<div class="px-posts-list" id="lscf-manage-post-custom-fields">

	<br/>
	<br/>

	<h3 class="plugin-section-headline">Create your custom fields</h3>
	<label class="headline-label">Select a custom post from the list.</label>  

	<div class="posts-list-custom-fields">

		<form id="lscf-custom-fields-form" method="post" action="<?php echo esc_url( $screen . '&doAction=saveCFP' ); ?>">

			<?php
			if ( $post_types_list ) :
				foreach ( $post_types_list as $key => $post_type_name ) :
					if ( 'wooframework' == $key ) { continue; }
				?>
					<div class="px-post-type-row" data-key="<?php echo esc_attr( $key ) ?>">

						<label class="headline">
							<span class="px-post-name"><?php echo esc_attr( $post_type_name ) ?></span>
							<span class="edit-custom-post"><label>edit</label></span>
							<span class="cf-remove-custom-post"><label>remove</label></span>
						</label>

						<div class="px_innerContainer">

							<div class="fileds-settings">

								<div class="add-field-box">

									<h2>Add a custom field to selected Post Type</h2>
									<strong>Select the field type</strong>
									<select class="px-custom-select PX_add-field-type" name="add-field-type">
										<option value="0"><label>Select</label></option>
										<option value="date">Date</option>
										<option value="select-box">Dropdown</option>
										<option value="text">Text</option>
										<option value="radio">Radio</option>
										<option value="checkbox">Checkbox</option>
										<option value="checkbox-icon">Checkbox/w icons</option>
										<option value="cf-relationship">Variations/Relationship</option>
									</select>

									<button class="px_add-new-custom-field" type="button" class="button">Add</button>

									<hr />

									<div class="clear"></div>

								</div>

							</div>

							<!-- Container for Dynamic custom fields --> 
							<div class="custom_fields-container">
								<input class="px_post_key-data" type="hidden" name="post_key[]" value="<?php echo esc_attr( $key ); ?>">
							</div>

							<?php
							if ( isset( $custom_fields_data[ $key ] ) ) :

								$posts_custom_fields_list = $custom_fields_data[ $key ];

								$row_count = 0;
								foreach ( $custom_fields_data[ $key ] as $field_type => $fields ) {

									switch ( $field_type ) {

										case 'px_date':

											$k = 0;

											foreach ( $fields as $full_slug => $field ) :

												$row_count++;
												$even_class = ( 0 == $row_count % 2  ? 'even' : '' );

												?>    
												<div class="px_field-box px-date <?php echo esc_attr( $even_class ); ?>">

													<div class="lscf-cf-label">
														<i> Field ID: <b><?php echo esc_attr( $full_slug ); ?> </b> </i>
													</div>


													<span class="remove-custom-field">Remove Field</span>

													<div class="inline">

														<label>Field Name:</label>
														<input type="text" class="px_date" name="px_date_<?php echo esc_attr( $key ); ?>-name[<?php echo esc_attr( $k ) ?>]" value="<?php echo esc_attr( $field['name'] )?>">

														<div class="field-type-group">
															<span>Type:</span>
															<strong><?php echo esc_attr( $fields_opt[ $field['slug'] ]['name'] );?></strong>
														</div>

													</div>

													<input type="hidden" name="px_date_<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $k ) ?>]" value="<?php echo esc_attr( px_sanitize( $field['name'] ) ); ?>">
													<input type="hidden" name="px_date_<?php echo esc_attr( $key ); ?>_fieldUniqueID[<?php echo esc_attr( $k ) ?>]" value="<?php echo esc_attr( $full_slug ); ?>"/>

												</div>    
												<?php
												$k++;

											endforeach;

											break;

										case 'px_text':

											$k = 0;

											foreach ( $fields as $full_slug => $field ) :

												$row_count++;
												$even_class = ( 0 == $row_count % 2  ? 'even' : '' );
												?>    

												<div class="px_field-box px-text <?php echo esc_attr( $even_class ); ?>">

													<div class="lscf-cf-label">
														<i> Field ID: <b><?php echo esc_attr( $full_slug ); ?> </b> </i>
													</div>

													<span class="remove-custom-field">Remove Field</span>

													<div class="inline">

														<label>Field Name:</label>
														<input type="text" class="px_text" name="px_text_<?php echo  esc_attr( $key ); ?>-name[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( $field['name'] );?>">

														<div class="field-type-group">
															<span>Type:</span>
															<strong><?php echo esc_attr( $fields_opt[ $field['slug'] ]['name'] );?></strong>

														</div>

													</div>

													<input type="hidden" name="px_text_<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( px_sanitize( $field['name'] ) ); ?>">
													<input type="hidden" name="px_text_<?php echo esc_attr( $key ); ?>_fieldUniqueID[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( $full_slug ); ?>"/>

												</div>    

												<?php
												$k++;
											endforeach;

											break;

										case 'px_radio_box':

											$index = 0;

											foreach ( $fields as $full_slug => $field ) :

												$row_count++;
												$even_class = ( 0 == $row_count % 2  ? 'even' : '' );

												?>    
												<div class="px_field-box lscf-radio-field <?php echo esc_attr( $even_class ); ?> px_radio_box_<?php echo esc_attr( $key ); ?>" data-type="px_radio_box_<?php echo esc_attr( $key ) . '_' . esc_attr( $index ); ?>">

													<div class="lscf-cf-label">
														<i> Field ID: <b><?php echo esc_attr( $full_slug ); ?> </b> </i>
													</div>

													<span class="remove-custom-field">Remove Field</span>

													<div class="inline">

														<label>Field Name:</label>
														<input type="text" class="px_radio_box" name="px_radio_box_<?php echo esc_attr( $key ); ?>-name[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $field['name'] ); ?>">

														<div class="field-type-group">
															<span>Type:</span>
															<strong><?php echo esc_attr( $fields_opt[ $field['slug'] ]['name'] );?></strong>
														</div>

													</div>

													<input type="hidden" name="px_radio_box_<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( px_sanitize( $field['name'] ) ); ?>">

													<ul class="px_select-options-container">

														<?php if ( isset( $field['options'] ) && is_array( $field['options'] ) && count( $field['options'] ) > 0 ) : ?>

															<?php foreach ( $field['options'] as $option ) : ?>

																	<li>
																		<span class="lscf_option_text"> <?php echo esc_attr( $option ); ?> </span>
																		<span class="px_removeOption">Delete</span>
																		<span class="lscf_edit_option">Edit</span>
																		
																		<span class="lscf_option_value">
																			<input type="text" class="lscf_hidden_field" name="px_options_px_radio_box_<?php echo esc_attr( $key ) . '_' . esc_attr( $index ); ?>[]" value="<?php echo esc_attr( $option );?>"/>
																			<input type="hidden" name="px_options_px_radio_box_<?php echo esc_attr( $key ) . '_' . esc_attr( $index ); ?>-old-val[]" value="<?php echo esc_attr( $option );?>">
																			<span class="lscf_update_option" data-type="default"></span>
																		</span>
																	</li>

															<?php endforeach;?>            

														<?php endif; ?>

													</ul>

													<div class="px-option-add-new">

														<input type="text" class="px_optionValue" name="px_add_new_select_option[]"/>
														<span class="px_add_new_select_option px_addNewOption">Add option</span>

													</div>

													<input type="hidden" name="px_radio_box_<?php echo esc_attr( $key ); ?>_fieldUniqueID[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $full_slug ); ?>"/>

												</div>    
												<?php

												$index++;

											endforeach;

											break;

										case 'px_check_box':

											$index = 0;

											foreach ( $fields as $full_slug => $field ) :

												$row_count++;
												$even_class = ( 0 == $row_count % 2 ? 'even' : '' );

											?>    

												<div class="px_field-box lscf-checkbox-field <?php echo esc_attr( $even_class ); ?> px_check_box_<?php echo esc_attr( $key ); ?>" data-type="px_check_box_<?php echo esc_attr( $key ) . '_' . esc_attr( $index ); ?>">

													<div class="lscf-cf-label">
														<i> Field ID: <b><?php echo esc_attr( $full_slug ); ?> </b> </i>
													</div>

													<span class="remove-custom-field">Remove Field</span>

													<div class="inline">

														<label>Field Name:</label>
														<input type="text" class="px_check_box" name="px_check_box_<?php echo esc_attr( $key ); ?>-name[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $field['name'] ); ?>">

														<div class="field-type-group">
															<span>Type:</span>
															<strong><?php echo esc_attr( $fields_opt[ $field['slug'] ]['name'] );?></strong>
														</div>

													</div>

													<input type="hidden" name="px_check_box_<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( px_sanitize( $field['name'] ) ); ?>">

													<ul class="px_select-options-container">
													<?php if ( isset( $field['options'] ) && is_array( $field['options'] ) && count( $field['options'] ) > 0 ) : ?>

														<?php foreach ( $field['options'] as $option ) : ?>

															<li>
																<span class="lscf_option_text"> <?php echo esc_attr( $option ); ?> </span>
																<span class="px_removeOption">Delete</span>
																<span class="lscf_edit_option">Edit</span>

																<span class="lscf_option_value">
																	<input 
																		type="text" 
																		class="lscf_hidden_field" 
																		name="px_options_px_check_box_<?php echo esc_attr( $key ) . '_' . esc_attr( $index ); ?>[]" 
																		value="<?php echo esc_attr( $option ); ?>">

																	<input 
																		type="hidden" 
																		name="px_options_px_check_box_<?php echo esc_attr( $key ) . '_' . esc_attr( $index ); ?>-old-val[]" 
																		value="<?php echo esc_attr( $option ); ?>">

																	<span class="lscf_update_option" data-type="default"></span>
																</span>
															</li>

														<?php endforeach;?>            

													<?php endif; ?>
													</ul>

													<div class="px-option-add-new">
														<input type="text" class="px_optionValue" name="px_add_new_select_option[]"/>
														<span class="px_add_new_select_option px_addNewOption">Add option</span>
													</div>

													<input type="hidden" name="px_check_box_<?php echo esc_attr( $key ); ?>_fieldUniqueID[]" value="<?php echo esc_attr( $full_slug ); ?>"/>

												</div>    
												<?php

												$index++;

											endforeach;

											break;

										case 'px_icon_check_box' :

											$index = 0;

											foreach ( $fields as $full_slug => $field ) :

												$row_count++;
												$even_class = ( 0 == $row_count % 2  ? 'even' : '' );

												?>    
												<div class="px_field-box lscf-icon-checkbox-field <?php echo esc_attr( $even_class ); ?> px_icon_check_box_<?php echo esc_attr( $key ); ?>" data-type="px_icon_check_box_<?php echo esc_attr( $key ) . '_' . esc_attr( $index ); ?>">
		
													<div class="lscf-cf-label">
														<i> Field ID: <b><?php echo esc_attr( $full_slug ); ?> </b> </i>
													</div>
		
													<span class="remove-custom-field">Remove Field</span>

													<div class="inline">

														<label>Field Name:</label>
														<input type="text" class="px_icon_check_box" name="px_icon_check_box_<?php echo esc_attr( $key ); ?>-name[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $field['name'] )?>">

														<div class="field-type-group">
															<span>Type:</span>
															<strong><?php echo esc_attr( $fields_opt[ $field['slug'] ]['name'] );?></strong>
														</div>

													</div>

													<input type="hidden" name="px_icon_check_box_<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( px_sanitize( $field['name'] ) ); ?>">

													<ul class="px_select-options-container">

														<?php if ( isset( $field['options'] ) && is_array( $field['options'] ) && count( $field['options'] ) > 0 ) : ?>

															<?php foreach ( $field['options'] as $option ) : ?>
															<li>
																<div class="choose_photo_box checkbox-icon-option">
																	<span class="lscf-check-w-icon-remove remove_attachment"></span>
																	<div class="imgContainer">
																		<img class="option-icon" src="<?php echo esc_attr( $option['icon'] )?>" width="30">
																	</div>
																	<span class="px_choose_img hidden">Add Icon <strong>(40x40)</strong></span> &nbsp;
																	
																	<input type="hidden" name="px_options_icon_px_icon_check_box_<?php echo esc_attr( $key ) . '_' . esc_attr( $index ); ?>[]" class="img-link" value="<?php echo esc_attr( $option['ID'] );?>">

																	<span class="lscf_option_value">
																		<input type="text" class="lscf_hidden_field" name="px_options_px_icon_check_box_<?php echo esc_attr( $key ) . '_' . esc_attr( $index ); ?>[]" value="<?php echo esc_attr( $option['opt'] );?>">
																		<input type="hidden" name="px_options_px_icon_check_box_<?php echo esc_attr( $key ) . '_' . esc_attr( $index ); ?>-old-val[]" value="<?php echo esc_attr( $option['opt'] );?>">
																		<span class="lscf_update_option" data-type="icon-checkbox"></span>
																	</span>

																	<span class="lscf_option_text"> <?php echo esc_attr( $option['opt'] ); ?> </span>
																</div>
																			
																<span class="px_removeOption">Delete</span>
																<span class="lscf_edit_option">Edit</span>

															</li>

															<?php endforeach;?>            

														<?php endif; ?>
													</ul>

													<div class="px-option-add-new">
														<input type="text" class="px_optionValue" name="px_add_new_select_option[]"/>
														<span class="px_add_new_select_option px_addNewOption">Add option</span>
													</div>

													<input type="hidden" name="px_icon_check_box_<?php echo esc_attr( $key ); ?>_fieldUniqueID[]" value="<?php echo esc_attr( $full_slug ); ?>"/>

												</div>    

												<?php
												$index++;
											endforeach;

											break;


										case 'px_select_box':

											$index = 0;

											foreach ( $fields as $full_slug => $field ) :

												$row_count++;
												$even_class = ( 0 == $row_count % 2  ? 'even' : '' );
												?>    

												<div class="px_field-box lscf-dropdown-field <?php echo esc_attr( $even_class ); ?> px_select_box_<?php echo esc_attr( $key ); ?>" data-type="px_select_box_<?php echo esc_attr( $key ) . '_' . esc_attr( $index ); ?>">

													<div class="lscf-cf-label">
														<i> Field ID: <b><?php echo esc_attr( $full_slug ); ?> </b> </i>
													</div>

													<span class="remove-custom-field">Remove Field</span>

													<div class="inline">

														<label>Field Name:</label>
														<input type="text" class="px_select_box" name="px_select_box_<?php echo esc_attr( $key ); ?>-name[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $field['name'] )?>">

														<div class="field-type-group">
															<span>Type:</span>
															<strong><?php echo esc_attr( $fields_opt[ $field['slug'] ]['name'] );?></strong>
														</div>

													</div>

													<input type="hidden" name="px_select_box_<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( px_sanitize( $field['name'] ) )?>">

													<ul class="px_select-options-container">

														<?php if ( isset( $field['options'] ) && is_array( $field['options'] ) && count( $field['options'] ) > 0 ) : ?>

															<?php foreach ( $field['options'] as $option ) : ?>
																<li>
																	
																	<span class="lscf_option_text"> <?php echo esc_attr( $option ); ?> </span>
																	
																	<span class="px_removeOption">Delete</span>
																	<span class="lscf_edit_option">Edit</span>

																	<span class="lscf_option_value">
																		<input type="text" class="lscf_hidden_field" name="px_options_px_select_box_<?php echo esc_attr( $key ) . '_' . esc_attr( $index ); ?>[]" value="<?php echo esc_attr( $option );?>">
																		<input type="hidden" name="px_options_px_select_box_<?php echo esc_attr( $key ) . '_' . esc_attr( $index ); ?>-old-val[]" value="<?php echo esc_attr( $option );?>">
																		<span class="lscf_update_option" data-type="default"></span>
																	</span>

																</li>
															<?php endforeach;?>            
														<?php endif; ?>

													</ul>

													<div class="px-option-add-new">
														<input type="text" class="px_optionValue" name="px_add_new_select_option[]"/>
														<span class="px_add_new_select_option px_addNewOption">Add option</span>
													</div>

													<input type="hidden" name="px_select_box_<?php echo esc_attr( $key ); ?>_fieldUniqueID[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $full_slug ); ?>"/>

												</div>    
												<?php

												$index++;

											endforeach;

											break;

										case 'px_cf_relationship':

											$index = 0;

											foreach ( $fields as $full_slug => $field ) :

												$active_variation_custom_field = null;
												$row_count++;
												$even_class = ( 0 == $row_count % 2  ? 'even' : '' );
											?>

												<div class="px_field-box px-cf-relationship <?php echo esc_attr( $even_class ); ?> px_cf_relationship_<?php echo esc_attr( $key ); ?>" data-type="px_cf_relationship_<?php echo esc_attr( $key ) . '_' . esc_attr( $index ); ?>">

													<div class="lscf-cf-label">
														<i> Field ID: <b><?php echo esc_attr( $full_slug ); ?> </b> </i>
													</div>

													<span class="remove-custom-field">Remove Field</span>

													<div class="inline">

														<label>Field Name:</label>
														<input type="text" class="px_cf_relationship px_cf_relationship-name px_cf_relationship-input" name="px_cf_relationship_<?php echo esc_attr( $key ); ?>-name[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $field['name'] )?>">

														<div class="field-type-group">
															<span>Type:</span>
															<strong><?php echo esc_attr( $fields_opt[ $field['slug'] ]['name'] );?></strong>
														</div>

													</div>

													<input type="hidden" name="px_cf_relationship_<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( px_sanitize( $field['name'] ) )?>">

													
													<div class="lscf-variation-cf">

														<label>Select custom field that variates.<i>( dropdowns, checkboxes, radio buttons)</i></label>

														<select name="<?php echo esc_attr( $field['slug'] . '_' . $key ) ?>-cf-id[<?php echo (int) $index; ?>]" class="lscf-variation-fields">
															
															<?php 
															foreach ( $posts_custom_fields_list as $cf_type => $cf_data ):

																if ( 'px-text' == $cf_type || 'px-date' == $cf_type  || 'px_cf_relationship' == $cf_type ) {
																	continue;
																}

																foreach ( $cf_data as $cf_single ) :

																	if ( $cf_single['value'] == $field['parent'] ) {
																		$active_variation_custom_field_id = $field['parent'];
																	}

																?>
																	<option value="<?php echo esc_attr( $cf_single['value'] ) ?>"  <?php if ( $cf_single['value'] == $field['parent'] ) echo 'selected' ?> ><?php echo esc_attr( $cf_single['name'] )?></option>
																<?php
																endforeach;

															endforeach;
															?>

														</select>
														<br/>
														<label>Connect items with selected variation above:</label>

														<div class="lscf-variation-rel-items-container">
															<?php 
															foreach ( $posts_custom_fields_list as $cf_type => $cf_data ):

																if ( 'px_cf_relationship' == $cf_type ) {
																	continue;
																}

																foreach ( $cf_data as $cf_single ) :

																	if ( $active_variation_custom_field_id == $cf_single['value'] ) {
																		continue;
																	}

																?>
																	

																		<div class="lscf-variation-item-checkbox">
																			<input <?php echo ( in_array( $cf_single['value'], $field['item_ids'] ) ? 'checked' : '' ) ?> type="checkbox" name="<?php echo esc_attr( $field['slug'] . '_' . $key ) ?>-item_<?php  echo (int) $index; ?>[]" value="<?php echo esc_attr( $cf_single['value'] )?>"><?php echo esc_attr( $cf_single['name'] ) ?>
																		</div>

																<?php
																endforeach;

															endforeach;
															?>

														</div>

													</div>
													

													<input type="hidden" name="px_cf_relationship_<?php echo esc_attr( $key ); ?>_fieldUniqueID[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $full_slug ); ?>"/>

												</div>    

											<?php

												$index++;

											endforeach;

											break;

									}
								}

							endif;
							?>
						</div>

						<div class="save-btn">
							<button class="button button-primary px-button">Save</button>
						</div>

					</div>
					<?php

				endforeach;
			endif;
				?>

			<br/>
			<br/>

		</form>

	</div>

</div>
