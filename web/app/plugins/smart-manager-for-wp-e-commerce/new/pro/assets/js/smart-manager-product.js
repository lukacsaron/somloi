
// Code for adding Add Attribute & Remove Attribute in Batch Update
jQuery(document).on('sm_batch_update_columns', function(e, sm_batch_update_columns) {

	if( typeof(sm_batch_update_columns['custom/product_attributes']) != 'undefined' && Object.keys(sm_batch_update_columns['custom/product_attributes']['values']).length > 0 ) {

		var attr_obj = sm_batch_update_columns['custom/product_attributes'];

		sm_batch_update_columns['custom/product_attributes_add'] = Object.assign({}, attr_obj);
		sm_batch_update_columns['custom/product_attributes_add']['name'] = 'Add Attribute';
		sm_batch_update_columns['custom/product_attributes_add']['type'] = 'list';

		if( !sm_batch_update_columns['custom/product_attributes_add']['values'].hasOwnProperty('custom') ) {
			sm_batch_update_columns['custom/product_attributes_add']['values'].custom = { 'lbl': 'Custom',
																						'type': 'string',
																						'val': new Array() };
		}

		sm_batch_update_columns['custom/product_attributes_remove'] = Object.assign({}, attr_obj);
		sm_batch_update_columns['custom/product_attributes_remove']['name'] = 'Remove Attribute';
		sm_batch_update_columns['custom/product_attributes_remove']['type'] = 'list';

		delete sm_batch_update_columns['custom/product_attributes'];
	}

	//Code for handling Product Category
	if( typeof(sm_batch_update_columns['terms/product_cat']) != 'undefined' && Object.keys(sm_batch_update_columns['terms/product_cat']['values']).length > 0 ) {

		var categories_obj = sm_batch_update_columns['terms/product_cat'],
			categories = sm_batch_update_columns['terms/product_cat']['values'],
			parent_categories = {},
			child_categories = {};

		for( term_id in categories ) {

			parent_categories[term_id] = categories[term_id].term;

			if( typeof(categories[term_id].parent) != 'undefined' && categories[term_id].parent > 0 ) {
				if( typeof(child_categories[categories[term_id].parent]) == 'undefined' ) {
					child_categories[categories[term_id].parent] = {};
				}
				child_categories[categories[term_id].parent][term_id] = categories[term_id].term;
			}
		}

		//Code for parent categories
		if( Object.keys(parent_categories).length > 0 ) {
			sm_batch_update_columns['custom/product_cat_parent'] = Object.assign({}, attr_obj);
			sm_batch_update_columns['custom/product_cat_parent']['name'] = 'Categories';
			sm_batch_update_columns['custom/product_cat_parent']['type'] = 'list';
			sm_batch_update_columns['custom/product_cat_parent']['values'] = parent_categories;
		}
		

		//Code for child categories 
		if( Object.keys(child_categories).length > 0 ) {
			for( term_id in child_categories ) {

				var parent_cat_name = categories[term_id].term
					key = 'custom/product_cat_'+convert_to_slug(parent_cat_name);

				sm_batch_update_columns[key] = Object.assign({}, attr_obj);
				sm_batch_update_columns[key]['name'] = 'Categories: '+parent_cat_name;
				sm_batch_update_columns[key]['type'] = 'list';
				sm_batch_update_columns[key]['values'] = child_categories[term_id];
			}
		}

		delete sm_batch_update_columns['terms/product_cat'];
	}	

});


//Code to handle after change of batch update field in case of product attributes and categories
jQuery(document).on('sm_batch_update_field_on_change', function(e, row_id, selected_field, type, col_val) {

	if( jQuery("#"+row_id+" #batch_update_value_td_2").length ) { //handling for removing custom attribute td
		jQuery("#"+row_id+" #batch_update_value_td_2").remove();
	}

	if( jQuery('#batchmod_sm_editor_grid #batch_update_value_td_2').length == 0 ) { //handling for restoring the batch update dialog size
		jQuery("#batchmod_sm_editor_grid").css('width','640px');
	}

	if( selected_field == 'custom/product_attributes_add' || selected_field == 'custom/product_attributes_remove' ) {
		// sm.skip_default_action = true;

		sm.batch_update_action_options_default = '<option value="" disabled selected>Select Attribute</option>';

		if( Object.keys(col_val).length > 0 ) {
			for( attr_nm in col_val ) {
				sm.batch_update_action_options_default += '<option value="'+ attr_nm +'">'+ col_val[attr_nm].lbl +'</option>';
			}
		}

	} else if( selected_field.indexOf('custom/product_cat') != -1 ) {
		sm.batch_update_action_options_default = '<option value="" disabled selected>Select Action</option>'+
													'<option value="set_to">set to</option>'+
													'<option value="add_to">add to</option>'+
													'<option value="remove_from">remove from</option>';
	}
});

//Code to handle after specific attribute has been selected in case of add or remove attribute
jQuery(document).on('change','#batch_update_action',function(){

	var row_id = jQuery(this).closest('tr').attr('id');

    var selected_field = jQuery( "#"+row_id+" #batch_update_field option:selected" ).val(),
        selected_action = jQuery( "#"+row_id+" #batch_update_action option:selected" ).val(),
        type = column_names_batch_update[selected_field].type,
        col_val = column_names_batch_update[selected_field].values;

    if( jQuery("#"+row_id+" #batch_update_value_td_2").length ) { //handling for removing custom attribute td
		jQuery("#"+row_id+" #batch_update_value_td_2").remove();
	}

	if( jQuery('#batchmod_sm_editor_grid #batch_update_value_td_2').length == 0 ) { //handling for restoring the batch update dialog size
		jQuery("#batchmod_sm_editor_grid").css('width','640px');
	}

    if( selected_field == 'custom/product_attributes_add' || selected_field == 'custom/product_attributes_remove' ) {

    	if( selected_action != 'custom' ) { //code for handling action for non-custom attribute

    		var batch_update_value_options = '',
    			batch_update_value_select_options = '',
	    		value_options_empty = true;

	    	if( typeof (col_val[selected_action]) != 'undefined' && typeof (col_val[selected_action].val) != 'undefined' ) {

	    		col_val = col_val[selected_action].val;

	    		for (var key in col_val) {
		            if( typeof (col_val[key]) != 'object' && typeof (col_val[key]) != 'Array' ) {
		                value_options_empty = false;
		                batch_update_value_select_options += '<option value="'+key+'">'+ col_val[key] + '</option>';
		            }
		        }
	    	}

	        if( value_options_empty === false ) {
	        	batch_update_value_options = '<select id="batch_update_value" style="min-width:130px !important;">'+
	        									'<option value="all">All</option>'+
	        									batch_update_value_select_options +
	        									'</select>';
	            jQuery("#"+row_id+" #batch_update_value_td").empty().append(batch_update_value_options)
	            jQuery("#"+row_id+" #batch_update_value_td").find('#batch_update_value').select2({ width: '15em', dropdownCssClass: 'sm_beta_batch_update_field' });    
	        }

    	} else { //code for handling action for custom attribute

    		jQuery("#"+row_id+" #batch_update_value_td").html("<input type='text' id='batch_update_value' placeholder='Enter Attribute name...' class='FormElement ui-widget-content'>");
    		jQuery("<td id='batch_update_value_td_2' style='white-space: pre;'><input type='text' id='batch_update_value_2' placeholder='Enter values...' title='For more than one values, use pipe (|) as delimiter' class='FormElement ui-widget-content'></td>").insertAfter("#"+row_id+" #batch_update_value_td");
    		jQuery("#batchmod_sm_editor_grid").css('width','760px');
    	}
    	
    }

});


//Code to make changes to batch_update_actons on batch update form submit for custom attribute handling
jQuery(document).on('sm_batch_update_on_submit',function(){

	var index = 0;

	jQuery('tr[id^=batch_update_action_row_]').each(function() {
		var value2 = jQuery(this).find('#batch_update_value_2').val();

		if( typeof (value2) != 'undefined' ) {
			sm.batch_update_actions[index].value2 = value2; 
		}

		index++;
	});
});
