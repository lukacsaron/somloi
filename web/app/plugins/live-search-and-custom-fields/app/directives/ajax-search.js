angular.module(angAppName)
	.directive('ajaxSearch', function( $location, customFilterService ) {

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

	});