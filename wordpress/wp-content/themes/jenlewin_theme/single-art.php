<?php
/**
 * The template for displaying all single posts.
 *
 * @package _mbbasetheme
 */

get_header(); ?>
	<div id="primary" class="content-area">
		<h1 class="category-header">
			<a href = "<?php echo get_post_type_archive_link('art'); ?>">[<?php echo get_post_type( get_the_ID() ); ?>]</a>
		</h1>
		<main id="main" class="site-main" role="main">

		<?php while ( have_posts() ) : the_post(); ?>

			<?php get_template_part( 'content', 'art' ); ?>

		<?php endwhile; // end of the loop. ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer(); ?>
