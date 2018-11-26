var $j = jQuery;

function mediaLibrary(){
    var self = this,
        mediaLibrary_frame = [], 
		galleryLibrary = [],
        uploadBox;// jquery object of multiple elements 

    this.openMediaLibrary = function(size){
        
        $j(".choose_photo_box").each(function(index){
            var boxContainer = $j(this),
                imgContainer = boxContainer.find( ".imgContainer" ),
                imgLink 	 = boxContainer.find( ".px_choose_img"),
                imgInput 	 = boxContainer.find( ".img-link" ),
                delImgLink 	 = boxContainer.find( ".remove_attachment" ),
				thumbUrl	 = boxContainer.find( '.px-img-upload-thumb-url' );
            
			self.removeSelectedImage(boxContainer);

            imgLink.unbind("click");                    
            imgLink.bind("click", function(){
                
                /*if(mediaLibrary_frame[index]){
                    mediaLibrary_frame[index].open();
                    return;
                }*/
                
                mediaLibrary_frame[index] = wp.media({
                  title: 'Select or Upload Media Of Your Chosen Persuasion',
                  button: {
                    text: 'Use this media'
                  },
                  multiple: false  // Set to true to allow multiple files to be selected
                });
                
                mediaLibrary_frame[index].open();
                mediaLibrary_frame[index].on("select", function(){

                    var attachment =  mediaLibrary_frame[index].state().get('selection').first().toJSON();
                    
                    // Send the attachment URL to our custom image input field.
                    /*console.log(containerID);
                    console.log(index);*/
                    if(typeof size !== 'undefined' && size =='thumb' ){
                        var url = ( typeof attachment.sizes.thumbnail !== 'undefined' ? attachment.sizes.thumbnail.url : attachment.sizes.full.url);
                        imgContainer.html( '<img src="'+url+'" alt="" style="max-width:100%;"/>' );
                    }
                    else{
                        imgContainer.html( '<img src="'+attachment.url+'" alt="" style="max-width:100%;"/>' );    
                    }

                    // Send the attachment id to our hidden input
                    imgInput.val( attachment.id );

					thumbUrl.val(attachment.url);

                    // Hide the add image link
                    imgLink.addClass( 'hidden' );

                    // Unhide the remove image link
                    delImgLink.removeClass( 'hidden' );
                })    
            })
        })
    };
    
    this.removeSelectedImage = function(boxContainer){
        
        $j(".remove_attachment").each(function(index){
           
           $j(this).bind("click", function(){
                mediaLibrary_frame.splice(index, 1);     
                var temp = [];
        
                for(var prop in mediaLibrary_frame){
                    temp.push(mediaLibrary_frame[prop]);
                }
                
                mediaLibrary_frame = temp;

				$j( this ).addClass("hidden");
				$j( this ).closest('.choose_photo_box ').find( '.imgContainer' ).empty();
				$j( this ).closest('.choose_photo_box ').find( '.px_choose_img' ).removeClass("hidden");

           });                  
            
        });

        // boxContainer.find(".deleteImg").click(function(){
        //     $j(this).addClass("hidden");
        //     boxContainer.find(".imgContainer").empty();
        //     boxContainer.find(".px_choose_img").removeClass("hidden");
        // });
    }

	this.multipleUploads = function(size){
		
		$j(".px-multiple-thumbs-container").each(function(index){
            var boxContainer = $j(this),
                imgLink 	 = $j(".px_gallery_choose_img").eq(index),
                imgInput 	 = $j(".px-gallery-ids").eq(index),
                delImgLink 	 = $j(".deleteImg").eq(index),
				thumbUrl	 = $j('.px-gallery-thumbs').eq(index);
            
            imgLink.unbind("click");                    
            imgLink.bind("click", function(){
                
                /*if(mediaLibrary_frame[index]){
                    mediaLibrary_frame[index].open();
                    return;
                }*/
                
                galleryLibrary[index] = wp.media({
                  title: 'Select or Upload Media Of Your Chosen Persuasion',
                  button: {
                    text: 'Use this media'
                  },
                  multiple: true  // Set to true to allow multiple files to be selected
                });
    
                galleryLibrary[index].open();
                galleryLibrary[index].on("select", function(){

                    var attachments =  galleryLibrary[index].state().get('selection').toJSON();
					
                    // Send the attachment URL to our custom image input field.
					var activeImageIDs = ( '' != imgInput.val() ? imgInput.val().split(';') : [] );
					var activeThumbs   = ( '' != thumbUrl.val() ? thumbUrl.val().split(';') : [] );

					attachments.forEach(function(attachment){

						activeImageIDs.push( attachment.id );

						if ( typeof size !== 'undefined' && size =='thumb' ) {
							
							var url = ( typeof attachment.sizes.thumbnail !== 'undefined' ? attachment.sizes.thumbnail.url : attachment.sizes.full.url);
							
							boxContainer.append( '<div class="pxg-img-container"><span class="remove_attachment"></span><span class="pxg-preview-thumb"><img src="'+url+'" alt="" style="max-width:100%;"/></span></div>' );

							activeThumbs.push( url );

						}
						else{

							boxContainer.append( '<div class="pxg-img-container"><span class="pxg-remove-item"></span><span class="pxg-preview-thumb"><img src="'+attachment.url+'" alt="" style="max-width:100%;"/></span></div>' );
							activeThumbs.push( attachment.url );

						}

					});

                    // Send the attachment id to our hidden input
                    imgInput.val( activeImageIDs.join(';') );

					thumbUrl.val( activeThumbs.join(';') );

					self.resetDataIndex( $j('.pxg-remove-item') );

                    self.remove_gallery_item();
                })    
            })
        });	
	}

	this.remove_gallery_item = function(){

		$j('.pxg-remove-item').unbind('click');

		$j('.pxg-remove-item').each(function(){
			
			$j(this).bind( 'click', function(){
				
				var parentContainer = $j(this).closest('.px-multiple-thumbs-container'),
					index = parseInt( $j(this).attr('data-index') );

				var activeImageIDs  = parentContainer.find('.px-gallery-ids').val().split(';'),
					activeThumbs    = parentContainer.find('.px-gallery-thumbs').val().split(';');

				delete activeImageIDs[index];
				delete activeThumbs[index];

				activeImageIDs = self.resetArraykeys( activeImageIDs );
				activeThumbs = self.resetArraykeys( activeThumbs );

				parentContainer.find('.px-gallery-ids').val( activeImageIDs.join(';') );
				parentContainer.find('.px-gallery-thumbs').val( activeThumbs.join(';') );

				galleryLibrary.splice(index, 1);     
                
				var temp = [],
					parentContainer = $j(this).closest('.pxg-img-container').remove();
        
                for ( var prop in galleryLibrary ){
                    temp.push( galleryLibrary[prop] );
                }
                
                galleryLibrary = temp;

				self.resetDataIndex( $j('.pxg-remove-item') );

			});
		})
	};

	this.resetDataIndex = function( elem ){
		
		elem.each(function(index){
			$j(this).attr({'data-index':index});
		})
	}

	this.resetArraykeys = function( arr ) {

		var newArr = [];

		for ( key in arr ) {
			newArr.push( arr[key] );
		}

		return newArr;

	};
    
}