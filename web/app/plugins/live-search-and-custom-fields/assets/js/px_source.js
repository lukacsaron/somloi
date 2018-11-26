
if ( getCookie('px_new_custom_post') ) {
	var cookieAddedNewCustomPost = getCookie('px_new_custom_post');
	if ( cookieAddedNewCustomPost == 1 ) {
		alert("A new Custom Post Type was added!\nPlease reset your Permalinks to avoid 404 on new created custom post type");
	}
	document.cookie = 'px_new_custom_post=0';

}

var $j = jQuery;
$j(document).ready(function(){
    
	var filterCallbacks = new ajaxCallbacks();
		
    var adminPanel = new PX_pluginLF();
		
		if ( document.URL.match( /plugin-tab=filter-generator&edit_filter=[0-0a-zA-Z]+/ ) ) {
			var matches = document.URL.match( /plugin-tab=filter-generator&edit_filter=([0-9a-zA-Z_-]+)/ );
			adminPanel.edit_shortcode.filterID = matches[ 1 ];
		};

		filterCallbacks.template = adminPanel.template;
		adminPanel.callbacks = filterCallbacks;

		adminPanel.init();
    
	var wpMedia = new mediaLibrary();

    var customFields = new PX_customFields();
		customFields.wpMediaLibrary = wpMedia;
		customFields.init();

	var GeneralSettings = new LscfSettings();
	GeneralSettings.init();
	GeneralSettings.saveSettings();

	LscfWooCommerce.prototype.init_tax_subcategories = function(){			
		return filterCallbacks.init_tax_subcategories();
	}

	$j('.initCalendar').each(function(){
		
		var alternativeFormatClassname = $j(this).attr('data-alternative');

		$j(this).datepicker({
			altField: '.' + alternativeFormatClassname,
			altFormat: 'mm/dd/yy',
		})
	})

    $j(".initCalendar").datepicker();
	$j(".lscf-colorpick").colorPicker();
    
});

var isStep2Active = new is_step2_active();
function is_step2_active() {
    
    var $j = jQuery,
        called = false;
    
    return function () {
        
        if (!called) {

            called = true;
            
            $j('.step2 input[type="checkbox"]').each(function () {
        
                $j(this).click(function () {

                    $j('.step2 input[type="checkbox"]').each(function () {
                        
                        var isChecked = ($j(this).attr("checked") ? 1 : 0);
                        
                        $j(".step2").addClass("active");

                        if (isChecked == 1) return false;
                        
                    })
                })
            });    
        }
        
    }
    
}

var LscfWooComm = new LscfWooCommerce();

function LscfWooCommerce(){

	var $j =jQuery,
		self = this,
		plugin_url = adminData.lscf_url

	this.template = {
		"type":"woocommerce",
		"tax":{
			"headline":"WooCommerce data filter",
			"items":[],
			"templateUrl":plugin_url + 'assets/js/templates/filter/tax-woocommerce-template.html',
			"display_as_set_active":function() {
				return function( data, render ) {

					var value = render( data ).split('__'),
						activeValue = value[0],
						optionValue = value[1];

					if ( activeValue == optionValue ) {
						return "selected";
					}

				}
			},
			"set_checkbox_as_active":function() {
				return function( status, render ){

					if ( '1' == render( status )  ) {
						return "checked";
					}
				}
			}
		},
	}

	this.init_tax_subcategories;

	this.set_the_active_tax_terms = function( filter_shortcode_data ) {


		if ( '' == filter_shortcode_data || null == filter_shortcode_data.filterID ) { return; }

		for ( var i = 0; i < self.template.tax.items.length; i++ ) {

			for ( var k = 0; k < filter_shortcode_data.data.fields.length; k++ ) {

				if ('taxonomies' ==  filter_shortcode_data.data.fields[ k ].group_type ) {

					var tax_item = self.template.tax.items[ i ],
						shortcode_field = filter_shortcode_data.data.fields[ k ],
						shortcode_slug = shortcode_field.slug.split('_-_');
					
					shortcode_slug = shortcode_slug[0];


					if ( tax_item.taxonomy == shortcode_slug ) {

						tax_item.title = shortcode_field.name;
						tax_item.display_as = shortcode_field.display_as;
						
						if ( 'undefined' !== typeof shortcode_field.subcategories_hierarchy_display ) {
							tax_item.subcategories_hierarchy_display = shortcode_field.subcategories_hierarchy_display;
						}
						if ( 'undefined' !== typeof shortcode_field.display_all_terms ) {
							tax_item.display_all_terms = shortcode_field.display_all_terms;
						}
						if ( 'undefined' !== typeof shortcode_field.display_parent_categs_as_filters ) {
							tax_item.display_parent_categs_as_filters = shortcode_field.display_parent_categs_as_filters;
						} else {
							tax_item.display_parent_categs_as_filters = 0;
						}

						for ( var c = 0; c < tax_item.categs.length; c++ ) {
							
							if ( 'undefined' !== typeof shortcode_field.terms_list[ tax_item.categs[ c ].data.term_id ]  ) {
								
								tax_item.categs[ c ].data.active = 1;

							}


							if ( 'undefined' !== typeof tax_item.categs[ c ].subcategs ) {

								if ( 'undefined' !== typeof shortcode_field.subcategs_parent_id ) {

									if ( shortcode_field.subcategs_parent_id == tax_item.categs[ c ].subcategs.parent_id ) {
										
										tax_item.categs[ c ].subcategs.active = 1;
										tax_item.categs[ c ].subcategs.display_as = shortcode_field.display_as;
									}
								} else if ( 'undefined' !== typeof shortcode_field.subcategories_hierarchy_display && 1 === shortcode_field.subcategories_hierarchy_display ) {
							
									if ( 'undefined' !== typeof shortcode_field.terms_list[ tax_item.categs[ c ].subcategs.parent_id ] ) {
										
										tax_item.categs[ c ].subcategs.active = 1;
										tax_item.categs[ c ].subcategs.display_as = shortcode_field.terms_list[ tax_item.categs[ c ].subcategs.parent_id ].display_as;
									}
								}
							}
						}

						for ( var subcateg_id in tax_item.subcategs ) {
							
							for ( var cs = 0; cs < tax_item.subcategs[ subcateg_id ].length; cs ++ ) {
								
								var term_id = tax_item.subcategs[ subcateg_id ][ cs ].term_id;

								if ( 'undefined' !== typeof shortcode_field.terms_list[ term_id ] ) {
									tax_item.subcategs[ subcateg_id ][ cs ].active = 1;
								}
							}
						}


						for ( var cs = 0; cs < tax_item.unsorted_subcategories.length; cs ++ ) {
							var term_id = tax_item.unsorted_subcategories[ cs ].term_id;

							if ( 'undefined' !== typeof shortcode_field.terms_list[ term_id ] ) {
								tax_item.unsorted_subcategories[ cs ].active = 1;
							}
						}

					}

					self.template.tax.items[ i ] = tax_item;
				}
			}

		}


	};

	this.callbacks = {};

	this.callbacks.getPostTaxonomies = function( data, filter_shortcode_data ){

		var categsTemplate = '<h4>WooCommerce data filter </h4>';
        var count = 0;

		self.template.tax.items = [];

        $j("#px_post_categories").html(categsTemplate);

        data.forEach(function (tax) {
			
			var skip = true;
			
			if ( 'undefined' !== typeof tax.parent_categs ) {
			
				var taxTemplate = {};
				taxTemplate.index = count;
				taxTemplate.name = tax.taxonomy.replace('pa_', '').replace( '_', ' ' );
				taxTemplate.taxonomy = tax.taxonomy;
				taxTemplate.unsorted_subcategories = tax.unsorted_subcategs;
				taxTemplate.hasThumb = 0;

				switch ( tax.taxonomy ) {

					default:

						taxTemplate.type = 'attribute';

						if ( tax.taxonomy.match(/^pa_(.*)/) ) {
							
							skip = false;
							taxTemplate.hasThumb = 1;

							var attributeType = tax.taxonomy.match(/^pa_(.*)/);
							var title = attributeType[1].replace( /\b\w/g, function(l){ return l.toUpperCase() } );
							var headline = '<label>Filter by</label> Product Attributes - <strong>' + title + '</strong></span><br/>';

						}
						
						break;

					case 'product_type':
						skip = false;
						var title = 'Product Types';
						var headline = '<label>Filter by</label> <strong>Product Type</strong></span><br/>';

					break;

					case 'product_cat':
						skip = false;
						var title = 'Product Categories';
						var headline = '<label>Filter by</label> <strong>Product Categories</strong></span><br/>';

					break;

					case 'product_tag':
						skip = false;
						var title = 'Product Tags';
						var headline = '<label>Filter by</label> <strong>Product Tags</strong></span><br/>';

					break;

				}

				if ( false == skip  ) {
					
					taxTemplate.headline = headline;
					taxTemplate.title = title;
					taxTemplate.categs = [];
					
					if ( 'undefined' !== typeof tax.parent_categs && null !== tax.parent_categs  ) {

					
						
						tax.parent_categs.forEach(function(categ, index){
						
							var className = '';
							
							if ( 'undefined' !== typeof categ.has_subcategories && 1 === categ.has_subcategories && 'undefined' !== typeof categ.data ) {
								
								className = 'has-subcategs';
								categ.subcategs = {
									"parent_name": categ.data.name,
									"parent_id":categ.data.term_id,
									"list":"",
									"items":[]
								};

								var j = 0;
								tax.subcategs[categ.data.term_id].forEach(function(subcateg){
									
									if ( j <= 5 ) {
										categ.subcategs.list += subcateg.name + ', ';
									}
									categ.subcategs.items.push( subcateg );

									j++;
								});

								categ.subcategs.list = categ.subcategs.list.substring( 0, categ.subcategs.list.length -2  ) + '..';

							} else {
								categ.subcategs = false;
							};

							categ.className = className;
							taxTemplate.categs.push(categ);
						});

						taxTemplate.has_subcategories = ( 'undefined' !== typeof tax.has_subcategories ? tax.has_subcategories : 0 );

						taxTemplate.subcategs = ( 'undefined' !== typeof tax.subcategs ? tax.subcategs : '' );

						self.template.tax.items.push( taxTemplate );

						count++;
					}
				}
				
			}

		});

		$j.get( self.template.tax.templateUrl, function( template ) {
			

			self.set_the_active_tax_terms( filter_shortcode_data );

			var renderedTemplate = Mustache_2.render( $j( template ).filter('#template-tax-posts').html(), self.template.tax );

			$j("#px_post_categories").html( renderedTemplate );

			for ( var i = 0; i < count; i++ ) {
				checkAllCheckboxes( $j( '.px-post-tax' + i + ' .tax-categories-container' ) );
				checkAllCheckboxes( $j( '.px-post-tax' + i + ' .tax-subcategories-display' ), true );
			}

			setTimeout(function(){
				isStep2Active();
			}, 400);

			init_custom_dropdown( $j('.post-categories-group') );
			
			setTimeout(function(){

				self.init_tax_subcategories();
				lscf_taxonomies_subcategories_view_type();

				lscf_display_all_tax_terms();

				if ( null !== filter_shortcode_data.filterID ){

					lscf_taxonomies_subcategories_view_type_active();
				}

			}, 400);

			setTimeout(function(){

				if ( null !== filter_shortcode_data.filterID ){

					lscf_taxonomies_subcategories_view_type_active();
				}

			}, 4000);

		});

		
		if (count == 0)
            $j(".post-categories-group .expandable-container-headline").addClass("inactive");
        else 
            $j(".post-categories-group .expandable-container-headline").removeClass("inactive");

	}
	
	return {
		"callbacks":self.callbacks
	}

}

function PX_pluginLF(){
    
    var self = this,
        action = "px-plugin-ajax",
        ajaxURL = adminData.ajaxURL;

	this.edit_shortcode = {
		filterID:null,
		data:null
	};

	this.postType = '';

	this.callbacks;

	this.template = {
		"tax":{
			"items":[],
			"templateUrl" : adminData.lscf_url + 'assets/js/templates/filter/tax-template.html',
			"display_as_set_active":function() {
				return function( data, render ) {

					var value = render( data ).split('__'),
						activeValue = value[0],
						optionValue = value[1];

					if ( activeValue == optionValue ) {
						return "selected";
					}

				}
			},
			"set_checkbox_as_active":function() {
				return function( status, render ){

					if ( '1' == render( status )  ) {
						return "checked";
					}
				}
			}
		},
		"tax_subcategs":{
			"items":{},
			"templateUrl" : adminData.lscf_url + 'assets/js/templates/filter/tax-subcategs-template.html',
			"set_checkbox_as_active":function(){
				return function( status, render ){
					if ( '1' == render( status )  ) {
						return "checked";
					}
				}
			}
		},
		"custom_fields":{
			"items":{},
			"templateUrl" : adminData.lscf_url + 'assets/js/templates/filter/custom-fields-template.html',
			"set_checkbox_as_active":function() {
				return function( status, render ){

					if ( '1' == render( status )  ) {
						return "checked";
					}
				}
			},
			"display_as_set_active":function() {
				return function( data, render ) {

					var value = render( data ).split('__'),
						activeValue = value[0],
						optionValue = value[1];

					if ( activeValue == optionValue ) {
						return "selected";
					}

				}
			}
		},
		"featured_fields":{
			
			"templateUrl" : adminData.lscf_url + 'assets/js/templates/filter/featured-fields-template.html',
			"set_as_active":function(){
				return function( status, render ) {

					if ( 1 === parseInt( render( status ) ) ) {
						return "checked";
					}
				}
			},

		},
		"additional_fields":{
			"range":{
				"templateUrl" : adminData.lscf_url + 'assets/js/templates/filter/af-range-template.html'
			},
			"search":{
				"templateUrl" : adminData.lscf_url + 'assets/js/templates/filter/af-search-template.html'
			},
			"date_interval":{
				"templateUrl" : adminData.lscf_url + 'assets/js/templates/filter/af-date-interval-template.html'
			},
			"text_fields":{
				"items":[]
			},
			"date_fields":{
				"items":[]
			},
			"set_as_active":function(){
				return function( data, render ) {
					var IDs = render( data ).split("___");
					if ( IDs[0] == IDs[1] ) {
						return "checked";
					}
				}
			},
			"templateUrl" : adminData.lscf_url + 'assets/js/templates/filter/af-template.html',
			"items":[]
		},
		
		"date_fields" : {
			"items":null
		},
		
		"text_fields" : {
			"items":null
		},
		
	}

	this.isWooCommerce = false;

	this.template.get_text_custom_fields = function( postType, callback ) {

		self.getCustomFieldsByPostType( postType, ["number"], function( data ) {

			if ( data.success == 1 && typeof data.data.data.fields !== "undefined" ) {
				self.template.text_fields.items = data.data.data.fields;
			}

			if ( 'undefined' !== typeof callback ) {
				callback();
			}

		});

	};

	this.template.get_date_custom_fields = function( postType, callback ){

		self.getCustomFieldsByPostType( postType, ["date"], function( data ) {

			if ( data.success == 1 && typeof data.data.data.fields !== "undefined" ) {
				self.template.date_fields.items = data.data.data.fields;
			}

			if ( 'undefined' !== typeof callback ) {
				callback();
			}
		});

	};

    this.init = function(){

        self.removeAdditionalFilterFields(); 
        self.sanitizeAdditionalFilterFieldsName();

		self.retrievefilterShortcodeData( function(){
			
			var loaded_data = {
				"date_fields":false,
				"text_fields":false
			}
			
			self.postType =  self.edit_shortcode.data.post_type;

			if ( null !== self.edit_shortcode.data.filterID && 'product' == self.edit_shortcode.data.post_type  ){
				
				self.getPostCategories( self.edit_shortcode.data.post_type, LscfWooComm.callbacks.getPostTaxonomies );

				self.isWooCommerce = true;
				self.callbacks.isWooCommerce = self.isWooCommerce;

				$j('#px-filter-for').val('woocommerce');

			} else {
				self.getPostCategories( self.edit_shortcode.data.post_type, self.callbacks.get_post_categories_callback );	
				$j('#px-filter-for').val('custom-posts');
			}

			self.template.get_date_custom_fields( self.edit_shortcode.data.post_type, function(){
				loaded_data.date_fields = true;
				self.load_additional_fields_template( loaded_data );
			});

			self.template.get_text_custom_fields( self.edit_shortcode.data.post_type, function() {	
				loaded_data.text_fields = true;
				self.load_additional_fields_template( loaded_data);
			});

			self.load_featured_fields_template( self.edit_shortcode.data.post_type );
			self.generate_active_additional_fields();
			self.getCustomFieldsByPostType( self.edit_shortcode.data.post_type, null, self.callbacks.getCustomFieldsByPostType );

			

		});

		// Bind Function for Filter's additional fields 
        self.addAdditionalFields();   

		$j('#px_filter-name-shorcode-generator').keydown(function(event){
			if ( event.keyCode == 13 ) {
				event.preventDefault();
				return false;
			}
		});
	

		var filterNameInputElement = document.getElementById("px_filter-name-shorcode-generator");
		if ( null !== filterNameInputElement && filterNameInputElement.length > 0 ) {
		 
			document.getElementById("px_filter-name-shorcode-generator").onkeydown = function(event){
				
				if ( 13 == event.keyCode ) {
					
					if ( $j("#px_filter-name-shorcode-generator").val() === '' ) {
					
						$j("#px_filter_name_error").fadeIn();

						return false;
					}
					$j("#goToFilterFields").hide();
					$j("#px_filter_name_error").hide();
					$j(".lscf-step-2").fadeIn();
					$j('#active-shortcodes-list').hide();
				}
			}
		}

        $j("#goToFilterFields").click(function () {

			if ($j("#px_filter-name-shorcode-generator").val() === '') {
                
                $j("#px_filter_name_error").fadeIn();

                return false;
            }

			$j("#px_filter_name_error").hide();
            $j(this).hide();
            $j(".lscf-step-2").fadeIn();
            $j('#active-shortcodes-list').hide();

        });

		$j('.cf-remove-custom-post').each(function(){
	
			$j(this).click(function(event){

				event.stopPropagation();
				event.preventDefault();

				var parentRow = $j(this).closest(".px-post-type-row");
				var customPost_key = $j(this).closest(".px-post-type-row").data("key");
				var confirmDelete = confirm("Are you sure you want to remove the custom post type?");

				if( true === confirmDelete ) {

					self.removeCustomPostType_ajaxRequest( customPost_key, function(){
						parentRow.remove();
					});

				}


			});

		});

		$j(".remove-custom-post").each(function (index){
			
			$j(this).click(function (){
				
				var data = $j(this).parent().data("key");
				
				self.removeCustomPostType_ajaxRequest( data, function(){
					window.location.reload();
				});
				
			})
			
		});

		$j("#create-new-custom-post").click(function () {
			
			var name = $j("#customPostName").val();
			
			if (typeof name !== 'undefined' && name!='') {
				
				self.addNewCustomPostType_ajaxRequest(name);    
				
			}
			
		});

		$j(".expandable-container-headline").each(function (c) {
			
			$j(this).click(function () {
				
				if ($j(this).hasClass("inactive")) return;
				
				
				if ($j(this).hasClass("active"))
					$j(this).removeClass("active");

				else
					$j(this).addClass("active");

				
				if ($j(".expandable-container").eq(c).hasClass("active")) {
					
					$j(".expandable-container").eq(c).css({ 'min-height': '0px'});
					
					$j(".expandable-container").eq(c).animate({
						height: "0px"
					}, 300, function () {
						$j(".expandable-container").eq(c).removeClass("active");    
					});

				}
					
				else {

					$j(".expandable-container").eq(c).addClass("active");
					
					var heightToAnimate = $j(".expandable-container").eq(c).find(".data-container").height();

					heightToAnimate = ( heightToAnimate < 170 ? '180' : heightToAnimate );

					$j(".expandable-container").eq(c).animate({
						height: Math.round(heightToAnimate)
					}, 300, function () {

						$j(".expandable-container").eq(c).css({"min-height":heightToAnimate+"px", "height":"auto"});
						
						if ( $j('.expandable-container').eq(c).hasClass('filter-display-settings') && ! $j('.expandable-container').eq(c).hasClass('slick-active') ) {
							
							$j('.expandable-container').eq(c).addClass('slick-active')
							
							setTimeout(function(){
								$j(".theme-options-slider").slick({
									infinite:false
								});
							}, 200);	
							
						}

					});

				}

			});
		})

		setTimeout(function () {
			
			$j(".px_lf_post-type").find('.pxselect-options li').each(function () {
				
				$j(this).click(function () {
					
					var val = $j(this).attr("rel");
					var postName = $j(this).text();
					
					if( 'Products(WooCommerce)' === postName || 'product' == val ) {
						$j('#px-filter-for').val('woocommerce');
						self.isWooCommerce = true;
					} else {
						$j('#px-filter-for').val('custom-posts');
						self.isWooCommerce = false;
					}

					self.callbacks.isWooCommerce = self.isWooCommerce;

					$j("#px_additional-fields-container").empty();

					if (val != 0)
						$j(".step1").addClass("active");
					else
						$j(".step1").removeClass("active");

					self.postType = val;

                    // get custom fields
                    self.getCustomFieldsByPostType( val, null, self.callbacks.getCustomFieldsByPostType );

                    if ( true === self.isWooCommerce ) {
						$j( '.post-categories-group .expandable-container-headline' ).html('WooCommerce');
						// get post taxonomies and categories
						self.getPostCategories( val, LscfWooComm.callbacks.getPostTaxonomies );

					} else {
						$j( '.post-categories-group .expandable-container-headline' ).html('Categories');

						// get post taxonomies and categories
						self.getPostCategories(val, self.callbacks.get_post_categories_callback);

					}
				    
					// get featured labels
					self.load_featured_fields_template( val );

				});
			});
		}, 700);

		$j("#pxcf_generate-shortcode").click(function () {
		

			if ( $j( this ).hasClass('inactive') ) return false;

			$j("#lscf-shortcode-generated-message").hide();
			$j("#lscf-saving-shorcode-message").fadeIn();
			
			$j(this).addClass('inactive');

			var dataFields = $j("#generateshortcode-form").serializeArray(),
				editShortcode = ( 'edit' == $j(this).attr('data-type') ? true : false ),
				filterID = ( true === editShortcode ? $j(this).attr('data-filter-id') : null ),
				shortcodeButton = $j(this);
			
			if ( $j('.lscf-custom-theme:checked').length > 0 ){
				
				var customTemplateUrl = $j('.lscf-custom-theme:checked').attr('data-url'),
					name = $j('.lscf-custom-theme:checked').attr('data-name');

				dataFields.push({
					"name":"filter-custom-theme-url",
					"value":customTemplateUrl
				});
				
				dataFields.push({
					"name":"filter-custom-theme-name",
					"value":name
				});
			}

			var actionData = {
				"postType":self.postType,
				"editShortcode":editShortcode,
				"filterID":filterID
			};

			var attr = $j('.colorpick-rgba').attr('style');

			var matches = attr.match(/background-color\:(.+?);/);
			var mainRgbColor = matches[1].replace(/rgb\(|\)/g, '');

			
			if (dataFields.length > 0) {

				if (dataFields[0].value == null || dataFields[0].value == '') {
					
					$j("#px_filter_name_error").fadeIn();
					return;
					
				}

				dataFields.push( {"name":"filter-main-color-rgb", "value":mainRgbColor} );

				self.generateShortCode(dataFields, actionData, function (data) {

					if ( false === editShortcode ) {
						
						self.callbacks.generateShortCode_callback(data);
						self.removeFilterShortcode();      

						$j('.step4').addClass('active');

					}					
					$j("#lscf-saving-shorcode-message").hide();
					$j("#lscf-shortcode-generated-message").fadeIn();
					shortcodeButton.removeClass('inactive');

				});

			}

		});

		self.removeFilterShortcode();
	};

	this.generate_active_additional_fields = function(){

		var af_fields = self.edit_shortcode.data.fields,
			range_count = 0,
			search_count = 0,
			date_interval_count = 0;
		
		for ( var i = 0; i < af_fields.length; i++ ) {

			if ( 'additional_fields' == af_fields[ i ].group_type ) {

				switch ( af_fields[ i ].type ) {

					case "range":
					
						af_fields[ i ].index = range_count;
						af_fields[ i ].is_range = 1;
						self.template.additional_fields.items.push( af_fields[ i ] );

						range_count++;
						break;
					
					case "search":

						af_fields[ i ].index = search_count;
						af_fields[ i ].is_search = 1;
						self.template.additional_fields.items.push( af_fields[ i ] );

						search_count++;
						break;

					case "date-interval":

						af_fields[ i ].index = date_interval_count;
						af_fields[ i ].is_di = 1;
						self.template.additional_fields.items.push( af_fields[ i ] );

						date_interval_count++;
						break;

				}

			}
		}

	};

	this.load_featured_fields_template = function( postType ){
	

		self.getCustomFieldsByPostType( postType, "all", function( data ) {

			if ( typeof data.success !== 'undefined' && data.success == 1 ) {
			
				var data = data.data.data,
					featured_fields_template = {
						items:[
							{
								ID:"no-featured-label",
								value:"",
								name:"None"
							}
						]
					};

				data.success = 1;

				if ( null !== self.edit_shortcode.filterID && null !== self.edit_shortcode.data ) {
					if ( '' === self.edit_shortcode.data.featuredLabelFieldID ) {
						featured_fields_template.items[0].active = 1;
					}
				};
				
				data.fields.forEach(function (field) {

					if ( 'px_check_box' !== field.slug && 'px_icon_check_box' !== field.slug ) {

						if ( null !== self.edit_shortcode.filterID && null !== self.edit_shortcode.data ) {
							if ( field.value == self.edit_shortcode.data.featuredLabelFieldID ) {
								field.active = 1
							} else {
								field.active = 0;
							}
						} else {
							field.active = 0;
						}

						featured_fields_template.items.push( field );
					}
				
				});

				if ( true === self.isWooCommerce ) {
					
					featured_fields_template.woo_items = [];
					
					var wooPrice = {
						"ID":"woocommerce-featured-price",
						"value":"woocommerce-featured-price",
						"name":"Price ( WooCommerce )"
					}

					wooPrice.active = ( null !== self.edit_shortcode.data && 'woocommerce-featured-price' == self.edit_shortcode.data.featuredLabelFieldID ? 1 : 0 );

					featured_fields_template.woo_items.push( wooPrice );
				}

				featured_fields_template.is_woocommerce = self.isWooCommerce;

				self.get_featured_fields_template( featured_fields_template, function( generated_html ) {
					
					$j(".px_featured-field").find("h4").show();
					$j("#setAsFeaturedField").html( generated_html );
					$j(".featured-fields-group .expandable-container-headline").removeClass("inactive");

					$j('#setAsFeaturedField .px_radiobox').each(function(){
						$j(this).click(function(){
							$j('.featured-fields-group.step3').addClass('active');
						});
					})

				});

			} else {
				$j(".featured-fields-group .expandable-container-headline").addClass("inactive");
				$j(".px_featured-field").find("h4").hide();
				$j("#setAsFeaturedField").html("");
			
			}
		});

	}

	this.get_featured_fields_template = function( templateData, callback ) {

		$j.get( self.template.featured_fields.templateUrl, function( template ) {
			
			templateData.set_as_active = self.template.featured_fields.set_as_active;

			var renderedTemplate = Mustache_2.render( $j( template ).filter('#px-filter-featured-fields-template').html(), templateData );

			callback( renderedTemplate );

		});
	}

	this.retrievefilterShortcodeData = function( callback ) {

		if ( null == self.edit_shortcode.filterID ) { return; }

		self.getFilterData( self.edit_shortcode.filterID, function( data ){

			self.edit_shortcode.data = data;

			var filter_fields = [];

			for ( var i = 0; i < data.fields.length; i++ ) {

				if ( 'taxonomies' == data.fields[ i ].group_type ) {

					if ( 'undefined' === typeof filter_fields[ i ] ) { 

						filter_fields[ i ] = data.fields[ i ];
						filter_fields[ i ].terms_list = [];

					}

					if ( 'undefined' !== typeof data.fields[ i ].subcategs_parent_id ) {
						
						filter_fields[ i ].terms_list[ data.fields[ i ].subcategs_parent_id ] = {
							"data":{
								"name":data.fields[ i ].name,
								"value":data.fields[ i ].subcategs_parent_id
							}
						}

					}

					for ( var k = 0; k < data.fields[ i ].terms.length; k++ ) {
						
						var term = data.fields[ i ].terms[ k ];

						filter_fields[ i ].terms_list[ term.data.value ] = term;

					}

				} else {
					filter_fields.push( data.fields[ i ] );
				}

			}


			self.edit_shortcode.data.fields = filter_fields;

			callback();
		
		})
	}

	this.load_additional_fields_template = function( loaded_data ){
		
		if ( false === loaded_data.date_fields || false === loaded_data.text_fields ) { return false; }

		self.template.additional_fields.text_fields.items = self.template.text_fields.items;
		self.template.additional_fields.date_fields.items = self.template.date_fields.items;
		self.template.additional_fields.is_woocommerce = self.isWooCommerce;
		

		$j.get( self.template.additional_fields.templateUrl, function( template ) {

			var renderedTemplate = Mustache_2.render( $j( template ).filter('#px-filter-additional-fields-template').html(), self.template.additional_fields );
			
			$j("#px_additional-fields-container").prepend( renderedTemplate );

		});

	}

	this.sanitizeAdditionalFilterFieldsName = function(){

        $j("#px_additional-fields-container").on("blur", ".px_sanitize-OnBlur", function(){

            var parent = $j(this).parent();
            var value = $j(this).val();
            
            if (value!='') {
                
                value = pxSanitize(value);
                
                parent.find(".px_sanitized-key").val(value);    
            };
        });

	};

	this.addAdditionalFields = function(){ 

        $j("#px_add-additional-field").click(function(){
            
            var option = $j("#px_additional-fields").val();
            
            if( option == '' || option == null ) {
                
                alert("Please select an option");
                return;    
                
            }
            
            var postType = $j("#px-filter-selected-post-type").val();
            
            switch ( option ) {
                
                case "search":
                    
                    var searchLength = $j(".px_additional-fields-row.px_search").length;

					if ( true === self.isWooCommerce ) {

						if ( searchLength > 1 ) {
                        
							alert("The filter with WooCommerce can have only 2 fields of this type");
							return;

                    	}

					} else {

						if ( searchLength > 0 ) {
                        
							alert("The filter can have only 1 field of this type");
							return;

                    	}

					}
					
					self.template.additional_fields.search.is_woocommerce = ( true === self.isWooCommerce ? 1 : 0 );

                    if ( self.isWooCommerce ) {
                    
						var fieldTypeLength = ($j(".px_search")? $j(".px_search").length : 0 );
						self.template.additional_fields.search.search_field_length = fieldTypeLength;
					} 

					$j.get( self.template.additional_fields.search.templateUrl, function( template ) {

						var renderedTemplate = Mustache_2.render( $j( template ).filter('#px-filter-additional-fields-template').html(), self.template.additional_fields.search );
						
						$j("#px_additional-fields-container").prepend( renderedTemplate );

					});

					break;

                case "range":

                    self.getCustomFieldsByPostType( postType, ["number"], function( data ) {
						
                        if ( data.success == 1 && typeof data.data.data.fields !== "undefined" || true === self.isWooCommerce ) {
                            
                            var range_field_length = ( $j(".px_range") ? $j(".px_range").length : 0 );

							 self.template.additional_fields.range.is_woocommerce = ( true === self.isWooCommerce ? 1 : 0 );
							 self.template.additional_fields.range.range_field_length = range_field_length;

							if ( data.success == 1 && typeof data.data.data.fields !== "undefined" ) {

								self.template.additional_fields.range.items = data.data.data.fields;

							}

							$j.get( self.template.additional_fields.range.templateUrl, function( template ) {

								var renderedTemplate = Mustache_2.render( $j( template ).filter('#px-filter-additional-fields-template').html(), self.template.additional_fields.range );
								
								 $j("#px_additional-fields-container").prepend( renderedTemplate );

							});

                        }
                        else {
                            
                            alert("There is no any number/text field type for selected Post Type. Go to post\'s fields to add a number/text field type'")
                            
                        }

                    });

                    break;
                
                case "date-interval":
                    
                    self.getCustomFieldsByPostType(postType, ["date"], function(data){
                        
                        if ( data.success == 1 && typeof data.data.data.fields !== "undefined") {
                            
							var fieldTypeLength = ($j(".px_date-interval")? $j(".px_date-interval").length : 0 );
                            
							self.template.additional_fields.date_interval.items = data.data.data.fields;
							self.template.additional_fields.date_interval.di_field_length = fieldTypeLength;
                            
                            $j.get( self.template.additional_fields.date_interval.templateUrl, function( template ) {

								var renderedTemplate = Mustache_2.render( $j( template ).filter('#px-filter-additional-fields-template').html(), self.template.additional_fields.date_interval );
								
								 $j("#px_additional-fields-container").prepend( renderedTemplate );

							});

                        }
                        else {

                            alert('There is no any "Date" field type for current Post Type. Go to Post\'s field to add a "Date" field ')

                        };

                    });

                    break;

            };
            
        });
    }
    
    this.removeAdditionalFilterFields = function(){
        
        $j("#px_additional-fields-container").on("click", ".px_remove-additional-field", function(){
            
            var parent = $j(this).closest(".px_additional-fields-row");
            
            parent.remove();
        });
        
    }

    this.removeFilterShortcode = function(){

        $j(".px_remove-shortcode").on("click", function(){
            
            var filterID = $j(this).data("id");
            var postType = $j(this).data("post");
            
            self.removeShortcode(filterID, $j(this).closest(".single-shortcode"), self.callbacks.removeShortcode_callback);
            
        });
    };

    this.removeCustomPostType_ajaxRequest = function( customPostKey, callback ){

        $j.ajax({
            type:"POST",
            url:ajaxURL,
            data:{
                action:action,
                section:"removeCustomPostType",
                key:customPostKey
            },
            success:function(data){
				if( null != callback ) { callback( data ); }
            },
            dataType:"html"
        })

    };

    this.getPostCategories = function( postType, callback ) {

        $j.ajax({
            type:"POST",
            url:ajaxURL,
            data:{
                action:action,
                section:"getPostCategories",
                post_type:postType
            },
            success:function(data){
				callback( data, self.edit_shortcode );
            },
            dataType:"json"
        
            
        })
        
    }
    
    this.addNewCustomPostType_ajaxRequest = function(postName){
        
        $j.ajax({
            type:"POST",
            url:ajaxURL,
            data:{
                action:action,
                section:"addNewCustomPostType",
                name:postName
            },
            success:function(data){
				document.cookie = 'px_new_custom_post=1';
                window.location.reload();
            },
            dataType:"html"
        })
        
    };
    
    
    
    this.getCustomFieldsByPostType = function( postType, fieldType, callback ){
        
        $j.ajax({
            type:"POST",
            url:ajaxURL,
            data:{
                action:action,
                section:"getPostType_customFields",
                fieldType:fieldType,
                post_type:postType
            },
            success: function (data) {
                
                callback( data, self.edit_shortcode );
            },
            dataType:"json"
        });
    };
    
	this.getFilterData = function( filterID, callback ) {
		
		$j.ajax({
			type:"POST",
			url:ajaxURL,
			data:{
				filter_id:filterID,
				action:action,
				section:"get_filter_data"
			},
			success:function( data ) {
				callback( data );
			},
			dataType:"json"
		});
	};
    
    this.generateShortCode = function( data, actionData, callback ) {

		var postType = actionData.postType;

        $j.ajax({
            type:"POST",
            url:ajaxURL,
            data:{
                action:action,
                section:"generateShortcode",
				actionData:actionData,
				postType:postType,
                fieldsData:data
            },
            success:function(data){

                callback(data);    
			},

            dataType:"json"
        });
        
    };
    
    this.removeShortcode = function(filterID, jq_firedElement, callback){
        
        $j.ajax({
            type:"POST",
            url:ajaxURL,
            data:{
                action:action,
                section:"removeShortcode",
                filterID:filterID
            },
            success:function(data){
                callback(jq_firedElement, data);
            },
            dataType:"json"
        });
        
    };
    
    
};

function ajaxCallbacks(){
    
    var self = this;

	this.isWooCommerce = false;

	this.template = PX_pluginLF.template;
	
	this.initCustomFields = function(){
		
		$j('.post-custom-fields-group').each(function(){

			$j(this).find('.px-inline').each(function(){

				var parentLine = $j(this);

				$j(this).find('.px-checkbox-label').click(function(){
	
					var isChecked = ( parentLine.find('input[type="checkbox"]:checked').length > 0 ? 1 : 0 );

					if ( 1 === isChecked ) {
						parentLine.find('.select').addClass('inactive');	
					} else {
						parentLine.find('.select').removeClass('inactive');
					}

				});

			});
		});	
	}

    this.getCustomFieldsByPostType = function( data, filter_shortcode_data ) {

        if ( data.success == 1 || true === self.isWooCommerce ) {
			
			var hasCustomFields = data.success;
            
            var customFields = {
				"fields":[],
				"woo_fields":false
			};

			if ( 1 == hasCustomFields ) {

				var data = data.data.data;

				data.fields.forEach(function(field){

					switch ( field.slug ) {
						
						case "px_radio_box":
							field.display_as = [
								{
									"value":"default",
									"name":"Display as"
								},
								{
									"value":"px_radio_box",
									"name":"Radio"
								},
								{
									"value":"px_select_box",
									"name":"Dropdown"
								},
							]
							field.multiple_display = true;
						break;
						
						case "px_select_box":
							field.display_as = [
								{
									"value":"default",
									"name":"Display as"
								},
								{
									"value":"px_radio_box",
									"name":"Radio"
								},
								{
									"value":"px_select_box",
									"name":"Dropdown"
								},
							]
							field.multiple_display = true;
						break;
						
						case "px_cf_relationship":

							field.display_as = [
								{
									"value":"px_radio_box",
									"name":"Display as"
								},
								{
									"value":"px_radio_box",
									"name":"Radio"
								},
								{
									"value":"px_select_box",
									"name":"Dropdown"
								},
							]
							field.multiple_display = true;

						break;

						case "px_check_box":
							field.display_as = [
								{
									"value":"default",
									"name":"Display as"
								},
								{
									"value":"px_radio_box",
									"name":"Radio"
								},
								{
									"value":"px_select_box",
									"name":"Dropdown"
								},
								{
									"value":"px_check_box",
									"name":"Checkbox"
								},
							]
							field.multiple_display = true;
						break;
						
						case "px_icon_check_box":

							field.display_as = [
								{
									"value":"default",
									"name":"Display as"
								},
								{
									"value":"px_radio_box",
									"name":"Radio"
								},
								{
									"value":"px_select_box",
									"name":"Dropdown"
								},
								{
									"value":"px_check_box",
									"name":"Checkbox"
								},
								{
									"value":"px_check_icon-text_box",
									"name":"Icon \w text"
								},
								{
									"value":"px_check_icon-only_box",
									"name":"Icon only"
								}
							]
							field.multiple_display = true;
						break;

						default:
						case "px_date":
							field.display_as = false;
							field.multiple_display = false;
						break;
					}
				});

				customFields.fields = data;
			}


			if ( true === self.isWooCommerce ) {
				
				customFields.woo_fields = [];

				var woo_fields = [
					{
						"ID":"px-woocommerce-instock-field",
						"field_form_id":"",
						"name":"In stock/out of stock (WooCommerce)",
						"slug":"px-woocommerce-instock",
						"value":"woocommerce-instock"
					}
				]
				customFields.woo_fields = woo_fields;
				
			}

			self.template.custom_fields.items = customFields;

			$j.get( self.template.custom_fields.templateUrl, function( template ) {

				self.set_the_active_custom_fields( filter_shortcode_data );

				var renderedTemplate = Mustache_2.render( $j( template ).filter('#px-filter-custom-fields-template').html(), self.template.custom_fields );

				document.getElementById("px_post_fields").innerHTML = renderedTemplate;         
				document.getElementById("px_post_fields").setAttribute("data-post", data.post_type);         

				checkAllCheckboxes( $j("#px_post_fields"), true );
				
				$j(".post-custom-fields-group .expandable-container-headline").removeClass("inactive");
				
				$j(".px_dynamic-field").each(function () {
                	$j(this).show();
            	});

				setTimeout(function(){
					
					isStep2Active();
					
					init_custom_dropdown( $j('.post-custom-fields-group') );
					
					self.initCustomFields();

				}, 400);
				
			});

        }

		$j(".post-custom-fields-group .expandable-container-headline").each(function () {

			if ( ! $j(this).hasClass('always-active') ) {

				$j(this).addClass('inactive');						

			}

		});

        document.getElementById("px_post_fields").innerHTML = "";

        $j(".px_dynamic-field").each(function () {
            $j(this).show();
        });

    };

    this.generateShortCode_callback = function(data){

        var templateData = '<li>Filter name:<strong>'+data.name+'</strong><span data-id="'+data.filterID+'" data-post="'+data.post_type+'" class="px_remove-shortcode px_removeOption" >Remove</span><br/><textarea style="width:100%; text-align:left" rows="5" readonly="readonly">[px_filter id="'+data.filterID+'" post_type="'+data.post_type+'" ]</textarea><br/><i>Filter shortcode - copy shortcode to page editor for filter to show</i><hr/></li>';

        $j("ul#pxGenerateShortcodesContainer").prepend(templateData);

    }

    this.removeShortcode_callback = function(jq_firedElement, data){

        if ( data.success == 1 ) {
            jq_firedElement.hide();
            return;
        }

    }


    this.get_post_categories_callback = function ( data, filter_shortcode_data ) {

		var count = 0;
		self.template.tax.items = [];
		
		data.forEach(function (tax) {

			if ( 'undefined' !== typeof tax.parent_categs ){
				
				var taxTemplate = {};
				taxTemplate.index = count;
				taxTemplate.title = tax.taxonomy;
				taxTemplate.taxonomy = tax.taxonomy;

				taxTemplate.unsorted_subcategories = tax.unsorted_subcategs;
				taxTemplate.categs = [];
				tax.parent_categs.forEach(function(categ, index){
					
					var className = '';
					
					if ( 'undefined' !== typeof categ.has_subcategories && 1 === categ.has_subcategories ) {
						
						className = 'has-subcategs';
						categ.subcategs = {
							"parent_name": categ.data.name,
							"parent_id":categ.data.term_id,
							"list":"",
							"items":[]
						};

						var j = 0;
						tax.subcategs[categ.data.term_id].forEach( function( subcateg ) {
							
							if ( j <= 5 ) {
								categ.subcategs.list += subcateg.name + ', ';
							}
							categ.subcategs.items.push( subcateg );

							j++;
						});

						categ.subcategs.list = categ.subcategs.list.substring( 0, categ.subcategs.list.length -2  ) + '..';

					} else {
						categ.subcategs = false;
					};

					categ.className = className;
					taxTemplate.categs.push(categ);
				});

				taxTemplate.has_subcategories = ( 'undefined' !== typeof tax.has_subcategories ? tax.has_subcategories : 0 );

				taxTemplate.subcategs = ( 'undefined' !== typeof tax.subcategs ? tax.subcategs : '' );

				self.template.tax.items.push( taxTemplate );
				
				count++;
			}

        }); 

		$j.get( self.template.tax.templateUrl, function( template ) {

			self.set_the_active_tax_terms( filter_shortcode_data );

			var renderedTemplate = Mustache_2.render( $j( template ).filter('#template-tax-posts').html(), self.template.tax );
			
			$j("#px_post_categories").html( renderedTemplate );

			for ( var i = 0; i < count; i++ ) {
				checkAllCheckboxes( $j( '.px-post-tax' + i + ' .tax-categories-container' ) );
				checkAllCheckboxes( $j( '.px-post-tax' + i + ' .tax-subcategories-display' ), true );
			}
			
			setTimeout(function(){

				isStep2Active();
				init_custom_dropdown( $j('.post-categories-group') );
				lscf_display_all_tax_terms();

			}, 400);

			setTimeout(function(){
				self.init_tax_subcategories();
				lscf_taxonomies_subcategories_view_type();

				if ( null !== filter_shortcode_data.filterID ){

					lscf_taxonomies_subcategories_view_type_active();
				}

			}, 400);
			
			setTimeout(function(){

				if ( null !== filter_shortcode_data.filterID ){

					lscf_taxonomies_subcategories_view_type_active();
				};
			}, 3000);

		});

		if (count == 0)
			$j(".post-categories-group .expandable-container-headline").addClass("inactive");
		else 
			$j(".post-categories-group .expandable-container-headline").removeClass("inactive");

    }

	this.set_the_active_custom_fields = function( filter_shortcode_data ) {

		if ( '' == filter_shortcode_data || null == filter_shortcode_data.filterID || 'undefined' === self.template.custom_fields ) { return; }
		
		if ( 0 === self.template.custom_fields.items.fields.length ) { return; }

		for ( var i = 0; i < self.template.custom_fields.items.fields.fields.length; i++ ) {
			
			var custom_field = self.template.custom_fields.items.fields.fields[ i ];
			

			
			self.template.custom_fields.items.fields.fields[ i ].active = 0;
			self.template.custom_fields.items.fields.fields[ i ].display = 'default';

			for ( var cf = 0; cf < filter_shortcode_data.data.fields.length; cf++ ) {

				if ( 'custom_field' == filter_shortcode_data.data.fields[ cf ].group_type ) {

					if ( custom_field.value == filter_shortcode_data.data.fields[ cf ].ID ) {
						
						self.template.custom_fields.items.fields.fields[ i ].display = filter_shortcode_data.data.fields[ cf ].display;
						self.template.custom_fields.items.fields.fields[ i ].active = 1;

						break;
					} else if ( 'woocommerce-instock' == filter_shortcode_data.data.fields[ cf ].ID ) {

						for ( var kl = 0; kl < self.template.custom_fields.items.woo_fields.length; kl++ ) {
							if ( 'woocommerce-instock' == self.template.custom_fields.items.woo_fields[ kl ].value ) {
								self.template.custom_fields.items.woo_fields[ kl ].active = 1;
								self.template.custom_fields.items.woo_fields[ kl ].display = filter_shortcode_data.data.fields[ cf ].display;
							}
						}
					}

				}

			}
		}

	}

	this.set_the_active_tax_terms = function( filter_shortcode_data ) {

		if ( '' == filter_shortcode_data || null == filter_shortcode_data.filterID ) { return; }

		for ( var i = 0; i < self.template.tax.items.length; i++ ) {

			for ( var k = 0; k < filter_shortcode_data.data.fields.length; k++ ) {

				if ('taxonomies' ==  filter_shortcode_data.data.fields[ k ].group_type ) {

					var tax_item = self.template.tax.items[ i ],
						shortcode_field = filter_shortcode_data.data.fields[ k ],
						shortcode_slug = shortcode_field.slug.split('_-_');
					
					shortcode_slug = shortcode_slug[0];

					if ( tax_item.taxonomy == shortcode_slug ) {

						tax_item.title = shortcode_field.name;
						tax_item.display_as = shortcode_field.display_as;

						if ( 'undefined' !== typeof shortcode_field.subcategories_hierarchy_display ) {
							tax_item.subcategories_hierarchy_display = shortcode_field.subcategories_hierarchy_display;
						}
						if ( 'undefined' !== typeof shortcode_field.display_all_terms ) {
							tax_item.display_all_terms = shortcode_field.display_all_terms;
						}
						if ( 'undefined' !== typeof shortcode_field.display_parent_categs_as_filters ) {
							tax_item.display_parent_categs_as_filters = shortcode_field.display_parent_categs_as_filters;
						} else {
							tax_item.display_parent_categs_as_filters = 0;
						}

						for ( var c = 0; c < tax_item.categs.length; c++ ) {
							
							if ( 'undefined' !== typeof shortcode_field.terms_list[ tax_item.categs[ c ].data.term_id ]  ) {
								
								tax_item.categs[ c ].data.active = 1;

							}


							if ( 'undefined' !== typeof tax_item.categs[ c ].subcategs ) {

								if ( 'undefined' !== typeof shortcode_field.subcategs_parent_id ) {

									if ( shortcode_field.subcategs_parent_id == tax_item.categs[ c ].subcategs.parent_id ) {
										
										tax_item.categs[ c ].subcategs.active = 1;
										tax_item.categs[ c ].subcategs.display_as = shortcode_field.display_as;
									}
								} else if ( 'undefined' !== typeof shortcode_field.subcategories_hierarchy_display && 1 === shortcode_field.subcategories_hierarchy_display ) {
							
									if ( 'undefined' !== typeof shortcode_field.terms_list[ tax_item.categs[ c ].subcategs.parent_id ] ) {
										
										tax_item.categs[ c ].subcategs.active = 1;
										tax_item.categs[ c ].subcategs.display_as = shortcode_field.terms_list[ tax_item.categs[ c ].subcategs.parent_id ].display_as;
									}
								}
							}
						}

						for ( var subcateg_id in tax_item.subcategs ) {
							
							for ( var cs = 0; cs < tax_item.subcategs[ subcateg_id ].length; cs ++ ) {
								
								var term_id = tax_item.subcategs[ subcateg_id ][ cs ].term_id;

								if ( 'undefined' !== typeof shortcode_field.terms_list[ term_id ] ) {
									tax_item.subcategs[ subcateg_id ][ cs ].active = 1;
								}
							}
						}


						for ( var cs = 0; cs < tax_item.unsorted_subcategories.length; cs ++ ) {
							var term_id = tax_item.unsorted_subcategories[ cs ].term_id;

							if ( 'undefined' !== typeof shortcode_field.terms_list[ term_id ] ) {
								tax_item.unsorted_subcategories[ cs ].active = 1;
							}
						}

					}

					self.template.tax.items[ i ] = tax_item;
				}
			}

		}


	}


	this.init_tax_subcategories = function(){
		
		$j( '.taxonomies-block .px-post-categories' ).each(function( taxIndex ){
			
			var parentContainer = $j(this);
			
			parentContainer.closest('.tax-categories-container').find('.tax-list-check-all').click(function(){
				
				var countParentCategs = parentContainer.find('li.has-subcategs').length;

				var isCheckedAllCategs = ( parentContainer.closest('.tax-categories-container').find('.tax-list-check-all-checkbox:checked').length > 0 ? 1 : 0 );

				if ( 1 === isCheckedAllCategs && countParentCategs > 0 ) {
					
					$j('.tax-subcategories-display').eq(taxIndex).hide();	
					$j('.tax-subcategories-display').eq(taxIndex).find('.px-inline').hide();
					parentContainer.attr('data-active-parent-count', 0);

				} else if( countParentCategs > 0 ) {

					$j('.tax-subcategories-display').eq(taxIndex).fadeIn();	
					$j('.tax-subcategories-display').eq(taxIndex).find('.px-inline').fadeIn();
					parentContainer.attr('data-active-parent-count', countParentCategs);

				}
			});

			$j(this).find('.has-subcategs').find('.px-checkbox-label').each(function(){
				
				$j(this).click(function(){

					var countActiveParentChilds = parseInt( parentContainer.attr('data-active-parent-count') );
					var parent = $j(this).parent('li');
					var isChecked = ( parent.find('input[type="checkbox"]:checked').length > 0 ? 1 : 0 );
					var value = parent.find('input[type="checkbox"]').val().split('!#');
					var ID = value[0];

					if ( 1 === isChecked ){

						countActiveParentChilds--;
						
						if ( countActiveParentChilds < 1 ) {
							$j('.tax-subcategories-display').eq(taxIndex).hide();
						}

						parentContainer.attr('data-active-parent-count', countActiveParentChilds);
						$j('.subcateg-view-' + ID ).hide();

					} else{
						
						if ( countActiveParentChilds < 1 ) {
							$j('.tax-subcategories-display').eq(taxIndex).fadeIn();
						}

						countActiveParentChilds++;
						parentContainer.attr('data-active-parent-count', countActiveParentChilds);
						$j('.subcateg-view-' + ID ).fadeIn();

					}
				})
			})
		})

		$j('.tax-subcategories-display').each(function(){

			$j(this).find('.px-inline').each(function(){

				var parentLine = $j(this);

				$j(this).find('.px-checkbox-label').click(function(){
	
					var isChecked = ( parentLine.find('input[type="checkbox"]:checked').length > 0 ? 1 : 0 );

					if ( 1 === isChecked ) {
						parentLine.find('.select').addClass('inactive');	
					} else {
						parentLine.find('.select').removeClass('inactive');
					}

				});

			})
		})	
	};
}


function pxSanitize(string){
    return string.trim().toLowerCase().replace(/([\!\@\#\$\%\^\&\*\(\[\)\]\{\-\}\\\/\:\;\+\=\.\<\,\>\?\~\`\'\" ]+)/g, '_');
};

function lscf_taxonomies_subcategories_view_type(){
	var $j = jQuery;

	var parentContainer,
			checkbox
			is_checked = false;

		$j('.lscf-hierarchy-subcategories-display').each(function( index ){

			$j(this).click(function(){

				$j('.lscf-subcategories-independent-display').eq(index).removeAttr('checked');

				is_checked = ( $j(this).is(':checked') ? true : false );
				parentContainer = $j(this).closest('.taxonomies-block');

				if ( true === is_checked ) {
					
					parentContainer.find('.lscf-tax-all-subcategories').hide();
					
					parentContainer.find('.lscf-tax-all-subcategories').find('input[type="checkbox"]').each(function(){
						$j(this).removeAttr('checked');
					});

					parentContainer.find('.lscf-hierarchy-subcategories-container').fadeIn();
					lscf_init_subcategories_dependencies( parentContainer );

				} else {
					parentContainer.find('.lscf-tax-all-subcategories').fadeIn();
					parentContainer.find('.lscf-hierarchy-subcategories-container').hide();

				}
				
			});
		});

		$j('.lscf-subcategories-independent-display').each(function( index ){

			$j(this).click(function(){

				$j('.lscf-hierarchy-subcategories-display').eq(index).removeAttr('checked');

				is_checked = ( $j(this).is(':checked') ? true : false );
				parentContainer = $j(this).closest('.taxonomies-block');

				if ( true === is_checked ) {
					
					parentContainer.find('.lscf-tax-all-subcategories').hide();
					
					parentContainer.find('.lscf-tax-all-subcategories').find('input[type="checkbox"]').each(function(){
						$j(this).removeAttr('checked');
					});

					parentContainer.find('.lscf-hierarchy-subcategories-container').fadeIn();
					lscf_init_subcategories_dependencies( parentContainer );

				} else {
					parentContainer.find('.lscf-tax-all-subcategories').fadeIn();
					parentContainer.find('.lscf-hierarchy-subcategories-container').hide();

				}
				
			});
		});
}

function lscf_taxonomies_subcategories_view_type_active(){

	var $j = jQuery;

	$j('.lscf-subcategories-independent-display').each(function( index ){

		if ( $j(this).is(':checked') ){

			var parentContainer = $j(this).closest('.taxonomies-block');

				parentContainer.find('.lscf-tax-all-subcategories').hide();
				
				parentContainer.find('.lscf-tax-all-subcategories').find('input[type="checkbox"]').each(function(){
					$j(this).removeAttr('checked');
				});

				parentContainer.find('.lscf-hierarchy-subcategories-container').fadeIn();
				
				lscf_init_subcategories_dependencies( parentContainer );
		}

	});

	$j('.lscf-hierarchy-subcategories-display').each(function( index ){

		if ( $j(this).is(':checked') ){

			var parentContainer = $j(this).closest('.taxonomies-block');

				parentContainer.find('.lscf-tax-all-subcategories').hide();
				
				parentContainer.find('.lscf-tax-all-subcategories').find('input[type="checkbox"]').each(function(){
					$j(this).removeAttr('checked');
				});

				parentContainer.find('.lscf-hierarchy-subcategories-container').fadeIn();
				
				lscf_init_subcategories_dependencies( parentContainer );
		}

	});
}

function lscf_display_all_tax_terms(){

	var $j = jQuery;

	$j('.lscf-display-all-taxonomy-terms').each(function( index ){

		var checkbox = $j('.lscf-display-all-taxonomy-terms-checkbox').eq(index);
		var is_checked = ( checkbox.is(':checked') ? 1 : 0 );

		if ( 1 === is_checked ) {
			$j(this).closest('.parent-taxonomies-view-type').find('.lscf-custom-select-categories').hide();
		}

		$j(this).click(function(){

			is_checked = ( checkbox.is(':checked') ? 0 : 1 );

			if ( 1 === is_checked ) {
				$j(this).closest('.parent-taxonomies-view-type').find('.lscf-custom-select-categories').hide();
			} else {
				$j(this).closest('.parent-taxonomies-view-type').find('.lscf-custom-select-categories').fadeIn();
			}

		})
	})

}

function lscf_init_subcategories_dependencies( parentContainer ){
	
	var $j = jQuery,
		activeParentCategories = [];

	parentContainer.find('.px-post-categories').find('.has-subcategs').each(function(){
		
		if ( $j( this ).find('.px-checkbox').is(':checked') ) {
			
			var catVal = $j(this).find('.px-checkbox').val().split('!#');
			
			catVal = parseInt( catVal[0] );

			activeParentCategories.push( catVal );
		}

	});

	if ( activeParentCategories.length < 1 ) return;

	parentContainer.find( '.px-post-categories' ).attr({'data-active-parent-count':activeParentCategories.length});

	parentContainer.find( '.tax-subcategories-display' ).fadeIn();

	activeParentCategories.forEach( function( catID ) {

		parentContainer.find( '.px-inline.subcateg-view-'+catID ).fadeIn();
		parentContainer.find( '.px-inline.subcateg-view-'+catID ).find('.select').removeClass('inactive');

	});
}

function checkAllCheckboxes( parentContainer, taxSubcategs ) {
    
    var $ = jQuery;
        

    parentContainer.find(".tax-list-check-all").click(function () {
        
        var checkboxInputID = parentContainer.find(".tax-list-check-all").attr("for");

        var isChecked = (( $j('#'+checkboxInputID+":checked").length > 0 ) ? 1 : 0);
        
        parentContainer.find('input[type="checkbox"]').each(function () {
            
            if ( ! $j(this).hasClass('tax-list-check-all-checkbox') ) {
                
                if (isChecked == 1) {
                    if ( true === taxSubcategs ) {
						$j(this).closest('.px-inline').find('.select').addClass('inactive');
					}
                    $j(this).removeAttr("checked");    
                }
                else {
					if ( true === taxSubcategs ) {
						$j(this).closest('.px-inline').find('.select').removeClass('inactive');
					}
                    $j(this).attr({ "checked": "checked" });
                }
                
            }
            
        })
        
    });

}



function LscfSettings(){

	var $j = jQuery,
		self = this,
		action = "px-plugin-ajax",
		ajaxURL = adminData.ajaxURL;

	this.init = function(){
		self.reset_button_on_check();
	};

	this.reset_button_on_check = function(){

		$j('#reset-button-checkbox').click(function(){
			var isChecked = ( $j( '#px-reset-button:checked' ).length > 0 ? 1 : 0 );
			
			if ( 1 == isChecked ) {
				$j(this).closest('.lscf-opt').find('.lscf-extra-opt').removeClass('active');
			} else {
				$j(this).closest('.lscf-opt').find('.lscf-extra-opt').addClass('active');
			}
		});
		
	}

	this.saveSettings = function(){

		$j("#lscf-save-settings").click(function(){
			
			$j('.saving-status').removeClass('saved');
			$j('.saving-status').addClass('loading');

			var attr = $j('.colorpick-rgba').attr('style');

			var matches = attr.match(/background-color\:(.+?);/);
			var mainRgbColor = matches[1].replace(/rgb\(|\)/g, '');
			var mainColor = $j('.colorpick-rgba').val();

			var filterPostsPerPage = $j( '#px-filter-posts-count' ).val();
			var postsPagePostsPerPage = $j( '#px-posts-page-count' ).val();
			var resetButton  = {
				"status" : ( $j( "#px-reset-button:checked" ).length > 0 ? $j( '#px-reset-button' ).val() : 0 ),
				"name" : $j('#reset-button-extra-options').find('input[name="reset-button-name"]').val(),
				"position" : $j('#reset-button-extra-options').find('input[name="reset-button-possition"]:checked').val()
			}

			var gridViewAsDefault = ( $j( "#px-grid-view:checked" ).length > 0 ? $j( '#px-grid-view' ).val() : 0 );

			var seeMoreWriting = $j('#lscf-see-more-writing').val(),
				seeLessWriting = $j('#lscf-see-less-writing').val(),
				loadMoreWriting = $j('#lscf-load-more-writing').val(),
				selectWriting = $j('#lscf-select-writing').val(),
				anyWriting = $j('#lscf-any-writing').val(),
				viewWriting = $j('#lscf-view-writing').val(),
				filterWriting = $j('#lscf-filter-mobile-writing').val(),
				closeWriting = $j('#lscf-close-mobile-writing').val(),
				addToCart = $j('#lscf-add-to-cart-writing').val(),
				no_results = $j('#lscf-filter-no-results-writing').val(),
				sort_by = $j('#lscf-filter-sort-by-writing').val(),
				sort_asc = $j('#lscf-filter-asc-writing').val(),
				sort_desc = $j('#lscf-filter-desc-writing').val(),
				title_writing = $j('#lscf-filter-title-writing').val(),
				date_writing = $j('#lscf-filter-date-writing').val(),
				search_writing = $j('#lscf-search-writing').val(),
				price_writing = $j('#lscf-price-writing').val();


			var dataToSave = {
				color:{
					color: mainColor, 
					rgb: mainRgbColor
				},
				posts_per_page: {
					posts_only: postsPagePostsPerPage,
					filter: filterPostsPerPage
				},
				reset_button: resetButton,
				block_view: gridViewAsDefault,
				see_more_writing: seeMoreWriting,
				see_less_writing: seeLessWriting,
				load_more_writing: loadMoreWriting,
				any_writing: anyWriting,
				select_writing: selectWriting,
				view_writing: viewWriting,
				filter_writing: filterWriting,
				close_writing: closeWriting,
				add_to_cart: addToCart,
				no_results: no_results,
				sort_by: sort_by,
				sort_asc: sort_asc,
				sort_desc: sort_desc,
				title_writing: title_writing,
				date_writing: date_writing,
				search_writing: search_writing,
				price_writing: price_writing
			}


			self.saveSettingsAjaxRequest( dataToSave, function(data){

				$j('.saving-status').removeClass('loading');
				$j('.saving-status').addClass('saved');

			})

		});

	};

	this.saveSettingsAjaxRequest = function( data, callback ){

		$j.ajax({
            type:"POST",
            url:ajaxURL,
            data:{
                action:action,
                section:"save-general-settings",
				settings:data
            },
            success:function(data){
                callback(data);    
            },
            dataType:"html"
        });
	}

}


function getCookie(name) {
  var value = "; " + document.cookie;
  var parts = value.split("; " + name + "=");
  if (parts.length == 2) return parts.pop().split(";").shift();
  return false;
}

function init_custom_dropdown( parent ){
	var $ = jQuery;

	parent.find('.custom-select-type-1').each(function() {
		
		var dataClass=$j(this).attr('data-class');
		var $this=$j(this),
			numberOfOptions=$j(this).children('option').length;
		
		$this.addClass('s-hidden');
		
		$this.wrap('<div class="select '+dataClass+'"></div>');
		
		$this.after('<div class="styledSelect"></div>');
		
		var $styledSelect=$this.next('div.styledSelect');
		$styledSelect.text($this.children('option').eq(0).text());
		
		var $list = $j('<ul />',{'class':'options'}).insertAfter($styledSelect);
		
		for ( var i = 0; i < numberOfOptions; i++ ) {

			if ( $this.children('option').eq( i ).is( ':selected' ) ) {

				$styledSelect.text( $this.children('option').eq( i ).text() );
			 }
			$j('<li />',
				{ 
					text:$this.children('option').eq(i).text(), 
					rel:$this.children('option').eq(i).val(),
					class:$this.children('option').eq(i).attr('data-status')
				}
			).appendTo($list);

		}

		var $listItems = $list.children('li');
		$styledSelect.click(function(e){
			
			if( ! $j(this).closest('.select').hasClass('inactive') && ! $j(this).closest('.select').hasClass('not-available') ) {

				e.stopPropagation();
				
				if ( $j(this).hasClass('lscf-inactive') ) {
					return false;
				}

				$j('div.styledSelect.active').each(function(){
					$j(this).removeClass('active').next('ul.options').hide();
				});
				$j(this).toggleClass('active').next('ul.options').toggle();

			}
		});

		$listItems.click(function(e){
			e.stopPropagation();
			$styledSelect.text($j(this).text()).removeClass('active');
			$this.val($j(this).attr('rel'));
			$list.hide();
		});
		
		$j(document).click(function(){
			$styledSelect.removeClass('active');
			$list.hide();
		});
	});
}