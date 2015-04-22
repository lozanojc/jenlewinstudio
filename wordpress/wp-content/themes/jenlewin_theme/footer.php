<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package _mbbasetheme
 */
?>

	</div><!-- #content -->

	<footer id="colophon" class="site-footer" role="contentinfo">
	
<div style="clear:both; height:1px;"></div>
	<div id="footer" style="line-height:1.3em;">
		<div style = "float:right; width:270px;">
			<div class ="circle-logo">
				<img src="<?php bloginfo('template_directory'); ?>/assets/images/circle-logo.png" style="float:right; padding-top:5px;">
			<div>
			<div class="copyright">
				<div class="copyright-line">
					<a href="<?php get_home_url();?>contact/">Contact Jen Lewin Studio</a>
				</div>
				
				<div class="copyright-line">
					<p>&copy;<?php echo date('Y'); ?> Jen Lewin Studio</p>
				</div>

				<div class="copyright-line social">
					<a class="facebook" href="https://www.facebook.com/JenLewinStudio">.</a>
					<a class="twitter" href="https://twitter.com/jenlewin">.</a>
					<a class="tumblr" href="http://jenlewinstudio.tumblr.com/">.</a>
					<a class="instagram" href="http://instagram.com/jenlewinstudio/">.</a>
					<a class="vimeo" href="https://vimeo.com/jenlewinstudio">.</a>
				</div>
			</div>
		</div>	
	</div>
</div>
</div>
</div>
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
