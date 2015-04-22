<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package _mbbasetheme
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	
	
	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title space-top">', '</h1>' ); ?>
	</header><!-- .entry-header -->

	<div class="entry-content medium-12 small-12">
		<?php the_content(); ?>
	</div><!-- .entry-content -->
	<footer class="entry-footer">
		<?php edit_post_link( __( 'Edit', '_mbbasetheme' ), '<span class="edit-link">', '</span>' ); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-## -->
