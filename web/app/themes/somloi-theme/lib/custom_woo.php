<?php
// override woo columns settings with added bootstrap classes to loop elements
add_action( 'woocommerce_shortcode_before_products_loop', 'roka_before_products_shortcode_loop', 1, 10 );
add_action( 'woocommerce_shortcode_after_products_loop', 'roka_after_products_shortcode_loop', 0, 10 );

function roka_before_products_shortcode_loop( $atts ) {
    $GLOBALS[ 'roka_woocommerce_loop_template' ] =
        ( isset( $atts[ 'columns' ] ) ? $atts[ 'columns' ] : '' );
}

function roka_after_products_shortcode_loop( $atts ) {
    $GLOBALS[ 'roka_woocommerce_loop_template' ] = '';
}
?>