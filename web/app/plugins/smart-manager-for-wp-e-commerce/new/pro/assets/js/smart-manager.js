
sm_beta_smart_date_filter = '<div class="sm_date_range_container">'+
                                        '<div>'+
                                            '<input placeholder="Created Start Date" class="sm_date_selector start-date" id="sm_date_selector_start_date" title="Click to edit created start date">'+
                                        '</div>'+
                                        '<div class="date-separator">-</div>'+
                                        '<div>'+
                                            '<input placeholder="Created End Date" class="sm_date_selector end-date" id="sm_date_selector_end_date" title="Click to edit created end date">'+
                                        '</div>'+
                                        '<div class="dropdown">'+
                                            '<span class="dashicons dashicons-arrow-down-alt2 smart-date-icon dropdown-toggle" id="smartDatesDropdown" title="Click to select a pre-defined date range"></span>'+
                                            '<ul class="dropdown-menu pull-right" aria-labelledby="smartDatesDropdown">'+
                                                '<li><a href="" data-key="today">Today</a></li>'+
                                                '<li><a href="" data-key="yesterday">Yesterday</a></li>'+
                                                '<li role="separator" class="divider"></li>'+
                                                '<li><a href="" data-key="this_week">This Week</a></li>'+
                                                '<li><a href="" data-key="last_week">Last Week</a></li>'+
                                                '<li><a href="" data-key="last_4_week">Last 4 Weeks</a></li>'+
                                                '<li role="separator" class="divider"></li>'+
                                                '<li><a href="" data-key="this_month">This Month</a></li>'+
                                                '<li><a href="" data-key="last_month"">Last Month</a></li>'+
                                                '<li role="separator" class="divider"></li>'+
                                                '<li><a href="" data-key="3_months">3 Months</a></li>'+
                                                '<li><a href="" data-key="6_months">6 Months</a></li>'+
                                                '<li role="separator" class="divider"></li>'+
                                                '<li><a href="" data-key="this_year">This Year</a></li>'+
                                                '<li><a href="" data-key="last_year">Last Year</a></li>'+
                                                '<li role="separator" class="divider"></li>'+
                                                '<li><a href="" data-key="custom">Custom</a></li>'+
                                            '</ul>'+
                                        '</div>'+
                                    '</div>';

//Global Varibales for Pro
jQuery(document).on('ready', function() {
    if( typeof(sm) != 'undefined' ) {
        sm.batch_update_action_options_default = '';
        sm.batch_update_actions = new Array();    
    }

    if( batch_background_process == 1 ) { //if batch process is running

        var msg_txt = ( background_process_name == 'Batch Update' ) ? 'updated' : 'duplicated';  


        if( jQuery("#wpbody .sm_beta_pro_background_update_notice").length == 0 ) {
            jQuery('<div id="sm_beta_pro_background_update_notice" class="notice notice-info sm_beta_pro_background_update_notice"><p><strong>Smart Manager Beta '+ background_process_name +'</strong> – Your records are being '+msg_txt+' in the background and you will be notified on your email<strong> <code>'+sm_admin_email+'</code> </strong>once the batch process is completed.</p></div>').insertBefore('#wpbody .wrap');
        }

        var params = { 'func_nm': background_process_name };
        sm_beta_background_updater_heartbeat(params);
    }

    //code for daterange picker
    jQuery(document).on('sm_load_dashboard_complete', function() {
        jQuery(document).on('hover', '.sm_date_range_container .smart-date-icon, .sm_date_range_container .dropdown-menu', function() {
            jQuery('.sm_date_range_container .dropdown-menu').toggle();
        });

        jQuery(document).on('click', '.sm_date_range_container .dropdown-menu li a', function(e) {
            e.preventDefault();
            proSelectDate(jQuery(this).attr('data-key'));
        });

        //Code for initializing the date picker
        jQuery('.sm_date_range_container input.sm_date_selector').Zebra_DatePicker({
                                                                                                    format: 'd M y H:i:s',
                                                                                                    // format: 'dd-mm-yy H:i:s',
                                                                                                    show_icon: false,
                                                                                                    show_select_today: false,
                                                                                                    default_position: 'below',
                                                                                                    onSelect: function(fdate, jsdate) {
                                                                                                        $(this).change();
                                                                                                        var id = jQuery(this).attr('id'),
                                                                                                            selected_date_obj = new Date(fdate),
                                                                                                            params = {'start_date_formatted':'',
                                                                                                                        'start_date_default_format':'',
                                                                                                                        'end_date_formatted':'',
                                                                                                                        'end_date_default_format':''};

                                                                                                        if( id == 'sm_date_selector_start_date' ) { //if end_date is not set

                                                                                                            params.start_date_formatted = fdate;
                                                                                                            params.start_date_default_format = jsdate;

                                                                                                            var end_date = jQuery('#sm_date_selector_end_date').val(),
                                                                                                                end_time = '';

                                                                                                            if( end_date == '' ) {
                                                                                                                end_date_obj = new Date( selected_date_obj.getFullYear(), selected_date_obj.getMonth(), ( selected_date_obj.getDate() + 29 ) );
                                                                                                                end_time =  '23:59:59';
                                                                                                            } else {
                                                                                                                end_date_obj = new Date(end_date);
                                                                                                                end_time =  str_pad(end_date_obj.getHours(), 2) + ':' + str_pad(end_date_obj.getMinutes(), 2) + ':' + str_pad(end_date_obj.getSeconds(), 2);
                                                                                                            }
                                                                                                            var y = end_date_obj.getFullYear() + '',
                                                                                                                m = end_date_obj.getMonth(),
                                                                                                                d = str_pad(end_date_obj.getDate(), 2);
                                                                                                            
                                                                                                            params.end_date_formatted = d + ' ' + sm.month_names_short[m] + ' ' + y.substring(2) + ' ' + end_time;
                                                                                                            params.end_date_default_format = y + '-' + str_pad((m+1), 2) + '-' + d + ' ' + end_time;

                                                                                                            if( end_date == '' ) {
                                                                                                                end_date_datepicker = jQuery('.sm_date_range_container input.end-date').data('Zebra_DatePicker');
                                                                                                                end_date_datepicker.set_date(params.end_date_formatted);
                                                                                                                end_date_datepicker.update({'current_date': new Date(params.end_date_default_format)});
                                                                                                            }
                                                                                                            

                                                                                                        } else if( id == 'sm_date_selector_end_date' ) { //if start_date is not set

                                                                                                            params.end_date_formatted = fdate;
                                                                                                            params.end_date_default_format = jsdate;

                                                                                                            var start_date = jQuery('#sm_date_selector_start_date').val(),
                                                                                                                start_time = '';

                                                                                                            if( start_date == '' ) {
                                                                                                                start_date_obj = new Date( selected_date_obj.getFullYear(), selected_date_obj.getMonth(), ( selected_date_obj.getDate() - 29 ) );
                                                                                                                start_time = '23:59:59';
                                                                                                            } else {
                                                                                                                start_date_obj = new Date(start_date);
                                                                                                                start_time = str_pad(start_date_obj.getHours(), 2) + ':' + str_pad(start_date_obj.getMinutes(), 2) + ':' + str_pad(start_date_obj.getSeconds(), 2);
                                                                                                            }
                                                                                                            var y = start_date_obj.getFullYear() + '',
                                                                                                                m = start_date_obj.getMonth(),
                                                                                                                d = str_pad(start_date_obj.getDate(), 2);

                                                                                                            params.start_date_formatted = d + ' ' + sm.month_names_short[m] + ' ' + y.substring(2) + ' ' + start_time;
                                                                                                            params.start_date_default_format = y + '-' + str_pad((m+1), 2) + '-' + d + ' ' + start_time;

                                                                                                            if( start_date == '' ) {
                                                                                                                start_date_datepicker = jQuery('.sm_date_range_container input.start-date').data('Zebra_DatePicker');
                                                                                                                start_date_datepicker.set_date(params.start_date_formatted);
                                                                                                                start_date_datepicker.update({'current_date': new Date(params.start_date_default_format)});
                                                                                                            }
                                                                                                        }

                                                                                                        sm_handle_date_filter(params);
                                                                                                    }
                                                                                                });

        if( typeof(sm.post_data_params.date_filter_params) != 'undefined' ) {

            selected_dates = JSON.parse(sm.post_data_params.date_filter_params);

            start_date_datepicker = jQuery('.sm_date_range_container input.start-date').data('Zebra_DatePicker');
            start_date_datepicker.set_date(selected_dates.start_date_formatted);
            start_date_datepicker.update({'current_date': new Date(selected_dates.start_date_default_format)});

            end_date_datepicker = jQuery('.sm_date_range_container input.end-date').data('Zebra_DatePicker');
            end_date_datepicker.set_date(selected_dates.end_date_formatted);
            end_date_datepicker.update({'current_date': new Date(selected_dates.end_date_default_format)});

        }

    });
    
    //code for resetting the datepicker on dashboard change
    jQuery(document).on('sm_dashboard_change', function() {

        var params = {'start_date_formatted':'',
                    'start_date_default_format':'',
                    'end_date_formatted':'',
                    'end_date_default_format':''};

        start_date_datepicker = jQuery('.sm_date_range_container input.start-date').data('Zebra_DatePicker');
        start_date_datepicker.set_date(params.start_date_formatted);
        start_date_datepicker.update({'current_date': new Date(params.start_date_default_format)});

        end_date_datepicker = jQuery('.sm_date_range_container input.end-date').data('Zebra_DatePicker');
        end_date_datepicker.set_date(params.end_date_formatted);
        end_date_datepicker.update({'current_date': new Date(params.end_date_default_format)});

        if( sm.post_data_params.hasOwnProperty('date_filter_params') ) {
            delete sm.post_data_params['date_filter_params'];
        }

        if( sm.post_data_params.hasOwnProperty('date_filter_query') ) {
            delete sm.post_data_params['date_filter_query'];
        }

    });
    
});

//function to process the datepicker filter
var sm_handle_date_filter = function(params) {

    var search_array = new Array(),
        date_search_array = new Array();

    if( sm.dashboard_key == 'user' ) {
        date_search_array = new Array({"key":"User Registered","value":params.start_date_default_format,"type":"date","operator":">=","table_name":"wp_users","col_name":"user_registered","date_filter":1},
                                    {"key":"User Registered","value":params.end_date_default_format,"type":"date","operator":"<=","table_name":"wp_users","col_name":"user_registered","date_filter":1});
    } else {
        date_search_array = new Array({"key":"Post Date","value":params.start_date_default_format,"type":"date","operator":">=","table_name":"wp_posts","col_name":"post_date","date_filter":1},
                                    {"key":"Post Date","value":params.end_date_default_format,"type":"date","operator":"<=","table_name":"wp_posts","col_name":"post_date","date_filter":1});
    }

    sm.post_data_params['date_filter_params'] = JSON.stringify(params);
    sm.post_data_params['date_filter_query'] = JSON.stringify(date_search_array);

    load_dashboard ();
    jQuery('#sm_editor_grid').trigger( 'reloadGrid' );
}

//function to append 0's to str
str_pad = function(str, len) {

    str += '';
    while (str.length < len) str = '0' + str;
    return str;

},

proSelectDate = function (dateValue){
        
    var fromDate,
        toDate,
        from_time,
        to_time,
        from_date_formatted,
        from_date_default_format,
        to_date_formatted,
        to_date_default_format,
        now = new Date(),
        params = {'start_date_formatted':'',
                'start_date_default_format':'',
                'end_date_formatted':'',
                'end_date_default_format':''};

    switch (dateValue){

        case 'today':
        fromDate = now;
        toDate   = now;
        break;

        case 'yesterday':
        fromDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1);
        toDate   = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1);
        break;

        case 'this_week':
        fromDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - (now.getDay() - 1));
        toDate   = now;
        break;

        case 'last_week':
        fromDate = new Date(now.getFullYear(), now.getMonth(), (now.getDate() - (now.getDay() - 1) - 7));
        toDate   = new Date(now.getFullYear(), now.getMonth(), (now.getDate() - (now.getDay() - 1) - 1));
        break;

        case 'last_4_week':
        fromDate = new Date( now.getFullYear(), now.getMonth(), ( now.getDate() - 29 ) ); //for exactly 30 days limit
        toDate   = now;
        break;

        case 'this_month':
        fromDate = new Date(now.getFullYear(), now.getMonth(), 1);
        toDate   = now;
        break;

        case 'last_month':
        fromDate = new Date(now.getFullYear(), now.getMonth()-1, 1);
        toDate   = new Date(now.getFullYear(), now.getMonth(), 0);
        break;

        case '3_months':
        fromDate = new Date(now.getFullYear(), now.getMonth()-2, 1);
        toDate   = now;
        break;

        case '6_months':
        fromDate = new Date(now.getFullYear(), now.getMonth()-5, 1);
        toDate   = now;
        break;

        case 'this_year':
        fromDate = new Date(now.getFullYear(), 0, 1);
        toDate   = now;
        break;

        case 'last_year':
        fromDate = new Date(now.getFullYear() - 1, 0, 1);
        toDate   = new Date(now.getFullYear(), 0, 0);
        break;

        default:
        fromDate = new Date(now.getFullYear(), now.getMonth(), 1);
        toDate   = now;
        break;
    }

    //Code for format
    if( typeof fromDate === 'object' && fromDate instanceof Date ) {
        var y = fromDate.getFullYear() + '',
            m = fromDate.getMonth(),
            d = str_pad(fromDate.getDate(), 2);

        from_time =  '00:00:00';
        params.start_date_formatted = d + ' ' + sm.month_names_short[m] + ' ' + y.substring(2) + ' ' + from_time;
        params.start_date_default_format = y + '-' + str_pad((m+1), 2) + '-' + d + ' ' + from_time;
    }

    if( typeof toDate === 'object' && toDate instanceof Date ) {
        var y = toDate.getFullYear() + '',
            m = toDate.getMonth(),
            d = str_pad(toDate.getDate(), 2);

        to_time =  '23:59:59';
        params.end_date_formatted = d + ' ' + sm.month_names_short[m] + ' ' + y.substring(2) + ' ' + to_time;
        params.end_date_default_format = y + '-' + str_pad((m+1), 2) + '-' + d + ' ' + to_time;
    }

    var start_date = jQuery('.sm_date_range_container input.start-date').data('Zebra_DatePicker'),
        end_date = jQuery('.sm_date_range_container input.end-date').data('Zebra_DatePicker');

    if( typeof(start_date) != 'undefined') {
        start_date.set_date(params.start_date_formatted);
        start_date.update({'current_date': new Date(params.start_date_default_format)});    
    }

    if( typeof(end_date) != 'undefined') {
        end_date.set_date(params.end_date_formatted);
        end_date.update({'current_date': new Date(params.end_date_default_format)});
    }

    sm_handle_date_filter( params );

};

var sm_beta_hide_dialog = function(IDs, gID) {
    jQuery.jgrid.hideModal("#"+IDs.themodal,{gb:"#gbox_"+gID,jqm:true, onClose: null});
    index = 0;
}

//function to update background update progress
var sm_beta_background_updater_heartbeat = function( params ) {

  var background_process = (jQuery("#wpbody .sm_beta_pro_background_update_notice").length > 0) ? true : false; //for background process

  jQuery.ajax({
        type : 'POST',
        url : sm_ajax_url,
        dataType:"text",
        async: false,
        data: {
                    cmd: 'background_updater_heartbeat',
                    func_nm: params.func_nm,
                    active_module: sm.dashboard_key,
                    security: sm.sm_nonce,
                    background_process: background_process,
                    pro: true,
                    SM_IS_WOO30: SM_IS_WOO30
        },
        success: function(response) {
            response = JSON.parse(response);

            if( response.ack == 'Success' ) {
                //Code for updating the progressbar

                var per = response.per;

                if( background_process === false ) {
                    if( jQuery('#sm_beta_background_update_progressbar').html() == 'Intializing...' ) {
                        jQuery('#sm_beta_background_update_progressbar').html('');
                    }
                    jQuery('#sm_beta_background_update_progressbar').progressbar({ value: parseInt(per) }).children('.ui-progressbar-value').css({"background": "#BCBCBC"})
                    jQuery('#sm_beta_background_update_progressbar_text').html(Math.round(parseInt(per)) + '% Completed');
                }

                if( per < 100 ) {
                    setTimeout(function(){
                        sm_beta_background_updater_heartbeat(params);
                    }, 1000);
                } else {
                    setTimeout(function(){
                        if( background_process === false ) {
                            sm_beta_hide_dialog(params.IDs, params.gID);
                        }
                        location.reload();
                        // jQuery('#sm_editor_grid').trigger( 'reloadGrid' );
                    }, 1000);
                }
            }
        }
    });
}

//function to handle multiple search conditions
var smAddAdvancedSearchCondition = function() { 
    jQuery(function($){

        // old_count = count - 1;
        old_id_search_box = "sm_advanced_search_box_0";
        old_id_search_value = "sm_advanced_search_box_value_0";
        sm.search_count++;
        new_id_search_box = "sm_advanced_search_box_" + sm.search_count;
        new_id_search_value = "sm_advanced_search_box_value_" + sm.search_count;


        $("#sm_advanced_search_box").append($("#" + old_id_search_box).clone().attr('id', new_id_search_box));
        $("#sm_advanced_search_box").append($("#" + old_id_search_value).clone().attr({'id': new_id_search_value, 'name': new_id_search_value}));

        

        $("#" + new_id_search_box).empty();
        $("#" + new_id_search_value).val('');

        var visualsearch_params  = {
                                    el      : $("#"+new_id_search_box),
                                    placeholder: "Enter your search conditions here!",
                                    strict: false,
                                    search: function(json){

                                        // sm.search_query = JSON.parse(json);
                                        sm.search_query[sm.search_count] = json;
                                        $("#"+new_id_search_value).val(json);
                                    },
                                    parameters: col_model_search
                                };

        if( sm.search_query[sm.search_count] != '' && typeof(sm.search_query[sm.search_count]) != 'undefined' ) {
            visualsearch_params.defaultquery = JSON.parse(sm.search_query[sm.search_count]);
            $("#"+new_id_search_value).val(sm.search_query[sm.search_count]);
        }                            

        window.visualSearch = new VisualSearch(visualsearch_params);
    });
}



// ========================================================================
// EXPORT CSV
// ========================================================================

var generateCsvExport = function() {


    var post_data_params = {
                              cmd: 'get_export_csv',
                              active_module: sm.dashboard_key,
                              security: sm.sm_nonce,
                              pro: true,
                              SM_IS_WOO30: SM_IS_WOO30,
                              sort_params: (sm.dashboard_model.hasOwnProperty(sm.dashboard_key) && sm.dashboard_model[sm.dashboard_key].hasOwnProperty('sort_params') ) ? sm.dashboard_model[sm.dashboard_key].sort_params : '',
                              table_model: (sm.dashboard_model.hasOwnProperty(sm.dashboard_key) && sm.dashboard_model[sm.dashboard_key].hasOwnProperty('tables') ) ? sm.dashboard_model[sm.dashboard_key].tables : ''
                          };

    post_data_params['search_query[]'] = sm.search_query;

    window.location = sm_ajax_url + '&cmd='+ post_data_params['cmd'] +'&active_module='+ post_data_params['active_module'] +'&security='+ post_data_params['security'] +'&pro='+ post_data_params['pro'] +'&SM_IS_WOO30='+ post_data_params['SM_IS_WOO30'] +'&SM_IS_WOO30='+ post_data_params['SM_IS_WOO30'] +'&sort_params='+ encodeURIComponent(JSON.stringify(post_data_params['sort_params'])) +'&table_model='+ encodeURIComponent(JSON.stringify(post_data_params['table_model'])) +'&search_query[]='+ encodeURIComponent(sm.search_query);

}

// ========================================================================
// PRINT INVOICE
// ========================================================================

var printInvoice = function() {

    var grid = jQuery("#sm_editor_grid");
    var id_key = ( sm.dashboard_key == 'user' ) ? 'users_id' : 'posts_id';
    var row_ids = grid.jqGrid('getGridParam', 'selarrrow');
    var selected_rows = JSON.parse(JSON.stringify(row_ids));
    row_ids = new Array();
    for( var i in selected_rows ) {
        rowData = grid.getRowData(selected_rows[i]);
        row_ids[i] = rowData[id_key];
    }

    if ( row_ids.length <= 0 ) {
        return;
    }

    var post_data_params = {
                              cmd: 'get_print_invoice',
                              active_module: sm.dashboard_key,
                              security: sm.sm_nonce,
                              pro: true,
                              SM_IS_WOO30: SM_IS_WOO30,
                              sort_params: (sm.dashboard_model.hasOwnProperty(sm.dashboard_key) && sm.dashboard_model[sm.dashboard_key].hasOwnProperty('sort_params') ) ? sm.dashboard_model[sm.dashboard_key].sort_params : '',
                              table_model: (sm.dashboard_model.hasOwnProperty(sm.dashboard_key) && sm.dashboard_model[sm.dashboard_key].hasOwnProperty('tables') ) ? sm.dashboard_model[sm.dashboard_key].tables : '',
                              selected_ids: JSON.stringify(row_ids)
                          };

    post_data_params['search_query[]'] = sm.search_query;

    var url = sm_ajax_url + '&cmd='+ post_data_params['cmd'] +'&active_module='+ post_data_params['active_module'] +'&security='+ post_data_params['security'] +'&pro='+ post_data_params['pro'] +'&SM_IS_WOO30='+ post_data_params['SM_IS_WOO30'] +'&SM_IS_WOO30='+ post_data_params['SM_IS_WOO30'] +'&sort_params='+ encodeURIComponent(JSON.stringify(post_data_params['sort_params'])) +'&table_model='+ encodeURIComponent(JSON.stringify(post_data_params['table_model'])) +'&search_query[]='+ encodeURIComponent(sm.search_query) + '&selected_ids=' + post_data_params['selected_ids'];

    var win = window.open('', 'Invoice');

    jQuery.ajax({
        url: url,
        method: 'post',
        dataType: 'html',
        success: function( response ){
            win.document.write(response);
            win.document.close();
            win.print();
        }
    });

}

// ========================================================================
// DUPLICATE RECORDS
// ========================================================================

var processDuplicateRecords = function( btn_id ) {

    var grid = jQuery("#sm_editor_grid"),
            gID = grid[0].id,
            IDs = {
                themodal:'duprecordsmod_'+gID,
                modalhead:'duprecordshd_'+gID,
                modalcontent:'duprecordscnt_'+gID,
                scrollelm:'duprecordsTbl_'+gID
            },
            window_width = jQuery(window).width(),
            window_height = jQuery(window).height(),
            modal_width = 550,
            modal_height = 150,
            row_ids = grid.jqGrid('getGridParam', 'selarrrow'),
            cb_header_selected = jQuery("#jqgh_sm_editor_grid_cb input").is(':checked'),
            storewide_option = '',
            dup_records_progressbar = '<div id="sm_beta_background_update_progressbar" style="text-align:center;line-height:2.5em;height:2.5em;position:relative;margin:0 auto;width:98%;margin-top:2em;margin-bottom:2.5em;"> <span id="sm_beta_background_update_progressbar_text" style="color: #686868;position: absolute;width: 50%;left:25%;font-size:1.5em;font-weight:bold;" >Intializing...</span></div><div id="sm_beta_batch_update_background_link" style="text-align:right;cursor:pointer;text-decoration:underline;color:#0073aa;margin-right:0.5em;font-size:1.2em;">Continue in background</div>',
            hideDialog = function() {
                jQuery.jgrid.hideModal("#"+IDs.themodal,{gb:"#gbox_"+gID,jqm:true, onClose: null});
                index = 0;
            }


    if (jQuery('#'+IDs.themodal).length===0) {
        // dialog not yet exist. we need create it.

        jQuery.jgrid.createModal(
            IDs,
            dup_records_progressbar,
            {
                gbox: "#gbox_"+gID,
                caption: 'Duplicate Records',
                jqModal: true,
                left: ((window_width - modal_width)/2),
                top: ((window_height - modal_height)/2),
                overlay: 10,
                width: modal_width,
                // height: 'auto',
                height: modal_height,
                zIndex: 950,
                drag: false,
                resize: false,
                closeOnEscape: false,
                closeicon: false
            },
            "#gview_"+gID,
            jQuery("#gview_"+gID)[0]);
    }

    jQuery.jgrid.viewModal("#"+IDs.themodal,{gbox:"#gbox_"+gID,jqm:true, overlay: 10, modal:true});

    if( btn_id == 'sm_beta_dup_entire_store' ) {
        row_ids = new Array();
        storewide_option = 'entire_store';
    } else {

        var id_key = ( sm.dashboard_key == 'user' ) ? 'users_id' : 'posts_id';

        var selected_rows = JSON.parse(JSON.stringify(row_ids));
        row_ids = new Array();

        for( var i in selected_rows ) {
            rowData = grid.getRowData(selected_rows[i]);
            row_ids[i] = rowData[id_key];
        }
    }
    //Ajax request to duplicate the selected records
    jQuery.ajax({
            type : 'POST',
            url : sm_ajax_url,
            dataType:"text",
            async: false,
            data: {
                        cmd: 'duplicate_records',
                        active_module: sm.dashboard_key,
                        security: sm.sm_nonce,
                        pro: true,
                        storewide_option: storewide_option,
                        selected_ids: JSON.stringify(row_ids),
                        table_model: JSON.stringify(sm_store_table_model),
                        SM_IS_WOO30: SM_IS_WOO30,
                        SM_IS_WOO22: SM_IS_WOO22,
                        SM_IS_WOO21: SM_IS_WOO21
            },
            success: function(response) {
            }
        });

    var params = { 'func_nm':'duplicate_records', 'IDs':IDs, 'gID':gID };

    sm_beta_background_updater_heartbeat( params );
    
    jQuery(document).on('click','#sm_beta_batch_update_background_link',function() {
        jQuery("#batchcnt_"+gID).html(dup_records_progressbar); //replacing the content with batch update html
        sm_beta_hide_dialog(IDs, gID);

        jQuery('#batch_update_sm_editor_grid, .sm_beta_dropdown').addClass('ui-state-disabled'); //code to disable the batch update and dplicate records button

        if( jQuery("#wpbody .sm_beta_pro_background_update_notice").length == 0 ) {
            jQuery('<div id="sm_beta_pro_background_update_notice" class="notice notice-info sm_beta_pro_background_update_notice"><p><strong>Smart Manager Beta Duplicate Records</strong> – Your records are being duplicated in the background and you will be notified on your email<strong> <code>'+sm_admin_email+'</code> </strong>once the duplicate records process is completed.</p></div>').insertBefore('#wpbody .wrap');
        }

    });
};


// ========================================================================
// BATCH UPDATE
// ========================================================================

var createBatchUpdateDialog = function() {

        var grid = jQuery("#sm_editor_grid"),
            gID = grid[0].id,
            IDs = {
                themodal:'batchmod_'+gID,
                modalhead:'batchhd_'+gID,
                modalcontent:'batchcnt_'+gID,
                scrollelm:'BatchTbl_'+gID
            },
            window_width = jQuery(window).width(),
            window_height = jQuery(window).height(),
            modal_width = 640,
            modal_height = 175,
            row_ids = grid.jqGrid('getGridParam', 'selarrrow'),
            cb_header_selected = jQuery("#jqgh_sm_editor_grid_cb input").is(':checked'),
            entire_store_batch_update_html = '',
            batch_update_field_options = '<option value="" disabled selected>Select Field</option>'

            jQuery(document).trigger("sm_batch_update_columns",[column_names_batch_update]);

            if( cb_header_selected ) {
                entire_store_batch_update_html = "<tr>"+
                                                    "<td style='white-space: pre;'><input type='radio' name='batch_update_storewide' value='selected_ids' checked/>Selected Items</td>"+
                                                    "<td style='white-space: pre;'><input type='radio' name='batch_update_storewide' value='entire_store' />All Items In Store</td>"+
                                                "</tr>";
            }

            for (var key in column_names_batch_update) {
                batch_update_field_options += '<option value="'+key+'" data-type="'+column_names_batch_update[key].type+'">'+ column_names_batch_update[key].name +'</option>';
            }

            //Formating options for default actions
            sm.batch_update_action_options_default = '<option value="" disabled selected>Select Action</option>';
            sm.batch_update_action_options_default += '<option value="set_to">set to</option>';

            //Formating options for string actions
            var batch_update_action_options_string = '<option value="" disabled selected>Select Action</option>';
            var selected = '';
            for (var key in batch_update_action_string) {
                selected = '';
                if( key == 'set_to' ) {
                    selected = 'selected';
                }

                batch_update_action_options_string += '<option value="'+key+'" '+ selected +'>'+ batch_update_action_string[key] +'</option>';
            }

            //Formating options for string actions
            var batch_update_action_options_number = '<option value="" disabled selected>Select Action</option>';
            for (var key in batch_update_action_number) {
                selected = '';
                if( key == 'set_to' ) {
                    selected = 'selected';
                }
                batch_update_action_options_number += '<option value="'+key+'" '+selected+'>'+ batch_update_action_number[key] +'</option>';
            }




            // "<a href='javascript:void(0)' id='btn_batch_update' class='fm-button ui-state-default ui-corner-all'>Update</a>"+

        var batch_update_actions_row = "<td style='white-space: pre;'><select required id='batch_update_field' style='min-width:130px;width:auto !important;'>"+batch_update_field_options+"</select></td>"+
                                        "<td style='white-space: pre;'><select required id='batch_update_action' style='min-width:130px !important;'>"+sm.batch_update_action_options_default+"</select></td>"+
                                        "<td id='batch_update_value_td' style='white-space: pre;'><input type='text' id='batch_update_value' placeholder='Enter a value...' class='FormElement ui-widget-content'></td>"+
                                        "<td id='batch_update_add_delete_row' style='float:right;'><div class='dashicons dashicons-plus' style='color:#0073aa;cursor:pointer;line-height:1.7em;'></div><div class='dashicons dashicons-trash' style='color:#FF5B5E;cursor:pointer;line-height:1.5em;'></div></td>";

        var batch_update_dlg_content =
            "<form id='batch_update_form'>"+
                "<div id='"+IDs.scrollelm+"' class='formdata' style='width: 100%; overflow: auto; position: relative; height: auto;'>"+
                    "<table class='batch_update_table' width='100%'>"+
                        "<tbody>"+
                            entire_store_batch_update_html +
                            "<tr id='batch_update_action_row_0'>"+
                                batch_update_actions_row+
                            "</tr>"+
                            "<tr>"+
                                "<td>&#160;</td>"+
                            "</tr>"+
                        "</tbody>"+
                    "</table>"+
                "</div>"+
                "<table cellspacing='0' cellpadding='0' border='0' class='EditTable' id='"+IDs.scrollelm+"_2'>"+
                    "<tbody>"+
                        "<tr>"+
                            "<td>"+
                                "<hr class='ui-widget-content' style='margin: 1px' />"+
                            "</td>"+
                        "</tr>"+
                        "<tr>"+
                            "<td class='DelButton EditButton'>"+
                                "<input type='submit' id='btn_batch_update' class='fm-button ui-state-default ui-corner-all' value='Update'>"+
                            "</td>"+
                        "</tr>"+
                    "</tbody>"+
                "</table>"
            "</form>";

        if (jQuery('#'+IDs.themodal).length===0) {
            // dialog not yet exist. we need create it.

            jQuery.jgrid.createModal(
                IDs,
                batch_update_dlg_content,
                {
                    gbox: "#gbox_"+gID,
                    caption: 'Batch Update',
                    jqModal: true,
                    left: ((window_width - modal_width)/2),
                    top: ((window_height - modal_height)/2),
                    overlay: 10,
                    width: modal_width,
                    // height: 'auto',
                    height: modal_height,
                    zIndex: 950,
                    drag: false,
                    resize: true,
                    closeOnEscape: true,
                    closeicon: [false,'right','ui-icon-close'],
                    onClose: hideDialog
                },
                "#gview_"+gID,
                jQuery("#gview_"+gID)[0]);
        }

        jQuery.jgrid.viewModal("#"+IDs.themodal,{gbox:"#gbox_"+gID,jqm:true, overlay: 10, modal:false});

        jQuery('#BatchTbl_sm_editor_grid').css('height','9em');

        jQuery('#batch_update_field, #batch_update_action').each(function() {
            jQuery(this).select2({ width: '15em', dropdownCssClass: 'sm_beta_batch_update_field' });
        })

        jQuery("#batch_update_action_row_0").find('#batch_update_add_delete_row .dashicons-trash').hide(); //for hiding the delete icon for the first row

        //function for handling add row in batch update dialog
        jQuery(document).off('click').on('click','#batch_update_add_delete_row .dashicons-plus', function() {
            var count = jQuery('tr[id^=batch_update_action_row_]').length,
                current_id = 'batch_update_action_row_'+count;
            jQuery('.batch_update_table tr:last').before("<tr id="+current_id+">"+ batch_update_actions_row +"</tr>");

            jQuery("#"+current_id).find('#batch_update_field, #batch_update_action').select2({ width: '15em', dropdownCssClass: 'sm_beta_batch_update_field' });

            jQuery(this).hide();

        });

        //function for handling delete row in batch update dialog
        jQuery(document).on('click','#batch_update_add_delete_row .dashicons-trash', function() {

            var add_row_visible = jQuery(this).closest('td').find('.dashicons-plus').is(":visible");
            jQuery(this).closest('tr').remove();

            if( add_row_visible === true ) { //condition for removing plus icon only if visible
                jQuery('tr[id^=batch_update_action_row_]:last()').find('.dashicons-plus').show();
            }

        });

        // For the time now
        Date.prototype.timeNow = function () {
             return ((this.getHours() < 10)?"0":"") + this.getHours() +":"+ ((this.getMinutes() < 10)?"0":"") + this.getMinutes() +":"+ ((this.getSeconds() < 10)?"0":"") + this.getSeconds();
        }

        jQuery(document).on('change','#batch_update_field',function(){

            var row_id = jQuery(this).closest('tr').attr('id');

            var selected_field = jQuery( "#"+row_id+" #batch_update_field option:selected" ).val(),
                type = column_names_batch_update[selected_field].type,
                col_val = column_names_batch_update[selected_field].values;
                
            sm.skip_default_action = false;

            //Formating options for default actions
            sm.batch_update_action_options_default = '<option value="" disabled selected>Select Action</option>';
            sm.batch_update_action_options_default += '<option value="set_to">set to</option>';

            jQuery(document).trigger("sm_batch_update_field_on_change",[row_id, selected_field, type, col_val]);

            if( sm.skip_default_action === true ) {
                return;
            }

            if (type == 'number') {
                jQuery("#"+row_id+" #batch_update_action").empty().append(batch_update_action_options_number);
            } else if (type == 'string' || type == 'longstring') {
                jQuery("#"+row_id+" #batch_update_action").empty().append(batch_update_action_options_string);
            } else {
                jQuery("#"+row_id+" #batch_update_action").empty().append(sm.batch_update_action_options_default);
                jQuery("#"+row_id+" #batch_update_action").find('[value="set_to"]').attr('selected','selected');
            }

            jQuery("#"+row_id+" #batch_update_value").val('');

            jQuery("#"+row_id+" #batch_update_value_td").empty().append('<input type="text" id="batch_update_value" placeholder="Enter a value..." class="FormElement ui-widget-content" >');

            if (type == 'date' || type == 'datetime') {
                var placeholder = (type == 'datetime') ? 'YYYY-MM-DD HH:MM:SS' : 'YYYY-MM-DD';
                jQuery("#"+row_id+" #batch_update_value").attr('placeholder',placeholder);
                jQuery("#"+row_id+" #batch_update_value").datepicker({ dateFormat: 'yy-mm-dd' });
            } else {
                jQuery("#"+row_id+" #batch_update_value").attr('placeholder','Enter a value...');
                jQuery("#"+row_id+" #batch_update_value").datepicker('destroy');
            }

            if( type == 'datetime' ) { //code to handle the time in datetime type values
                jQuery("#batch_update_value").on("keyup change", function(){
                    var newDate = new Date();
                    var date = jQuery(this).val();
                    jQuery("#batch_update_value").val(date + " " + newDate.timeNow());
                });
            }

            if(type == 'toggle') {
                jQuery("#"+row_id+" #batch_update_value_td").empty().append('<select id="batch_update_value" style="min-width:130px !important;">'+
                                                            '<option value="yes"> Yes </option>'+
                                                            '<option value="no"> No </option>'+
                                                        '</select>')
                jQuery("#"+row_id+" #batch_update_value_td").find("#batch_update_value").select2({ width: '15em', dropdownCssClass: 'sm_beta_batch_update_field' });
            } else if (col_val != '' && type == 'list') {
                
                var batch_update_value_options = '<select id="batch_update_value" style="min-width:130px !important;">',
                    value_options_empty = true;

                for (var key in col_val) {
                    if( typeof (col_val[key]) != 'object' && typeof (col_val[key]) != 'Array' ) {
                        value_options_empty = false;
                        batch_update_value_options += '<option value="'+key+'">'+ col_val[key] + '</option>';
                    }
                }

                batch_update_value_options += '</select>';

                if( value_options_empty === false ) {
                    jQuery("#"+row_id+" #batch_update_value_td").empty().append(batch_update_value_options)
                    jQuery("#"+row_id+" #batch_update_value_td").find('#batch_update_value').select2({ width: '15em', dropdownCssClass: 'sm_beta_batch_update_field' });    
                }

            } else if (type == 'longtext') {
                jQuery("#"+row_id+" #batch_update_value_td").empty().append('<textarea id="batch_update_value" placeholder="Enter a value..." class="FormElement ui-widget-content"> </textarea>');
            }

        });

        jQuery(document).on('submit', '#batch_update_form', function(event){
            event.preventDefault();

            // var batch_update_actions = new Array();
            var batch_update_html = jQuery("#batchcnt_"+gID).html();

            //getting the batch update form data
            jQuery('tr[id^=batch_update_action_row_]').each(function() {

                var row_id = jQuery(this).attr('id'),
                    field_nm = jQuery(this).find('#batch_update_field').val(),
                    field_type = jQuery(this).find('option:selected','#batch_update_field').attr('data-type'),
                    field_display_text = jQuery("option:selected","#"+row_id+" #batch_update_field").text(),
                    action_display_text = jQuery("option:selected","#"+row_id+" #batch_update_action").text(),
                    value_display_text = jQuery("option:selected","#"+row_id+" #batch_update_value").text(),
                    action = jQuery(this).find('#batch_update_action').val(),
                    value = jQuery(this).find('#batch_update_value').val(),
                    table_nm = '',
                    col_nm = '';

                    if( typeof (field_nm) != 'undefined' ) {
                        field_params = field_nm.split("/");
                        if( field_params.length > 0 ) {

                            table_nm = field_params[0];

                            if( field_params.length > 2 ) {
                                field_meta = field_params[1].split("=");
                                col_nm = field_meta[1];
                            } else {
                                col_nm = field_params[1];
                            }
                        }    
                    }

                sm.batch_update_actions.push({'table_nm' : table_nm, 'col_nm' : col_nm, 'action' : action, 'value' : value, 'type' : field_type, 'field_display_text' : field_display_text, 'action_display_text' : action_display_text, 'value_display_text' : value_display_text });
            });

            jQuery(document).trigger("sm_batch_update_on_submit"); //trigger to make changes in batch_update_actions

            var storewide_option = jQuery('input[name=batch_update_storewide]:checked').val();

            if( storewide_option == 'entire_store' ) {
                row_ids = new Array();
            } else {

                var id_key = ( sm.dashboard_key == 'user' ) ? 'users_id' : 'posts_id';

                var selected_rows = JSON.parse(JSON.stringify(row_ids));
                row_ids = new Array();

                for( var i in selected_rows ) {
                    rowData = grid.getRowData(selected_rows[i]);
                    row_ids[i] = rowData[id_key];
                }
            }
            //Ajax request to batch update the selected records
            jQuery.ajax({
                    type : 'POST',
                    url : sm_ajax_url,
                    dataType:"text",
                    async: false,
                    data: {
                                cmd: 'batch_update',
                                active_module: sm.dashboard_key,
                                security: sm.sm_nonce,
                                pro: true,
                                storewide_option: storewide_option,
                                batch_update_actions: JSON.stringify(sm.batch_update_actions),
                                selected_ids: JSON.stringify(row_ids),
                                table_model: JSON.stringify(sm_store_table_model),
                                SM_IS_WOO30: SM_IS_WOO30
                    },
                    success: function(response) {
                        jQuery("#batchcnt_"+gID).html('<div id="sm_beta_background_update_progressbar" style="text-align:center;line-height:2.5em;height:2.5em;position:relative;margin:0 auto;width:98%;margin-top:2em;margin-bottom:2.5em;"> <span id="sm_beta_background_update_progressbar_text" style="color: #686868;position: absolute;width: 50%;left:25%;font-size:1.5em;font-weight:bold;" >Intializing...</span></div><div id="sm_beta_batch_update_background_link" style="text-align:right;cursor:pointer;text-decoration:underline;color:#0073aa;margin-right:0.5em;font-size:1.2em;">Continue in background</div>');
                    }
                });

            var params = { 'func_nm':'batch_update', 'IDs':IDs, 'gID':gID };

            sm_beta_background_updater_heartbeat( params );
            
            jQuery(document).on('click','#sm_beta_batch_update_background_link',function() {
                jQuery("#batchcnt_"+gID).html(batch_update_dlg_content); //replacing the content with batch update html
                sm_beta_hide_dialog(IDs, gID);

                jQuery('#batch_update_sm_editor_grid, .sm_beta_dropdown').addClass('ui-state-disabled'); //code to disable the batch update and dplicate records button

                if( jQuery("#wpbody .sm_beta_pro_background_update_notice").length == 0 ) {
                    jQuery('<div id="sm_beta_pro_background_update_notice" class="notice notice-info sm_beta_pro_background_update_notice"><p><strong>Smart Manager Beta Batch Update</strong> – Your records are being updated in the background and you will be notified on your email<strong> <code>'+sm_admin_email+'</code> </strong>once the batch update is completed.</p></div>').insertBefore('#wpbody .wrap');
                }

            });


        });
    };

// ========================================================================