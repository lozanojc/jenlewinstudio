<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package _mbbasetheme
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	

	<div class ="gallery-wrapper">
		<?php the_field(gallery_shortcode)?>
	</div>
	
	<header class="entry-header space-top">

		<div class="post-title large-4 medium-4 small-12">
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		</div>
		
		<div class="project-nav large-8 medium-8 small-12">
			<?php _mbbasetheme_post_nav(); ?>
		</div>

	</header><!-- .entry-header -->

		<div class="entry-content columns large-9 medium-9 small-12">
		<?php the_content(); ?>
		<?php
			wp_link_pages( array(
				'before' => '<div class="page-links">' . __( 'Pages:', '_mbbasetheme' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .entry-content -->

	<div class="sidebar-content columns large-3 medium-3 small 12">
		<?php the_field(sidebar_info)?>
	</div>


	<footer class="entry-footer">
		<?php edit_post_link( __( 'Edit', '_mbbasetheme' ), '<span class="edit-link">', '</span>' ); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-## -->
