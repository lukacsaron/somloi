<?php
/**
 * Template Name: Test Template
 */
?>

<?php while (have_posts()) : the_post(); ?>

   
  <?php get_template_part('templates/content', 'page'); ?>

<?php
$args = array( 'taxonomy' => 'product_cat' );
$terms = get_terms('product_cat', $args);
if (count($terms) > 0) { ?>
  <ul>
    <?php  foreach ($terms as $term) { ?>
    <li>
      <img src="img/icons/placholder.jpg" alt="">
      <h2><?php echo $term->name; ?></h2>
    </li>
    <?php }; ?>
  </ul>
<?php }; ?>

<?php endwhile; ?>
