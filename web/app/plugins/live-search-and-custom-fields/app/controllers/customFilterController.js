angular.module(angAppName)
	.controller("pxfilterController", function ($location, $scope, $sce, $attrs, customFilterService ) {


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
	});