
function lscf_cf_variations(){

	var self = this,
		$j = jQuery;
	
	this.lscf_methods = new lscf_methods();

	this.templateUrls = {
		"main_dropdown" : adminData.lscf_url + 'assets/js/templates/custom-fields/variation-fields-dropdown.html',
		"items_checkboxes_list" : adminData.lscf_url + 'assets/js/templates/custom-fields/variation-fields-items.html',
	}

	this.variation_fields = {
		"fields":null,
		"items":null,
		"id":"",
		"cf_index":0,
	};

	this.init = function( post_type ){

		var get_field_types = [ "px_select_box", "px_check_box", "px_icon_check_box", "px_radio_box" ];

		self.lscf_methods.get_lscf_custom_fields( post_type, get_field_types, function(data){

			if ( 1 == data.success ) {
				self.variation_fields.fields = data.data.data.fields;
			}

		});

		self.lscf_methods.get_lscf_custom_fields( post_type, get_field_types.concat( [ "px_text", "px_date" ] ), function( data ){
			if ( 1 == data.success ) {
				self.variation_fields.items = data.data.data.fields;
			}
		});

	};

	this.init_variation_field = function( parent, post_row_container, variation_id ) {

		self.variation_fields.id = variation_id;
		
		self.variation_fields.cf_index = parseInt( post_row_container.find( '.px_field-box.'+variation_id ).length ) - 1;

		$j.get( self.templateUrls.main_dropdown, function( template ) {

			var renderedTemplate = Mustache_2.render( $j( template ).filter('#lscf-variations-cf-template').html(), self.variation_fields );

			parent.find('.px_field-box').append( renderedTemplate );

			self.load_checkboxes_items( parent );

		});

	};

	this.load_checkboxes_items = function( parent ){
		
		parent.find( '.lscf-variation-fields' ).change( function(){
			var ID = $j(this).val();

			if ( 0 != ID ) {

				var  variation_type = $j(this).closest( '.px_field-box.px-cf-relationship' ).attr('data-type');
				var matches = variation_type.match( /(px_cf_relationship_.+?)_[0-9]+$/ );

				self.variation_fields.id = matches[1];

				var variation_items = {
					"fields":[],
					"id":self.variation_fields.id,
					"cf_index":self.variation_fields.cf_index,
				};


				for ( var vi_key in self.variation_fields.items ) {
					if ( self.variation_fields.items[ vi_key ].value != ID ) {
						variation_items.fields.push( self.variation_fields.items[ vi_key ] );
					}
				}

				$j.get( self.templateUrls.items_checkboxes_list, function( data_template ){
					
					var renderedTemplate = Mustache_2.render( $j( data_template ).filter('#lscf-variations-cf-template').html(), variation_items );


					parent.find('.lscf-variation-rel-items-container').html( renderedTemplate );

					variation_items = null;

				})

			} else {
				
				parent.find('.lscf-variation-rel-items-container').html('');
			}		

		});

	};

};


function PX_customFields(){

	var self = this,
		defaultTemplate = {
			'customField':{
				'fieldName_template':{
					'classNames':'dynamic-added-field',
					'fieldName':'px_',
					'html':'<input class="dynamic-added-field" type="text" name="field_name" value=""/>',
					'title':'Field name.'
				},
				
				"selectBox_template":{
					'className_ofOptionsContainer':'px_select-options-container',
					'label_addNewOption':'<span class="px_add_new_select_option px_addNewOption">+Add option</span>',
					'newOptionInput_ClassName':'px_optionValue',
					'htmlInput_addNewOption':'<input type="text" class="px_optionValue" name="px_add_new_select_option[]" value=""/>',
					'removeOption':'<span class="px_removeOption">Delete</span>',
					'removeOptionClassName':'px_removeOption'
				}
			},
			'removeButton':{
				'html':'<span class="remove-custom-field recent-added">Remove Field</span>'
			}
		},
		
		fieldTypesName = {
			"px_text":"Text",
			"px_date":"Date",
			"px_check_box":"Checkbox",
			"px_icon_check_box":"Checkbox /w icons",
			"px_radio_box":"Radio",
			"px_select_box":"Select",
			"px_cf_relationship":"Variation/Relationship"
		};

	this.wpMediaLibrary;

	this.cf_variations = new lscf_cf_variations();

	this.init = function(){

		self.postListAccordion();
		self.initCustomFieldTemplate();
		self.edit_option_custom_fields();
		self.update_option_custom_fields();
		self.wpMediaLibrary.openMediaLibrary("thumb");

		$j(".px-post-type-row").each(function(){

			var customPost_key = $j(this).data("key");

			self.initRemoveButton($j(this));
			self.initRemoveOptionButton_selectBox($j(this));
			self.cf_variations.load_checkboxes_items( $j(this) );

            $j(this).find(".px_field-box").each(function(){
                
                var _this = $j(this);
                
                if($j(this).find(".px_addNewOption")){
                    
                    $j(this).find(".px_addNewOption").click(function(){
                        
                        var optionValue = _this.find(".px_optionValue").val();
                        
                        if(optionValue){
                            var value = optionValue;
                            var inputName = 'px_options_'+_this.data("type");
                            var dataField = self.DataFields(inputName, '', value, "hidden");
                            var boxType = _this.data("type");
                            
                            if(boxType.match(/^px_icon_check_box(.*?)/)){
                                
                                var imageInputName = 'px_options_icon_'+_this.data("type");
                                var imageField = self.DataFields(imageInputName, 'img-link', '', 'hidden');
                                
                                var htmlList = 
                                    '<li><div class="choose_photo_box checkbox-icon-option"><span class="lscf-check-w-icon-remove remove_attachment hidden deleteImg"></span><div class="imgContainer"></div><span class="px_choose_img">Add Icon <strong>(40x40)</strong></span> &nbsp;'
                                        + imageField
                                        + optionValue + defaultTemplate.customField.selectBox_template.removeOption + dataField+
                                    '</div></li>';
                                
                                
                                _this.find(".px_select-options-container").append(htmlList);
                                _this.find(".px_optionValue").val("");
                                
                                // remove button for each option from list
                                self.initRemoveOptionButton_selectBox($j(this));
                                // Init Wp Media Upload
                                self.wpMediaLibrary.openMediaLibrary("thumb");
                                
                                return;
                            }
                            
                            var htmlList = "<li>" + optionValue + defaultTemplate.customField.selectBox_template.removeOption + dataField+"</li>";
                               
                            _this.find(".px_select-options-container").append(htmlList);
                            _this.find(".px_optionValue").val("");
                            
                            // remove button for each option from list
                            self.initRemoveOptionButton_selectBox($j(this));
                              
                        }
                    });          
                }
            })
        });

    };
    
    
    this.template_makeFieldBoxHtml = function(html, removeBtn, title, containerClassName, type, fieldType){

        var boxesLength = $j(".px-post-type-row.active").find('.px_field-box.'+type).length;
        
        var fieldslength = $j(".px-post-type-row.active").find(".recent-added").length;
        
        var evenClass = ( fieldslength % 2 == 0 ? 'even' : '');

		if ( type.match(/^px_icon_check_box/) ) {
			
			var fieldClassName = 'px-icon-check-box ' + evenClass+' '+type;

		} else if ( type.match(/^px_radio_box/) ) {
			
			var fieldClassName = 'px-radio-box ' + evenClass+' '+type;
			
		} else if ( type.match(/^px_check_box/) ) {
			
			var fieldClassName = 'px-check-box ' + evenClass+' '+type;

		} else if ( type.match(/^px_select_box/) ) {
			
			var fieldClassName = 'px-select-box ' + evenClass+' '+type;

		} else if ( type.match(/^px_cf_relationship/) ) {
			
			var fieldClassName = 'px-cf-relationship ' + evenClass+' '+type;

		} else {
			var fieldClassName = evenClass+' '+type;	
		}


        var fieldBox = '<div class="px_field-box dynamic-added-box '+fieldClassName+' " data-type="'+type+'_'+boxesLength+'" data-index="'+boxesLength+'">';
            
            fieldBox  += '<span class="px-error-msg"></span>';
            fieldBox  += '<div class="inline">';
            fieldBox  += "<label>"+title+"</label>"+removeBtn;
            
            if (typeof html === 'object') {
                
                fieldBox += html.field_name;
                fieldBox  += "<span>Type:</span><strong>"+fieldTypesName[fieldType]+"</strong>";
                fieldBox += "</div>";
                fieldBox += html.options;
                fieldBox += "</div>";
                
                return fieldBox;
            }
        
            fieldBox += html;
            fieldBox  += "<span>Type:</span><strong>"+fieldTypesName[fieldType]+"</strong>";
            fieldBox += "</div>";
            fieldBox  += "</div>";
            
        return fieldBox;   
        
    };
    
	this.edit_option_custom_fields = function(){

		$j('.lscf_edit_option').unbind("click");
		$j('.lscf_edit_option').each(function(){

			$j(this).bind( 'click', function(){
				var parent = $j(this).parent('li');
				parent.find('.lscf_option_value').addClass('active');
				parent.find('.lscf_option_text').addClass('inactive');
			});

		});
	};

	this.update_option_custom_fields = function(){
	
		$j('.lscf_update_option').unbind( 'click' );

		$j('.lscf_update_option').bind('click', function(){
			
			var optionType = $j(this).attr('data-type'),
				parentContainer = $j(this).closest('li');
	

			switch ( optionType ) {

				default:
				
					var optionValue = parentContainer.find('.lscf_option_value').find('input[type="text"]').val();
						parentContainer.find('.lscf_option_text').text( optionValue );
						parentContainer.find('.lscf_option_text').removeClass('inactive');
						parentContainer.find('.lscf_option_value').removeClass('active');

					break;

				case 'icon-checkbox':

					var optionValue = parentContainer.find('.lscf_option_value').find('input[type="text"]').val();
						parentContainer.find('.lscf_option_text').text( optionValue );
						parentContainer.find('.lscf_option_text').removeClass('inactive');
						parentContainer.find('.lscf_option_value').removeClass('active');

					break;
			}

		});

	};

    this.postListAccordion = function(){
        
        $j(".px-button").click(function (event) {
			event.preventDefault();
			event.stopPropagation();
			
			if ( self.checkForEmptyFields() ){
				$j("#lscf-custom-fields-form").submit();
			}
			return false;
			 
        });

		$j('.px_innerContainer').each(function(){
			
			$j(this).click(function(){

				$j(this).find('.styledSelect').removeClass('active');
				$j(this).find('.select').children('ul').hide();
				
			});

		})

        $j(".px-post-type-row").each(function(){

            $j(this).click(function (event) {
				
				$j(".styledSelect").removeClass("active");
				$j(".select").children('ul').hide();
				
                // event.preventDefault();
                event.stopPropagation();

                var _this = $j(this);
                var activeHeight = $j(this).find(".px_innerContainer").height()+57;
				var post_type = $j(this).attr('data-key');

				self.cf_variations.init( post_type );
                
                $j(this).find(".px_innerContainer").click(function ( e ) {

					if ( e.target.nodeName != 'INPUT' ){
						return false;
					} else {
						e.stopPropagation();
					}
		
                })

                if ($j(this).hasClass("active")) {
                    
                    $j(this).animate({
                        height: 30
                    }, 380, function () {
                        $j(this).removeClass("active ready");
                    });

					if ( event.target.nodeName != 'INPUT' ){
						return false;
					} else{
						event.stopPropagation();
					}

                };
                
				$j(this).find(".px_innerContainer").find("input").click(function(){
					return true;
				});

                $j(".px-post-type-row.active").animate({
                    height:30
                }, 380);
                $j(".px-post-type-row").removeClass("active ready");
                
                _this.addClass("active");
                
                _this.animate({
                    
                    height:activeHeight
                    
                }, 380, function(){
                    
                    $j(this).attr({style:"height:auto"});
                    
                });
                
            });
        });
    };
    
    this.initHeight = function(_this){
        
        var childContainer = _this.find(".custom_fields-container");
        if(childContainer.height()+childContainer.position().top>=_this.height()){
            _this.css({"height":"auto"});
        }
    };
	this.checkForEmptyFields = function(){

		$j('.px_field-box').removeClass('px-error');
		$j('.px-error-msg').hide();

		var activeContainer = document.getElementsByClassName('px-post-type-row active');
		
		if ( typeof activeContainer === 'undefined' || activeContainer == null || activeContainer.length <= 0 ) return false;

		var formElements = activeContainer[0].getElementsByTagName('input');
		
		if ( formElements ) {

			for ( index in formElements ) {

				if ( typeof formElements[index].name !== 'undefined' ) {

					var regexp = new RegExp('\-name(.*?)$');

					if ( formElements[index].name.match( regexp ) ){

						if ( formElements[index].value === '' ){
							
							var topOffset = $j(formElements[index]).offset().top-80;
							var parent = $j(formElements[index]).closest('.px_field-box');
							parent.addClass('px-error');
							parent.find('.px-error-msg').text('Field is empty');
							parent.find('.px-error-msg').show();

							$j('html, body').animate({
								scrollTop:topOffset
							}, 400);

							return false;
						}
					}

				}
				
			}
			
		}
		return true;

	}
    this.initCustomFieldTemplate = function(){
        
        var parent = $j(".px-post-type-row"),
            container = $j(".custom_fields-container");
                
        $j(".px_add-new-custom-field").each(function(index){
            
            var customPost_key = parent.eq(index).data("key");
            
            $j(this).click(function() {
                var customPost_key = parent.eq(index).data("key");
                var selectedTemplate = $j(".PX_add-field-type").eq(index).find("option:selected").val().toLowerCase();
            
                switch(selectedTemplate) {
                    
					case "date":
                        
                         self.customFieldTemplate_date(container.eq(index), "px_date_"+customPost_key, "px_date", parent.eq(index));
                         self.addDataFields_onInputBlur("px_field-box.px-date", "px-date px_date", "px_date_"+customPost_key, parent.eq(index));
                         
                    break;     
                    
                    case "text":
                    
                        self.customFieldTemplate_text(container.eq(index), "px_text_"+customPost_key, "px_text", parent.eq(index));
                        self.addDataFields_onInputBlur("px_field-box.px-text", "px-text px_text", "px_text_"+customPost_key, parent.eq(index));
                        
                    break;
                    
                    case "select-box":
                        
                        self.customFieldTemplate_select(container.eq(index), 'px_select_box_'+customPost_key, "px_select_box", parent.eq(index));    
                        self.initAddNewOption_toSelectBox(parent.eq(index), 'px_select_box_'+customPost_key);
                        self.addDataFields_onInputBlur("px_field-box.px-select-box", "px-option px_select_box", "px_select_box_"+customPost_key, parent.eq(index));
                        
                    break;
                       
                    case "radio":
                    
                        self.customFieldTemplate_select(container.eq(index), 'px_radio_box_'+customPost_key, "px_radio_box", parent.eq(index));    
                        self.initAddNewOption_toSelectBox(parent.eq(index), 'px_radio_box_'+customPost_key);
                        self.addDataFields_onInputBlur("px_field-box.px-radio-box", "px-option px_radio_box", "px_radio_box_"+customPost_key, parent.eq(index));
                        
                    break;
                    
                    case "checkbox":
                    
                        self.customFieldTemplate_select(container.eq(index), 'px_check_box_'+customPost_key, "px_check_box", parent.eq(index));    
                        self.initAddNewOption_toSelectBox(parent.eq(index), 'px_check_box_'+customPost_key);
                        self.addDataFields_onInputBlur("px_field-box.px-check-box", "px-option px_check_box", "px_check_box_"+customPost_key, parent.eq(index));
                        
                    break;
                    
                    case "checkbox-icon":
                    
                        self.customFieldTemplate_select( container.eq( index ), 'px_icon_check_box_' + customPost_key, "px_icon_check_box", parent.eq( index ) );
                        self.initAddNewOption_toSelectBox( parent.eq(index), 'px_icon_check_box_' + customPost_key );
                        
						self.addDataFields_onInputBlur("px_field-box.px-icon-check-box", "px-option px_icon_check_box", "px_icon_check_box_" + customPost_key, parent.eq( index ) );
                        
                    break;

					case "cf-relationship":
						
						self.customFieldTemplate_variation( container.eq( index ), "px_cf_relationship_" + customPost_key, "px_cf_relationship", parent.eq( index ), function( field_container ){
							self.cf_variations.init_variation_field( field_container, parent.eq( index ), "px_cf_relationship_" + customPost_key );
						});
						
                        self.addDataFields_onInputBlur( "px_field-box.px-cf-relationship", "px_cf_relationship_" + customPost_key + "-input px_cf_relationship", "px_cf_relationship_" + customPost_key, parent.eq( index ) );
						
						break;
                }

                 // reset parent height if needed
                 self.initHeight(parent.eq(index));
                 self.initRemoveButton(parent.eq(index));
            })
        })
        
    };
    
    this.initRemoveButton = function(parent){
        
        var removeButton = parent.find(".remove-custom-field"),
            fieldBox = parent.find(".px_field-box");
            
        removeButton.unbind("click");
        
        removeButton.each(function(index){
            
            $j(this).bind("click", function(){
                fieldBox.eq(index).remove();
                $j(this).remove();    
            })
            
        });
    };
    
    this.initRemoveOptionButton_selectBox = function(parent){
        
        parent.find("."+defaultTemplate.customField.selectBox_template.className_ofOptionsContainer).each(function(p_index){
            
            var options = parent.eq(p_index).find(".px_select-options-container");
            var removeOption = parent.eq(p_index).find(".px_removeOption");
            
            options.each(function (optIndex) {

                var removeOption = $j(this).find(".px_removeOption"),
                    _this = $j(this);
                
                removeOption.unbind("click");

                $j(this).find(".px_removeOption").each(function (index) {
                
                    $j(this).bind("click", function () {
                        
                        var listToRemove = _this.find("li").eq(index);
                        listToRemove.css({ "boder": "5px solid red" });
                        listToRemove.remove();
                        self.initRemoveOptionButton_selectBox(parent);
                    })

                })
            })
            
        })
    };
    
	// here we generate the option input's name of select/check/radio box
	this.customOptions_generateTheOptionInputName = function(parent, name){
		
		parent.each(function(index){
			var dataType = parent.data("key");
			$j(this).find(".px_select-options-container").find(".px-data-input").each(function(){
				$j(this).attr('name', name+dataType);
			})
		})
		
	};

	this.sanitize = function(string){
		var sanitized_string = string.toLowerCase().replace(/([^a-z0-9]+)/g, '_');

		return sanitized_string.replace(/[_]+$/, '');
	};

	this.DataFields = function(name, className, value, type, fieldType, fieldBox){


		if( typeof fieldType != 'undefined'){

			var elemLength = parseInt( fieldBox.data('index') );
			var inputName = name+"["+elemLength+"]";            
		}
		else{
			var inputName = name+"[]";
		}
		
		if(typeof type=== 'undefined'){
			type = "text";
		}
		return '<input type="'+type+'" name="'+inputName+'" class="hide_px_dynamic-field '+className+'" value="'+value+'"/>'
	};

	this.addDataFields_onInputBlur = function(parent, clname, name, parentPostTypeRow){
		
		$j("."+parent).each(function(pIndex){
			
			var className = clname.split(" ");
			var field_box = $j("."+parent).eq(pIndex);  
			var elements = $j("."+parent).eq(pIndex).find("."+className[0]);

			elements.unbind("blur");
			elements.each(function(index){
				$j(this).bind("blur", function(){
								
					var value = self.sanitize( $j(this).val() );
					
					if ( $j( "." + parent ).eq( pIndex ).find( "."+className[0]+"-data" ).length>0 ) {
						$j( "." + parent ).eq( pIndex ).find("."+className[0]+"-data").eq( index ).val( value );
					}
					else{
						var dataField = self.DataFields(name, className[0]+"-data", value, "hidden", className[1], field_box);
						$j("."+parent).eq(pIndex).append(dataField);
					}
				})
			})
		});
		
	};

	this.initAddNewOption_toSelectBox = function(parentRow, boxType){

		var button = parentRow.find(".px_addNewOption")
			appendContainer = parentRow.find("."+defaultTemplate.customField.selectBox_template.className_ofOptionsContainer);

		button.unbind( "click" );
		
		button.each( function( index ){
			
			$j( this ).click( function(){
				
				var parent = $j(this).closest(".px_field-box");
				var boxType = parent.data('type');
				var optionValue = parent.find("."+defaultTemplate.customField.selectBox_template.newOptionInput_ClassName).val();
				
				if ( optionValue ) {
					
					var value = optionValue;
					var inputName = 'px_options_'+parent.data("type");
					var dataField = self.DataFields( inputName, '', value, "hidden" );
					
					
					if ( boxType.match(/^px_icon_check_box(.*?)/)){
						
						var imageInputName = 'px_options_icon_'+parent.data("type");
						var imageField = self.DataFields(imageInputName, 'img-link', '', 'hidden');

						var htmlList = 
								'<li><div class="choose_photo_box checkbox-icon-option"><span class="lscf-check-w-icon-remove remove_attachment hidden deleteImg"></span><div class="imgContainer"></div><span class="px_choose_img">Add Icon <strong>(40x40)</strong></span> &nbsp;'
									+ imageField
									+ optionValue + defaultTemplate.customField.selectBox_template.removeOption + dataField+
								'</div></li>';

						appendContainer.eq( index ).append( htmlList );
						parent.find( "." + defaultTemplate.customField.selectBox_template.newOptionInput_ClassName ).val( "" );
						
						// remove button for each option from list
						self.initRemoveOptionButton_selectBox( parentRow );
						// Init Wp Media Upload
						self.wpMediaLibrary.openMediaLibrary( "thumb" );
						
						self.edit_option_custom_fields();
						self.update_option_custom_fields();

						return;
					}
				
					var htmlList = "<li>" + 
						'<span class="lscf_option_text">'+ optionValue + '</span>' +
						defaultTemplate.customField.selectBox_template.removeOption + '<span class="lscf_edit_option">Edit</span> <span class="lscf_option_value">'+ dataField + '<span class="lscf_update_option" data-type="default"></span> </span>' + "</li>";    
					
					
				appendContainer.eq( index ).append( htmlList );

				parent.find("."+defaultTemplate.customField.selectBox_template.newOptionInput_ClassName).val("");
					
				// remove button for each option from list
				self.initRemoveOptionButton_selectBox(parentRow);

				self.edit_option_custom_fields();
				self.update_option_custom_fields();
					
				}
			})
		});
	}

	this.customFieldTemplate_date = function(container, type, fieldType, parentPostType){
		
		var ElemLength = parentPostType.find("."+fieldType).length;
		
		var template = defaultTemplate;
		
		template.customField.fieldName_template.html = '<input type="text" name="'+type+'-name['+ElemLength+']" class="'+template.customField.fieldName_template.classNames+' px-date '+fieldType+'">';
		
		var fieldBox = self.template_makeFieldBoxHtml(template.customField.fieldName_template.html, template.removeButton.html, template.customField.fieldName_template.title, "px-date", "px-date", fieldType);
		
		container.prepend(fieldBox);  
					
	};

    this.customFieldTemplate_text = function( container, type, fieldType, parentPostType, callback ){
        
        var ElemLength = parentPostType.find( "."+fieldType ).length;
        
        var template = defaultTemplate;
        
        template.customField.fieldName_template.html = '<input type="text" name="'+type+'-name['+ElemLength+']" class="'+template.customField.classNames+' px-text '+fieldType+'">'
        
        var fieldBox = self.template_makeFieldBoxHtml( template.customField.fieldName_template.html, template.removeButton.html, template.customField.fieldName_template.title, "px-text", "px-text", fieldType );

		if ( 'undefined'!== typeof callback && null !== callback ) {

			fieldBox = $j('<div />').html( fieldBox );
			container.prepend( fieldBox ); 

			callback( fieldBox );

		} else {
			container.prepend( fieldBox );
		}
    };
    
	this.customFieldTemplate_variation = function( container, type, fieldType, parentPostType, callback ) {

		var ElemLength = parentPostType.find( "." + fieldType ).length;
        
        var template = defaultTemplate;
        
        template.customField.fieldName_template.html = '<input type="text" name="'+type+'-name['+ElemLength+']" class=" px-cf-relationship-name ' + type + '-input ' + fieldType + '">';
        
        var fieldBox = self.template_makeFieldBoxHtml( template.customField.fieldName_template.html, template.removeButton.html, template.customField.fieldName_template.title, "px-cf-relationship", type, fieldType );

		if ( 'undefined'!== typeof callback && null !== callback ) {

			fieldBox = $j('<div />').html( fieldBox );
			container.prepend( fieldBox ); 

			callback( fieldBox );

		} else {
			container.prepend( fieldBox );
		}

	}

    // generate the template for "select" html field type
    this.customFieldTemplate_select = function( container, type, fieldType, parentPostType ) {
        
        var ElemLength = parentPostType.find( "." + fieldType ).length;
        
        var template = defaultTemplate,
            optionsContainer = '',
            button_removeOption = '',
            button_addNewOption = '',
            selectBoxTemplate = {};
        
        template.customField.fieldName_template.html = '<input type="text" name="'+type+'-name['+ElemLength+']" class="px-option '+fieldType+'">'
        
        selectBoxTemplate.field_name = template.customField.fieldName_template.html;
        
        optionsContainer = '<ul class="'+template.customField.selectBox_template.className_ofOptionsContainer+'"></ul>';
        
        // add container of options that will be dynamically added/remove by user
        selectBoxTemplate.options = optionsContainer;
        
        //add input text for options. Here user will add a new option to select box
        selectBoxTemplate.options += '<div class="px-option-add-new">';
        selectBoxTemplate.options += template.customField.selectBox_template.htmlInput_addNewOption;
        selectBoxTemplate.options += template.customField.selectBox_template.label_addNewOption;
        selectBoxTemplate.options += '</div>';
        
        var fieldBox = self.template_makeFieldBoxHtml(selectBoxTemplate, template.removeButton.html, template.customField.fieldName_template.title, defaultTemplate.customField.selectBox_template.className_ofOptionsContainer, type, fieldType);
        
        container.prepend(fieldBox);
           
    }
}