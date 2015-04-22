<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package _mbbasetheme
 */
?>

<div class= "indv-project columns large-4 medium-6 small-12">

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<a href= "<?php the_permalink(); ?>">
			<div class="entry-content">
				<figure class= "archive-thumbnail">
				<?php if ( has_post_thumbnail() ) {the_post_thumbnail('large'); } ?>
				</figure> 
				
					<?php the_title( '<h3 class="entry-title">', '</h3>' ); ?>
			</div><!-- .entry-content -->
		</a>

		<footer class="entry-footer">
		</footer><!-- .entry-footer -->

	</article><!-- #post-## -->
</div>