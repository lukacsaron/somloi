<?php
/**
 * Template Name: LS&CF - Blank template.
 *
 * @package 1.0| LS&CF.
 */

get_header();
?>

<div class="px-capf-container">
<?php
while ( have_posts() ) : the_post();
	?>

	<div class="px-lscf-heading">
	
	</div>

	<?php
	the_content();

endwhile;
?>
</div>

<?php
get_footer();

