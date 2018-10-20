<header class="banner">
  <div class="container">
    <div class="row active-header">
    <div class="col-md-3 header-left">
      <span class="welcome-msg">Üdvözlünk a Somlói Borok Boltjában!</span>
      <br>
      <br>
      <a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" title="<?php _e('My Account',''); ?>"><?php _e('Fiókom',''); ?></a>
    </div>
    <div class="col-md-6 header-center">
    <a class="brand" href="<?= esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
    </div>
    <div class="col-md-3 header-right">
      <span class="sales-msg">Expressz kiszállítás!</span>
      <br>
      <br>
      <a href="<?php echo get_permalink( wc_get_page_id( 'cart' ) ); ?>">Kosaram</a>
    </div>
    </div>
    <nav class="nav-primary">
      <?php
      if (has_nav_menu('primary_navigation')) :
        wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav']);
      endif;
      ?>
    </nav>
  </div>
</header>
