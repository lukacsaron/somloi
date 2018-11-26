var angAppName = '';
var appElems = document.querySelectorAll( '[ng-app]' );
var lscfContainers = document.querySelectorAll('.lscf-container');
var lscfDomainName = lscfShortcodeData[0].site_url.match( /((http|https):\/\/(.+))?\./ );

lscfDomainName = ( 'undefined' !== typeof lscfDomainName[1] ? lscfDomainName[1] : '' );
if ( appElems.length >= 1 ) {
	angAppName = appElems[ 0 ].getAttribute( 'ng-app' );

	if ( angAppName === 'lscf-app' && 1 <= lscfContainers.length ) {

		angular.module( 'lscf-app', [ 'ngSanitize', 'ngAnimate' ] )
			.config( function( $sceDelegateProvider ){
				$sceDelegateProvider.resourceUrlWhitelist([
					// Allow same origin resource loads.
					'self',
					// Allow loading from outer templates domain.
					lscfShortcodeData[0].plugin_url + '**',
					'**'
				]);

			});
	}
} else {

	angAppName = 'lscf-app';
	angular.module( 'lscf-app', [ 'ngSanitize', 'ngAnimate' ])
		.config( function( $sceDelegateProvider ){

			$sceDelegateProvider.resourceUrlWhitelist([
				// Allow same origin resource loads.
				'self',
				// Allow loading from outer templates domain.
				lscfShortcodeData[0].plugin_url + '**',
				'**'
			]);
		});
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