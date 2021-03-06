/* ========================================================================
 * DOM-based Routing
 * Based on http://goo.gl/EUTi53 by Paul Irish
 *
 * Only fires on body classes that match. If a body class contains a dash,
 * replace the dash with an underscore when adding it to the object below.
 *
 * .noConflict()
 * The routing is enclosed within an anonymous function so that you can
 * always reference jQuery with $, even when in .noConflict() mode.
 * ======================================================================== */

(function($) {

  // Use this variable to set up the common and page specific functions. If you
  // rename this variable, you will also need to rename the namespace below.
  var Sage = {
    // All pages
    'common': {
      init: function() {
        // JavaScript to be fired on all pages
        
        jQuery('#menu-main-menu .menu-shop, #submenu-borok').hover(
            function(){jQuery('#submenu-borok').toggleClass('hover');}
          ); 
        

      },
      finalize: function() {
        // JavaScript to be fired on all pages, after page specific JS is fired
      }
    },
    // Home page
    'home': {
      init: function() {
        // JavaScript to be fired on the home page
      },
      finalize: function() {
        // JavaScript to be fired on the home page, after the init JS
        $('.home-slider').slick({
          autoplaySpeed : 1000,
          arrows        : false,
          dots          : false
        });
        
        $('.multiple .products').slick({
          infinite: true,
          slidesToShow: 4,
          slidesToScroll: 3
        });
        
        // media query change
          function WidthChange(mq) {
          if (mq.matches) {
           jQuery('.multiple .products').slick('unslick');
          } else {
          $('.multiple .products').slick({
          infinite: true,
          slidesToShow: 4,
          slidesToScroll: 3
        });
          }

          }
       // media query event handler
          if (matchMedia) {
          const mq = window.matchMedia("(max-width: 720px)");
          mq.addListener(WidthChange);
            
          }

          

    
      }
    },
    // About us page, note the change from about-us to about_us.
    'about_us': {
      init: function() {
        // JavaScript to be fired on the about us page
      }
    }
  };

  // The routing fires all common scripts, followed by the page specific scripts.
  // Add additional events for more control over timing e.g. a finalize event
  var UTIL = {
    fire: function(func, funcname, args) {
      var fire;
      var namespace = Sage;
      funcname = (funcname === undefined) ? 'init' : funcname;
      fire = func !== '';
      fire = fire && namespace[func];
      fire = fire && typeof namespace[func][funcname] === 'function';

      if (fire) {
        namespace[func][funcname](args);
      }
    },
    loadEvents: function() {
      // Fire common init JS
      UTIL.fire('common');

      // Fire page-specific init JS, and then finalize JS
      $.each(document.body.className.replace(/-/g, '_').split(/\s+/), function(i, classnm) {
        UTIL.fire(classnm);
        UTIL.fire(classnm, 'finalize');
      });

      // Fire common finalize JS
      UTIL.fire('common', 'finalize');
    }
  };

  // Load Events
  $(document).ready(UTIL.loadEvents);

})(jQuery); // Fully reference jQuery after this point.

var flag = true;

function addQuantity() {
  console.log('addQuantity triggered');
    jQuery('.lscf-template3-add-to-cart').click(function(){
          var $i = jQuery(this).attr("data-product_id");
          console.log('product id var populated');
          var $input_q = jQuery("#quantity-"+$i).val();
          console.log('input quantity var populated');
          jQuery(this).data("data-quantity", $input_q);
          this.href += $input_q;
          console.log('href changed');
          
        });
}

// function addQuantity2() {
  //  jQuery('input').click(function(){
  //        var $i = jQuery(this).attr("data-product_id");
  //        console.log('product id var populated');
  //        var $input_q = jQuery(this).val();
  //        console.log('input quantity var populated');
   //       jQuery(this).data("data-quantity", $input_q);
    //      this.href += $input_q;
    //      console.log('href changed');
    //    });
// }

jQuery(document).ajaxSuccess(function() {
    console.log('ajax Success');
    if(flag){ 
    setTimeout(addQuantity, 2000);
    }
});

jQuery(document).ajaxComplete(function( event, xhr, settings ) {
    console.log('ajax Complete');
    console.log('Triggered ajaxComplete handler. The result is' +
      xhr.responseText );
  
});

function consolewrite() {
  console.log("clicked on input box");
}

jQuery('input').click(function(){
  consolewrite();
});