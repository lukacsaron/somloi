function customRange(){
    var $j = jQuery,
        self = this;
    this.init = function(){
        
		$j(".customRange").each(function(){
          
          var _this = $j(this);
          
          self.defaultPosition(_this);
          
          _this.find(".draggablePoint").draggable({
              drag:function(){
                  var x = $j(this).position().left+15,
                      rangeVal = 0;
                  _this.find(".range_draggable").css({
                      "width":parseInt(x)+"px"
                  });
                  rangeVal = self.calculateCurrentRangeValue(_this, x);
              },
              axis:"x",
              containment: _this
          });


        });    
    };
    
    this.calculateCurrentRangeValue = function(rangeElement, position){
        var _this = rangeElement;
        var containerWidth = _this.width();
        var maxValue = _this.data('maxval');
        
        var rangeValue = Math.round(position*maxValue/containerWidth);
        
        rangeValue = rangeValue>maxValue?maxValue:rangeValue;
        
        _this.find(".rangeVal").text(rangeValue);
        _this.find('input[type="hidden"]').val(rangeValue);
        
        return rangeValue;
    };

    this.defaultPosition = function(_this){
        var percentage = _this.data('defaultpos'),
            x = 0;
        _this.find(".range_draggable").css({"width":percentage+"%"});
        
        x = parseInt(_this.find(".draggablePoint").position().left);
        self.calculateCurrentRangeValue(_this, x);
    }
    
}
(function(){
   $j = jQuery;
   $j(function(){
        var rangeField = new customRange();
        rangeField.init();     
   })
   
})();;var adjustPostContainerHeight = new posts_block_container();
adjustPostContainerHeight();

function posts_block_container(){

	var $j = jQuery,
		called = false,
		self = this;

	this.check_container_block_width = function() {

		if ( $j('.lscf-grid-view').length > 0 ) {
			called  = true;

			var containerWidth = $j('.lscf-grid-view').width();

			if ( containerWidth < 800 )
				$j('.lscf-grid-view').addClass("small-view");
			else 
				$j('.lscf-grid-view').removeClass("small-view");

			if (containerWidth > 840)
				$j('.lscf-grid-view').addClass("large-view");
			else
				$j('.lscf-grid-view').removeClass("large-view");

		}
	};

	return function() {
		
		self.check_container_block_width();

		if (!called) {
			setTimeout(function() {
				self.check_container_block_width();
			}, 400);
		}

	}

}

function lscfLoadFiltersFromUrl( urlParams, filterFieldsData ) {

	var self = this,
		$j = jQuery,
		activeFilters = {
			filters: [],
			range: [],
			generalSearch: []
		};
		activeFields = [];

	this.initFiltersData = function() {

		for ( var key in urlParams ) {

			var values = urlParams[ key ].split(',');

			if ( key.match( /(.+?)_-_[0-9]+$/ ) ) {
				var hierarchicalFilter = self.buildHierarchicalFilterTerms( filterFieldsData, key, values );
				activeFilters.filters.push( hierarchicalFilter );
				activeFields[ key ] = values;
			} else if ( 'undefined' !== typeof filterFieldsData[ key ] && '' !== urlParams[ key ] ) {

				if ( '' !== urlParams[ key ] && 'boolean' !== typeof urlParams[ key ] ) {

					filterFieldsData[ key ].value = values;

					activeFields[ key ] = values;

					switch ( filterFieldsData[ key ].group_type ) {

						case 'custom_field':
						case 'taxonomies':

								activeFilters.filters.push( filterFieldsData[ key ] );

							break;

						case 'additional_fields':

							if ( 'undefined' !== typeof filterFieldsData[ key ].type && 'range' === filterFieldsData[ key ].type ) {

								var rangeFilter = filterFieldsData[ key ],
									values		= filterFieldsData[ key ].value;
								rangeFilter.ID = rangeFilter.fieldID;
								
								filterFieldsData[ key ].value = {};
								rangeFilter.value.min = values[0];
								rangeFilter.value.max = values[1];
								rangeFilter.filter_as = null;

								activeFilters.filters.push( rangeFilter );
							};

							if ( 'search' === key ) {

								var mainDefaultSearch = filterFieldsData[ key ];
								mainDefaultSearch.ID = 'ajax-main-search';
								mainDefaultSearch.filter_as = null;
								mainDefaultSearch.type = 'main-search';
								mainDefaultSearch.value = filterFieldsData[ key ].value[0];
								activeFilters.filters.push( mainDefaultSearch );

								activeFilters.generalSearch.push( mainDefaultSearch );

							}

							if ( 'search_by_sku' === key ) {
								activeFilters.generalSearch.push( filterFieldsData[ key ] );
							}

							break;
					}
				}
			}

		}

		return true;

	};

	this.showActiveHierarchicalFilters = function() {
		var activeTerms = [];
		$j('.lscf-hierarchical-terms').each(function(){
			$j(this).find('.px-select-box').each(function(){
				var dropdownValue = $j(this).find('select option:selected').val();
				activeTerms.push( parseInt( dropdownValue ) );
			});

			$j(this).find('.px_checkboxesList').each(function(){
				if ( $j(this).find('.pxRadioLabel').length > 0 ) {
					var checkboxValue = $j(this).find('input:checked').val();
					activeTerms.push( parseInt( checkboxValue ) );
				} else {
					$j(this).find('.px_checkbox-li').each(function(){
						var checboxLabel = $j(this).find('.px_checkbox');
						if ( checboxLabel.hasClass('active') ) {
							var checkboxValue = $j(this).find('input[type="checkbox"]').val();
							activeTerms.push( parseInt( checkboxValue ) );
						}
					});
				}
			});

			for ( var i = 0; i < activeTerms.length; i++ ) {
				$j('.subcategs-tax').each( function(){
					var parentTermId = parseInt( $j(this).attr('data-parent') );
					if ( -1 < activeTerms.indexOf( parentTermId ) ) {
						$j(this).show();
					}
				})
			}

		});

	}

	this.buildHierarchicalFilterTerms = function( filterFieldsData, urlKeyParam, values ) {

		if ( ! urlKeyParam.match( /(.+?)_-_[0-9]+$/ ) ) {
			return false;
		}

		var taxSubterm = {
			ID: urlKeyParam,
			filter_as: 'checkbox_post_terms',
			group_type: 'taxonomies',
			type: 'checkbox_post_terms',
			value: values,
			variation_id: null
		};
		
		return taxSubterm; 

	}

	this.getUrlLabelByID = function( itemId ) {

		switch ( itemId ) {

			case 'ajax-main-search':
				return 'search'

		}

		return itemId;
	};

	this.buildUrlLocation = function( queryArgs ) {
		
		var urlLocation = {};

		queryArgs.forEach( function( item ) {

			var urlLabel = self.getUrlLabelByID( item.ID );

			if ( 'undefined' !== typeof item.type && 'range' === item.type ) {

				urlLocation[ 'range_' + urlLabel ] = item.value.min + ',' +item.value.max;

			} else if ( Array.isArray( item.value ) ) {
				urlLocation[ urlLabel ] = item.value.join( ',' );
			} else {
				urlLocation[ urlLabel ] = item.value;
			};
		});

		return urlLocation;
	}

	this.hasCustomFieldsOrTaxonomies = function () {

		if ( activeFilters.filters.length > 0 ) {
			return true;
		}

		return false;

	};

	this.hasRangeFields = function () {

		if ( activeFilters.range.length > 0 ) {
			return true;
		}
		return false;

	};

	this.hasGeneralSearch = function () {

		if ( activeFilters.generalSearch.length > 0 ) {
			return true;
		};

		return false;
	};


	(function(){ 
		setTimeout( function(){
			self.showActiveHierarchicalFilters();
		}, 400 );
		return self.initFiltersData() 
	})();

	return {

		hasGeneralSearch: self.hasGeneralSearch,
		hasCustomFieldsOrTaxonomies: self.hasCustomFieldsOrTaxonomies,
		hasRangeFields: self.hasRangeFields, 
		activeFilters: activeFilters,
		activeFields: activeFields,
		buildUrlLocation: self.buildUrlLocation
	};

}

function pxFilterFieldsAction( shortcodeSettings ){

	var $j = jQuery,
		self = this,
		scriptInterval,
		wrapper = $j( '.lscf-container' ).eq( shortcodeSettings.shortcodeIndex );

	this.reset_fields = function(){
		 
		wrapper.find( '.pxSelectField' ).each(function(){
			 
			 if ( $j( this ).hasClass('active-val') ) {
				 $j(this).find('.options .lscf-dropdown-option[rel="0"]').trigger("click");
			 }
		 });

		 wrapper.find( '.pxDateField' ).each(function(){
			 $j(this).find('.initCalendar').val('');
		 });

		 wrapper.find( '.pxCheckField' ).each(function(){
			 $j( this ).find('.px_checkboxesList .px_checkbox').each(function(){
				 $j(this).removeClass('active');
			 })
		 });
		 wrapper.find( '.pxRadioField' ).each(function(){
			 $j( this ).find('.px_checkbox-li input[type="radio"]').each(function(){
				 $j(this).removeAttr('checked');
			 })
		 });

		 wrapper.find( '.subcategs-tax' ).hide();

	};

	this.mobileExpandFilter = function(){

		wrapper.find('.px-filter-label-mobile').on( 'click', function(){

			if ( $j(this).closest('.px-capf-wrapper').hasClass('lscf-wrapper-independent-filters') ) {
				return false;
			};

			var animationHeight = $j('.px-field-wrapper-container').height()+140;
			$j(this).closest('.px-capf-wrapper').css({"min-height":(animationHeight+200)+"px"});

			if ( wrapper.find('.px-fiels-wrapper').hasClass('active') ){
				wrapper.find('.px-fiels-wrapper').removeClass('ready');
				wrapper.find('.px-fiels-wrapper').animate({
					height:"41px"
				}, 400, function(){
					$j(this).removeClass('active');
					$j(this).closest('.px-capf-wrapper').css({"min-height":"none"});
					$j(this).closest('.px-capf-wrapper').attr({
						style: 'min-height:unset'
					});
				});
			} else {
				wrapper.find('.px-fiels-wrapper').addClass('active');

				wrapper.find('.px-fiels-wrapper').animate({
					height:animationHeight
				}, 300, function(){
					$j(this).addClass('ready');
				});
			}
		});

	};

	this.initSeeMore = function(){
		
		wrapper.find( '.lscf-see-more' ).on( 'click', function () {
	
			var parent = $j(this).closest('.px_capf-field');
			
			if ( parent.hasClass('active') ) {
				$j(this).text( shortcodeSettings.options.writing.see_more );
				parent.removeClass('active');

			} else {
				$j(this).text( shortcodeSettings.options.writing.see_less );
				parent.addClass('active');

			}

		});

	}

	this.reset_subcategs = function( parent, isCurrentSubcateg ){

		if ( 'undefined' === typeof isCurrentSubcateg ) { isCurrentSubcateg = false; }

		if ( true === isCurrentSubcateg ) {

			parent.find(".pxSelectField").each(function(){
				$j(this).removeClass('active-val');
				$j(this).find('.styledSelect').text( lscfShortcodeData[0].options.writing.select );
			});
	
			parent.find(".pxCheckField").each(function(){
				$j( this ).find('.px_checkboxesList .px_checkbox').each(function(){
					 $j(this).removeClass('active');
				 })
			});
			
			parent.find(".pxRadioField").each(function(){
				$j( this ).find('.px_checkbox-li input[type="radio"]').each(function(){
					 $j(this).removeAttr('checked');
				})
			});	

			return;
		}

		parent.find(".subcategs-tax .pxSelectField").each(function(){
			$j(this).removeClass('active-val');
			$j(this).find('.styledSelect').text( lscfShortcodeData[0].options.writing.select );
		});

		parent.find(".subcategs-tax .pxCheckField").each(function(){
			$j( this ).find('.px_checkboxesList .px_checkbox').each(function(){
				 $j(this).removeClass('active');
			 })
		});
		
		parent.find(".subcategs-tax .pxRadioField").each(function(){
			$j( this ).find('.px_checkbox-li input[type="radio"]').each(function(){
				 $j(this).removeAttr('checked');
			})
		});
	}

	this.reset_subcategs_data = function( parentTax, data, filterData, subcategIndex ) {

		if ( 'undefined' !== typeof subcategIndex ) {

			parentTax.find( '.px_capf-subfield' ).each( function( index ) {

				if ( index > subcategIndex ) {
					
					$j(this).removeClass('active-val');
					$j(this).find('.styledSelect').text('Select');

					var reset_value = "0";

					if ( 'px_check_box' == filterData['filterAs'] || 'px_icon_check_box' == filterData['filterAs'] ) {

						var new_val = [];
							new_val[0] = "0";
						reset_value = new_val;
					}

					data.push({
						"ID":$j(this).data('id'),
						"value": reset_value,
						"type": filterData['type'],
						"filter_as": filterData['filterAs'],
						"group_type": filterData['group_type'],
						"variation_id": filterData['variation_id']
					});
				};
			});

			return data;
		}

		parentTax.find('.px_capf-subfield').each(function(index){

			var reset_value = "0";

			if ( 'px_check_box' == filterData['filterAs'] || 'px_icon_check_box' == filterData['filterAs'] ) {
				var new_val = [];
					new_val[0] = "0";
				reset_value = new_val;
			}

			data.push({
				"ID": $j(this).data('id'),
				"value": reset_value,
				"type": filterData['type'],
				"filter_as": filterData['filterAs'],
				"group_type": filterData['group_type'],
				"variation_id": filterData['variation_id']
			});

		});

		return data;

	};

	this.construct = function(callback){
				
		scriptInterval = setInterval( function(){
			self.init(callback);
		}, 500 );
		
		setTimeout( function(){
			clearInterval(scriptInterval);
		}, 1100 );
	}

	this.init = function(callback){

		self.pxSelect(callback);
		self.pxDate(callback);
		self.pxDateInterval(callback);
		self.pxCheckbox(callback);
		self.pxRadiobox(callback);
		self.mobileExpandFilter();

		setTimeout(function(){
			self.initSeeMore();
		}, 2000);
		

	};

	this.pxSelect = function(callback){

		wrapper.find('.pxSelectField').ready(function(){

			clearInterval( scriptInterval );

			wrapper.find( '.pxSelectField' ).each(function(){

				var ID = $j(this).data( 'id' );
				var _parent = $j(this),
					group_type = $j(this).closest( '.lscf-group-type' ).attr( 'data-group-type' ),
					variation_id = ( $j(this).closest( '.lscf-variation-field' ).length > 0 ? $j(this).closest('.lscf-variation-field').attr('data-variation-id') : null );
				
				var filterTypeAttr = $j(this).attr('data-filter-as');
				
				if ( typeof filterTypeAttr !== typeof undefined && false !== filterTypeAttr  ) {
					var filterAs = filterTypeAttr;
				} else {
					var filterAs = "select";
				}

				var filterData = {
					'filterAs': filterAs,
					'group_type': group_type,
					'variation_id': variation_id,
					'type': 'select'
				};

				var dropdownField = $j(this);

				$j( this ).find(".options .lscf-dropdown-option").click(function(){

					var value = $j(this).attr("rel"),
						data = [];

					if ( 0 == value ) {

						if ( ! dropdownField.hasClass('px_capf-subfield') ) {

							self.reset_subcategs( _parent.closest('.lscf-taxonomies-fields') );

							data = self.reset_subcategs_data( _parent.closest('.lscf-taxonomies-fields'), data, filterData );

						} else {
							var subcategIndex = parseInt( dropdownField.closest('.subcategs-tax').attr('data-index') );
							data = self.reset_subcategs_data( _parent.closest('.lscf-taxonomies-fields'), data, filterData, subcategIndex );
						}

						_parent.removeClass('active-val');

					} else {

						_parent.addClass('active-val');

						if ( ! dropdownField.hasClass('px_capf-subfield') ) {

							self.reset_subcategs( _parent.closest('.lscf-taxonomies-fields') );

							_parent.closest('.lscf-taxonomies-fields').find('.px_capf-subfield').each(function(index){

								var reset_value = "0",
									matches = $j(this).data('id').match( /(.+?)_-_([0-9]+)$/ );

								if ( parseInt( matches[2] ) != parseInt( value ) ) {

									if ( 'px_check_box' == filterAs || 'px_icon_check_box' == filterAs ) {

										var new_val = [];
											new_val[0] = "0";
										reset_value = new_val;

									}

									data.push({
										"ID":$j(this).data('id'),
										"value":reset_value,
										"type":"select",
										"filter_as":filterAs,
										"group_type":group_type,
										"variation_id":variation_id
									});
								}

							});
						} else {
							var subcategIndex = parseInt( dropdownField.closest('.subcategs-tax').attr('data-index') );
							data = self.reset_subcategs_data( _parent.closest('.lscf-taxonomies-fields'), data, filterData, subcategIndex );
						}
					}

					if ( 'px_check_box' == filterAs || 'px_icon_check_box' == filterAs ) {
						
						var new_val = [];
							new_val[0] = value;
						value = new_val;
					}

					data.push({
						"ID": ID,
						"value": value,
						"type": "select",
						"filter_as": filterAs,
						"group_type": group_type,
						"variation_id": variation_id
					});

					callback(data);
				});
				
			});
			
			
		});
		
	};

	this.pxDate = function(callback){
		
		$j(".pxDateField").ready(function(){
			
			clearInterval( scriptInterval );
			
			wrapper.find( '.pxDateField' ).each(function(){
				
				var ID = $j(this).data("id"),
					alternativeFormatClassname = $j(this).attr('data-alternative');
				
				// remove Date When input is empty
				$j(this).find('input[type="text"]').blur(function(){
					var inputVal = $j(this).val();
					
					if(inputVal ==='' && !$j(this).hasClass("empty")){
						var data = {
							"ID":ID,
							"value":"",
							"type":"date"
						}
						callback(data);
						$j(this).addClass("empty");
					}
				});
				
				$j(this).find('input[type="text"]').datepicker({
					altField: '.' + alternativeFormatClassname,
					altFormat: 'mm/dd/yy',
					onSelect: function(date){
						var data = {
							"ID":ID,
							"value":$j('.' + alternativeFormatClassname ).val(),
							"type":"date"
						}
						$j(this).removeClass("empty");
						callback(data);
					}
				});
				
			});
			
		})
		
	},

	this.pxDateInterval = function(callback){
		
		$j( '.pxDateIntervalField' ).ready(function(){
			clearInterval( scriptInterval );
			wrapper.find( '.pxDateIntervalField' ).each(function(){
				
				var ID = $j(this).data("id");
				
				var data = {
					"type":"date-interval",
					"ID":ID,
					"fields":{
						"from":"",
						"to":""
					}
				};

				$j(this).find('input[type="text"]').each(function(index){

					var alternativeFormatClassname = $j(this).attr('data-alternative');

					$j(this).datepicker({
						altField: '.' + alternativeFormatClassname,
						altFormat: 'mm/dd/yy',
						onSelect: function(date){

							data.fields[$j(this).data("type")] = {
								"value":$j('.' + alternativeFormatClassname ).val()
							};

							callback(data);
						}
					});
				})
			});
			
		})
		
	}

	this.pxCheckbox = function(callback){
		
		$j(".pxCheckField").find("label.px_checkbox").ready(function(){
			
			clearInterval(scriptInterval);
			var values = new Array();

			wrapper.on( 'click', '#lscf-reset-filters', function(){
				for ( var s in values ) {
					values[ s ] = [];
				}
			});
			
			wrapper.find('.pxCheckField').each(function(c){

				var filterTypeAttr = $j(this).attr('data-filter-as'),
					group_type = $j(this).closest( '.lscf-group-type' ).attr( 'data-group-type' ),
					variation_id = ( $j(this).closest( '.lscf-variation-field' ).length > 0 ? $j(this).closest('.lscf-variation-field').attr('data-variation-id') : null ),
					_this = $j(this);

				if ( typeof filterTypeAttr !== 'undefined' && false !== filterTypeAttr  ) {
					var filterAs = filterTypeAttr;
				} else {
					var filterAs = "px_check-box";
				}

				var checkboxType = $j(this).data('type');
				var ID = wrapper.find(".pxCheckField").eq(c).data("id");

				values[ c ] = new Array();

				wrapper.find( '.pxCheckField' ).eq(c).find( '.px_checkboxInput:checked' ).each(function(){
					values[ c ].push( $j(this).val() );
				});

				wrapper.find(".pxCheckField").eq(c).find("label.px_checkbox").each(function(index){

					$j(this).click(function(e){

						e.preventDefault();
						e.stopPropagation();
						e.stopImmediatePropagation();
						
						$j(this).toggleClass("active");
						
						var value = $j(".pxCheckField").eq(c).find(".px_checkboxInput").eq(index).val(),
							data = [];

						if( $j(this).hasClass("active") ){

							values[c].push(value);

						} else {

							if ( _this.hasClass('px_tax-field') && !_this.hasClass('px_capf-subfield') ) {

								wrapper.find('.lscf-subcategory-child-of-' + value ).each(function(subcategIndex){

									self.reset_subcategs( $j(this), true );

									var subcategID = $j(this).find('.px_capf-subfield').data('id');

									data.push({
										"ID": subcategID,
										"value":[],
										"type":checkboxType,
										"filter_as":filterAs,
										"group_type":group_type,
										"variation_id":variation_id
									});

								});
							} else if( _this.hasClass('px_capf-subfield') ) {

								var subCategActiveIndex = _this.data('index'),
									className = _this.closest('.subcategs-tax').data('classname');

								$j( '.' + className ).each(function(subcategIndex){

									var subcategID = $j(this).find('.px_capf-subfield').data('id'),
										subcategIndex = $j(this).find('.px_capf-subfield').data('index');

									if ( parseInt( subCategActiveIndex ) < parseInt( subcategIndex ) ) {
										self.reset_subcategs( $j(this), true );
										data.push({
											"ID": subcategID,
											"value":[],
											"type":checkboxType,
											"filter_as":filterAs,
											"group_type":group_type,
											"variation_id":variation_id
										});
									}
								});

							}

							var valueIndex = values[ c ].indexOf( value );
							if ( valueIndex > -1 ){
								values[ c ].splice( valueIndex, 1 );
							}
						}

						data.push({
							"ID":ID,
							"value":values[ c ],
							"type":checkboxType,
							"filter_as":filterAs,
							"group_type":group_type,
							"variation_id":variation_id
						});
						
						callback(data);
						
						return false;
					})
				});

			})
		});

	}

	this.pxRadiobox = function(callback){

		$j(".pxRadioField").ready(function(){

			clearInterval( scriptInterval );

			wrapper.find(".pxRadioField").each(function(){

				var ID = $j(this).data("id");
				var _this = $j(this),
					group_type = $j(this).closest( '.lscf-group-type' ).attr( 'data-group-type' ),
					variation_id = ( $j(this).closest( '.lscf-variation-field' ).length > 0 ? $j(this).closest('.lscf-variation-field').attr('data-variation-id') : null );
				
				var filterTypeAttr = $j(this).attr('data-filter-as');

				if ( typeof filterTypeAttr !== typeof undefined && false !== filterTypeAttr  ) {
					var filterAs = filterTypeAttr;
				} else {
					var filterAs = "radio";
				}


				$j(this).find('.pxRadioLabel').each(function(index){

					$j(this).click(function(){

						var value = _this.find('input[type=radio]').eq(index).val(),
							data = [];

						if ( 0 == value ) {

							if ( _this.hasClass('px_tax-field') && !_this.hasClass('px_capf-subfield') ) {

								self.reset_subcategs( _this.closest('.lscf-taxonomies-fields') );

								_this.closest('.lscf-taxonomies-fields').find('.pxRadioField').each(function(){
									
									data.push({
										"ID":$j(this).data('id'),
										"value":0,
										"type":"radio",
										"filter_as":filterAs,
										"group_type":group_type,
										"variation_id":variation_id
									});

								});

							} else if( _this.hasClass('px_capf-subfield') ) {
								
								var subcategIndex = parseInt( _this.closest('.subcategs-tax').attr('data-index') );

								_this.closest('.lscf-taxonomies-fields').find('.px_capf-subfield.pxRadioField').each(function(index){

									if ( index > subcategIndex ) {
										
										data.push({
											"ID":$j(this).data('id'),
											"value":0,
											"type":"radio",
											"filter_as":filterAs,
											"group_type":group_type,
											"variation_id":variation_id
										});

									}

								})
							}
						}

						if ( 'px_check_box' == filterAs || 'px_icon_check_box' == filterAs ) {
							var new_val = [];
								new_val[0] = value;
							value = new_val;
						}

						if ( ! _this.hasClass('px_capf-subfield') ) {
							
							self.reset_subcategs( _this.closest('.lscf-taxonomies-fields') );

							_this.closest('.lscf-taxonomies-fields').find('.px_capf-subfield').each(function(index){

								var reset_value = "0",
									matches = $j(this).data('id').match( /(.+?)_-_([0-9]+)$/ );

								if ( parseInt( matches[2] ) != parseInt( value ) ) {

									if ( 'px_check_box' == filterAs || 'px_icon_check_box' == filterAs ) {

										var new_val = [];
											new_val[0] = "0";
										reset_value = new_val;

									}

									data.push({
										"ID":$j(this).data('id'),
										"value":reset_value,
										"type":"radio",
										"filter_as":filterAs,
										"group_type":group_type,
										"variation_id":variation_id
									});
								}

							});
						}

						data.push({
							"ID":ID,
							"value":value,
							"type":"radio",
							"filter_as":filterAs,
							"group_type":group_type,
							"variation_id":variation_id
						});

						callback(data);
						
					});

				});

			});

		})

	}

}


function px_customRange( shortcodeIndex ){

	var $j = jQuery,
		self = this, 
		rangeInterval,
		wrapper = $j( '.lscf-container' ).eq( shortcodeIndex ) ;

	this.construct = function(callback){
		
		
		rangeInterval = setInterval(function(){
			self.init(callback);
		}, 500); 
		
		setTimeout(function(){
			
			clearInterval(rangeInterval);
			
		}, 1100);
	};

	this.init = function( callback ){
		
		wrapper.find( '.customRange' ).ready(function(){
			
			clearInterval( rangeInterval );

			wrapper.find( '.customRange' ).each(function(index){

				var _this = $j(this);
				var rangeVal = 0;
				var ID = wrapper.find('.pxRangeField').eq(index).data("id");

				var valueLabel = _this.find(".rangeVal").data('labelval'),
					labelPosition = _this.data('labelpos');

				var rangeValues = {
					"min":0,
					"max": parseInt( _this.data('maxval') )
				};

				self.defaultPosition(_this);

				_this.find(".draggablePoint").draggable({
					drag: function( event ) {

						var x = ( $j(this).position().left < 30 ? $j(this).position().left : $j(this).position().left + 15 );
						_this.find(".range_draggable").css({
							"width":parseInt(x) - _this.find('.startPoint').position().left + "px"
						});

						_this.find(".range_draggable").attr('data-width', parseInt(x) );
						
						rangeVal = self.calculateCurrentRangeValue(_this, x);
						_this.attr('data-value', rangeVal );
						_this.find(".rangeVal").text( self.formatRangeLabel( valueLabel, rangeVal, labelPosition ) );
						_this.find('input[type="hidden"]').val( rangeVal );
						
						rangeValues.max = rangeVal;

					},
					axis:"x",
					stop:function(){
					var data = {
						"ID":ID,
						"value":rangeValues,
						"type":"range"
					}  
					callback(data);
					},
					containment: _this
				});

				_this.find(".startDraggablePoint").draggable({

					drag:function(){
						var x = $j(this).position().left,
							dataWidth = _this.find('.range_draggable').attr('data-width'),
							rangeTrackerWidth = ( dataWidth != '-1' ? dataWidth : _this.find('.range_draggable').width() ),
							rangeVal = 0;

						if ( '-1' == dataWidth ) {
								_this.find(".range_draggable").attr('data-width', _this.find('.range_draggable').width() );
						}

						rangeVal = self.calculateCurrentRangeValue( _this, x );
						_this.attr('data-value', rangeVal );

						_this.find(".defaultVal").text( self.formatRangeLabel( valueLabel, rangeVal, labelPosition ) );

						_this.find(".range_draggable").css({
							"width":( rangeTrackerWidth - x ) + "px" ,
							"left":x + "px"
						});

						rangeValues.min = rangeVal;

					},
					axis:"x",
					containment: _this,
					stop: function(){
						var data = {
							"ID": ID,
							"value": rangeValues,
							"type": "range"
						}  
						callback(data);
					},
					containment: _this
				
				});

			});

		});
	};

	this.formatRangeLabel = function( label, value, labelPosition ) {

		if ( 'undefined' !== typeof labelPosition && 'right' === labelPosition ) {
			return value + label;
		}
		return label + value;
	}

	this.calculateCurrentRangeValue = function( rangeElement, position ) {
		var _this = rangeElement;
		var containerWidth = _this.width();
		var maxValue = parseInt( _this.data('maxval') );
		var startValue = parseInt( _this.data('minval') );

		var rangeValue = Math.round( position * ( maxValue - startValue ) / containerWidth );
		rangeValue = startValue + rangeValue;

		rangeValue = rangeValue > maxValue ? maxValue : rangeValue;

		return rangeValue;
	};

	this.defaultPosition = function( _this ) {

		var defaultPosLeftPercentage = _this.data('defaultpos-left'),
			defaultPosRightPercentage = _this.data('defaultpos'),
			rangeFullWidth = _this.find('.range_draggable').width();

		if ( 'default' === defaultPosLeftPercentage || 'default' === defaultPosRightPercentage ) {
			return;
		}

		_this.find('.startDraggablePoint').css({'left': defaultPosLeftPercentage + '%' });
		_this.find('.draggablePoint').css({'left': defaultPosRightPercentage + '%' });

		var defaultLeftPosPixels = _this.find('.startDraggablePoint').position().left,
			defaultRightPosPixels = ( _this.find('.draggablePoint').position().left < 30 ? _this.find('.draggablePoint').position().left : _this.find('.draggablePoint').position().left + 15 );
		
		_this.find( '.range_draggable').css({
			'width': ( defaultRightPosPixels - defaultLeftPosPixels ) + 'px',
			'left': defaultLeftPosPixels + 'px'
		});

	};



}

function customSelectBox(){

	var $j = jQuery,
		self = this,
		scriptInterval;
	
	this.construct = function(){
		
		scriptInterval = setInterval(function(){
			self.init();
		}, 500);
		
		setTimeout(function(){
			clearInterval(scriptInterval);
		}, 1100);
	}

	this.init = function(){

		$j(".custom-select").ready(function(){

			clearInterval(scriptInterval);

			$j('.custom-select').each(function(){
				var dataClass=$j(this).attr('data-class');
				var $this=$j(this),
					numberOfOptions=$j(this).children('option').length;
				$this.addClass('s-hidden');
				$this.wrap('<div class="select '+dataClass+'"></div>');
				$this.after('<div class="styledSelect"></div>');
				var $styledSelect=$this.next('div.styledSelect');

				if ( $this.children('option:selected') ) {

					$styledSelect.text( $this.children('option:selected').text() );
					$styledSelect.addClass('active');
				} else {

					$styledSelect.text($this.children('option').eq(0).text());
				};

				var $list=$j('<div />',{'class':'options'}).insertAfter($styledSelect);

				for ( var i=0; i<numberOfOptions; i++ ) {
					
					var listClassName = ( 0 == i ? 'lscf-dropdown-option pxselect-hidden-list' : 'lscf-dropdown-option' );
				
					if ( 0 !== parseInt( $this.children('option').eq(i).val() ) ) {
						listClassName += " lscf-field-option";
					}

					if ( $this.children('option').eq(i).attr('data-class') ) {
						listClassName += " " + $this.children('option').eq(i).attr('data-class');
					}
					
					$j('<div />',{
						text:$this.children('option').eq(i).text(),
						rel:$this.children('option').eq(i).val(),
						'data-index':$this.children('option').eq(i).attr('data-index'),
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
					e.preventDefault();
					e.stopPropagation();
					$listItems.removeClass('pxselect-hidden-list');
					$j(this).addClass('pxselect-hidden-list');
					$styledSelect.text($j(this).text()).removeClass('active');
					$this.val($j(this).attr('rel'));
					$list.hide();
				});

				$j('.lscf-container').on('click', function( event ){

					if ( $j('body').hasClass('not-selectable') ){
						return;
					}

					$styledSelect.removeClass('active');
					$list.hide();
				});

			});
		})
	}

}

function lscfPosts() {
	var $j = jQuery,
		self = this,
		scriptInterval;

	this.constructHover = function(){
		
		scriptInterval = setInterval(function(){
			self.blockPosts_hover();
		}, 500);
		
		setTimeout( function(){
			clearInterval(scriptInterval);
		}, 1100);
		
	}

	this.init = function(){
		self.viewMode();
		self.choseDisplayMode_ofListing();
	}
	this.viewMode = function(){
		
		$j(".viewMode #blockView").on("click", function(){
			$j(".viewMode div").removeClass("active");
			$j(this).addClass("active");
			$j("#lscf-posts-container-defaultTheme").addClass("block-view");
		});

		$j(".viewMode #listView").on("click", function(){
			$j(".viewMode div").removeClass("active");
			$j(this).addClass("active");
			$j("#lscf-posts-container-defaultTheme").removeClass("block-view");
		});
		
	};

	this.choseDisplayMode_ofListing = function(){
		var windowWidth = $j(window).width(),
			previousScreen=0;// possible values: 0=desktop; 1=mobile

		if ( windowWidth <= 768 ) {
			$j(".viewMode #blockView").trigger("click");
		}

		$j( window ).resize(function(){

			var windowWidth = $j( window ).width(),
				currentScreen = ( windowWidth > 768 ? 0 : 1 );

			if ( previousScreen != currentScreen ){

				previousScreen = currentScreen;

				if ( currentScreen == 1 ) {
					$j(".viewMode #blockView").trigger("click");
				}

			}
			
		});
		
	};

	this.blockPosts_hover = function(){
		$j(".post-list").ready(function(){
			
			clearInterval(scriptInterval);
			
			$j(".post-block, .post-list .post-featuredImage").each(function(){
				$j(this).hover(function(){
						$j(this).find(".post-overlay").addClass("active");
					},
					function(){
						$j(this).find(".post-overlay").removeClass("active");
					}
				)
			})
		
		});
		
	}
}


var pxDecodeEntities = (function() {

	var element = document.createElement('div');

	function decodeHTMLEntities (str) {

		if ( str && typeof str === 'string' ) {

			str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
			str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
			element.innerHTML = str;
			str = element.textContent;
			element.textContent = '';
		}

		return str;
	}

	return decodeHTMLEntities;

})();

var lscfExtraFunctionalities = (function(){
	
	var self = this,
		$j = jQuery,
		bounceAnimation;

	this.init = function(){
		
		$j(window).load(function(){
			self.shakeSettingsButton();
		});
			
		bounceAnimation = setInterval(function(){
			self.shakeSettingsButton();
		}, 7000);

	};

	this.shakeSettingsButton = function(){

		if ( $j('.lscf-open-customizer').hasClass('deactivate-animations') ) {
			clearInterval( bounceAnimation );
			return; 
		}

		if ( $j('.lscf-sidebar-live-customizer').hasClass('active') ) {
			return;
		}

		$j('.lscf-open-customizer').addClass('shake');

		setTimeout( function(){
			$j('.lscf-open-customizer').removeClass('shake');
		}, 1000 )

	};

	return this.init();

})();

;function lscfOrderFilterFields( shortcodeSettings ){
	
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
