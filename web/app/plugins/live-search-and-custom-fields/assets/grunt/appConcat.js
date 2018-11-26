var angAppName = '';
var appElems = document.querySelectorAll( '[ng-app]' );
var lscfContainers = document.querySelectorAll('.lscf-container');
var lscfDomainName = lscfShortcodeData[0].site_url.match( /((http|https):\/\/(.+))?\./ );

lscfDomainName = ( 'undefined' !== typeof lscfDomainName[1] ? lscfDomainName[1] : '' );
if ( appElems.length >= 1 ) {
	angAppName = appElems[ 0 ].getAttribute( 'ng-app' );

	if ( angAppName === 'lscf-app' && 1 <= lscfContainers.length ) {

		angular.module( 'lscf-app', [ 'ngSanitize', 'ngAnimate' ] )
			.config( ['$sceDelegateProvider', function( $sceDelegateProvider ){
				$sceDelegateProvider.resourceUrlWhitelist([
					// Allow same origin resource loads.
					'self',
					// Allow loading from outer templates domain.
					lscfShortcodeData[0].plugin_url + '**',
					'**'
				]);

			}]);
	}
} else {

	angAppName = 'lscf-app';
	angular.module( 'lscf-app', [ 'ngSanitize', 'ngAnimate' ])
		.config( ['$sceDelegateProvider', function( $sceDelegateProvider ){

			$sceDelegateProvider.resourceUrlWhitelist([
				// Allow same origin resource loads.
				'self',
				// Allow loading from outer templates domain.
				lscfShortcodeData[0].plugin_url + '**',
				'**'
			]);
		}]);
}



function lscf_data_to_filter_subcategories( dataToFilter, data, fieldsData ){

	var tax_slug = lscf_return_tax_main_slug( data.ID ),
		catID,
		parentIDs,
		parentID;

	dataToFilter = removeObjectKey( dataToFilter, data.ID );

	tax_slug = lscf_return_tax_main_slug( data.ID );
	catID = parseInt( data.value );

	dataToFilter = removeObjectLikeKey( dataToFilter, tax_slug );

	if ( 'undefined' !== typeof fieldsData.post_taxonomies[ tax_slug ] ) {

		if ( 'undefined' !== typeof fieldsData.post_taxonomies[ tax_slug ].tax.subcategs_hierarchy && 'undefined' !== typeof fieldsData.post_taxonomies[ tax_slug ].tax.subcategs_hierarchy[ catID ] ) {
		
			parentIDs = fieldsData.post_taxonomies[ tax_slug ].tax.subcategs_hierarchy[ catID ];

			for ( var i = 0; i < parentIDs.length; i++ ) {
				
				if ( 'undefined' !== typeof fieldsData.post_taxonomies[ tax_slug ].tax.subcategs_hierarchy[ parentIDs[ i ] ] ) {
					
					parentID = tax_slug + '_-_' + parentIDs[ i ];
					
					dataToFilter[ parentID ] = {
						"ID":parentID,
						"type":data.type,
						"value":parentIDs[ i ],
						"filter_as":data.filter_as
					};

				}
				
			}

		}

	}

	return dataToFilter;
}

function removeObjectKey(objectData, key) {

	var temp = {};
	for ( var prop in objectData ) {
		if ( objectData[ prop ].ID != key ) {
			temp[ prop ] = objectData[ prop ];
		}
	}
	return temp;
}

function removeObjectLikeKey(objectData, key){

	var temp = {};
	for (var prop in objectData) {

		if ( -1 === prop.indexOf(key) ) {
			temp[prop] = objectData[prop];
		}

	}

	return temp;
}

function lscf_display_tax_subcategs( objTax ){

	var $j = jQuery;

	$j('.lscf-taxonomies-fields').each( function(){
		var shortcodeIndex = $j(this).closest('.lscf-container').attr('data-index');
		var tax_slug = $j( this ).find('.px_capf-field').attr('data-id');

		if ( 'undefined' === typeof objTax[ tax_slug ] ) {

			$j( this ).find('.subcategs-tax').hide();	

			return;
		}

		var activeParentIDs = objTax[ tax_slug ];

		$j( this ).find('.subcategs-tax').each(function () {

			var ID = parseInt( $j(this).attr('data-parent') );
			var subCateg = $j(this);

			if ( activeParentIDs.indexOf( ID ) != -1 ) {

				subCateg.fadeIn();

			} else {

				subCateg.hide();
				subCateg.find('.styledSelect').text( lscfShortcodeData[ shortcodeIndex ].options.writing.select );
				subCateg.find('.px_capf-field').removeClass('active-val');
				subCateg.find('input[type="radio"]').removeAttr( "checked" );

			}

		});
	});

}

function lscf_reset_default_filter( default_filter, termData ){

	var termID = termData.value,
		tax_slug = lscf_return_tax_main_slug( termData.ID );

	if ( 'undefined' !== typeof default_filter.active_terms ) {
		for ( var i in default_filter.active_terms ) {
			if ( 'undefined' !== typeof default_filter.active_terms[ i ] ) {
				var matchRegex = new RegExp( tax_slug, 'g' );
				if ( default_filter.active_terms[ i ].match( matchRegex ) ){
					delete default_filter.active_terms[ i ];
					default_filter.active_terms.length--;
				}
			}
		}
	}

	if ( 'undefined' !== typeof default_filter[ tax_slug ] ) {

		var tax = default_filter[ tax_slug ].tax,
			termsData = [];

		for ( var t in tax.terms ){

			if ( 0 === termID ) {

				delete default_filter[ tax_slug ];

			} else if ( 'undefined' !== typeof default_filter[ tax_slug ] && 'undefined' !== typeof default_filter[ tax_slug ].tax &&  tax.terms[ t ].data.value != termID ) {

				if ( tax.terms.length == 1 ) {
					delete default_filter[ tax_slug ];
				} else {

					delete default_filter[ tax_slug ].tax.terms[ t ];
					var terms_t = lscf_reset_object_index( default_filter[ tax_slug ].terms );
					default_filter[ tax_slug ].terms = terms_t;
					default_filter[ tax_slug ].terms.length = terms_t.length;

					if ( terms_t.length === 0 ) {
						delete default_filter[ tax_slug ];
					}

				 }	
			}
		}
	}
	
	return default_filter;

}

function lscf_return_tax_main_slug( string ){

	var tax_slug = string;

	if ( string.match( /(.+?)_-_([0-9]+)$/ ) ) {

		var matches = string.match( /(.+?)_-_([0-9]+)$/ );

		tax_slug = matches[1];
	}

	return tax_slug;
}

function lscf_reset_object_index( objData ) {

	var results = [];

	for ( var key in objData ) {
		
		if ( 'undefined' === typeof results[ key ] ) {
			results[ key ] = [];
		}
		for ( var i in objData[ key ] ) {
			results[ key ].push( objData[ key ] [ i ] );
		}
	}

	return results;
}

function lscf_custom_dropdowns_update_options_classnames(){

	var $j = jQuery;

	$j('.pxSelectField').each(function(){

		var _this = $j(this);

		$j(this).find('select option').each(function( index ){

			if ( 1 == $j(this).attr('ng-data-disabled') ) {

				_this.find('.lscf-dropdown-option').eq( index ).addClass('lscf-option-disabled');	
			}
			else {
				_this.find('.lscf-dropdown-option').eq( index ).removeClass('lscf-option-disabled');
			}
			
		});

	});

}

function lscf_sanitize_string( string ) {
	string = string.replace( /([\!\@\#\$\%\^\&\*\(\[\)\]\{\-\}\\\/\:\;\+\=\.\\<\,\>\?\~\`\'\" ]+)/g, '_');
	return string.toLowerCase();
}

function lscf_convert_object_to_array( data ) {
	if ( 'undefined' === typeof data ) {
		return [];
	}
	if ( 'undefined' !== typeof data.length ) {
		return data;
	}

	var results = [];
	for ( var key in data ) {
		results.push( data[ key ] );
	}
	return results;
}
angular.module(angAppName)
	.controller("pxfilterController", ['$location', '$scope', '$sce', '$attrs', 'customFilterService', function ($location, $scope, $sce, $attrs, customFilterService ) {


		var shortcodeSettings = lscfShortcodeData[ $attrs.index ];
			shortcodeSettings.shortcodeIndex = $attrs.index;
		var postsPerPage = shortcodeSettings.post_per_page,
			page = 1;

		var filterID = shortcodeSettings.ID,
			postType = shortcodeSettings.postType,
			filterFieldsTemplate,
			wrapperGeneralClassNames,
			sidebarSection,
			previousSidebarSection,
			rangeField = new px_customRange( parseInt( $attrs.index ) ),
			customSelectBoxes = new customSelectBox(),
			filterFieldsMethod = new lscfOrderFilterFields( shortcodeSettings ),
			defaultFilter = null,
			filterFieldsAction = new pxFilterFieldsAction( shortcodeSettings );


		$scope.loading = false;
		$scope.noResults = false;
		$scope.morePostsAvailable = false;
		$scope.variations = {};

		$scope.featuredLabel = false;
		$scope.postType = postType;

		$scope.existsPosts = false;
		$scope.filterPostsTemplate = {};

		// methods: ready; afterPostsLoadCallback 
		$scope.directiveInfo = {};

		$scope.liveSearchTemplate = {
			'class': ''
		};

		$scope.actionSettings = {
			'controllerAttributes': $attrs,
			'style': {
				'postMaxHeight': null
			},
			'filtersOnly': false,
			'customFields': [],
			'urlActiveFilters': null,
			'pxCurrentPage': page,
			'postsPerPage': postsPerPage,
			'lsLoadPosts': true,
			'activeTerms': [],
			'postsCount': '',
			'pagesCount': '',
			'activeQuery': [],
			'filterPostsTemplate': [],
			'previousSidebarPosition': '',
			'disableInactiveTerms': false,
			'initSidebar': false,
			'initPostTheme': false,
			'initFieldsDraggable': false,
			'isAdministrator': ( 'undefined' !== typeof shortcodeSettings.settings.is_administrator ? parseInt( shortcodeSettings.settings.is_administrator ) : 0 )
		};
		
		$scope.lscfSidebar = {};

		$scope.loadMoreBtn = {
			'morePostsAvailable': false,
			'noResults': false,
			'loading': false,
			'postsLoading': true,
			'sidebarLoading': true,
			'ready': true,
			'type': 'default'
		};

		$scope.pluginSettings = {
			'className': {
				'sidebar': '',
				'posts_theme': ''
			},
			'existsPosts': true,
			'pluginPath': shortcodeSettings.plugin_url,
			'filterSettings': shortcodeSettings.settings,
			'generalSettings': shortcodeSettings.options,
			'editShortcodeLink': shortcodeSettings.site_url + '/wp-admin/admin.php?page=pxLF_plugin&plugin-tab=filter-generator&edit_filter=' + shortcodeSettings.ID
		};


		$scope.alternativeFilteringTaxInnactiveClass = function( is_inactive, className ) { 
			if ( true === is_inactive && 1 == $scope.actionSettings.disableInactiveTerms ) {
				return className;
			}
		};

		$scope.checkboxListClassname = function( itemIndex ) {

			var className = '';

			if ( 1 !== $scope.pluginSettings.generalSettings.hide_see_more_on_checkboxes_list && itemIndex > 4 ) {
				className += ' px-hidden-opt';
			}

			return className;
		};

		$scope.returnFieldClassnames = function( field ) {

			var classNames = '';

			if ( 'undefined' !== typeof field.tax && 'undefined' !== typeof field.tax.subcategories_hierarchy_display && 
				1 === field.tax.subcategories_hierarchy_display ) {
				classNames += ' lscf-hierarchical-terms';
			}

			if ( 'undefined' !== typeof field.tax ) {
				if ( 'undefined' !== typeof field.tax && ( field.tax.display_as == 'px_check_box' || 
				field.tax.display_as == 'px_radio_box' ) ) {

					classNames += ' lscf-large-field';

					return classNames;
				}
			}

			if ( 'undefined' !== typeof field.group_type && field.group_type == 'additional_fields' ) {
				classNames += ' lscf-large-field';
				return classNames;
			}

			if ( 'undefined' !== typeof field.display_as ) {
				if ( field.display_as == 'px_radio_box' || field.display_as == 'px_check_box' ||
				field.display_as == 'px_icon_check_box' || field.display_as == 'px_check_icon-only_box' ||
				field.display_as == 'px_check_icon-text_box' ) {

					classNames += ' lscf-large-field';
					return classNames;
				}
			}

			return classNames;
		};

		$scope.actionSettings.buildUrlLocation = function( lscfQueryArgs ) {

			if ( 'undefined' === typeof $scope.pluginSettings.generalSettings || 1 !== $scope.pluginSettings.generalSettings.url_history ) {
				return;
			}

			var urlLocationData = $scope.actionSettings.urlActiveFilters.buildUrlLocation( lscfQueryArgs );
			$location.search(urlLocationData);
		};

		$scope.trust_as_html = function( html_code ) {
			return $sce.trustAsHtml( html_code );
		};

		$scope.formatRangeValue = function( label, value, labelPosition ) {

			if ( 'undefined' !== typeof labelPosition && 'right' === labelPosition ) {
				return value + label;
			}
			return label + value;
		};

		$scope.initRangeValue = function( field, type ) {

			var fieldID = 'range_' + field.fieldID,
				label = field.label,
				defaultVal = ( 'max' === type ? field.max : field.min );

			if ( null === $scope.actionSettings.urlActiveFilters ) {
				return $scope.formatRangeValue( label, defaultVal, field.label_position ); 
			}

			if ( 'undefined' === typeof $scope.actionSettings.urlActiveFilters.activeFields || 
			'undefined' === typeof $scope.actionSettings.urlActiveFilters.activeFields[ fieldID ] ) {
				return $scope.formatRangeValue( label, defaultVal, field.label_position );
			}
			
			var rangeField = $scope.actionSettings.urlActiveFilters.activeFields[ fieldID ];
			if ( 'undefined' === typeof rangeField[0] || 'undefined' === typeof rangeField[1] ) {
				return $scope.formatRangeValue( label, defaultVal, field.label_position );
			}

			if ( 'min' === type ) {
				return $scope.formatRangeValue( label, rangeField[ 0 ], field.label_position );
			}

			if ( 'max' === type ) {
				return $scope.formatRangeValue( label, rangeField[ 1 ], field.label_position );
			}

			return $scope.formatRangeValue( label, defaultVal, field.label_position );
		};


		$scope.calculateRangePosition = function( ID, minVal, maxVal, type ) {
			
			var fieldID = 'range_' + ID;

			if ( null === $scope.actionSettings.urlActiveFilters ) { return 'default'; }
			if ( 'undefined' === typeof $scope.actionSettings.urlActiveFilters.activeFields || 'undefined' === typeof $scope.actionSettings.urlActiveFilters.activeFields[ fieldID ] ) {
				return 'default';
			}
			
			var rangeField = $scope.actionSettings.urlActiveFilters.activeFields[ fieldID ];
			if ( 'undefined' === typeof rangeField[0] || 'undefined' === typeof rangeField[1] ) {
				return 'default';
			}
			
			if ( 'min' === type ) {

				var min = ( rangeField[ 0 ] - minVal ) * 100 / ( maxVal -  minVal );
				return min;
			}

			if ( 'max' === type ) {
				var max = ( rangeField[ 1 ] - minVal ) * 100 / ( maxVal -  minVal );

				return max;
			}
			return 'default';
		};

		$scope.filterFieldIsSelected = function( fieldId, value, isChecked ) {

			if ( true === isChecked ) {
				return true;
			}

			var urlFilters = $scope.actionSettings.urlActiveFilters;
			if ( null === urlFilters ) { return false; }

			if ( 'undefined' === typeof urlFilters.activeFields[ fieldId ] ) { return false; }

			if ( -1 < urlFilters.activeFields[ fieldId ].indexOf( value.toString() ) ) {
				return true;
			}

		};

		$scope.filterFieldClassIsActive = function ( fieldId, value ) {
			if ( true === $scope.filterFieldIsSelected( fieldId, value, false ) ) {
				return 'active';
			}

			return '';
		};

		$scope.standartize_shortcodes_container_height = function(){

			var $j = jQuery,
				called = false,
				maxHeight = $scope.actionSettings.style.postMaxHeight;

				if ( $j( '.lscf-standartize-shorcodes-container-height' ).length > 0 ) {
					called = true;

					$j( '.lscf-standartize-shorcodes-container-height' ).each(function(){

						var elHeight = $j( this ).height();

						if ( maxHeight < elHeight ) { maxHeight = elHeight; }

					});

					setTimeout( function() {
						$j('.lscf-standartize-shorcodes-container-height').attr({
							'style': 'height:' + maxHeight + 'px'
						});
					}, 200 );
		
					$scope.actionSettings.style.postMaxHeight = maxHeight;

					return;
			}

			setTimeout( function(){
				$scope.standartize_shortcodes_container_height();
			}, 300);

		};

		if ( 'woocommerce' === $scope.pluginSettings.generalSettings.filter_type ) {
			if ( 'undefined' !== typeof $scope.pluginSettings.generalSettings.woo_instock && 1 === $scope.pluginSettings.generalSettings.woo_instock ) {
				$scope.actionSettings.activeQuery.push({
					ID: 'woocommerce-instock',
					filter_as:'px_select_box',
					group_type:'custom_field',
					type:'select',
					value:'instock',
					variation_id: null
				});
			}
		}

		$scope.lscfPrepareFilterData = function( dataFilters ) {

			var dataToFilter = $scope.actionSettings.activeQuery,
				dataAction;

			dataFilters.forEach(function( data ) {
				//reset the action key
				dataAction = "add";

				switch ( data.type ) {

					case "date-interval":

						if ( data.fields.from === '' || data.fields.to === '' ) {

							return;
						}

						break;

					case "checkbox_post_terms":

						if ( data.value.length === 0 ) { 

							dataToFilter = removeObjectKey( dataToFilter, data.ID );

							if ( null !== defaultFilter ) {
								defaultFilter.default_filter.post_taxonomies = lscf_reset_default_filter( defaultFilter.default_filter.post_taxonomies, data );
								break;
							}

							if ( 'cf_variation' == data.group_type ) {
								if ( 'undefined' === typeof $scope.variations[ data.ID ] ) {
									$scope.variations[ data.variation_id ] = {};
								}

								$scope.variations[ data.variation_id ].active = 'default';
							}
							dataAction = 'remove';
						}

						
						break;

					case "px_icon_check_box":
					case "checkbox":

						if ( data.value.length === 0 ) {

							dataToFilter = removeObjectKey(dataToFilter, data.ID);

							if ( 'checkbox_post_terms' == data.filter_as ) {
									
								if ( null !== defaultFilter ) {
									defaultFilter.default_filter.post_taxonomies = lscf_reset_default_filter( defaultFilter.default_filter.post_taxonomies, data );
									break;
								}
							}

							if ( 'cf_variation' == data.group_type ) {
								if ( 'undefined' === typeof $scope.variations[ data.ID ] ) {
									$scope.variations[ data.variation_id ] = {};
								}

								$scope.variations[ data.variation_id ].active = 'default';
							}

							dataAction = 'remove';

						}

						break;


					case "select":

						if ( data.value.constructor === Array ) {
							sVal = data.value[0];
						} else {
							sVal = data.value;
						}

						if (sVal.toLowerCase() == 'select' || sVal == '0') {

							dataToFilter = removeObjectKey(dataToFilter, data.ID);
							dataToFilter = removeObjectLikeKey( dataToFilter, data.ID );


							if ( 'cf_variation' == data.group_type ) {
								if ( 'undefined' === typeof $scope.variations[ data.ID ] ) {
									$scope.variations[ data.variation_id ] = {};
								}

								$scope.variations[ data.variation_id ].active = 'default';
							}

							dataAction = 'remove';

							if ( 'checkbox_post_terms' == data.filter_as ) {
									
								if ( null !== defaultFilter ) {
									defaultFilter.default_filter.post_taxonomies = lscf_reset_default_filter( defaultFilter.default_filter.post_taxonomies, data );
									break;
								}
							}

						} else if( 'checkbox_post_terms' == data.filter_as ) {
							dataToFilter = lscf_data_to_filter_subcategories( dataToFilter, data, filterFieldsTemplate );
						}

						break;

					case "radio":
						
						if ( data.value.constructor === Array ) {
							sVal = data.value[0];
						} else {
							sVal = data.value;
						}

						if ( sVal == '0' ) {
							dataToFilter = removeObjectKey(dataToFilter, data.ID);
							dataAction = 'remove';

							if ( 'cf_variation' == data.group_type ) {
								if ( 'undefined' === typeof $scope.variations[ data.ID ] ) {
									$scope.variations[ data.variation_id ] = {};
								}

								$scope.variations[ data.variation_id ].active = 'default';
							}

							if ( 'checkbox_post_terms' == data.filter_as ) {
									
								if ( null !== defaultFilter ) {
									defaultFilter.default_filter.post_taxonomies = lscf_reset_default_filter( defaultFilter.default_filter.post_taxonomies, data );
									break;
								}
							}

						} else if( 'checkbox_post_terms' == data.filter_as ) {
							dataToFilter = lscf_data_to_filter_subcategories( dataToFilter, data, filterFieldsTemplate );
						}

						break;

					case "date":

						if (data.value === '') {

							dataToFilter = removeObjectKey(dataToFilter, data.ID);
							dataAction = 'remove';
						}

						break;
				}

				if ( dataAction == 'add' ) {

					if ( 'cf_variation' == data.group_type ) {

						if ( 'undefined' === typeof $scope.variations[ data.ID ] ) {
							$scope.variations[ data.variation_id ] = {};
						}
						var variation_option = ( data.value.constructor == Array ? data.value[0] : data.value );
						$scope.variations[ data.variation_id ].active = lscf_sanitize_string( variation_option );
						
					}

					var fdFieldExists = false;

					for ( var k in dataToFilter ) {

						if ( dataToFilter[ k ].ID == data.ID ) {

							dataToFilter[ k ] = data;
							fdFieldExists = true;

							break;
						}

					}

					if ( false === fdFieldExists ) {
						dataToFilter[ data.ID ] = data;
					}

					if ( 'checkbox_post_terms' == data.filter_as ) {
						for ( var k_rr in dataToFilter ) {

							if ( 'default_filter' == dataToFilter[ k_rr ].type ) {
								defaultFilter.default_filter.post_taxonomies = lscf_reset_default_filter( defaultFilter.default_filter.post_taxonomies, data );
								break;
							}
						}
					}

					$scope.loadMoreBtn.type = 'default';	
				}

			});

			return dataToFilter;

		};

		$scope.lscfGetTaxDataFromFilters = function( dataToFilter, filterFields ) {

			var taxonomiesActiveIds = [],
				taxListIDs = [],
				activeParentIDs;

			for ( var key in dataToFilter ) {

				var item = dataToFilter[ key ];
				
				if ( 'undefined' !== typeof item.ID ) {

					if ( 'undefined' !== typeof item.filter_as && 'checkbox_post_terms' == item.filter_as ) {

						var tax_slug = lscf_return_tax_main_slug( item.ID ),
							catValue;

						if ( 'undefined' === typeof taxonomiesActiveIds[ tax_slug ] ) {
							taxonomiesActiveIds[ tax_slug ] = [];
							taxListIDs.push( item.ID );
						}

						if ( Array === item.value.constructor ) {
							
							for ( var i = 0; i < item.value.length; i++ ) {

								if ( 'undefined' !== typeof  filterFields.post_taxonomies[ tax_slug ] &&
										'undefined' !== typeof  filterFields.post_taxonomies[ tax_slug ].tax.subcategs_hierarchy && 
										'undefined' !== typeof  filterFields.post_taxonomies[ tax_slug ].tax.subcategs_hierarchy[ item.value[ i ] ] ) 
								{

									activeParentIDs = filterFields.post_taxonomies[ tax_slug ].tax.subcategs_hierarchy[ item.value[ i ] ];

									for ( n = 0; n < activeParentIDs.length; n++ ) {
										
										catValue = parseInt( activeParentIDs[ n ] );

										taxonomiesActiveIds[ tax_slug ][ catValue ] = catValue;
									}

								}
								catValue = parseInt( item.value[ i ] );

								taxonomiesActiveIds[ tax_slug ][ catValue ] = catValue;
							}

						} else {

							catValue = parseInt( item.value );

							taxonomiesActiveIds[ tax_slug ][ catValue ] = catValue;

							if ( 'undefined' !== typeof  filterFields.post_taxonomies[ tax_slug ] &&
								'undefined' !== typeof  filterFields.post_taxonomies[ tax_slug ].tax.subcategs_hierarchy &&
								'undefined' !== typeof  filterFields.post_taxonomies[ tax_slug ].tax.subcategs_hierarchy[ item.value ] ) 
							{

								activeParentIDs = filterFields.post_taxonomies[ tax_slug ].tax.subcategs_hierarchy[ item.value ];

								for ( n = 0; n < activeParentIDs.length; n++ ) {

									catValue = parseInt( activeParentIDs[ n ] );
									taxonomiesActiveIds[ tax_slug ][ catValue ] = catValue;
								}

							}

						}
						
					}
				}
			}
			return {
				activeTaxonomySlugs: taxListIDs,
				activeTaxonomyTermIds: taxonomiesActiveIds
			};
		};

		$scope.buildQueryFromDefaultFilter = function( defaultFilter, queryActiveTaxonomies ) {

			if ( null !== defaultFilter && 
				'undefined' !== typeof defaultFilter.default_filter.post_taxonomies && 
				'undefined' !== typeof defaultFilter.default_filter.post_taxonomies.active_terms &&
				defaultFilter.default_filter.post_taxonomies.active_terms.length > 0 ) 
			{
				var defaultQuery = {};

				for ( ki = 0; ki < defaultFilter.default_filter.post_taxonomies.active_terms.length; ki++ ) {

					var dqActiveTerm = defaultFilter.default_filter.post_taxonomies.active_terms[ ki ];

					if ( dqActiveTerm.match( /([0-9]+)-(.+?)$/ ) ) {

						var matches = dqActiveTerm.match( /([0-9]+)-(.+?)$/ );

						var qdTermID = matches[1],
							qdTaxSlug = matches[2];

						if ( -1 == queryActiveTaxonomies.indexOf( qdTaxSlug ) ) {
							
							if ( 'undefined' === typeof defaultQuery[ qdTaxSlug ] ) {

								defaultQuery[ qdTaxSlug ] = {
									"ID":qdTaxSlug,
									"filter_as":"checkbox_post_terms",
									"group_type":"taxonomies",
									"type":"checkbox_post_terms",
									"value":[ qdTermID ],
									"variation_id":null
								};

							} else {

								defaultQuery[ qdTaxSlug ].value.push( qdTermID );
							}
						}
					}
				}

				return defaultQuery;
			}
		};

		$scope.lscfBuildFilterQueryArgs = function( filterData, defaultFilter, filterFields ) {

			$scope.loadMoreBtn.postsLoading = true;

			if ( filterData.constructor !== Array ) {
				var dataArray = [];
					dataArray[0] = filterData;
					filterData = dataArray;
			}


			// Build Query Args of actioned filter's fields -> Frontend
			dataToFilter = $scope.lscfPrepareFilterData( filterData );

			// Get taxonomies data from Query Data. Used to hide/show hierarchy.
			var filterTaxonomyData = $scope.lscfGetTaxDataFromFilters( dataToFilter, filterFields );
			var taxonomyActiveIDs = filterTaxonomyData.activeTaxonomyTermIds,
				taxListIDs = filterTaxonomyData.activeTaxonomySlugs;

			taxonomyActiveIDs = lscf_reset_object_index( taxonomyActiveIDs );
			lscf_display_tax_subcategs( taxonomyActiveIDs );

			var defaultQuery = $scope.buildQueryFromDefaultFilter( defaultFilter, taxListIDs );
			
			var q = [];

			for ( var key in dataToFilter ) {

				var item = dataToFilter[ key ];
				
				if ( 'undefined' !== typeof item.ID && '' !== item.value ) {
					q.push( item );
				}
			}

			for ( var taxSlug in defaultQuery ) {
				q.push( defaultQuery[ taxSlug ] );
			}

			$scope.actionSettings.activeQuery = q;
			$scope.actionSettings.customFields = q;

			return q;

		};

		$scope.reset_filter = function(){

			filterFieldsAction.reset_fields();
			customFilterService.getPosts( postType, $scope.actionSettings.postsPerPage, 1, $scope.actionSettings.activeQuery, shortcodeSettings )
			.then(function (data) {
				data  = data.data;
				$scope.actionSettings.activeTerms = data.active_terms;
				$scope.filterPostsTemplate.filter_type = data.filter_type;
				$scope.loadMoreBtn.postsLoading = false;
				
				if (data.posts.length > 0) $scope.loadMoreBtn.noResults = false;
				else $scope.loadMoreBtn.noResults = true;

				if (data.featuredLabel === 1) $scope.featuredLabel = true;

				$scope.actionSettings.postsCount = data.postsCount;
				$scope.actionSettings.pagesCount = data.pages;
				$scope.actionSettings.pxCurrentPage = page + 1;
				$scope.allPostsCount = data.postsCount;


				if ( $scope.actionSettings.pxCurrentPage <= data.pages ) $scope.loadMoreBtn.morePostsAvailable = true;

				$scope.actionSettings.filterPostsTemplate = data.posts;

				$scope.filterPostsTemplate.posts = $scope.actionSettings.filterPostsTemplate;

				$scope.directiveInfo.ready();

				$scope.directiveInfo.afterPostsLoadCallback();

				$scope.loadMoreBtn.postsLoading = false;

			
			});

			dataToFilter = null;
			dataToFilter = [];
			$scope.actionSettings.customFields = null;
			$scope.actionSettings.customFields = [];
			$scope.actionSettings.pxCurrentPage = 2;
			$scope.loadMoreBtn.noResults = false;

			$scope.directiveInfo.afterPostsLoadCallback();
			$location.search('');

		};

		function lscf_infinite_scrolling() {

			var scrollTop = document.documentElement.scrollTop || document.body.scrollTop,
				body = document.body,
				html = document.documentElement,
				documentHeight = Math.max( body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight ),
				windowHeight = window.innerHeight;

			if ( ( scrollTop + windowHeight ) + 250 >= documentHeight  && false === $scope.loadMoreBtn.loading ) {
				$scope.load_more();
				documentHeight = Math.max( body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight );
			}
		}
		
		if ( 1 === $scope.pluginSettings.generalSettings.infinite_scrolling ) {
			window.addEventListener( 'scroll', lscf_infinite_scrolling );
			
		} else {
			window.removeEventListener( 'scroll', lscf_infinite_scrolling );
		}

		$scope.makeWrapperClassName = function( ) {

			var dataClass = {
				"sidebar":"",
				"posts_theme":""
				
			};

			if ( 'top' == $scope.pluginSettings.filterSettings.theme.sidebar.position ||  '0' == $scope.pluginSettings.filterSettings.theme.sidebar.position ) {
				dataClass.sidebar = 'col-sm-12 col-md-12 col-lg-12 lscf-horizontal-sidebar ';
				dataClass.posts_theme = 'col-sm-12 col-md-12 col-lg-12 lscf-wide-posts ';	
			} else {
				dataClass.sidebar =  ( shortcodeSettings.settings.theme.columns > 3 ? 'col-sm-2 col-md-2 col-lg-2 ' : 'col-sm-3 col-md-3 col-lg-3 ' );
				dataClass.posts_theme = ( shortcodeSettings.settings.theme.columns > 3 ? 'col-sm-10 col-md-10 col-lg-10 ' : 'col-sm-9 col-md-9 col-lg-9 ' );
			}

			return dataClass;

		};

		$scope.$watch('actionSettings.postsPerPage', function( newVal, oldVal ){

			if ( newVal != oldVal ) {
				
				customFilterService.getPosts( postType, $scope.actionSettings.postsPerPage, page, $scope.actionSettings.activeQuery, shortcodeSettings )
					.then(function (data) {
						data  = data.data;

						$scope.actionSettings.postsCount = data.postsCount;
						$scope.actionSettings.pagesCount = data.pages;
						$scope.actionSettings.pxCurrentPage = page + 1;

						if ($scope.actionSettings.pxCurrentPage > data.pages) $scope.loadMoreBtn.morePostsAvailable = false;
						$scope.loadMoreBtn.loading = false;

						$scope.actionSettings.filterPostsTemplate = data.posts;

						$scope.filterPostsTemplate.posts = $scope.actionSettings.filterPostsTemplate;

						$scope.directiveInfo.afterPostsLoadCallback();

				});
			}
		});

		$scope.$watch('actionSettings.initFieldsDraggable', function( newVal, oldVal ){

			if ( true === newVal ){
				
				filterFieldsMethod.fieldsData = {
					"fields": filterFieldsTemplate.default_data.fields,
					"filterID": filterID
				};
				
				setTimeout(function(){
					
					filterFieldsMethod.draggable.initFilterFields();
					filterFieldsMethod.draggable.initOptionOrder();

				}, 300);

			}

			if ( false === newVal ) {
				filterFieldsMethod.draggable.unbindOrder();
				filterFieldsMethod.draggable.unbindOptionOrder();
			}

		});

		$scope.$watch('actionSettings.initPostTheme', function( newVal, oldVal ){

			if ( true === newVal ) {
				$scope.actionSettings.initPostTheme = false;
				$scope.directiveInfo.ready();
			}

		});

		wrapperGeneralClassNames = $scope.makeWrapperClassName( $scope.pluginSettings.filterSettings.theme.sidebar.position );

		$scope.pluginSettings.className.sidebar = wrapperGeneralClassNames.sidebar;
		$scope.pluginSettings.className.posts_theme = wrapperGeneralClassNames.posts_theme;

		$scope.load_more = function () {

			$scope.loadMoreBtn.loading = true;

			if ( false === $scope.loadMoreBtn.morePostsAvailable ) { return; }

			jQuery( '.lscf-standartize-shorcodes-container-height' ).removeClass('lscf-standartize-shorcodes-container-height');

			if ( 'range' == $scope.loadMoreBtn.type ) {

				var page = $scope.actionSettings.rangeCurrentPage,
					posts_per_page = $scope.actionSettings.postsPerPage,
					offset = parseInt( page ) * parseInt( posts_per_page ),
					data_posts = [];
				
				for ( var i = offset; i < $scope.matched_posts.length; i++ ) {

					if ( 'undefined' !== typeof $scope.matched_posts[i] && i < ( offset + parseInt( posts_per_page ) ) ) {
						data_posts.push( $scope.matched_posts[i] );
					} else {

						break;
					}
					
				}

				if ( $scope.matched_posts.length <= ( offset + parseInt( posts_per_page ) ) ) {
					$scope.loadMoreBtn.morePostsAvailable = false;
				} else {
					$scope.loadMoreBtn.morePostsAvailable = true;
				}

				$scope.actionSettings.filterPostsTemplate = $scope.actionSettings.filterPostsTemplate.concat( data_posts );
				$scope.filterPostsTemplate.posts = $scope.actionSettings.filterPostsTemplate;
				$scope.directiveInfo.afterPostsLoadCallback();
				$scope.actionSettings.rangeCurrentPage += 1;
				$scope.loadMoreBtn.loading = false;

				return;
			}

			var loadMoreQ = $scope.actionSettings.activeQuery;

			if ( null !== defaultFilter && loadMoreQ.length === 0 ) {
				loadMoreQ = defaultFilter;
			}

			customFilterService
				.getPosts( postType, $scope.actionSettings.postsPerPage, $scope.actionSettings.pxCurrentPage, loadMoreQ, shortcodeSettings )
					.then(function (data) {
						data  = data.data;

						$scope.actionSettings.postsCount = data.postsCount;
						$scope.actionSettings.pagesCount = data.pages;
						$scope.actionSettings.pxCurrentPage += 1;

						if ($scope.actionSettings.pxCurrentPage > data.pages) $scope.loadMoreBtn.morePostsAvailable = false;
						$scope.loadMoreBtn.loading = false;

						$scope.actionSettings.filterPostsTemplate = $scope.actionSettings.filterPostsTemplate.concat( data.posts );
						$scope.filterPostsTemplate.posts = $scope.actionSettings.filterPostsTemplate;
						$scope.directiveInfo.afterPostsLoadCallback();

					});

		};

		customFilterService.getSidebar()
			.then(function( data ){
				data  = data.data;
				$scope.lscfSidebar.html = data;
			});

		customFilterService.getFilterFields( filterID )
			.then( function ( data ) {
				data  = data.data;

				var lscfQueryArgs;

				$scope.pluginSettings.main = data.settings;
				$scope.loadMoreBtn.sidebarLoading = false;
				$scope.actionSettings.urlActiveFilters = new lscfLoadFiltersFromUrl( $location.search(), data.fields_object );

				if ( 'undefined' !== typeof ( data.default_data.custom_templates ) ) {
					$scope.pluginSettings.custom_templates = data.default_data.custom_templates;
				}

				if ( 'undefined' !== typeof data.default_data.settings.theme.posts_display_from && 
					'undefined' !== data.default_data.settings.theme.posts_display_from.post_taxonomies.active_terms && 
					 data.default_data.settings.theme.posts_display_from.post_taxonomies.active_terms.length > 0 )  
				{

					var default_filter = {"default_filter":{}};
					if ( 'undefined' !== typeof data.fields ) {
						default_filter.default_filter.fields = data.fields;
					}

					default_filter.default_filter.post_taxonomies = data.default_data.settings.theme.posts_display_from.post_taxonomies;

					defaultFilter = default_filter;
					lscfQueryArgs = $scope.buildQueryFromDefaultFilter( defaultFilter, [] );

					for ( var key in lscfQueryArgs ) {
						$scope.actionSettings.activeQuery.push( lscfQueryArgs[ key ] );
					}

					if ( 'woocommerce' === $scope.pluginSettings.generalSettings.filter_type ) {
						if ( 'undefined' !== typeof $scope.pluginSettings.generalSettings.woo_instock && 1 === $scope.pluginSettings.generalSettings.woo_instock ) {
							lscfQueryArgs.woo_instock = {
								ID: 'woocommerce-instock',
								filter_as:'px_select_box',
								group_type:'custom_field',
								type:'select',
								value:'instock',
								variation_id: null
							};
						}
					}

				} else {
					defaultFilter = null;
				}

				if ( $scope.actionSettings.urlActiveFilters.hasCustomFieldsOrTaxonomies() ) {

					lscfQueryArgs = $scope.lscfBuildFilterQueryArgs( $scope.actionSettings.urlActiveFilters.activeFilters.filters, defaultFilter, data );
				}

				lscfQueryArgs = lscf_convert_object_to_array( lscfQueryArgs );

				$scope.actionSettings.activeQuery = lscfQueryArgs;

				customFilterService.getPosts(postType, $scope.actionSettings.postsPerPage, page, lscfQueryArgs, shortcodeSettings )
					.then(function ( data ) {
						data  = data.data;

						$scope.actionSettings.activeTerms = data.active_terms;
						$scope.filterPostsTemplate.filter_type = data.filter_type;
						$scope.loadMoreBtn.postsLoading = false;
						
						if ( data.posts.length > 0 ) $scope.loadMoreBtn.noResults = false;
						else $scope.loadMoreBtn.noResults = true;

						if ( data.featuredLabel === 1 ) $scope.featuredLabel = true;

						$scope.actionSettings.postsCount = data.postsCount;
						$scope.actionSettings.pagesCount = data.pages;
						$scope.actionSettings.pxCurrentPage = page + 1;
						$scope.allPostsCount = data.postsCount;


						if ( $scope.actionSettings.pxCurrentPage <= data.pages ) $scope.loadMoreBtn.morePostsAvailable = true;

						$scope.actionSettings.filterPostsTemplate = data.posts;
						
						$scope.filterPostsTemplate.posts = $scope.actionSettings.filterPostsTemplate;

						$scope.directiveInfo.ready();

						$scope.directiveInfo.afterPostsLoadCallback();

						$scope.loadMoreBtn.postsLoading = false;

				});

				var dataToFilter = [];
				if ( null !== defaultFilter ) {

					for ( var i = 0; i < defaultFilter.default_filter.post_taxonomies.active_terms.length; i++ ) {

						var matches = defaultFilter.default_filter.post_taxonomies.active_terms[i].match( /^([0-9]+)-(.*)/ ),
							catID = parseInt( matches[1] ),
							taxID = matches[2];

						for ( var k = 0; k < data.fields.length; k++ ) {

							if ( 'taxonomies' == data.fields[ k ].group_type &&
								'undefined' !== typeof data.fields[ k ].tax  && 
								data.fields[ k ].tax.slug == taxID ) 
							{

								data.fields[ k ].tax.activeTermsLength = defaultFilter.default_filter.post_taxonomies.active_terms.length;

								for ( var t = 0; t < data.fields[ k ].tax.terms.length; t++ ) {
									
									var term = data.fields[ k ].tax.terms[ t ];
									
									if ( catID == term.data.value ) {
										data.fields[ k ].tax.terms[ t ].checked = true;
									} 
								}
							}
						}
					}
				}

				filterFieldsTemplate = data;

				$scope.filterFieldsTemplate = filterFieldsTemplate;
				
				$scope.actionSettings.initSidebar = true;
				$scope.$watch('actionSettings.initSidebar', function( newVal, oldVal ){

					sidebarSection = $scope.pluginSettings.filterSettings.theme.sidebar.position == 'left' || $scope.pluginSettings.filterSettings.theme.sidebar.position == 'top' ? 1 :2;

					if ( $scope.actionSettings.previousSidebarPosition == 'left' || $scope.actionSettings.previousSidebarPosition == 'top' ) {
						
						previousSidebarSection = 1;

					} else if( '' !== $scope.actionSettings.previousSidebarPosition ) {
						previousSidebarSection = 2;
					}

					if ( true === newVal ) {

						$scope.actionSettings.initSidebar = false;
						$scope.actionSettings.previousSidebarPosition = $scope.pluginSettings.filterSettings.theme.sidebar.position;

						if ( sidebarSection === previousSidebarSection ) {
							return;
						}

						customSelectBoxes.construct();

						// callback; 
						//it's called on field action
						filterFieldsAction.construct(function ( dataValue ) {

							var q = $scope.lscfBuildFilterQueryArgs( dataValue, defaultFilter, data );

							// build URL location
							$scope.actionSettings.buildUrlLocation( q );

							q = lscf_convert_object_to_array( q );
							customFilterService.getPosts( postType, $scope.actionSettings.postsPerPage, page, q, shortcodeSettings )
								.then(function ( data ) {
									data  = data.data;

									$scope.actionSettings.lsLoadPosts = true;

									// Adaptive filtering.
									if ( data.active_terms.length > 0 ) {
										
										$scope.actionSettings.disableInactiveTerms = true;

										var filterFieldsData = $scope.filterFieldsTemplate.fields;

										for ( var i = 0; i < filterFieldsData.length; i++ ) {

											if ( 'taxonomies' == filterFieldsData[ i ].group_type ) {

												for ( var t = 0; t < filterFieldsData[ i ].tax.terms.length; t++ ) {

													var term_id = filterFieldsData[ i ].tax.terms[ t ].data.value;

													if ( 'undefined' === typeof data.active_terms[ term_id ] ) {
														filterFieldsData[ i ].tax.terms[ t ].data.not_active = true;
													} else {
														filterFieldsData[ i ].tax.terms[ t ].data.not_active = false;
													}
												}

											} else if ( 'custom_field' == filterFieldsData[ i ].group_type ) {

												if ( 'undefined' !== typeof data.active_terms.custom_fields && 'undefined' !== typeof filterFieldsData[ i ].options ) {

													for ( var k = 0; k < filterFieldsData[ i ].options.length; k++ ) {

														var slug = filterFieldsData[ i ].ID + lscf_sanitize_string( filterFieldsData[ i ].options[ k ].opt );

														if ( 'undefined' === typeof data.active_terms.custom_fields[ slug ] ) {
															filterFieldsData[ i ].options[ k ].not_active = true;
														} else {
															filterFieldsData[ i ].options[ k ].not_active = false;
														}
													}
												}
											}
										}


										$scope.filterFieldsTemplate.fields = filterFieldsData;


										setTimeout(function(){
											lscf_custom_dropdowns_update_options_classnames();
										}, 500);
										

									} else {
										$scope.actionSettings.disableInactiveTerms = false;
										setTimeout(function(){
											lscf_custom_dropdowns_update_options_classnames();
										}, 500);
									}


									if (data.posts.length > 0) $scope.loadMoreBtn.noResults = false;
									else $scope.loadMoreBtn.noResults = true;

									$scope.actionSettings.activeTerms = data.active_terms;
									$scope.actionSettings.postsCount = data.postsCount;
									$scope.actionSettings.pagesCount = data.pages;
									$scope.actionSettings.pxCurrentPage = page + 1;
									$scope.actionSettings.customFields = q;

									if ( $scope.actionSettings.pxCurrentPage <= data.pages ) $scope.loadMoreBtn.morePostsAvailable = true;
									else $scope.loadMoreBtn.morePostsAvailable = false;

									$scope.actionSettings.filterPostsTemplate = data.posts;
									$scope.filterPostsTemplate.posts = data.posts;
									$scope.loadMoreBtn.postsLoading = false;

									$scope.directiveInfo.afterPostsLoadCallback();

								});
						});
					}
				});


				rangeField.construct(function ( data ) {

					// reset page
					page = 1;

					var q = $scope.actionSettings.activeQuery,
						fieldExists = false;


					for ( var i = 0; i < q.length; i++ ) {

						if ( 'undefined' !== typeof q[ i ].ID ) {
							if ( data.ID === q[ i ].ID &&  'range' === q[ i ].type ){
								q[ i ] = data;
								fieldExists = true;
							}
						}
					}

					if ( false === fieldExists ) {
						q.push( data );
					}

					$scope.loadMoreBtn.postsLoading = true;
					$scope.actionSettings.rangeCurrentPage = 2;

					// build URL location
					$scope.actionSettings.buildUrlLocation( q );

					q = lscf_convert_object_to_array( q );
					customFilterService.getPosts(postType, $scope.actionSettings.postsPerPage, page, q, shortcodeSettings)
						.then(function ( data ) {
							data  = data.data;

							if (data.posts.length > 0) $scope.loadMoreBtn.noResults = false;
							else $scope.loadMoreBtn.noResults = true;
							

							$scope.actionSettings.activeTerms = data.active_terms;
							$scope.matched_posts = data.matched_posts;

							$scope.actionSettings.postsCount = data.postsCount;
							$scope.actionSettings.pagesCount = data.pages;
							$scope.actionSettings.pxCurrentPage = page + 1;
							$scope.actionSettings.customFields = q;

							if ( $scope.actionSettings.pxCurrentPage <= data.pages ) $scope.loadMoreBtn.morePostsAvailable = true;
							else $scope.loadMoreBtn.morePostsAvailable = false;	

							$scope.actionSettings.filterPostsTemplate = data.posts;
							$scope.filterPostsTemplate.posts = data.posts;

							$scope.directiveInfo.afterPostsLoadCallback();

							$scope.loadMoreBtn.postsLoading = false;

						});
				});
			});
	}]);
angular.module(angAppName)
	.controller("lscfFiltersOnlyController", ['$scope', '$sce', '$location', '$attrs', 'customFilterService', function ( $scope, $sce, $location, $attrs, customFilterService ) {

		var shortcodeIndex = $attrs.index,
			shortcodeSettings = lscfShortcodeData[ shortcodeIndex ];
			shortcodeSettings.shortcodeIndex = $attrs.index;

		var rangeField = new px_customRange( parseInt( $attrs.index ) ),
			customSelectBoxes = new customSelectBox(),
			filterFieldsMethod = new lscfOrderFilterFields( shortcodeSettings ),
			filterFieldsAction = new pxFilterFieldsAction( shortcodeSettings ),
			filterID = shortcodeSettings.ID;
			filterFieldsTemplate = [];
		
		$scope.actionSettings = {
			'controllerAttributes': $attrs,
			'redirectURI': '',
			'filtersOnly': true,
			'activeQuery': [],
			'style': {
				'postMaxHeight': null
			},
			'initSidebar': false,
			'customFields': [],
			'isAdministrator': ( 'undefined' !== typeof shortcodeSettings.settings.is_administrator ? parseInt( shortcodeSettings.settings.is_administrator ) : 0 )
		};

		$scope.pluginSettings = {
			'className': {
				'sidebar': '',
				'posts_theme': ''
			},
			'ajaxSearch': true,
			'existsPosts': true,
			'pluginPath': shortcodeSettings.plugin_url,
			'filterSettings': shortcodeSettings.settings,
			'generalSettings': shortcodeSettings.options,
			'editShortcodeLink': shortcodeSettings.site_url + '/wp-admin/admin.php?page=pxLF_plugin&plugin-tab=filter-generator&edit_filter=' + shortcodeSettings.ID
		};

		$scope.filterPostsTemplate = {};

		$scope.reload_page = function( $event ) {
			var currentWindowUrl = window.location.href.replace( /(.+?)(\/$)|(\/#.*$)/, '$1' ),
				redirectPage = scope.pluginSettings.filterSettings.redirect_page.replace( /(.+?)(\/$)|(\/#.*$)/, '$1' );

			window.location.href = scope.pluginSettings.filterSettings.redirect_page + '#!/' + scope.actionSettings.redirectURI;

			if ( currentWindowUrl == redirectPage ) {
				window.location.reload();
			}
		};

		$scope.checkboxListClassname = function( itemIndex ) {

			var className = '';

			if ( 1 !== $scope.pluginSettings.generalSettings.hide_see_more_on_checkboxes_list && itemIndex > 5 ) {
				className += ' px-hidden-opt';
			}

			return className;
		};

		$scope.returnFieldClassnames = function( field ) {

			var classNames = '';

			if ( 'undefined' !== typeof field.tax && 'undefined' !== typeof field.tax.subcategories_hierarchy_display && 
				1 === field.tax.subcategories_hierarchy_display ) {
				classNames += 'lscf-hierarchical-terms';
			}

			if ( 'undefined' !== typeof field.tax ) {
				if ( 'undefined' !== typeof field.tax && ( field.tax.display_as == 'px_check_box' || 
				field.tax.display_as == 'px_radio_box' ) ) {

					classNames += 'lscf-large-field';
					return classNames;
				}
			}

			if ( 'undefined' !== typeof field.group_type && field.group_type == 'additional_fields' ) {
				classNames += 'lscf-large-field';
				return classNames;
			}

			if ( 'undefined' !== typeof field.display_as ) {
				if ( field.display_as == 'px_radio_box' || field.display_as == 'px_check_box' ||
				field.display_as == 'px_icon_check_box' || field.display_as == 'px_check_icon-only_box' ||
				field.display_as == 'px_check_icon-text_box' ) {

					classNames += 'lscf-large-field';
					return classNames;
				}
			}

			return classNames;
		};

		$scope.formatRangeValue = function( label, value, labelPosition ) {
			if ( 'undefined' !== labelPosition && 'right' === labelPosition ) {
				return value + label;
			}
			return label + value;
		};

		$scope.initRangeValue = function( field, type ) {
			
			var fieldID = 'range_' + field.fieldID,
				label = field.label,
				defaultVal = ( 'max' === type ? field.max : field.min );

			if ( 'undefined' === typeof field.label_position ) {
				field.label_position = 'right';
			}

			if ( null === $scope.actionSettings.urlActiveFilters ) { return $scope.formatRangeValue( label, defaultVal, field.label_position ); }

			if ( 'undefined' === typeof $scope.actionSettings.urlActiveFilters.activeFields || 
			'undefined' === typeof $scope.actionSettings.urlActiveFilters.activeFields[ fieldID ] ) {
				return $scope.formatRangeValue( label, defaultVal, field.label_position );
			}
			
			var rangeField = $scope.actionSettings.urlActiveFilters.activeFields[ fieldID ];
			if ( 'undefined' === typeof rangeField[0] || 'undefined' === typeof rangeField[1] ) {
				return $scope.formatRangeValue( label, defaultVal, field.label_position );
			}

			if ( 'min' === type ) {
				return $scope.formatRangeValue( label, rangeField[ 0 ], field.label_position );
			}

			if ( 'max' === type ) {
				return $scope.formatRangeValue( label, rangeField[ 1 ], field.label_position );
			}

			return $scope.formatRangeValue( label, defaultVal, field.label_position );
		};
		
		$scope.lscfPrepareFilterData = function( dataFilters ) {

			var dataToFilter = $scope.actionSettings.customFields,
				dataAction;

			dataFilters.forEach(function( data ) {
				//reset the action key
				dataAction = "add";

				switch ( data.type ) {

					case "date-interval":

						if ( data.fields.from === '' || data.fields.to === '' ) {

							return;
						}

						break;

					case "checkbox_post_terms":

						if ( data.value.length === 0 ) { 

							dataToFilter = removeObjectKey( dataToFilter, data.ID );

							if ( 'cf_variation' == data.group_type ) {
								if ( 'undefined' === typeof $scope.variations[ data.ID ] ) {
									$scope.variations[ data.variation_id ] = {};
								}

								$scope.variations[ data.variation_id ].active = 'default';
							}
							dataAction = 'remove';
						}

						
						break;

					case "px_icon_check_box":
					case "checkbox":

						if ( data.value.length === 0 ) {

							dataToFilter = removeObjectKey(dataToFilter, data.ID);

							if ( 'checkbox_post_terms' == data.filter_as ) {
							
								for ( var k_cb in dataToFilter ) {
								
									if ( 'default_filter' == dataToFilter[ k_cb ].type ) {
										dataToFilter[ k_cb ].default_filter.post_taxonomies = lscf_reset_default_filter( dataToFilter[ k_cb ].default_filter.post_taxonomies, data );
										break;
									}
								}

							}

							if ( 'cf_variation' == data.group_type ) {
								if ( 'undefined' === typeof $scope.variations[ data.ID ] ) {
									$scope.variations[ data.variation_id ] = {};
								}

								$scope.variations[ data.variation_id ].active = 'default';
							}

							dataAction = 'remove';

						}

						break;


					case "select":

						if ( data.value.constructor === Array ) {
							sVal = data.value[0];
						} else {
							sVal = data.value;
						}

						if (sVal.toLowerCase() == 'select' || sVal == '0') {

							dataToFilter = removeObjectKey(dataToFilter, data.ID);
							dataToFilter = removeObjectLikeKey( dataToFilter, data.ID );


							if ( 'cf_variation' == data.group_type ) {
								if ( 'undefined' === typeof $scope.variations[ data.ID ] ) {
									$scope.variations[ data.variation_id ] = {};
								}

								$scope.variations[ data.variation_id ].active = 'default';
							}

							dataAction = 'remove';

							if ( 'checkbox_post_terms' == data.filter_as ) {
								for ( var k_s in dataToFilter ) {
									
									if ( 'default_filter' == dataToFilter[ k_s ].type ) {
										dataToFilter[ k_s ].default_filter.post_taxonomies = lscf_reset_default_filter( dataToFilter[ k_s ].default_filter.post_taxonomies, data );
										break;
									}
								}
							}

						} else if( 'checkbox_post_terms' == data.filter_as ) {

							dataToFilter = lscf_data_to_filter_subcategories( dataToFilter, data, filterFieldsTemplate );

						}

						break;

					case "radio":
						
						if ( data.value.constructor === Array ) {
							sVal = data.value[0];
						} else {
							sVal = data.value;
						}

						if ( sVal == '0' ) {
							
							dataToFilter = removeObjectKey(dataToFilter, data.ID);

							dataAction = 'remove';

							if ( 'cf_variation' == data.group_type ) {
								if ( 'undefined' === typeof $scope.variations[ data.ID ] ) {
									$scope.variations[ data.variation_id ] = {};
								}

								$scope.variations[ data.variation_id ].active = 'default';
							}

							if ( 'checkbox_post_terms' == data.filter_as ) {
								for ( var k_r in dataToFilter ) {
								
									if ( 'default_filter' == dataToFilter[ k_r ].type ) {
										dataToFilter[ k_r ].default_filter.post_taxonomies = lscf_reset_default_filter( dataToFilter[ k_r ].default_filter.post_taxonomies, data );
										break;
									}
								}
							}

						} else if( 'checkbox_post_terms' == data.filter_as ) {

							dataToFilter = lscf_data_to_filter_subcategories( dataToFilter, data, filterFieldsTemplate );

						}

						break;

					case "date":

						if (data.value === '') {

							dataToFilter = removeObjectKey(dataToFilter, data.ID);
							dataAction = 'remove';
						}

						break;
				}

				if ( dataAction == 'add' ) {

					if ( 'cf_variation' == data.group_type ) {

						if ( 'undefined' === typeof $scope.variations[ data.ID ] ) {
							$scope.variations[ data.variation_id ] = {};
						}
						var variation_option = ( data.value.constructor == Array ? data.value[0] : data.value );
						$scope.variations[ data.variation_id ].active = lscf_sanitize_string( variation_option );
						
					}


					var fdFieldExists = false;
					for ( var k in dataToFilter ) {

						if ( dataToFilter[ k ].ID == data.ID ) {

							dataToFilter[ k ] = data;
							fdFieldExists = true;

							break;
						}

					}

					if ( false === fdFieldExists ) {
						dataToFilter[ data.ID ] = data;
					}

					if ( 'checkbox_post_terms' == data.filter_as ) {
						for ( var k_rr in dataToFilter ) {

							if ( 'default_filter' == dataToFilter[ k_rr ].type ) {
								dataToFilter[ k_rr ].default_filter.post_taxonomies = lscf_reset_default_filter( dataToFilter[ k_rr ].default_filter.post_taxonomies, data );
								break;
							}
						}
					}
				}

			});

			return dataToFilter;

		};

		$scope.lscfGetTaxDataFromFilters = function( dataToFilter, filterFields ) {

			var taxonomiesActiveIds = [],
				taxListIDs = [],
				activeParentIDs;

			for ( var key in dataToFilter ) {

				var item = dataToFilter[ key ];
				
				if ( 'undefined' !== typeof item.ID ) {

					if ( 'undefined' !== typeof item.filter_as && 'checkbox_post_terms' == item.filter_as ) {

						var tax_slug = lscf_return_tax_main_slug( item.ID ),
							catValue;

						if ( 'undefined' === typeof taxonomiesActiveIds[ tax_slug ] ) {
							taxonomiesActiveIds[ tax_slug ] = [];
							taxListIDs.push( item.ID );
						}

						if ( Array === item.value.constructor ) {
							
							for ( var i = 0; i < item.value.length; i++ ) {

								if ( 'undefined' !== typeof  filterFields.post_taxonomies[ tax_slug ] &&
										'undefined' !== typeof  filterFields.post_taxonomies[ tax_slug ].tax.subcategs_hierarchy && 
										'undefined' !== typeof  filterFields.post_taxonomies[ tax_slug ].tax.subcategs_hierarchy[ item.value[ i ] ] ) 
								{

									activeParentIDs = filterFields.post_taxonomies[ tax_slug ].tax.subcategs_hierarchy[ item.value[ i ] ];

									for ( n = 0; n < activeParentIDs.length; n++ ) {
										
										catValue = parseInt( activeParentIDs[ n ] );

										taxonomiesActiveIds[ tax_slug ][ catValue ] = catValue;
									}

								}
								catValue = parseInt( item.value[ i ] );

								taxonomiesActiveIds[ tax_slug ][ catValue ] = catValue;
							}

						} else {

							catValue = parseInt( item.value );

							taxonomiesActiveIds[ tax_slug ][ catValue ] = catValue;

							if ( 'undefined' !== typeof  filterFields.post_taxonomies[ tax_slug ] &&
								'undefined' !== typeof  filterFields.post_taxonomies[ tax_slug ].tax.subcategs_hierarchy &&
								'undefined' !== typeof  filterFields.post_taxonomies[ tax_slug ].tax.subcategs_hierarchy[ item.value ] ) 
							{

								activeParentIDs = filterFields.post_taxonomies[ tax_slug ].tax.subcategs_hierarchy[ item.value ];

								for ( n = 0; n < activeParentIDs.length; n++ ) {

									catValue = parseInt( activeParentIDs[ n ] );
									taxonomiesActiveIds[ tax_slug ][ catValue ] = catValue;
								}

							}

						}
						
					}

				}
			}

			return {
				activeTaxonomySlugs: taxListIDs,
				activeTaxonomyTermIds: taxonomiesActiveIds
			};
		};

		$scope.buildQueryFromDefaultFilter = function( defaultFilter, queryActiveTaxonomies ) {

			if ( null !== defaultFilter && 
				'undefined' !== typeof defaultFilter.default_filter.post_taxonomies && 
				'undefined' !== typeof defaultFilter.default_filter.post_taxonomies.active_terms &&
				defaultFilter.default_filter.post_taxonomies.active_terms.length > 0 ) 
			{
				var defaultQuery = {};

				for ( ki = 0; ki < defaultFilter.default_filter.post_taxonomies.active_terms.length; ki++ ) {

					var dqActiveTerm = defaultFilter.default_filter.post_taxonomies.active_terms[ ki ];

					if ( dqActiveTerm.match( /([0-9]+)-(.+?)$/ ) ) {

						var matches = dqActiveTerm.match( /([0-9]+)-(.+?)$/ );

						var qdTermID = matches[1],
							qdTaxSlug = matches[2];

						if ( -1 == queryActiveTaxonomies.indexOf( qdTaxSlug ) ) {
							
							if ( 'undefined' === typeof defaultQuery[ qdTaxSlug ] ) {

								defaultQuery[ qdTaxSlug ] = {
									"ID":qdTaxSlug,
									"filter_as":"checkbox_post_terms",
									"group_type":"taxonomies",
									"type":"checkbox_post_terms",
									"value":[ qdTermID ],
									"variation_id":null
								};

							} else {

								defaultQuery[ qdTaxSlug ].value.push( qdTermID );
							}
						}
					}
				}

				return defaultQuery;
			}
		};

		$scope.lscfBuildFilterQueryArgs = function( filterData, defaultFilter, filterFields ) {

			if ( filterData.constructor !== Array ) {
				var dataArray = [];
					dataArray[0] = filterData;
					filterData = dataArray;
			}

			// Build Query Args of actioned filter's fields -> Frontend
			dataToFilter = $scope.lscfPrepareFilterData( filterData );

			// Get taxonomies data from Query Data. Used to hide/show hierarchy.
			var filterTaxonomyData = $scope.lscfGetTaxDataFromFilters( dataToFilter, filterFields );
			var taxonomyActiveIDs = filterTaxonomyData.activeTaxonomyTermIds,
				taxListIDs = filterTaxonomyData.activeTaxonomySlugs;

			taxonomyActiveIDs = lscf_reset_object_index( taxonomyActiveIDs );
			lscf_display_tax_subcategs( taxonomyActiveIDs );

			var defaultQuery = $scope.buildQueryFromDefaultFilter( defaultFilter, taxListIDs );

			var q = [];
			for ( var key in dataToFilter ) {

				var item = dataToFilter[ key ];
				
				if ( 'undefined' !== typeof item.ID ) {
					q.push( item );
				}
			}

			for ( var taxSlug in defaultQuery ) {
				q.push( defaultQuery[ taxSlug ] );
			}

			$scope.actionSettings.customFields = q;
			$scope.actionSettings.activeQuery = q;

			return q;

		};

		customFilterService.getFilterFields( filterID )
			.then( function ( data ) {
				data  = data.data;
				filterType = data.filter_type;

				var lscfQueryArgs = null;
				$scope.actionSettings.urlActiveFilters = new lscfLoadFiltersFromUrl( $location.search(), data.fields_object );

				if ( $scope.actionSettings.urlActiveFilters.hasCustomFieldsOrTaxonomies() ) {
					lscfQueryArgs = $scope.lscfBuildFilterQueryArgs( $scope.actionSettings.urlActiveFilters.activeFilters.filters, null, data );
				}

				filterFieldsTemplate = data;

				$scope.filterFieldsTemplate = filterFieldsTemplate;
				$scope.actionSettings.initSidebar = true;

				customSelectBoxes.construct();

				filterFieldsAction.construct(function ( dataValue ) {

					var q = $scope.lscfBuildFilterQueryArgs( dataValue, null, data );

					// build URL location
					var urlLocationData = $scope.actionSettings.urlActiveFilters.buildUrlLocation( q );

					var urlString = $location.search( urlLocationData ).url();

					$location.search('');
					$scope.$apply( function(){
						$scope.actionSettings.redirectURI = urlString;
					});

				});

				rangeField.construct(function ( data ) {

					var q = $scope.actionSettings.customFields,
						fieldExists = false;

					for ( var i = 0; i < q.length; i++ ) {

						if ( 'undefined' !== typeof q[ i ].ID ) {
							if ( data.ID === q[ i ].ID &&  'range' === q[ i ].type ){
								fieldExists = true;
							}
						}
					}

					if ( false === fieldExists ) {
						q.push( data );
					}

					$scope.actionSettings.customFields = q;

					// build URL location
					var urlLocationData = $scope.actionSettings.urlActiveFilters.buildUrlLocation( q );
					var urlString = $location.search( urlLocationData ).url();
					$location.search('');
					$scope.$apply( function(){
						$scope.actionSettings.redirectURI = urlString;
					});
				});
		});
		
	}]);

angular.module(angAppName)
	.factory( "capfAPI", ['$http', function($http){

		var URI = pxData.ajaxURL+"?action=px-ang-http",
			restApiUri = lscfShortcodeData[0].rest_api_uri + 'lscf_rest/';

		return{
			uri: URI,
			restApi: {
				uri: restApiUri,
				getSidebar: restApiUri + 'get_sidebar',
				filterPosts: restApiUri + 'filter_posts'
			},

		};

	}]);
angular.module(angAppName)
	.factory( "customFilterService", ['$http', 'capfAPI', function($http, capfAPI){

		var isWpml = ( '1' === lscfShortcodeData[0].wpml ? 1 : 0 ),
			lang = ( false !== isWpml ? lscfShortcodeData[0].lang : null  );

		function getFilterFields( ID ){
			
			return $http({
				method:"post",
				url:capfAPI.restApi.getSidebar,
				data:{
					filter_id:ID
				}
			});
			
		}

		function getSidebar(  ){
			
			return $http({
				method: 'post',
				url: capfAPI.uri,
				data: {
					section: 'getSidebar'
				}
			});
			
		}

		function getPosts(postType, postsPerPage, page, q, shortcodeSettings ) {
			
			return $http({
				method: 'post',
				url: capfAPI.restApi.filterPosts,
				data: {
					post_type: postType,
					featured_label: shortcodeSettings.featuredLabel,
					limit: postsPerPage,
					page: page,
					q: q,
					is_wpml: isWpml,
					lang: lang,
					filter_id: shortcodeSettings.ID
				}
			});
			
		}
		function getAllPosts( postType, postsPerPage, page, q, shortcodeSettings ){
			return $http({
				method: 'post',
				url: capfAPI.restApi.filterPosts,
				data: {
					post_type: postType,
					featured_label: shortcodeSettings.featuredLabel,
					limit: postsPerPage,
					page: page,
					q: q,
					is_wpml: isWpml,
					lang: lang,
					filter_id: shortcodeSettings.ID
				}
			});
		}

		return{
			getAllPosts: getAllPosts,
			getFilterFields: getFilterFields,
			getPosts: getPosts,
			getSidebar: getSidebar
		};

	}]);
angular.module(angAppName)
	.directive('viewmodeAccordion', ['customFilterService', function(customFilterService){

		return{
			
			restrict:"AE",
			scope:true,
			bindToController: true,
			controllerAs: 'vm',
			templateUrl: lscfShortcodeData[0].plugin_url + 'app/views/posts-accordion.html',
			link:function(scope, elem, attrs){

				var accordionPosts = new lscfAccordionPosts();
				
				scope.actionSettings.initPostTheme = true;

				scope.directiveInfo.ready = function(){

					accordionPosts.options.link_type = ( 'undefined' !== typeof scope.pluginSettings.filterSettings.theme.link_type ? scope.pluginSettings.filterSettings.theme.link_type : 0 );

					setTimeout(function(){
						accordionPosts.init();
					}, 500);
					

				};					
					
				scope.directiveInfo.afterPostsLoadCallback = function(){

					setTimeout(function(){
						accordionPosts.init();
					}, 500);
				};


				function lscfAccordionPosts(){
	
					var $j = jQuery,
						self = this;
				
					this.options = {
						"link_type":0
					};
				
					this.init = function(){
						$j('.lscf-posts-accordion .lscf-title').unbind('click');
				
						if ( 'link-only' === self.options.link_type ) {
							return false;
						 }
						
						$j('.lscf-posts-accordion .lscf-title').bind( 'click', function(event){
				
							var parentContainer = $j(this).closest('.lscf-accordion-post');
				
							if ( parentContainer.hasClass('active') ) {
								parentContainer.find('.post-caption').animate({
									height:0
								}, 400);	
								parentContainer.removeClass('active');
								parentContainer.addClass('inactive');
								return false;
							}
				
							$j('.lscf-accordion-post').removeClass('active');
							$j('.lscf-accordion-post').addClass('inactive');
				
							
							parentContainer.addClass('active');
							parentContainer.removeClass('inactive');
				
							var animateHeight = parentContainer.find('.caption').height()+40;
				
							parentContainer.find('.post-caption').animate({
								height:animateHeight
							}, 400);
				
							$j('.lscf-accordion-post.inactive').find('.post-caption').animate({
								height:0
							}, 300);
				
							event.preventDefault();
							event.stopPropagation();
				
							return false;
						});
				
					};
				
				}
			}
		};
	}]);

angular.module(angAppName)
	.directive('ajaxSearch', ['$location', 'customFilterService', function( $location, customFilterService ) {

		return{

			restrict: 'AE',
			require: '?ngModel',
			scope: true,
			bindToController: true,
			controllerAs: 'vm',
			link: function( scope, elem, attrs, ngModel ) {

				var activePostsList = [];
				var searchType = attrs.searchType,
					shortcodeSettings = lscfShortcodeData[ scope.actionSettings.controllerAttributes.index ];

				scope.preventInputBlur = false;

				if ( null !== scope.actionSettings.urlActiveFilters && scope.actionSettings.urlActiveFilters.hasGeneralSearch() ) {
					var generalSearches = scope.actionSettings.urlActiveFilters.activeFilters.generalSearch;
					var searchValue = generalSearches[0].value;
					scope.pxsearch = searchValue;
				}

				scope.buildLocationOnBlur = function(){
					if ( ! scope.actionSettings.filtersOnly ) {
						if ( true === scope.preventInputBlur ) {
							scope.preventInputBlur = false;
							return;
						}
						scope.ajaxSearch();
						return;
					}
					var fieldExists = false,
						activeQuery = scope.actionSettings.activeQuery;

					for ( var i = 0; i < activeQuery.length; i++ ) {
						if ( 'undefined' !== typeof activeQuery[ i ].ID && 'ajax-main-search' === activeQuery[ i ].ID ) {
							activeQuery[ i ].value = scope.pxsearch;
							fieldExists = true;
						}
					}

					var q = activeQuery;
					if ( false === fieldExists ) {
						q.push({ ID: 'ajax-main-search', value: scope.pxsearch });
					}

					var urlLocationData = scope.actionSettings.urlActiveFilters.buildUrlLocation( q );
					var urlParams =  $location.search(urlLocationData).url();
					scope.actionSettings.redirectURI = urlParams;


					var currentWindowUrl = window.location.href.replace( /(.+?)(\/$)|(\/#.*$)/, '$1' ),
						redirectPage = scope.pluginSettings.filterSettings.redirect_page.replace( /(.+?)(\/$)|(\/#.*$)/, '$1' );

					if ( currentWindowUrl != redirectPage ) {
						$location.search('');
					}
				};

				// don't run posts filtering in case if it's displayed as filter only.
				if ( 'undefined' !== typeof scope.actionSettings.filtersOnly && true === scope.actionSettings.filtersOnly ) {

					scope.pxsearch = '';
					elem.bind( 'keydown', function ( event ) {

						if ( event.which === 13 ) {
							scope.buildLocationOnBlur();
							var currentWindowUrl = window.location.href.replace( /(.+?)(\/$)|(\/#.*$)/, '$1' ),
								redirectPage = scope.pluginSettings.filterSettings.redirect_page.replace( /(.+?)(\/$)|(\/#.*$)/, '$1' );

							window.location.href = scope.pluginSettings.filterSettings.redirect_page + '#!' + scope.actionSettings.redirectURI;
							if ( currentWindowUrl == redirectPage ) {
								window.location.reload();
							}
						}

					});
					return;
				}

				scope.ajaxSearch = function(){

					var searchQ,
						query = {},
						field_exists = false,
						field;

					if ( 'undefined' === typeof scope.pxsearch && 'undefined' === typeof scope.pxsearch_woo_sku ) {
						return;
					}

					switch ( searchType ) {

						case "general-search":

							searchQ = ( '' !== scope.pxsearch ? scope.pxsearch : ' ' );
							field_exists = false;

							for ( var c = 0; c < scope.actionSettings.activeQuery.length; c++ ) {

								field = scope.actionSettings.activeQuery[ c ];

								if ( 'ajax-main-search' == field.ID ) {

									scope.actionSettings.activeQuery[ c ].value = searchQ;

									field_exists = true;
								}
							}

							if ( false === field_exists ) {

								query = {
									'ID': 'ajax-main-search',
									'filter_as': null,
									'type': 'main-search',
									'value': searchQ
								};

								scope.actionSettings.activeQuery.push( query );
							}

							break;

						case "woo-product-sku":

							searchQ = scope.pxsearch_woo_sku;
							field_exists = false;

							for ( var i = 0; i < scope.actionSettings.activeQuery.length; i++ ) {
								field = scope.actionSettings.activeQuery[ i ];
								if ( 'ajax-product-sku-search' == field.ID ) {
									scope.actionSettings.activeQuery[ i ].value = searchQ;
									field_exists = true;
								}
							}

							if ( false === field_exists ) {
								query = {
									'ID': 'ajax-product-sku-search',
									'filter_as': null,
									'type': 'main-search',
									'value': searchQ
								};
								scope.actionSettings.activeQuery.push( query );
							}
							break;
					}

					scope.loadMoreBtn.postsLoading = true;

					scope.actionSettings.buildUrlLocation( scope.actionSettings.activeQuery );

					customFilterService.getPosts( scope.postType, scope.actionSettings.postsPerPage, 1, scope.actionSettings.activeQuery, shortcodeSettings )
						.then(function( data ) {
							data  = data.data;

							scope.actionSettings.postsCount = data.postsCount;
							scope.actionSettings.pagesCount = data.pages;
							scope.actionSettings.pxCurrentPage = 2;

							if ( scope.actionSettings.pxCurrentPage <= data.pages) scope.loadMoreBtn.morePostsAvailable = true;
							else scope.loadMoreBtn.morePostsAvailable = false;

							if (data.posts.length > 0) scope.loadMoreBtn.noResults = false;
							else scope.loadMoreBtn.noResults = true;

							scope.actionSettings.filterPostsTemplate = data.posts;
							scope.filterPostsTemplate.posts = data.posts;
							scope.loadMoreBtn.postsLoading = false;

							scope.directiveInfo.afterPostsLoadCallback();

						});
				};

				elem.bind( 'keydown', function ( event ) {

					if ( event.which === 13 ) {
						scope.ajaxSearch();
						scope.preventInputBlur = true;
						event.preventDefault();
					}
				});

			}

		};

	}]);
angular.module(angAppName)
    .directive('viewmodeCustom', ['customFilterService', function( customFilterService ) {
		
        return{
            
            restrict:"AE",
            scope:true,
            bindToController: true,
            controllerAs: 'vm',
            link:function( scope, elem, attrs ) {

				var accordionPosts = new lscfAccordionPosts();
				scope.directiveInfo.ready = function(){

					setTimeout(function(){
						accordionPosts.init();
					}, 500);

					if ( 'undefined' !== typeof lscfOnCustomTemplateReady ) {
						lscfOnCustomTemplateReady();
					}

					setTimeout(function(){
						lscfEventListenerOnCustomTemplateReady();
					}, 300);
				};					
					
				scope.directiveInfo.afterPostsLoadCallback = function(){

					setTimeout(function(){
						accordionPosts.init();
					}, 500);

					if ( 'undefined' !== typeof lscfPostsLoadCallback ) {
						lscfPostsLoadCallback();
					}
					setTimeout(function(){
						lscfEventListenerPostsLoadCallback();					
					},400);
				};
				
				scope.changeGridType = function( element, column ) {
					var gridChangers = document.getElementsByClassName('lscf-woo-grid-type');
	
					for ( var i = 0; i < gridChangers.length; i++ ) {
						gridChangers[ i ].className = 'lscf-woo-grid-type';
					}
	
					element.currentTarget.className = 'lscf-woo-grid-type active';
					scope.gridColumns = column;
				};

			},
			template: '<div ng-include="pluginSettings.filterSettings.theme.custom_template.url">'
		};
	}]);



function lscfEventListenerOnCustomTemplateReady( state ) {
    
	var evt = new CustomEvent('lscf_on_custom_template_ready', { detail: state });

    window.dispatchEvent( evt );
}

function lscfEventListenerPostsLoadCallback( state ) {
    
	var evt = new CustomEvent('lscf_posts_load_callback', { detail: state });

    window.dispatchEvent( evt );
}

function lscfAccordionPosts(){
	
	var $j = jQuery,
		self = this;

	this.options = {
		"link_type":0
	};

	this.init = function(){
		
		if ( !$j('.lscf-custom-template-wrapper').hasClass('lscf-posts-accordion') ) {
			return;
		}

		$j('.lscf-posts-accordion .lscf-title').unbind('click');

		if ( 'link-only' === self.options.link_type ) {
			return false;
		 }
		
		$j('.lscf-posts-accordion .lscf-title').bind( 'click', function(event){

			var parentContainer = $j(this).closest('.lscf-accordion-post');

			if ( parentContainer.hasClass('active') ) {
				parentContainer.find('.post-caption').animate({
					height:0
				}, 400);	
				parentContainer.removeClass('active');
				parentContainer.addClass('inactive');
				return false;
			}

			$j('.lscf-accordion-post').removeClass('active');
			$j('.lscf-accordion-post').addClass('inactive');

			
			parentContainer.addClass('active');
			parentContainer.removeClass('inactive');

			var animateHeight = parentContainer.find('.caption').height()+40;

			parentContainer.find('.post-caption').animate({
				height:animateHeight
			}, 400);

			$j('.lscf-accordion-post.inactive').find('.post-caption').animate({
				height:0
			}, 300);

			event.preventDefault();
			event.stopPropagation();

			return false;
		});

	};

}
angular.module(angAppName)
	.directive('viewmodeDefault', ['customFilterService', function( customFilterService ) {

		return{

			restrict:"AE",
			scope:true,
			bindToController: true,
			controllerAs: 'vm',
			templateUrl: lscfShortcodeData[0].plugin_url + 'app/views/posts-default.html',
			link: function( scope, elem, attrs ) {

				scope.actionSettings.initPostTheme = true;

				var lscfViewChangerPosts = new lscfPosts();
					shortcodeSettings = lscfShortcodeData[ scope.actionSettings.controllerAttributes.index ];

				scope.directiveInfo.ready = function(){

					scope.standartize_shortcodes_container_height();
					lscfViewChangerPosts.init();
					if ( 'undefined' !== typeof shortcodeSettings.settings && 'undefined' !== typeof shortcodeSettings.settings.theme ) {
						if ( 'undefined' === typeof shortcodeSettings.settings.theme.viewchanger || 'undefined' === typeof shortcodeSettings.settings.theme.viewchanger.list || 1 == shortcodeSettings.settings.theme.viewchanger.grid || 1 != shortcodeSettings.settings.theme.viewchanger.list ) {
							
							jQuery( '.lscf-posts-block' ).addClass('block-view');
							jQuery( '.viewMode > div' ).removeClass('active');
							jQuery( '.viewMode #blockView' ).addClass('active');

						}
					}

				};

				scope.directiveInfo.afterPostsLoadCallback = function(){
					scope.standartize_shortcodes_container_height();
				};

			}
		};
	}]);
angular.module(angAppName)
    .directive('liveSearch', ['$location', 'customFilterService', function( $location, customFilterService){

		return{

			restrict:"AE",
			require: "?ngModel",
			scope:true,
			bindToController: true,
			controllerAs: 'vm',
			link: function( scope, elem, attrs, ngModel ) {

				var activePostsList = [],
					default_filter = null,
					postsFirstLoad = true,
					activeQuery = [],
					shortcodeSettings = lscfShortcodeData[ scope.actionSettings.controllerAttributes.index ];

				scope.postsHasLoaded = false;


				scope.buildLocationOnBlur = function(){

					var q = [];

					//when active query is null.
					//When first time is triggered the search and the defaul filter is empty as well.
					if ( null === activeQuery ) { 
						q.push({ ID: 'ajax-main-search', value: scope.pxsearch });
						scope.actionSettings.buildUrlLocation( q );
						return; 
					}

					var fieldExists = false;

					for ( var i = 0; i < activeQuery.length; i++ ) {

						if ( 'undefined' !== typeof activeQuery[ i ] ) {

							if ( 'undefined' !== typeof activeQuery[ i ].ID && 'ajax-main-search' === activeQuery[ i ].ID ) {
								if ( '' !== scope.pxsearch ) {
									activeQuery[ i ].value = scope.pxsearch;
								} else {
									delete activeQuery[ i ];
									activeQuery.length -= 1;
								}
								fieldExists = true;
							}
						}
					}

					q = activeQuery;
					if ( false === fieldExists && '' !== scope.pxsearch ) {
						q.push({ ID: 'ajax-main-search', value: scope.pxsearch });
					}

					scope.actionSettings.buildUrlLocation( q );
				};

				scope.loadPostsOnFocus = function() {
					
					if ( true === scope.actionSettings.lsLoadPosts ) {

						activeQuery = null;
	
						scope.postsHasLoaded = false;

						if ( 'undefined' !== typeof shortcodeSettings.settings.theme.posts_display_from && 
							'undefined' !== shortcodeSettings.settings.theme.posts_display_from.post_taxonomies.active_terms && 
								shortcodeSettings.settings.theme.posts_display_from.post_taxonomies.active_terms.length > 0 ) 
						{
							default_filter = {
								"default_filter":{}
							};

							default_filter.default_filter.post_taxonomies = shortcodeSettings.settings.theme.posts_display_from.post_taxonomies;
							activeQuery = default_filter;
						}

						if ( scope.actionSettings.activeQuery.length > 0 ) {
							activeQuery = scope.actionSettings.activeQuery;
						}

						customFilterService.getAllPosts( scope.postType, 300, 1, activeQuery, shortcodeSettings )
							.then( function( data ) {
								data  = data.data;
								scope.postsHasLoaded = true;
								scope.actionSettings.lsLoadPosts = false;

								if ( true === postsFirstLoad ) { 
									lscfPostsList = new lscfPosts();
								}

								postsFirstLoad = false;
								posts = data.posts;

						});
					}
				};

				lscf_initLiveSearch( function() {

					if ( null !== scope.actionSettings.urlActiveFilters && scope.actionSettings.urlActiveFilters.hasGeneralSearch() ) {
						var generalSearches = scope.actionSettings.urlActiveFilters.activeFilters.generalSearch;
						var searchValue = generalSearches[0].value;
						scope.pxsearch = searchValue;
					}
					$j('.lscf-live-search-input').focus(function(){
						scope.loadPostsOnFocus();
					});

				});

				scope.$watch( "pxsearch", function( newVal, oldVal ) {

					if ( typeof newVal !== 'undefined' && newVal != oldVal ) {
						scope.loadMoreBtn.postsLoading = true;
					}

					scope.$watch( "postsHasLoaded", function( new_posts_status, old_posts_status ) {

						if ( true === new_posts_status ) {

							if ( typeof newVal !== 'undefined' && newVal != oldVal  ) {
								
								scope.$parent.loadMoreBtn.morePostsAvailable = false;
								var updatedPostList = [];



								if ( newVal !== '' && 'undefined' !== typeof posts ) {

									posts.forEach(function(post){
									
										var sTitleLong = pxDecodeEntities( post.title.long.replace( /(<strong(.*?)class\=\"matched-word\"\>)|(<\/strong\>)/ig, '' ) );
										var sTitleShort = pxDecodeEntities( post.title.short.replace( /(<strong(.*?)class\=\"matched-word\"\>)|(<\/strong\>)/ig, '' ) );
										var sContent = pxDecodeEntities ( post.content.replace( /(<strong(.*?)class\=\"matched-word\"\>)|(<\/strong\>)/ig, '' ) );
							
										if ( newVal.toLowerCase() == sTitleShort.toLowerCase() ) {
											post.class_name = 'ls-matches-search';
										} else {
											post.class_name = '';
										}

										if ( sTitleLong.toLowerCase().indexOf(newVal.toLowerCase()) != -1 || post.full_content.toLowerCase().indexOf(newVal.toLowerCase()) != -1 ) {
												
												sTitleLong = sTitleLong.replace(new RegExp( '(' + newVal + ')', 'ig'), '<strong class="matched-word">' + "$1" + '</strong>');
												sTitleShort = sTitleShort.replace(new RegExp( '(' + newVal + ')', 'ig'), '<strong class="matched-word">' + "$1" + '</strong>');
												sContent = sContent.replace(new RegExp( '(' + newVal + ')', 'ig'), '<strong class="matched-word">' + "$1" + '</strong>');
												
												post.title.long = sTitleLong;
												post.title.short = sTitleShort;
												post.content = sContent;
												
												updatedPostList.push(post);
										}
										
									});
									
								}
								else {
									if ( 'undefined' !== typeof posts ) {

										scope.loadMoreBtn.postsLoading = true;

										posts.forEach(function(post){
										
											var sTitleLong = post.title.long.replace( /(<strong(.*?)class\=\"matched-word\"\>)|(<\/strong\>)/ig, '' );
											var sTitleShort = post.title.short.replace( /(<strong(.*?)class\=\"matched-word\"\>)|(<\/strong\>)/ig, '' );
											var sContent = post.content.replace( /(<strong(.*?)class\=\"matched-word\"\>)|(<\/strong\>)/ig, '' );
											
											post.title.long = sTitleLong;
											post.title.short = sTitleShort;
											post.content = sContent;
											
											updatedPostList.push(post);
												
										});
									}
									
								}
								
								scope.$parent.filterPostsTemplate.posts = updatedPostList;
								lscfPostsList.constructHover();
								scope.directiveInfo.afterPostsLoadCallback();
								scope.loadMoreBtn.postsLoading = false; 

							}
						}
					});
				});

				scope.$watch( "pxsearch_woo_sku", function( newVal, oldVal ) {

					if ( typeof newVal !== 'undefined' && newVal != oldVal ) {
						scope.loadMoreBtn.postsLoading = true;
					}

					scope.$watch( "postsHasLoaded", function( new_posts_status, old_posts_status ) {
						
						if ( true === new_posts_status ) {

							if ( typeof newVal !== 'undefined' && newVal != oldVal  ){
								
								scope.$parent.loadMoreBtn.morePostsAvailable = false;
								var updatedPostList = [];

								if (newVal !== '') {
									
									posts.forEach(function(post){
									
										if ( 'undefined' !== typeof post.woocommerce.sku && post.woocommerce.sku.toLowerCase().indexOf(newVal.toLowerCase()) != -1 ) {
												updatedPostList.push(post);
										}
									});
									
								}
								else{
									if ( 'undefined' !== posts ) {

										posts.forEach(function(post){
											updatedPostList.push(post);
										});
									}
								}

								scope.$parent.filterPostsTemplate.posts = updatedPostList;
								lscfPostsList.constructHover(); 

							}
						}
					});

				});
			}

		};

	}]);

	function lscf_initLiveSearch( callback ) {

		var $j = jQuery,
			self = this,
			checkInterval;

		this.ready = function(){

			if ( $j('.lscf-live-search-input').length && $j('.lscf-live-search-input').length > 0 ){
				clearInterval( checkInterval );
				callback();
			}
		};

		checkInterval = setInterval( self.ready(), 500 );

		setTimeout(function(){
			clearInterval( checkInterval );
		}, 3000);

	}
angular.module(angAppName)
	.directive('viewmodePortrait', ['customFilterService', function(customFilterService){

		return{

			restrict:"AE",
			scope:true,
			bindToController: true,
			controllerAs: 'vm',
			templateUrl: lscfShortcodeData[0].plugin_url + 'app/views/posts-portrait.html',
			link:function(scope, elem, attrs){

				scope.actionSettings.initPostTheme = true;

				scope.directiveInfo.ready = function(){

				};

				scope.directiveInfo.afterPostsLoadCallback = function(){
					
				};

			}
		};
	}]);
angular.module(angAppName)
	.directive('sidebarLiveCustomizer', ['customFilterService', function(customFilterService){

		return{

			restrict:"AE",
			require: "?ngModel",
			scope:true,
			bindToController: true,
			controllerAs: 'vm',
			templateUrl: lscfShortcodeData[0].plugin_url + 'app/views/sidebar-live-customizer.html',
			link: function( scope, elem, attrs, ngModel ) {

				scope.$watch('pluginSettings.custom_templates', function(newVal, oldVal){
					
					if ( 'undefined' !== typeof scope.pluginSettings.filterSettings.theme.custom_template &&
						 'undefined' !== typeof scope.pluginSettings.custom_templates && 
						 'custom-theme' == scope.pluginSettings.filterSettings.theme.display ) 
					{
						for ( var i=0; i < scope.pluginSettings.custom_templates.length; i++ ) {
							if ( scope.pluginSettings.filterSettings.theme.custom_template.slug == scope.pluginSettings.custom_templates[i].slug ) {
								scope.pluginSettings.custom_templates[i].checked = true;
							} else {
								scope.pluginSettings.custom_templates[i].checked = false;
							}
						}
					}
				});

				var filterSettings,
					wrapperGeneralClassNames,
					shortcodeSettings = lscfShortcodeData[ scope.actionSettings.controllerAttributes.index ],
					liveCustomizer = new lscfSidebarCustomizator( shortcodeSettings );

				setTimeout( function(){
					
					liveCustomizer.init();

					liveCustomizer.initColorpicker(function(data){
						liveCustomizer.generateDynamicCssColor( data );

						scope.$apply(function(){
							scope.pluginSettings.filterSettings['main-color'] = data.hex;
							scope.pluginSettings.filterSettings['main-color-rgb'] = data.rgb;
							
							filterSettings = angular.toJson( scope.pluginSettings );
							liveCustomizer.saveSettings( filterSettings );
							
						});
					});

					liveCustomizer.saveExtraOptions( function(data){

						scope.$apply(function(){


							switch ( data.type ) {

								case 'taxonomies-listing':

										scope.pluginSettings.filterSettings.theme.posts_display_from = data.data;

									break;

								case 'settings-page':

										scope.pluginSettings.generalSettings.run_shortcodes = liveCustomizer.templateData.settings.run_shortcodes.value;
										scope.pluginSettings.generalSettings.disable_empty_option_on_filtering = liveCustomizer.templateData.settings.disable_empty_option_on_filtering.value;

										scope.pluginSettings.generalSettings.infinite_scrolling = liveCustomizer.templateData.settings.infinite_scrolling.value;

										scope.pluginSettings.generalSettings.checkboxes_conditional_logic = liveCustomizer.templateData.settings.checkboxes_conditional_logic.value;
										scope.pluginSettings.generalSettings.lscf_custom_fields_order_as = liveCustomizer.templateData.settings.lscf_custom_fields_order_as.value;

										scope.pluginSettings.generalSettings.order_by = liveCustomizer.templateData.settings.order_by;
										scope.pluginSettings.generalSettings.default_order_by = liveCustomizer.templateData.settings.default_order_by;
										scope.pluginSettings.generalSettings.url_history = liveCustomizer.templateData.settings.url_history.value;
										scope.pluginSettings.generalSettings.instant_search = liveCustomizer.templateData.settings.instant_search.value;
										scope.pluginSettings.generalSettings.general_search_by = liveCustomizer.templateData.settings.general_search_by;
										scope.pluginSettings.generalSettings.hide_see_more_on_checkboxes_list = liveCustomizer.templateData.settings.hide_see_more_on_checkboxes_list.value;
										scope.pluginSettings.generalSettings.general_search_conditional_logic = liveCustomizer.templateData.settings.general_search_conditional_logic.value;
										scope.pluginSettings.generalSettings.general_search_algorithm = liveCustomizer.templateData.settings.general_search_algorithm.value;
										scope.pluginSettings.generalSettings.range_filtering_type = liveCustomizer.templateData.settings.range_filtering_type.value;
									break;

								case 'woo-settings':

									scope.pluginSettings.generalSettings.woo_price_format = liveCustomizer.templateData.settings.woo_price_format.value;
									scope.pluginSettings.generalSettings.woo_instock = liveCustomizer.templateData.settings.woo_instock.value;

									break;
							}

							filterSettings = angular.toJson( scope.pluginSettings);

							liveCustomizer.saveSettings( filterSettings );

						});
					});

					liveCustomizer.onFormAction(function(data){

						switch ( data.dataType ) {

							case 'order-fields':
									scope.$apply(function(){
										scope.actionSettings.initFieldsDraggable = data.fieldValue;
									});

								break;

							case 'sidebar-position':

								scope.$apply(function(){

									scope.pluginSettings.filterSettings.theme.sidebar.position = data.fieldValue;
									scope.actionSettings.initSidebar = true;
									
									wrapperGeneralClassNames = scope.makeWrapperClassName();

									scope.pluginSettings.className.sidebar = wrapperGeneralClassNames.sidebar;
									scope.pluginSettings.className.posts_theme = wrapperGeneralClassNames.posts_theme;

								});
								
								
								break;

							case 'theme-style':
								
								scope.$apply(function(){

									scope.pluginSettings.filterSettings.theme.display = data.fieldValue;

									if ( 'custom-theme' == data.fieldValue ) {
										
										var activeCustomThemeIndex = parseInt( data.custom_theme_active_index );

										if ( 'undefined' === typeof scope.pluginSettings.filterSettings.theme.custom_template ) {
											scope.pluginSettings.filterSettings.theme.custom_template = {};
										}

										scope.pluginSettings.filterSettings.theme.custom_template.url = scope.pluginSettings.custom_templates[ activeCustomThemeIndex ].url;
										scope.pluginSettings.filterSettings.theme.custom_template.name = scope.pluginSettings.custom_templates[ activeCustomThemeIndex ].name;
										scope.pluginSettings.filterSettings.theme.custom_template.slug = scope.pluginSettings.custom_templates[ activeCustomThemeIndex ].slug;

									}

								});

								break;

							case 'columns-number':
									
									var columnsNumber = parseInt( data.fieldValue );
									
									scope.$apply(function(){
										scope.pluginSettings.filterSettings.theme.columns = columnsNumber;
										scope.pluginSettings.className.sidebar = ( columnsNumber > 3 ? 'col-sm-2 col-md-2 col-lg-2' : 'col-sm-3 col-md-3 col-lg-3' );
										scope.pluginSettings.className.posts_theme = ( columnsNumber > 3 ? 'col-sm-10 col-md-10 col-lg-10' : 'col-sm-9 col-md-9 col-lg-9' );
									});
									
								
								break;

							case 'view-changer':
								
								switch( data.fieldValue ) {
									
									case 'full':
										
										scope.$apply(function(){
											scope.pluginSettings.filterSettings.theme.viewchanger.grid = 1;
											scope.pluginSettings.filterSettings.theme.viewchanger.list = 1;
										});

										jQuery( '.viewMode' ).fadeIn();
										jQuery( '.lscf-posts-block' ).addClass('block-view');
										jQuery( '.viewMode > div' ).removeClass('active');
										jQuery( '.viewMode #blockView' ).addClass('active');

										break;

									case 'list':
										
										scope.$apply(function(){
											scope.pluginSettings.filterSettings.theme.viewchanger.grid = 0;
											scope.pluginSettings.filterSettings.theme.viewchanger.list = 1;
										});

										jQuery( '.viewMode' ).hide();
										jQuery( '.lscf-posts-block' ).removeClass('block-view');

										break;

									case 'grid':

										scope.$apply(function(){
											scope.pluginSettings.filterSettings.theme.viewchanger.grid = 1;
											scope.pluginSettings.filterSettings.theme.viewchanger.list = 0;
										});

										jQuery( '.viewMode' ).hide();
										jQuery( '.lscf-posts-block' ).addClass('block-view');

										break;
								}

								break;

							case 'link-type':
								scope.$apply(function(){
									scope.pluginSettings.filterSettings.theme.link_type = data.fieldValue;
									scope.actionSettings.initPostTheme = true;
								});
								break;

							case 'posts-per-page':
								scope.$apply(function(){
									scope.actionSettings.postsPerPage = data.fieldValue;
									scope.pluginSettings.filterSettings['posts-per-page'] = data.fieldValue;
								});
								break;
						}

						filterSettings = angular.toJson( scope.pluginSettings );
						liveCustomizer.saveSettings( filterSettings );

					});


				}, 800 );

			}

		};

	}]);


function lscfSidebarCustomizator( shortcodeSettings ){

	var $j = jQuery,
		self = this;
	
	this.ajaxRequest = new lscfGeneralAjaxRequests( shortcodeSettings );
	
	this.templateData = {
		active_terms: [],
		settings: {
			run_shortcodes: {
				key: 'run_shortcodes',
				value: ( 'undefined' !== typeof shortcodeSettings.options.run_shortcodes ? shortcodeSettings.options.run_shortcodes : 0 )
			},
			disable_empty_option_on_filtering: {
				key: 'disable_empty_option_on_filtering',
				value:( 'undefined' !== typeof shortcodeSettings.options.disable_empty_option_on_filtering ? shortcodeSettings.options.disable_empty_option_on_filtering : 0 )
			},
			infinite_scrolling: {
				key: 'infinite_scrolling',
				value:( 'undefined' !== typeof shortcodeSettings.options.infinite_scrolling ? shortcodeSettings.options.infinite_scrolling : 0 )
			},
			url_history: {
				key: 'url_history',
				value: ( ( 'undefined' !== typeof shortcodeSettings.options.url_history ? shortcodeSettings.options.url_history : 0 ) )
			},
			instant_search: {
				key: 'instant_search',
				value: ( ( 'undefined' !== typeof shortcodeSettings.options.instant_search ? shortcodeSettings.options.instant_search : 1 ) )
			},
			hide_see_more_on_checkboxes_list: {
				key: 'hide_see_more_on_checkboxes_list',
				value:  ( 'undefined' !== typeof shortcodeSettings.options.hide_see_more_on_checkboxes_list ? shortcodeSettings.options.hide_see_more_on_checkboxes_list : 0 )
			},
			general_search_data: [
				{
					id: 'post_title',
					name: 'Post Title'
				},
				{
					id: 'post_content',
					name: 'Post Content'
				}
			],
			range_filtering_type: {
				key: 'range_filtering_type',
				value:  ( 'undefined' !== typeof shortcodeSettings.options.range_filtering_type ? shortcodeSettings.options.range_filtering_type : 'lscf_number' )
			},
			general_search_conditional_logic: {
				key: 'general_search_conditional_logic',
				value: ( 'undefined' !== typeof shortcodeSettings.options.general_search_conditional_logic ? shortcodeSettings.options.general_search_conditional_logic : 'and' )
			},
			general_search_algorithm: {
				key: 'general_search_algorithm',
				value: ( 'undefined' !== typeof shortcodeSettings.options.general_search_algorithm ? shortcodeSettings.options.general_search_algorithm : 'algorithm-1' )
			},
			checkboxes_conditional_logic: {
				key: 'checkboxes_conditional_logic',
				value: ( 'undefined' !== typeof shortcodeSettings.options.checkboxes_conditional_logic ? shortcodeSettings.options.checkboxes_conditional_logic : 'or' )
			},
			lscf_custom_fields_order_as: {
				key: 'lscf_custom_fields_order_as',
				value: ( 'undefined' !== typeof shortcodeSettings.options.lscf_custom_fields_order_as? shortcodeSettings.options.lscf_custom_fields_order_as : 'number' )
			},

			woo_price_format: {
				key: 'woo_price_format',
				value: ( ( 'undefined' !== typeof shortcodeSettings.options.woo_price_format ? shortcodeSettings.options.woo_price_format : 0 ) )
			},

			woo_instock: {
				key: 'woo_instock',
				value: ( ( 'undefined' !== typeof shortcodeSettings.options.woo_instock ? shortcodeSettings.options.woo_instock : 0 ) )
			},


			orderable_list: [
				{
					id: 'post_title',
					name: ( 'undefined' !== typeof shortcodeSettings.options.writing.title ? shortcodeSettings.options.writing.title : 'Title' )
				},
				{
					id: 'post_date',
					name: ( 'undefined' !== typeof shortcodeSettings.options.writing.date ? shortcodeSettings.options.writing.date : 'Date' )
				},
				{
					id: 'id',
					name: 'ID'
				},
				{
					id: 'menu_order',
					name: 'Menu Order'
				},
				{
					id: 'woo_price',
					name: 'WooCommerce Price'
				}
			],

			order_by: {
				items: []
			},
			general_search_by: {
				items: []
			},
			default_order_by: {
				key: 'default_order_by',
				value: ( 'undefined' !== typeof shortcodeSettings.options.default_order_by ? shortcodeSettings.options.default_order_by.value : 'post_date' ),
				order_as: ( 'undefined' !== typeof shortcodeSettings.options.default_order_by ? shortcodeSettings.options.default_order_by.order_as : 'asc' )
			}
		},

		post_taxonomies: {
			items: [],
			set_tax_term_as_active: function() {
				return function( term_id, render ) {
					if ( self.templateData.active_terms.length > 0 && -1 !== self.templateData.active_terms.indexOf( render( term_id ) ) ) {
						return 'checked="checked"';
					}
				};
			},
		},

		post_custom_fields: {
			items: []
		},
		

		set_settings_option_as_active: function(){
			return function( key, render ) {
				if ( self.templateData.settings[ render( key ) ].value == 1 ) {
					return 'checked="checked"';
				}

			};
		},
		set_as_active_if_match: function(){
			return function( dataVal, render ){
			
				var values = render( dataVal ).split('__pxlt__');

				if ( values[0].trim() == values[1].trim() ) {
					return "checked";
				}
			};
		},
		set_order_fields_as_active: function(){
			return function ( key, render ) {

				for ( var ls = 0; ls < self.templateData.settings.order_by.items.length; ls++  ) {

					if ( self.templateData.settings.order_by.items[ ls ].id == render( key ) ) {

						return 'checked="checked"';
					}
				}
			};
		},
		set_general_search_active_items: function(){

			return function ( key, render ) {
				for ( var ls = 0; ls < self.templateData.settings.general_search_by.items.length; ls++  ) {

					if ( self.templateData.settings.general_search_by.items[ ls ].id == render( key ) ) {
						return 'checked="checked"';
					}
				}
			};
		}
	};

	this.templates = {};

	this.templates.loadTaxonomies = function( templateData, callback ) {

		var url = shortcodeSettings.plugin_url + 'assets/js/templates/live-customizer/tax-template.html';

		$j.get( url, function( template ) {

			var renderedTemplate = Mustache_2.render( $j( template ).filter('#template-tax').html(), templateData );

			callback(renderedTemplate);

		});

	};

	this.templates.loadTemplate = function( templateData, templateUrl, callback ) {

		$j.get( templateUrl, function( template ) {

			var renderedTemplate = Mustache_2.render( $j( template ).filter('#template-tax').html(), templateData );

			callback( renderedTemplate );

		});
	};

	this.serializeExtraOptions = function(){
		
		var containerType = $j('.lscf-extrasidebar-template').attr('data-fields-type'),
			data = {
				"type" : containerType,
				"data" :{}
			};

		switch ( containerType ) {
			
			case 'taxonomies-listing':

				data.data.post_taxonomies = {};

				var slug, value, name;

				self.templateData.active_terms = [];

				$j('.lscf-taxonomies-block').each(function(){

					$j(this).find('.px-checkbox:checked').each(function(){
						
						slug = $j(this).attr('data-taxonomy');
					
						if ( 'undefined' === typeof data.data.post_taxonomies[slug] ) {
							data.data.post_taxonomies[slug] = {};
							data.data.post_taxonomies[slug].ID = slug;
							data.data.post_taxonomies[slug].group_type= 'taxonomies';
							data.data.post_taxonomies[slug].tax = {
								"terms":[]
							};

						}

						value = parseInt( $j(this).val().split("!#")[0] );
						name = $j(this).val().split("!#")[1];

						data.data.post_taxonomies[slug].tax.terms.push({
							"data":{
								"name":name,
								"value":value
							}
						});

						self.templateData.active_terms.push(value+'-'+slug);

					});

				});

				data.data.post_taxonomies.active_terms = self.templateData.active_terms;

				break;

			case 'woo-settings':
			case 'settings-page':

				data.data.settings = {};
				self.templateData.settings.order_by.items = [];
				self.templateData.settings.general_search_by.items = [];

				$j('.lscf-fronteditor-settings input[type="checkbox"]').each(function(){

					var key = $j(this).attr('data-key');

					if ( ! $j(this).hasClass( 'has-multple-values' ) ) {

						self.templateData.settings[ key ].value = ( $j(this).prop("checked") ? 1 : 0 );

						data.data.settings[ key ] = self.templateData.settings[ key ];

					} else {

						if ( $j(this).prop( "checked" ) ) {

							self.templateData.settings[ key ].items.push({
								"id": $j(this).val(),
								"name": $j(this).attr('data-name')
							});
						}

						data.data.settings[ key ] = self.templateData.settings[ key ];
					}

				});

				$j('.lscf-fronteditor-settings input[type="radio"]').each(function(){

					var key = $j(this).attr('data-key'),
						customValueName = $j(this).attr('data-value');

					if ( $j(this).is( ":checked" ) ) {

						if ( 'undefined' !== typeof customValueName ) {
							self.templateData.settings[ key ][ customValueName ] = $j( this ).val();
						} else {
							self.templateData.settings[ key ].value = $j( this ).val();
						}

						data.data.settings[ key ] = self.templateData.settings[ key ];
					}

				});

				break;
		}

		return data;

	};


	this.init = function(){

		if ( 'undefined' !== typeof shortcodeSettings.options.order_by && 'undefined' !== typeof shortcodeSettings.options.order_by.items ) {	
			self.templateData.settings.order_by.items = shortcodeSettings.options.order_by.items;
		}

		if ( 'undefined' !== typeof shortcodeSettings.options.general_search_by && 'undefined' !== typeof shortcodeSettings.options.general_search_by.items ) {	
			self.templateData.settings.general_search_by.items = shortcodeSettings.options.general_search_by.items;
		}

		self.ajaxRequest.getCustomFieldsByPostType( shortcodeSettings.postType, 'all', function(data){
			if (typeof data.success !== 'undefined' && data.success == 1) {
				
				var customFields = data.data.data.fields;

				customFields.forEach(function(customField) {
					var id = ( 'px_date' === customField.slug ? customField.field_form_id + '_timestamp' : customField.field_form_id );
					var custom_field = {
						"id": id,
						"name":customField.name
					};

					self.templateData.post_custom_fields.items.push( custom_field );
					self.templateData.settings.orderable_list.push( custom_field );
					self.templateData.settings.general_search_data.push( custom_field );
				});

			}

		});


		if ( 'undefined' !== typeof shortcodeSettings.settings.theme.posts_display_from && 
			'undefined' !== typeof shortcodeSettings.settings.theme.posts_display_from.post_taxonomies &&
			'undefined' !== typeof shortcodeSettings.settings.theme.posts_display_from.post_taxonomies.active_terms ) 
		{

			self.templateData.active_terms = shortcodeSettings.settings.theme.posts_display_from.post_taxonomies.active_terms;
		}

		self.get_post_taxonomies_and_terms(
			function(data){
				self.templateData.post_taxonomies.items = data;
		});


		$j('#lscf-expand-sidebar-extra-options, .lscf-open-extra-sidebar').click(function(){
			
			$j('.lscf-close-customizer').addClass('lscf-hide');
			$j('.lscf-sidebar-extra-container').addClass('active');	

			var container_type = $j(this).closest('.lscf-sidebar-option').attr('data-type');

			self.loadExtraSidebarContent( container_type );

		});

	
		$j('.lscf-open-customizer').click(function(){
			$j(this).addClass('deactivate-animations');
			$j('.lscf-sidebar-live-customizer').addClass('active');
			$j('#lscf-posts-wrapper').addClass('translate');
		});
		$j('.lscf-close-customizer').click(function(){
			$j('.lscf-sidebar-live-customizer').removeClass('active');

			$j('#lscf-posts-wrapper').removeClass('translate');
		});


		self.initCustomDropdown();
		

		var activeTheme = $j('.lscf-theme-list').find('input[type="radio"]:checked').val();
		self.initThemeOptions( activeTheme );

	};

	this.loadExtraSidebarContent = function( content_type ){
		var templateUrl;

		switch ( content_type ) {

			case 'show-from-categories':
				
				self.templates.loadTaxonomies( self.templateData.post_taxonomies, function(data){

					$j('.lscf-sidebar-extra-container-wrapper').html( data );
					$j('.lscf-sidebar-extra-container-wrapper').customScrollbar();

				});

				break;

			case 'filter-settings':

				templateUrl = shortcodeSettings.plugin_url + 'assets/js/templates/live-customizer/settings-template.html';

				self.templates.loadTemplate( self.templateData, templateUrl, function( data ){

					$j('.lscf-sidebar-extra-container-wrapper').html( data );
					$j('.lscf-sidebar-extra-container-wrapper').customScrollbar();

				});

				break;
			
			case 'woo-settings':

				templateUrl = shortcodeSettings.plugin_url + 'assets/js/templates/live-customizer/woo-settings-template.html';

				self.templates.loadTemplate( self.templateData, templateUrl, function( data ){

					$j('.lscf-sidebar-extra-container-wrapper').html( data );
					$j('.lscf-sidebar-extra-container-wrapper').customScrollbar();

				});

				break;

		}	

	};

	this.saveExtraOptions = function( callback ){

		$j('#save-and-close-extra-options').click(function() {

			$j('.lscf-close-customizer').removeClass('lscf-hide');
			$j('.lscf-sidebar-extra-container').removeClass('active');	
			
			var formData = self.serializeExtraOptions();

			callback( formData );

		});	

	};

	this.initColorpicker = function( colorpickerCallback ){
		
		var colorPickerToggled = false;

		$j('.lscf-colorpicker').colorPicker({
			renderCallback:function(elem, toggle){
				if ( true === toggle ) {
					colorPickerToggled = false;
				}
				if ( false === toggle && false === colorPickerToggled ) {
					
					colorPickerToggled = true;
					var rgbColor = elem[0].style.backgroundColor.replace(/rgb\(|\)/g, ''),
					color = elem[0].value;

					colorpickerCallback({
						"hex":color,
						"rgb":rgbColor
					});

				}
				
			}
		});
	};

	this.generateDynamicCssColor = function( color ){
		
		$j.ajax({
			type:"POST",
			url:shortcodeSettings.ajax_url,
			data:{
				action:"px-plugin-ajax",
				section:"generate-theme-color-style",
				color:color
			},
			success:function(data){
				var dynamicStyle = document.getElementById("px_base-inline-css");
				dynamicStyle.innerHTML = data;
			},
			dataType:"html"
		});
	};

	this.get_post_taxonomies_and_terms = function( callback ) {

		$j.ajax({
			type:"POST",
			url:shortcodeSettings.ajax_url,
			data:{
				action:"lscf-administrator-ajax",
				section:"get_taxonomies_and_terms",
				post_type:shortcodeSettings.postType
			},
			success:function(data){
				callback( data );
			},
			dataType:"json"
		});

	};

	this.saveSettings = function( settings ) {

		$j.ajax({
			type:"POST",
			url:shortcodeSettings.ajax_url,
			data:{
				action:"lscf-administrator-ajax",
				section:"save-filter-settings",
				filter_id:shortcodeSettings.ID,
				settings:settings
			},
			success:function(data){

			},
			dataType:"html"
		});
	};

	this.onFormAction = function( callback ){

		var fieldType,
			dataType,
			fieldValue,
			data = {},
			customThemeIndex;


		$j('.lscf-sidebar-option').each(function(){
			
			fieldType = $j(this).attr('data-field-type');

			switch ( fieldType ) {

				case 'dropdown':

					$j(this).find('.options li ').each(function(){
						
						$j(this).click(function(){

							fieldValue = $j(this).attr('rel');
							dataType = $j(this).closest('.lscf-sidebar-option').attr('data-type');

							data = {
								"fieldType":fieldType,
								"dataType":dataType,
								"fieldValue":fieldValue
							};

							callback( data );

						});

					});

					break;

				case 'checkbox':
					
					$j(this).find('#lscf-fields-ordering').click(function(){
						
						var parent = $j(this).closest('.lscf-check-btn');
						
						fieldValue = ( 'undefined' !== typeof parent.find('input[type="checkbox"]:checked').val() ? parseInt( parent.find('input[type="checkbox"]:checked').val() ) : 0 );
						dataType = $j(this).closest('.lscf-sidebar-option').attr('data-type');

						data = {
							"fieldType":fieldType,
							"dataType":dataType,
							"fieldValue":!fieldValue
						};

						callback( data );

					});

					break;


				case 'radiobutton':

					$j(this).on('click', '.lscf-live-customizer-radiobutton-label', function(){

							fieldValue = $j(this).attr('data-value');

							self.initThemeOptions( fieldValue );

							if ( 'custom-theme' == fieldValue  ) {
								customThemeIndex = $j(this).attr('data-index');
							}

							dataType = 'theme-style';

							data = {
								"fieldType":fieldType,
								"dataType":dataType,
								"fieldValue":fieldValue,
								"custom_theme_active_index":customThemeIndex
							};

							callback( data );
					});

					break;

				case 'number':
					
					$j(this).find('input[type="number"]').blur(function(){

						fieldValue = ('NaN' !== parseInt( $j(this).val() ) ? parseInt( $j(this).val() ) : 15 );
						dataType = $j(this).closest('.lscf-sidebar-option').attr('data-type');
						
						data = {
							"fieldType":fieldType,
							"dataType":dataType,
							"fieldValue":fieldValue
						};

						callback( data );
					});
					
					break;

			}

		});

	};

	this.initThemeOptions = function( theme ) {

			$j('.lscf-optional-option').hide();

			switch ( theme ) {
				
				case 'default':

					$j('.lscf-optional-grid').fadeIn();
					$j('.lscf-optional-viewchanger').fadeIn();

					break;
				
				case 'accordion':
					
					$j('.lscf-optional-linktype').fadeIn();
					
					break;

				case 'portrait':
					
					$j('.lscf-optional-grid').fadeIn();
					
					break;

				
				case 'woocommerce-grid':
				case 'basic-grid':
					
					$j('.lscf-optional-grid').fadeIn();

					break;
			}

	};

	this.initCustomDropdown = function(){

		$j('.lscf-custom-select-dropwdown').each(function(){
			var dataClass=$j(this).attr('data-class');
			var $this=$j(this),
				numberOfOptions=$j(this).children('option').length;
			$this.addClass('s-hidden');
			$this.wrap('<div class="select '+dataClass+'"></div>');
			$this.after('<div class="styledSelect"></div>');
			var $styledSelect=$this.next('div.styledSelect'),
				optionValue,
				selectedOptionIndex;

			$this.find('option').each(function(index){
				
				if ( $j(this).is(':selected') ) {
					selectedOptionIndex = index;
				}
			});

			optionValue = ( $this.find('option:selected') ?  $this.find('option:selected').text() : $this.children('option').eq(0).text() );

			$styledSelect.text( optionValue );
			var $list=$j('<ul />',{'class':'options'}).insertAfter($styledSelect);
			for ( var i=0; i<numberOfOptions; i++ ) {
				
				var listClassName = ( selectedOptionIndex == i ? 'pxselect-hidden-list' : '' );
				
				$j('<li />',{
					text:$this.children('option').eq(i).text(),
					rel:$this.children('option').eq(i).val(),
					class:listClassName
				}).appendTo( $list );
			}
			var $listItems = $list.children('li');
			$styledSelect.click(function(e){
				e.stopPropagation();
				$j('div.styledSelect.active').each(function(){
					$j(this).removeClass('active').next('ul.options').hide();
				});
				$j(this).toggleClass('active').next('ul.options').toggle();
			});
			$listItems.click(function(e){
				$listItems.removeClass('pxselect-hidden-list');
				$j(this).addClass('pxselect-hidden-list');
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

	};

}

function lscfGeneralAjaxRequests( shortcodeSettings ) {
	var self = this,
		$j = jQuery,
		action = "px-plugin-ajax",
		adminAction = "lscf-administrator-ajax";
	

	this.getCustomFieldsByPostType = function( postType, fieldType, callback ) {

		$j.ajax({
            type:"POST",
            url:shortcodeSettings.ajax_url,
            data:{
                action:action,
                section:"getPostType_customFields",
                fieldType:fieldType,
                post_type:postType
            },
            success: function (data) {
                callback(data);
            },
            dataType:"json"
        });

	};

}
angular.module(angAppName)
    .directive('sortBy', ['customFilterService', function(customFilterService){

		return{

			restrict:"AE",
			require: "?ngModel",
			scope:true,
			bindToController: true,
			controllerAs: 'vm',
			link: function( scope, elem, attrs, ngModel ) {

				var field_exists = false,
					field,
					orderByID,
					dropdownTemplate,
					$j = jQuery,
					shortcodeSettings = lscfShortcodeData[ scope.actionSettings.controllerAttributes.index ];

				scope.$watch( 'pluginSettings.generalSettings.order_by.items', function( newVal, oldVal ){
					
						if ( 'undefined' !== typeof scope.pluginSettings.generalSettings.order_by && 
							'undefined' !== typeof scope.pluginSettings.generalSettings.order_by.items &&
							scope.pluginSettings.generalSettings.order_by.items.length > 0 ) {

								dropdownTemplate = '<select data-class="lscf-order-by-dropdown lscf-sorting-by" class="lscf-sorting-custom-dropdown">';
								dropdownTemplate += '<option value="0">' + scope.pluginSettings.generalSettings.writing.sort_by + '</option>';

								scope.pluginSettings.generalSettings.order_by.items.forEach( function( item ){
									var name = ( 'woo_price' === item.id ? scope.pluginSettings.generalSettings.writing.price : item.name );
									dropdownTemplate += '<option value="' + item.id + '">' + name + '</option>';
								});

								dropdownTemplate += '</select>';

								dropdownTemplate += '<div class="lscf-sorting-opt">';
								dropdownTemplate += '<div class="lscf-sort-up"><span class="glyphicon glyphicon-triangle-top"></span></div>';
								dropdownTemplate += '<div class="lscf-sort-down"><span class="glyphicon glyphicon-triangle-bottom"></span></div>';
								dropdownTemplate += '</div>';


								elem[0].innerHTML = dropdownTemplate;
								lscfSortingCustomDropddown();

								if ( 'undefined' !== typeof shortcodeSettings.settings.theme.posts_display_from && 
								'undefined' !== shortcodeSettings.settings.theme.posts_display_from.post_taxonomies.active_terms && 
									shortcodeSettings.settings.theme.posts_display_from.post_taxonomies.active_terms.length > 0 ) 
								{

									scope.actionSettings.activeQuery.push({
										"ID":"default_filter",
										"type":"default_filter",
										"default_filter":{
											"post_taxonomies":shortcodeSettings.settings.theme.posts_display_from.post_taxonomies
										} 
									});

								}


								$j('.px-capf-wrapper').addClass('lscf-active-sort-by');

								$j('.lscf-sort-up').click(function(){
									
									$j(this).addClass('active');
									$j('.lscf-sort-down').removeClass('active');

									var order = "DESC";	

									for ( var c = 0; c < scope.actionSettings.activeQuery.length; c++ ) {

										field = scope.actionSettings.activeQuery[ c ];
										
										if ( 'order-by' == field.ID ) {

											scope.actionSettings.activeQuery[ c ].order = order;
											field_exists = true;
											break;
										}
									}
									
									if ( false === field_exists ) {

										query = {
											"ID":"order-by",
											"filter_as":null,
											"type":"order-posts",
											"order":order,
											"value":"post_date"
										};

										scope.actionSettings.activeQuery.push( query );
									}

									scope.loadMoreBtn.postsLoading = true;

									customFilterService.getPosts( scope.postType, scope.actionSettings.postsPerPage, 1, scope.actionSettings.activeQuery, shortcodeSettings )
										.then(function( data ) {
											data = data.data;
											scope.actionSettings.postsCount = data.postsCount;
											scope.actionSettings.pagesCount = data.pages;
											scope.actionSettings.pxCurrentPage = 2;

											if ( scope.actionSettings.pxCurrentPage <= data.pages) scope.loadMoreBtn.morePostsAvailable = true;
											else scope.loadMoreBtn.morePostsAvailable = false;

											scope.actionSettings.filterPostsTemplate = data.posts;
											scope.filterPostsTemplate.posts = data.posts;
											scope.loadMoreBtn.postsLoading = false;

											scope.directiveInfo.afterPostsLoadCallback();

									});

								});

								$j('.lscf-sort-down').click(function(){
									
									$j(this).addClass('active');
									$j('.lscf-sort-up').removeClass('active');
									
									var order = 'ASC';	

									for ( var c = 0; c < scope.actionSettings.activeQuery.length; c++ ) {
						
										field = scope.actionSettings.activeQuery[ c ];
										
										if ( 'order-by' == field.ID ) {

											scope.actionSettings.activeQuery[ c ].order = order;
											
											field_exists = true;
											break;

										}
									}
									
									if ( false === field_exists ) {

										query = {
											"ID":"order-by",
											"filter_as":null,
											"type":"order-posts",
											"order":order,
											"value":"post_date"
										};

										scope.actionSettings.activeQuery.push( query );
									}

									scope.loadMoreBtn.postsLoading = true;

									customFilterService.getPosts( scope.postType, scope.actionSettings.postsPerPage, 1, scope.actionSettings.activeQuery, shortcodeSettings )
										.then(function( data ) {
											data = data.data;
											scope.actionSettings.postsCount = data.postsCount;
											scope.actionSettings.pagesCount = data.pages;
											scope.actionSettings.pxCurrentPage = 2;

											if ( scope.actionSettings.pxCurrentPage <= data.pages) scope.loadMoreBtn.morePostsAvailable = true;
											else scope.loadMoreBtn.morePostsAvailable = false;

											scope.actionSettings.filterPostsTemplate = data.posts;
											scope.filterPostsTemplate.posts = data.posts;
											scope.loadMoreBtn.postsLoading = false;

											scope.directiveInfo.afterPostsLoadCallback();

									});
								});
 
								$j('.lscf-sorting-by .lscf-dropdown-option').each(function() {
									
									$j(this).click(function(){
										
										orderByID = $j(this).attr('rel');	

										if ( '0' == orderByID ) {

											//filter default by post_date
											orderByID = 'post_date';

											$j('.lscf-sorting-by').removeClass('active');

											$j('.lscf-sort-down').removeClass('active');											
											$j('.lscf-sort-up').removeClass('active');											

										} else {
											$j('.lscf-sorting-by').addClass('active');
										}
										for ( var c = 0; c < scope.actionSettings.activeQuery.length; c++ ) {
							
											field = scope.actionSettings.activeQuery[ c ];
											
											if ( 'order-by' == field.ID ) {

												scope.actionSettings.activeQuery[ c ].value = orderByID;
												
												field_exists = true;

												break;

											}
										}
										
										if ( false === field_exists ) {

											query = {
												"ID":"order-by",
												"filter_as":null,
												"type":"order-posts",
												"order":"ASC",
												"value":orderByID
											};

											scope.actionSettings.activeQuery.push( query );
										}

										scope.loadMoreBtn.postsLoading = true;

										customFilterService.getPosts( scope.postType, scope.actionSettings.postsPerPage, 1, scope.actionSettings.activeQuery, shortcodeSettings )
											.then(function( data ) {
												data = data.data;
												scope.actionSettings.postsCount = data.postsCount;
												scope.actionSettings.pagesCount = data.pages;
												scope.actionSettings.pxCurrentPage = 2;

												if ( scope.actionSettings.pxCurrentPage <= data.pages) scope.loadMoreBtn.morePostsAvailable = true;
												else scope.loadMoreBtn.morePostsAvailable = false;

												scope.actionSettings.filterPostsTemplate = data.posts;
												scope.filterPostsTemplate.posts = data.posts;
												scope.loadMoreBtn.postsLoading = false;

												scope.directiveInfo.afterPostsLoadCallback();

										});

									});
								});
							}
					});
				}
        };
    }]);

function lscfSortingCustomDropddown() {
	
	var $j = jQuery;

	$j('.lscf-sorting-custom-dropdown').each(function(){
		var dataClass = $j(this).attr('data-class');
		var $this=$j(this),
			numberOfOptions=$j(this).children('option').length;
		$this.addClass('s-hidden');
		$this.wrap('<div class="select '+dataClass+'"></div>');
		$this.after('<div class="styledSelect"></div>');
		var $styledSelect=$this.next('div.styledSelect');
		$styledSelect.text($this.children('option').eq(0).text());
		var $list=$j('<div />',{'class':'options'}).insertAfter($styledSelect);

		for ( var i=0; i < numberOfOptions; i++ ) {
			
			var listClassName = ( 0 === i ? 'lscf-dropdown-option pxselect-hidden-list' : 'lscf-dropdown-option' );
		
			$j('<div />',{
				text:$this.children('option').eq(i).text(),
				rel:$this.children('option').eq(i).val(),
				'class':listClassName
			}).appendTo( $list );
		}
		var $listItems = $list.children('.lscf-dropdown-option');
		$styledSelect.click(function(e){
			e.stopPropagation();
			$j('div.styledSelect.active').each(function(){

				$j(this).removeClass('active').next('div.options').hide();
			});

			$j(this).toggleClass('active').next('div.options').toggle();
			$j(this).toggleClass('active').next('div.options').customScrollbar();
		});

		$listItems.click(function(e){
			$listItems.removeClass('pxselect-hidden-list');
			$j(this).addClass('pxselect-hidden-list');
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
angular.module(angAppName)
    .directive('viewmodeBasicGrid', ['customFilterService', function( customFilterService ){
        
        return{
            
            restrict:"AE",
            scope:true,
            bindToController: true,
            controllerAs: 'vm',
			templateUrl: lscfShortcodeData[0].plugin_url + 'app/views/posts-basic-grid.html',
            link:function(scope, elem, attrs){
				
				scope.actionSettings.initPostTheme = true;

				scope.directiveInfo.ready = function(){		

				};					
					
				scope.directiveInfo.afterPostsLoadCallback = function(){
					
				};

			}
		};
	}]);
angular.module(angAppName)
    .directive('viewmodeMasonryGrid', ['customFilterService', function( customFilterService ){
        
        return{
            
            restrict:"AE",
            scope:true,
            bindToController: true,
            controllerAs: 'vm',
			templateUrl: lscfShortcodeData[0].plugin_url + 'app/views/posts-mansonry.html',
            link:function(scope, elem, attrs){
				
				scope.actionSettings.initPostTheme = true;

				scope.directiveInfo.ready = function(){		

				};					
					
				scope.directiveInfo.afterPostsLoadCallback = function(){
					
				};

			}
		};
	}]);
angular.module(angAppName)
.directive('viewmodeWoocommerceGrid', ['customFilterService', function( customFilterService ){
	
	return{
		
		restrict:"AE",
		scope:true,
		bindToController: true,
		controllerAs: 'vm',
		templateUrl: lscfShortcodeData[0].plugin_url + 'app/views/posts-woocommerce-grid-2.html',
		link:function(scope, elem, attrs){

			scope.gridColumns = 3;

			scope.changeGridType = function( element, column ) {
				var gridChangers = document.getElementsByClassName('lscf-woo-grid-type');

				for ( var i = 0; i < gridChangers.length; i++ ) {
					gridChangers[ i ].className = 'lscf-woo-grid-type';
				}

				element.currentTarget.className = 'lscf-woo-grid-type active';
				scope.gridColumns = column;
			};

			scope.actionSettings.initPostTheme = true;

			scope.directiveInfo.ready = function(){
				
			};					
				
			scope.directiveInfo.afterPostsLoadCallback = function(){
				
			};

		}
	};
}]);
angular.module(angAppName)
    .directive('viewmodeWoocommerce', ['customFilterService', function( customFilterService ){
        
        return{
            
            restrict:"AE",
            scope:true,
            bindToController: true,
            controllerAs: 'vm',
			templateUrl: lscfShortcodeData[0].plugin_url + 'app/views/posts-woocommerce.html',
            link:function(scope, elem, attrs){
				
				scope.actionSettings.initPostTheme = true;

				scope.directiveInfo.ready = function(){		
					
				};					
					
				scope.directiveInfo.afterPostsLoadCallback = function(){
					
				};

			}
		};
	}]);