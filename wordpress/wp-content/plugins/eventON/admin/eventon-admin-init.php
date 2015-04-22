<?php
/**
 * EventON Admin
 *
 * Main admin file which loads all settings panels and sets up admin menus.
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	EventON/Admin
 * @version     1.3
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Functions for the ajde_events post type
 */
 
include_once('eventon-admin-functions.php' );
include_once('post_types/ajde_events.php' );
include_once('includes/welcome.php' );

/**
 * Setup the Admin menu in WordPress
 *
 * @access public
 * @return void
 */
function eventon_admin_menu() {
    global $menu, $eventon, $pagenow;

    if ( current_user_can( 'manage_eventon' ) )
    $menu[] = array( '', 'read', 'separator-eventon', '', 'wp-menu-separator eventon' );
	
	// check for saved plugin update status to modify menu button
	$licenses = get_option('_evo_licenses');
	$evo_notification = (!empty($licenses['eventon']['has_new_update']) && $licenses['eventon']['has_new_update'])? ' <span class="update-plugins count-1" title="1 Plugin Update"><span class="update-count">1</span></span>':null;
	
	// Create admin menu page 
	$main_page = add_menu_page(
		__('EventON - Event Calendar','eventon'), 
		'myEventON'.$evo_notification,
		'manage_eventon',
		'eventon',
		'eventon_settings_page', 
		AJDE_EVCAL_URL.'/assets/images/eventon_menu_icon.png'
	);

	
	
    add_action( 'load-' . $main_page, 'eventon_admin_help_tab' );	
	
	
	// includes
	if( $pagenow == 'post-new.php' || $pagenow == 'post.php' || $pagenow == 'edit.php' ) {
		include_once( 'post_types/ajde_events_meta_boxes.php' );
	}
}
add_action('admin_menu', 'eventon_admin_menu', 9);


/**
 * Highlights the correct top level admin menu item for Settings
 */
function eventon_admin_menu_highlight() {
	global $submenu;
	
	if ( isset( $submenu['eventon'] )  )  {
		$submenu['eventon'][0][0] = 'Settings';
		//unset( $submenu['eventon'][2] );
	}
}

add_action( 'admin_head', 'eventon_admin_menu_highlight' );



/**
 * Include some admin files conditonally.
 *
 * @access public
 * @return void
 */
function eventon_admin_init() {
	global $pagenow, $typenow, $wpdb, $post;	
	
	if ( $typenow == 'post' && ! empty( $_GET['post'] ) ) {
		$typenow = $post->post_type;
	} elseif ( empty( $typenow ) && ! empty( $_GET['post'] ) ) {
        $post = get_post( $_GET['post'] );
        $typenow = $post->post_type;
    }
	
	if ( $typenow == '' || $typenow == "ajde_events" ) {
	
		// Event Post Only
		$print_css_on = array( 'post-new.php', 'post.php' );

		foreach ( $print_css_on as $page ){
			add_action( 'admin_print_styles-'. $page, 'eventon_admin_post_css' );
			add_action( 'admin_print_scripts-'. $page, 'eventon_admin_post_script' );
		}
		
		// filter event post permalink edit options
		if(!defined('EVO_SIN_EV')){
			eventon_perma_filter();
		}
	}
	
	// check for installed addons and update list
	//eventon_check_addons();
	
	// check for plugin versions	
	eventon_plugin_version_check();	
	
	// create necessary pages	
	$_eventon_create_pages = get_option('_eventon_create_pages'); // get saved status for creating pages
	$events_page = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name='events'");
	
	if(empty($events_page) && (empty($_eventon_create_pages) || $_eventon_create_pages!= 1)	){
		require_once( 'eventon-admin-install.php' );
		eventon_create_pages();
		update_option('_eventon_create_pages',1);
	}
	
	
}
add_action('admin_init', 'eventon_admin_init');



/**
 * Include and display the settings page.
 */
function eventon_settings_page() {
	include_once( 'eventon-admin-settings.php' );
	eventon_settings();
}

/** 
 *	Load styles for EVENT POST TYPE
 */
function eventon_admin_post_css() {
	global $wp_scripts;
	
	
	// JQ UI styles
	$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';		
	wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_version . '/themes/smoothness/jquery-ui.css' );
	
	wp_enqueue_style( 'backend_evcal_post',AJDE_EVCAL_URL.'/assets/css/backend_evcal_post.css');
	wp_enqueue_style( 'evo_backend_admin',AJDE_EVCAL_URL.'/assets/css/admin.css');
	
}

/** 
 *	Load scripts for EVENT POST TYPE
 */
function eventon_admin_post_script() {
	
	/** COLOR PICKER **/
	wp_enqueue_script('color_picker',AJDE_EVCAL_URL.'/assets/js/colorpicker.js' ,array('jquery'),'1.0', true);
	wp_enqueue_style( 'ajde_backender_colorpicker_styles',AJDE_EVCAL_URL.'/assets/css/colorpicker_styles.css');
	
	
	// other scripts 
	wp_enqueue_script('evcal_backend_post',AJDE_EVCAL_URL.'/assets/js/eventon_backend_post.js', array('jquery','jquery-ui-core','jquery-ui-datepicker'), 1.0, true );
	
	wp_localize_script( 'evcal_backend_post', 'the_ajax_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));	
	
	do_action('eventon_admin_post_script');
}


/**
 * Include admin scripts and styles.
 */
function eventon_admin_scripts() {
	global $EventON, $pagenow;
	
	// JQuery UI Styles
	$calendar_ui_style_src = AJDE_EVCAL_URL.'/assets/css/jquery-ui.min.css';
	wp_enqueue_style( 'eventon_JQ_UI',$calendar_ui_style_src);
	
	// Scripts/ Styles for eventON Settings page only		
	if($pagenow=='admin.php' && $_GET['page']=='eventon'){
		wp_enqueue_script('evcal_backend_all',AJDE_EVCAL_URL.'/assets/js/eventon_all_backend.js',array('jquery'),1.0,true);
		wp_enqueue_script('evcal_backend',AJDE_EVCAL_URL.'/assets/js/eventon_backend.js',array('jquery'),1.0,true);		
		wp_enqueue_style( 'backend_evcal_settings',AJDE_EVCAL_URL.'/assets/css/backend_evcal_settings.css');
		wp_enqueue_style( 'evo_backend_admin',AJDE_EVCAL_URL.'/assets/css/admin.css');
		
		wp_localize_script( 'evcal_backend_all', 'the_ajax_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
		
		// LOAD thickbox
		if(isset($_GET['tab']) && $_GET['tab']=='evcal_5'){
			wp_enqueue_script('thickbox');
			wp_enqueue_style('thickbox');
		}
		
		do_action('eventon_admin_scripts');
	}
}
add_action( 'admin_enqueue_scripts', 'eventon_admin_scripts' );




/** scripts and styles for all backend **/
function eventon_all_backend_files(){
	wp_localize_script( 'evcal_backend_post', 'the_ajax_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
	wp_enqueue_script('evcal_backend_all',AJDE_EVCAL_URL.'/assets/js/eventon_all_backend.js',array('jquery'),1.0,true);
}
add_action( 'admin_enqueue_scripts', 'eventon_all_backend_files' );




/** check plugin version **/
function eventon_plugin_version_check(){
	global $eventon;
	
	$plugin_version = $eventon->version;
		
	// check installed version
	$installed_version = get_option('eventon_plugin_version');
	
	if($installed_version != $plugin_version){
		update_option('eventon_plugin_version', $plugin_version);			
	}else if(!$installed_version ){
		add_option('eventon_plugin_version', $plugin_version);			
	}else{
		update_option('eventon_plugin_version', $plugin_version);			
	}
	
	// delete options saved on previous version
	delete_option('evcal_plugin_version');
}


/**
 * Include and add help tabs to WordPress admin.
 */
function eventon_admin_help_tab() {
	include_once( 'eventon-admin-content.php' );
	eventon_admin_help_tab_content();
}

/**
 * Admin Head
 *
 * Outputs some styles in the admin <head> to show icons on the woocommerce admin pages
 */
function eventon_admin_head() {
	global $eventon;	?>
	
	<style type="text/css">
		#menu-posts-ajde_events .wp-menu-image {background: url(<?php echo AJDE_EVCAL_URL;?>/assets/images/calendar-day.png) no-repeat 6px -17px !important;}
		#menu-posts-ajde_events:hover .wp-menu-image, #menu-posts-ajde_events.wp-has-current-submenu .wp-menu-image {background-position:6px 7px!important;}
		
		<?php if ( isset($_GET['taxonomy']) && $_GET['taxonomy']=='event_type' ) : ?>
			.icon32-posts-ajde_events { background-position: -64px -0px !important; }
		<?php elseif ( isset($_GET['taxonomy']) && $_GET['taxonomy']=='event_type_2' ) : ?>
			.icon32-posts-ajde_events { background-position: -64px -0px !important; }
		<?php endif; ?>
	</style>
	<?php
}

add_action('admin_head', 'eventon_admin_head');


/**
 * Queue admin menu icons CSS.
 */
function eventon_admin_menu_styles() {
	global $eventon;
	wp_enqueue_style( 'eventon_admin_menu_styles', AJDE_EVCAL_URL . '/assets/css/menu.css' );
	wp_enqueue_style( 'evo_backend_admin',AJDE_EVCAL_URL.'/assets/css/admin.css');
}

add_action( 'admin_print_styles', 'eventon_admin_menu_styles' );




/**
 * Duplicate event action
 */
function eventon_duplicate_event_action() {
	include_once('post_types/duplicate_event.php');
	eventon_duplicate_event();
}

add_action('admin_action_duplicate_event', 'eventon_duplicate_event_action');


// ==========================
//	TAXONOMY
function event_type_description() {
	echo wpautop( __( 'Event categories for events can be managed here. To change the order of categories on the front-end you can drag and drop to sort them. To see more categories listed click the "screen options" link at the top of the page.', 'eventon' ) );
}
add_action( 'event_type_pre_add_form', 'event_type_description' );

// event type 1
add_filter( 'manage_edit-event_type_columns', 'event_type_edit_columns',5 );
add_filter( 'manage_event_type_custom_column', 'event_type_custom_columns',5,3 );


// event type 2
add_filter( 'manage_edit-event_type_2_columns', 'event_type_edit_columns',5 );
add_filter( 'manage_event_type_2_custom_column', 'event_type_custom_columns',5,3 );
function event_type_edit_columns($defaults){
    $defaults['event_type_id'] = __('ID');
    return $defaults;
}   

function event_type_custom_columns($value, $column_name, $id){
	if($column_name == 'event_type_id'){
		return (int)$id;
	}
}

?>