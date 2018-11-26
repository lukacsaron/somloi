function lscfOrderFilterFields( shortcodeSettings ){
	
	var self = this,
		$j = jQuery,
		interval;
	
	this.fieldsData;

	this.draggable = {};

	this.draggable.unbindOrder = function(){
		$j('.px-field-wrapper-container').sortable('destroy');
		$j('.lscf-filter-field').removeClass('lscf-draggable-field');
		$j('.subcategs-tax').hide();

	}

	this.draggable.initFilterFields = function(){
		
		if ( 'undefined' === typeof shortcodeSettings.settings.is_administrator || 1 !== shortcodeSettings.settings.is_administrator ) {
			return false;
		}

		$j('.px-field-wrapper-container').sortable({
			axis:false,
			items:".lscf-filter-field",
			update:function( event, ui ) {
				self.orderFieldsData();
			}
		});

		interval = setInterval(function() {
			self.init();
		}, 300);

	};

	this.draggable.unbindOptionOrder = function(){
		$j('.lscf-filter-field').sortable('destroy');
	};

	this.draggable.initOptionOrder = function(){

		$j('.lscf-filter-field').sortable({
			items:".lscf-field-option",
			update:function( event, ui ){
				
				var parent = ( ui.item.hasClass('lscf-hierarchy-subfield') ? ui.item.closest( '.subcategs-tax' ) : ui.item.closest( '.lscf-filter-field' ) );
				
				// index = ui.item[0].attributes['data-index'].value,

				self.orderFieldsOptionsData( parent, ui.item );
			}
		});
	}

	this.orderFieldsOptionsData = function( parentContainer, _this ){

		var fieldOptions = null,
			hierarchySubcategories = null,
			parentIndex =  parseInt( parentContainer.attr('data-index') );

		if ( parentContainer.hasClass('subcategs-tax') ) {

			parentIndex = parseInt( parentContainer.closest('.lscf-filter-field').attr('data-index') );

		} else {
			parentIndex = parseInt( parentContainer.attr('data-index') );
		}

		fieldOptions = [];
		hierarchySubcategories = [];

		parentContainer.find('.lscf-field-option').each(function( index ){
			
			var dataIndex = parseInt( $j(this).attr('data-index') );

			if ( 'taxonomies' == self.fieldsData.fields[ parentIndex ].group_type ) {

				if ( _this.hasClass('lscf-hierarchy-subfield') ) {

					if ( 'undefined' !== typeof self.fieldsData.fields[ parentIndex ].tax.subcategs ) {

						var parentTaxID =  $j(this).closest('.px_capf-field').attr('data-id').split('_-_'),
							taxSubcategsList = self.fieldsData.fields[ parentIndex ].tax.subcategs;

							parentTaxID = parseInt( parentTaxID[1] );

						taxSubcategsList.forEach( function( subcateg, subcategIndex ) {

							if ( subcateg.parent_id == parentTaxID ) {

								if ( 'undefined' !== typeof taxSubcategsList[ subcategIndex ] ) {
									hierarchySubcategories.push( taxSubcategsList[ subcategIndex ].data[ dataIndex ] );
								}
							}
						});
					}
				} else {
					if ( !$j( this ).hasClass('lscf-hierarchy-subfield') ) {
						fieldOptions.push( self.fieldsData.fields[ parentIndex ]['tax']['terms'][ dataIndex ] );
					}
				}

			} else if ( 'undefined' !== typeof self.fieldsData.fields[ parentIndex ]['parent'] &&
						'undefined' !== typeof self.fieldsData.fields[ parentIndex ]['parent']['options'] ) {

					fieldOptions.push( self.fieldsData.fields[ parentIndex ]['parent']['options'][ dataIndex ] );

			} else {

				fieldOptions.push( self.fieldsData.fields[ parentIndex ]['options'][ dataIndex ] );

			}
		});

		if ( 'taxonomies' == self.fieldsData.fields[ parentIndex ].group_type ) {

				if ( _this.hasClass('lscf-hierarchy-subfield') ) {

					if ( 'undefined' !== typeof self.fieldsData.fields[ parentIndex ].tax.subcategs ) {

						var parentTaxID =  _this.closest('.px_capf-field').attr('data-id').split('_-_'),
							taxSubcategsList = self.fieldsData.fields[ parentIndex ].tax.subcategs;

							parentTaxID = parseInt( parentTaxID[1] );
						
						taxSubcategsList.forEach( function( subcateg, subcategIndex ) {

							if ( subcateg.parent_id == parentTaxID ) {

								if ( 'undefined' !== typeof taxSubcategsList[ subcategIndex ] ) {

									self.fieldsData.fields[ parentIndex ]['tax']['subcategs'][ subcategIndex ].data = hierarchySubcategories;

								}

							}
						});
					}
				} else {

					self.fieldsData.fields[ parentIndex ]['tax']['terms'] = fieldOptions;
					self.fieldsData.fields[ parentIndex ]['terms'] = fieldOptions;

				}

		} else if ( 'undefined' !== typeof self.fieldsData.fields[ parentIndex ]['parent'] &&
					'undefined' !== typeof self.fieldsData.fields[ parentIndex ]['parent']['options'] ) {

			self.fieldsData.fields[ parentIndex ]['parent']['options'] = fieldOptions;
			self.fieldsData.fields[ parentIndex ].group_type = 'cf_variation';

		} else {

			self.fieldsData.fields[ parentIndex ]['options'] = fieldOptions;

		}

		self.updateFieldsDataOrder( {
			"saved_field_options" : 1
		}, null );

		self.resetFieldOptionsIndex( parentContainer );

	};

	this.orderFieldsData = function(){
		
		var fieldsData = [];

		$j('.lscf-filter-field').each(function(){
			
			var index = parseInt( $j(this).attr('data-index') );
			fieldsData.push( self.fieldsData.fields[index] );

		});

		self.resetFieldsIndex();

		self.fieldsData.fields = fieldsData;
		
		self.updateFieldsDataOrder( {
			"saved_field_options" : 1
		}, null );

	};

	

	this.updateFieldsDataOrder = function( options, callback ){

		$j.ajax({
			type:"POST",
			url:pxData.ajaxURL,
			data:{
				"action":"lscf-administrator-ajax",
				"section":"update-fields-order",
				"options":options,
				"fields":angular.toJson( self.fieldsData.fields ),
				"filter_id":self.fieldsData.filterID
			},
			success:function( data ){

				if ( null !== callback ) {
					callback( data );
				}
			},
			dataType:"html"
		})
	}

	this.resetFieldsIndex = function(){

		$j('.lscf-filter-field').each(function(index){
			$j(this).attr({
				'data-index':index
			});
		});

	}

	this.resetFieldOptionsIndex = function( parentField ){
		
		parentField.find('.px_capf-field').each(function(){
			
			$j( this ).find( '.lscf-field-option' ).each(function( index ){
				$j( this ).attr({
					'data-index':index
				});
			});
		})
		
	}


	this.init = function() {
		clearInterval( interval );
		$j('.lscf-filter-field').addClass('lscf-draggable-field');
		$j('.subcategs-tax').fadeIn();
	};

	
}
