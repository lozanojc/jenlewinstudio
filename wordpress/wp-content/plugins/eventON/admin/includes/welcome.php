<?php
/**
 * Welcome Page Class
 *
 * Shows a feature overview for the new version (major).
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	EventON/Admin
 * @version     1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class EVO_Welcome_Page {


	public function __construct() {
		
		add_action( 'admin_menu', array( $this, 'admin_menus') );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'welcome'    ) );
	}

	/**
	 * Add admin menus/screens
	 */
	public function admin_menus() {

		$welcome_page_title = __( 'Welcome to EventON', 'eventon' );

		// About
		$about = add_dashboard_page( $welcome_page_title, $welcome_page_title, 'manage_options', 'evo-about', array( $this, 'about_screen' ) );

		
		add_action( 'admin_print_styles-'. $about, array( $this, 'admin_css' ) );
	}

	/**
	 * admin_css function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_css() {
		wp_enqueue_style( 'eventon-activation', AJDE_EVCAL_URL.'/assets/css/activation.css' );
	}
	
	/**
	 * Add styles just for this page, and remove dashboard page links.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_head() {
		global $eventon;

		remove_submenu_page( 'index.php', 'evo-about' );

		
		?>
		<style type="text/css">
			
		</style>
		<?php
	}
	
	/**
	 * Into text/links shown on all about pages.
	 *
	 * @access private
	 * @return void
	 */
	private function intro() {
		global $eventon;
		

		// Drop minor version if 0
		$major_version = substr( $eventon->version, 0, 3 );
		//$major_version =  $eventon->version;
		?>
		<h1><?php printf( __( 'Welcome to EventON %s', 'eventon' ), $major_version ); ?></h1>

		<div class="about-text eventon-about-text">
			<?php
				$message = __( 'Thanks for purchasing!', 'eventon' );

				printf( __( '%s EventON %s is better than ever before with prettier UI and better functionality.', 'eventon' ), $message, $major_version );
			?>
		</div>

		<div class="evo-badge"><img src='<?php echo AJDE_EVCAL_URL?>/assets/images/welcome/badge.jpg'/></div>

		<p class="eventon-actions">
			<a href="<?php echo admin_url('admin.php?page=eventon'); ?>" class="button button-primary"><?php _e( 'Settings', 'eventon' ); ?></a>
			<a class="docs button button-primary" href="http://www.myeventon.com/documentation/"><?php _e( 'Documentation', 'eventon' ); ?></a>
			<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://www.myeventon.com/" data-text="Event Calendar Plugin for WordPress." data-via="EventON" data-size="large" data-hashtags="ashanjay">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		</p>

		<h2 class="nav-tab-wrapper">
			<a class="nav-tab <?php if ( $_GET['page'] == 'evo-about' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'evo-about' ), 'index.php' ) ) ); ?>">
				<?php _e( "What's New", 'eventon' ); ?>			
			</a>
		</h2>
		<?php
	}
	
	/**
	 * Output the about screen.
	 *
	 * @access public
	 * @return void
	 */
	public function about_screen() {
		global $eventon;
		?>
		<div class="wrap about-wrap">

			<?php $this->intro(); ?>

			<!--<div class="changelog point-releases"></div>-->

			<div class="changelog">

				<h3><?php _e( 'myEventON', 'eventon' ); ?></h3>

				<div class="feature-section images-stagger-right">					
					<p><?php _e( 'EventON just keeps getting better and better with your help and with your suggestions for improvement. We appreciate your continuing support to make EventON the best Event Plugin for WordPress.', 'eventon' ); ?></p>
				</div>

				<h3><?php _e( 'New Changes', 'eventon' ); ?></h3>

				<div class="feature-section col three-col">

					<div>
						<img src="<?php echo AJDE_EVCAL_URL . '/assets/images/welcome/001.jpg'; ?>" alt="Product panel screenshot" style="width: 99%; margin: 0 0 1em;" />
						<h4><?php _e( 'Minor UI updates', 'eventon' ); ?></h4>
						<p><?php _e( 'We have made few changes as far as UI to match the new brand name.', 'eventon' ); ?></p>
					</div>

					<div>
						<img src="<?php echo AJDE_EVCAL_URL . '/assets/images/welcome/002.jpg'; ?>" alt="Order panel screenshot" style="width: 99%; margin: 0 0 1em;" />
						<h4><?php _e( 'Addons', 'eventon' ); ?></h4>
						<p><?php _e( 'Addons are a new addition that will allow you to extend the capabilities of this plugin further.', 'eventon' ); ?></p>
					</div>

					<div class="last-feature">
						<img src="<?php echo AJDE_EVCAL_URL . '/assets/images/welcome/003.jpg'; ?>" alt="Download panel screenshot" style="width: 99%; margin: 0 0 1em;" />
						<h4><?php _e( 'Plugin Update Checker', 'eventon' ); ?></h4>
						<p><?php _e( 'You don\'t have to check on envato anymore to know if there is a newer version of this plugin available.', 'eventon' ); ?></p>
					</div>

				</div>
				<div class="feature-section col three-col">

					<div>
						<img src="<?php echo AJDE_EVCAL_URL . '/assets/images/welcome/004.jpg'; ?>" alt="Product panel screenshot" style="width: 99%; margin: 0 0 1em;" />
						<h4><?php _e( 'New Shortcode button', 'eventon' ); ?></h4>
						<p><?php _e( 'Now you can add shortcodes on the fly direct from the pages shortcode button.', 'eventon' ); ?></p>
					</div>

					<div>
						<img src="<?php echo AJDE_EVCAL_URL . '/assets/images/welcome/005.jpg'; ?>" alt="Order panel screenshot" style="width: 99%; margin: 0 0 1em;" />
						<h4><?php _e( 'Widget Update', 'eventon' ); ?></h4>
						<p><?php _e( 'Widgets now support upcoming events list for specified number of upcoming months.', 'eventon' ); ?></p>
					</div>

					

				</div>

				

			</div>

			<div class="changelog">
				<h3><?php _e( 'Under the Hood', 'eventon' ); ?></h3>

				<div class="feature-section col three-col">
					<div>
						<h4><?php _e( 'New class based strucutre', 'eventon' ); ?></h4>
						<p><?php _e( 'We have rebuilt the base core of the plugin using a classes based structure to allow future expansions.', 'eventon' ); ?></p>
					</div>
					<div>
						<h4><?php _e( 'New Hooks into calendar', 'eventon' ); ?></h4>
						<p><?php _e( 'We have included various hooks and filters into the code structure of this plugin so even you can plug-into these hooks to extend the functionalities.', 'eventon' ); ?></p>
					</div>
					
					<div class='last-feature'>
						<h4><?php _e( 'Better event data handling', 'eventon' ); ?></h4>
						<p><?php _e( 'We have improved the event filtering code structure to load events faster for the calendar months.', 'eventon' ); ?></p>
					</div>
					
				</div>

			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'eventon' ), 'admin.php' ) ) ); ?>"><?php _e( 'Go to myEventON Settings', 'eventon' ); ?></a>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Sends user to the welcome page on first activation
	 */
	public function welcome() {

		// Bail if no activation redirect transient is set
	    if ( ! get_transient( '_evo_activation_redirect' ) )
			return;

		// Delete the redirect transient
		delete_transient( '_evo_activation_redirect' );

		// Bail if we are waiting to install or update via the interface update/install links
		if ( get_option( '_evo_needs_update' ) == 1  )
			return;

		// Bail if activating from network, or bulk, or within an iFrame
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) || defined( 'IFRAME_REQUEST' ) )
			return;

		if ( ( isset( $_GET['action'] ) && 'upgrade-plugin' == $_GET['action'] ) && ( isset( $_GET['plugin'] ) && strstr( $_GET['plugin'], 'eventon.php' ) ) )
			return;

		wp_safe_redirect( admin_url( 'index.php?page=evo-about' ) );
		
		
		exit;
	}
	
}

new EVO_Welcome_Page();
?>