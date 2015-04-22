<?php
/**
 * EventON Admin Functions
 *
 * Hooked-in functions for EventON related events in admin.
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	EventON/Admin
 * @version     1.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly




/**
 * Prevent non-admin access to backend
 */
add_action( 'init', 'eventon_add_shortcode_button' );
add_filter( 'tiny_mce_version', 'eventon_refresh_mce' ); 
 
function eventon_prevent_admin_access() {
	if ( get_option('eventon_lock_down_admin') == 'yes' && ! is_ajax() && ! ( current_user_can('edit_posts') || current_user_can('manage_eventon') ) ) {
		//wp_safe_redirect(get_permalink(woocommerce_get_page_id('myaccount')));
		exit;
	}
}

/**
 * Add a SHORTCODE BUTTON to the WP editor.
 */
function eventon_add_shortcode_button() {
	global $pagenow, $typenow, $wpdb, $post;	
	
	if ( $typenow == 'post' && ! empty( $_GET['post'] ) ) {
		$typenow = $post->post_type;
	} elseif ( empty( $typenow ) && ! empty( $_GET['post'] ) ) {
        $post = get_post( $_GET['post'] );
        $typenow = $post->post_type;
    }
	
	if ( $typenow == "ajde_events" ) return;
	
	
	if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') ) return;
	
	if ( get_user_option('rich_editing') == 'true') :
		add_filter('mce_external_plugins', 'eventon_add_shortcode_tinymce_plugin');
		add_filter('mce_buttons', 'eventon_register_shortcode_button');	
		
	endif;
}
/**
 * Register the shortcode button.
 */
function eventon_register_shortcode_button($buttons) {
	eventon_shortcode_pop_content();
	array_push($buttons, "|", "EventONShortcodes");
	return $buttons;
}

/**
 * Short code popup content
 */
function eventon_shortcode_pop_content(){
	global $eventon;
	$shortcode_btns = array(
		'Basic Calendar'=>'[add_eventon]',
		'Calendar - unique ID'=>'[add_eventon cal_id="1"]',
		'Calendar - event type'=>'[add_eventon cal_id="1" event_type=""]',
		'Calendar - event type 2'=>'[add_eventon cal_id="1" event_type_2=""]',
		'Calendar - event count limit'=>'[add_eventon cal_id="1" event_count=""]',
		'Calendar - different start month'=>'[add_eventon cal_id="1" month_incre=""]',
		'Calendar - fixed month and year'=>'[add_eventon cal_id="1" fixed_month="10" fixed_year="2013"]',
		'Upcoming Month List'=>'[add_eventon cal_id="1" show_upcoming="1" number_of_months=""]',
	);
	
	// hook for addons
	if(has_filter('eventon_shortcode_options')){
		$shortcode_btns = apply_filters('eventon_shortcode_options',$shortcode_btns);
	}
	
	$content='<h2>Select EventON Shortcode Options</h2>';
	foreach($shortcode_btns as $sc_f=>$sc_v){
		$content.= "<p class='eventon_shortcode_btn' scode='{$sc_v}'>".$sc_f."</p>";
	}
	$content.="<div class='clear'></div><p><a target='_blank' href='http://www.myeventon.com/documentation/shortcode-guide/'>myEventON Shortcode Guide</a></p><div class='clear'></div>";
	
	echo $eventon->output_eventon_pop_window($content, 'eventon_shortcode', 'clear="false"');
}




/**
 * Add the shortcode button to TinyMCEy
 */
function eventon_add_shortcode_tinymce_plugin($plugin_array) {
	
	$plugin_array['EventONShortcodes'] = AJDE_EVCAL_URL . '/assets/js/editor_plugin.js';
	return $plugin_array;
}

/**
 * Force TinyMCE to refresh.
 *
 * @access public
 * @param mixed $ver
 * @return int
 */
function eventon_refresh_mce( $ver ) {
	$ver += 3;
	return $ver;
}




?>