<?php

namespace Roots\Sage\Extras;

use Roots\Sage\Setup;

/**
 * Add <body> classes
 */
function body_class($classes) {
  // Add page slug if it doesn't exist
  if (is_single() || is_page() && !is_front_page()) {
    if (!in_array(basename(get_permalink()), $classes)) {
      $classes[] = basename(get_permalink());
    }
  }

  // Add class if sidebar is active
  if (Setup\display_sidebar()) {
    $classes[] = 'sidebar-primary';
  }

  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\body_class');

/**
 * Clean up the_excerpt()
 */
function excerpt_more() {
  return ' &hellip; <a href="' . get_permalink() . '">' . __('Continued', 'sage') . '</a>';
}
add_filter('excerpt_more', __NAMESPACE__ . '\\excerpt_more');


function container($atts, $content) {
	ob_start();
	
	$atts = shortcode_atts(array(
		'class' => 'container',
		'id' => ''
	), $atts);
	
	?><section id="<?= $atts['id']; ?>" class="<?= $atts['class']; ?>"><?= do_shortcode($content); ?></section><?php
	
	return ob_get_clean();
}

add_shortcode('container', __NAMESPACE__ . '\container');


function row($atts, $content) {
	ob_start();
	
	$atts = shortcode_atts(array(
		'class' => 'row',
		'id' => ''
	), $atts);
	
	?><div class="<?= $atts['class']; ?>"><?= do_shortcode($content); ?></div><?php
	
	return ob_get_clean();
}

add_shortcode('row', __NAMESPACE__ . '\row');

function block_title($atts, $content) {
	ob_start();
	
	$atts = shortcode_atts(array(
		'class' => 'block-title',
		'id' => ''
	), $atts);
	
	?><div class="<?= $atts['class']; ?>"><?= do_shortcode($content); ?></div><?php
	
	return ob_get_clean();
}

add_shortcode('block_title', __NAMESPACE__ . '\block_title');

remove_filter( 'the_content', 'wpautop' );
remove_filter( 'the_excerpt', 'wpautop' );

add_filter('woocommerce_show_page_title', '__return_false');
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);


//show attributes after summary in product single view
add_action('woocommerce_single_product_summary', function() {
	//template for this is in storefront-child/woocommerce/single-product/product-attributes.php
	global $product;  
  echo "<h4 class=product-pinceszet >" . $product->get_attribute( 'pinceszet' ) . "</h4>";
  echo "<h4 class=product-borneve >" . $product->get_attribute( 'bor-neve' ) . "</h4>";
}, 0);


add_action( 'after_setup_theme', function() {
  remove_theme_support( 'wc-product-gallery-zoom' );
  add_theme_support( 'wc-product-gallery-lightbox' );
  add_theme_support( 'wc-product-gallery-slider' );
} ); 


add_action( 'woocommerce_order_details_after_order_table', 'woocommerce_order_again_button' );


// reorder tabs woocommerce products

add_filter( 'woocommerce_product_tabs', function ( $tabs ) {
	$tab_list = [
		'description'            => 20,
		'additional_information' => 10,
		'reviews'                => 60,
	];
	foreach ( $tab_list as $tab => $priority ) {
		if ( isset( $tabs[ $tab ] ) ) {
			$tabs[ $tab ]['priority'] = $priority;
		}
	}
	return $tabs;
}, 98 );



// introduce show child categories function

add_filter( 'terms_clauses', function ( $pieces, $taxonomies, $args )
{
    // Bail if we are not currently handling our specified taxonomy
    if ( !in_array( 'speight_plans', $taxonomies ) )
        return $pieces;

    // Check if our custom argument, 'wpse_parents' is set, if not, bail
    if (    !isset ( $args['wpse_parents'] )
         || !is_array( $args['wpse_parents'] )
    ) 
        return $pieces;

    // If  'wpse_parents' is set, make sure that 'parent' and 'child_of' is not set
    if (    $args['parent']
         || $args['child_of']
    )
        return $pieces;

    // Validate the array as an array of integers
    $parents = array_map( 'intval', $args['wpse_parents'] );

    // Loop through $parents and set the WHERE clause accordingly
    $where = [];
    foreach ( $parents as $parent ) {
        // Make sure $parent is not 0, if so, skip and continue
        if ( 0 === $parent )
            continue;

        $where[] = " tt.parent = '$parent'";
    }

    if ( !$where )
        return $pieces;

    $where_string = implode( ' OR ', $where );
    $pieces['where'] .= " AND ( $where_string ) ";

    return $pieces;
}, 10, 3 );