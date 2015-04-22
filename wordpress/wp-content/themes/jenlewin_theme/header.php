<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package _mbbasetheme
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<link rel="icon" type="image/png" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/favicon-32x32.png" sizes="32x32" />
	<link rel="icon" type="image/png" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/favicon-16x16.png" sizes="16x16" />

	<link rel="apple-touch-icon" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/apple-touch-icon.png">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="hfeed site">
	<!--[if lt IE 9]>
	    <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
	<![endif]-->

	<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', '_mbbasetheme' ); ?></a>
	<span><?php wp_nav_menu( array( 'theme_location' => 'primary' ) ); ?></span>

	<header id="masthead" class="site-header" role="banner">
		<div class="site-branding">
			<h1 class="site-title"><?php bloginfo( 'name' ); ?></h1>
			<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2> 
			<a class="transition" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><img class="logo" src="http://jenlewinstudio.com/wp-content/uploads/2015/01/jenlewin_top.png" /></a>
			<div class = "project-trigger">
				<a class="project-navigation" href="#"></a>
			</div>
		</div>

		<nav id="site-navigation" class="main-navigation closed" role="navigation">

			<div class="column large-4 medium-4 small-4 first-column">
			
				<?php
				$args = array(
					'post_type' => 'art',
					'orderby' => 'time',
					'order' => 'ASC'
				);
				$loop2 = new WP_Query( $args );

				$obj = get_post_type_object('art');
				?> 

				<h3 class="main-menu-category">
					<a class="transition" href="<?php echo get_post_type_archive_link( 'art' ); ?>">
							<?php echo $obj->labels->name; ?>
					</a>
				</h3>
				<?php
				
				?><ul><?php
				while ( $loop2->have_posts()) : $loop2->the_post(); ?>
					<li><a class="transition nav-links" href="<?php echo get_permalink();?>"><?php the_title(); ?></a></li>
				<?php endwhile; wp_reset_query(); ?>
				</ul>
			</div>

			<div class="column large-24 medium-24 small-24">
				<ul>
				<?php
				$args = array(
					'post_type' => 'objects',
					'orderby' => 'time',
					'order' => 'ASC'
				);
				$loop1 = new WP_Query( $args );

				$obj = get_post_type_object('objects');
				?> 

				<h3 class="main-menu-category">
					<a class="transition" href="<?php echo get_post_type_archive_link( 'objects' ); ?>">
							<?php echo $obj->labels->name; ?>
					</a>
				</h3>

				<?php
				
				$i = 1; 

				while ( $loop1->have_posts() ) : $loop1->the_post(); ?>
					<li><a class="transition nav-links" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
				<?php endwhile; wp_reset_query(); ?>
				</ul>
			</div>

			<div class="column large-24 medium-24 small-24">
				
				<?php
				$args = array(
					'post_type' => 'spaces',
					'orderby' => 'time',
					'order' => 'ASC'
				);
				$loop1 = new WP_Query( $args );

				$obj = get_post_type_object('spaces');
				?> 

				<h3 class="main-menu-category">
					<a class="transition" href="<?php echo get_post_type_archive_link( 'spaces' ); ?>">
							<?php echo $obj->labels->name; ?>
					</a>
				</h3>

				<?php
				
				
				?><ul><?php
				while ( $loop1->have_posts()) : $loop1->the_post(); ?>
					<li><a class="transition nav-links" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
				<?php endwhile; wp_reset_query(); ?>
				</ul>
			</div>
			<div class="spacing-menu"></div>
			<div class="column large-2 medium-2 small-2 last-menu-column">
				<ul>
					<?php
					$args = array(
						'post_type' => 'page',
						'orderby' => 'time',
						'order' => 'ASC'
					);
					$loop1 = new WP_Query( $args );
					

					while ( $loop1->have_posts()) : $loop1->the_post(); ?>
						<li><a class="transition nav-links" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
					<?php endwhile; wp_reset_query(); ?>

					<li class="header-search">
						<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
							<div>
								<input type="text" value="" name="s">
								<input type="submit" value="">
							</div>
						</form>
					</li>
					<li>
						<a class="transition nav-links" href="<?php echo esc_url( home_url( '/' ) ); ?>/contact">Sign up for News Updates</a>

				</ul>
			</div>

			
			
		</nav><!-- #site-navigation -->
	</header><!-- #masthead -->

	<div id="content" class="site-content">
