<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package _mbbasetheme
 */
?>

<div class= "indv-project columns columns large-4 medium-6 small-12">
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		

		<div class ="gallery-wrapper">
			<?php the_field(gallery_shortcode)?>
		</div>

		<div class="entry-content columns large-12 medium-12 small-12">
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
			<?php
				wp_link_pages( array(
					'before' => '<div class="page-links">' . __( 'Pages:', '_mbbasetheme' ),
					'after'  => '</div>',
				) );
			?>
		</div><!-- .entry-content -->
		<footer class="entry-footer">
			<?php edit_post_link( __( 'Edit', '_mbbasetheme' ), '<span class="edit-link">', '</span>' ); ?>
		</footer><!-- .entry-footer -->
	</article><!-- #post-## -->
</div>