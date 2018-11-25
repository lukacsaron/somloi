<header class="banner">
  <div class="container">
    <div class="row active-header">
    <div class="col-md-3 header-left">
      <span class="welcome-msg">Üdvözlünk a Somlói Borok Boltjában!</span>
      <br>
      <br>
      <div class="sales-btns">
      <a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" title="<?php _e('My Account',''); ?>"><?php _e('Fiókom',''); ?></a>
      </div>
    </div>
    <div class="col-md-6 header-center">
    <a class="brand" href="<?= esc_url(home_url('/')); ?>"><!--<?php bloginfo('name'); ?>-->
      <img src="<?= get_template_directory_uri(); ?>/dist/images/govolcanic-logo.png"></a>
    </div>
    <div class="col-md-3 header-right">
      <span class="sales-msg">Expressz kiszállítás!</span>
      <br>
      <br>
      <br>
      <div class="sales-btns">
        <a class="cart" href="<?php echo get_permalink( wc_get_page_id( 'cart' ) ); ?>">Kosár</a>
        <?php global $woocommerce; ?>
        <span class="value"><?php echo $woocommerce->cart->get_cart_total(); ?></span>
      </div>
    </div>
    </div>
    <nav class="nav-primary">
      <?php
      if (has_nav_menu('primary_navigation')) :
        wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav']);
      endif;
      ?>
      <div id="submenu-borok">
        <div class="container">
          <div class="row">
            <div class="col-md-4">
              <ul>
                <h2>Régió</h2>
                <?php
                $orderby = 'name';
$order = 'asc';
$hide_empty = false ;
$cat_args = array(
    'orderby'    => $orderby,
    'order'      => $order,
    'hide_empty' => $hide_empty,
    'parent' => 0
);
 
$product_categories = get_terms( 'product_cat', $cat_args );
 
if( !empty($product_categories) ){
    echo '
 
<ul>';
    foreach ($product_categories as $key => $category) {
        echo '
 
<li>';
        echo '<a href="'.get_term_link($category).'" >';
        echo $category->name;
        echo '</a>';
        echo '</li>';
    }
    echo '</ul>
 
 
';
}
                ?>
              </ul>
            </div>
            <div class="col-md-8"><ul>
                <h2>Borászatok</h2>
                <?php
                $orderby = 'name';
$order = 'asc';
$hide_empty = false ;
$cat_args = array(
    'orderby'    => $orderby,
    'order'      => $order,
    'hide_empty' => $hide_empty,
    'wpse_parents' => [38, 17]
);
 
$product_categories = get_terms( 'product_cat', $cat_args );
 
if( !empty($product_categories) ){
    echo '
 
<ul>';
    foreach ($product_categories as $key => $category) {
        echo '
 
<li>';
        echo '<a href="'.get_term_link($category).'" >';
        echo $category->name;
        echo '</a>';
        echo '</li>';
    }
    echo '</ul>
 
 
';
}
                ?>
              </ul></div>          
          </div>
        </div>
      </div>
    </nav>
  </div>
</header>