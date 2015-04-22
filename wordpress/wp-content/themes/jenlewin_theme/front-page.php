<?php
/**
 * The template for displaying the front page.
 *
 * This is the template that displays on the front page only.
 *
 * @package _mbbasetheme
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<?php the_field(gallery_shortcode)?>
			<?php the_content(); ?>
		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer(); ?>
