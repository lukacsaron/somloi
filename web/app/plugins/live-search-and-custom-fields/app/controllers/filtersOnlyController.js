angular.module(angAppName)
	.controller("lscfFiltersOnlyController", function ( $scope, $sce, $location, $attrs, customFilterService ) {

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
		
	});
