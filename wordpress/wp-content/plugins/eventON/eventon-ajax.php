<?php
/**
 * EventON Ajax Handlers
 *
 * Handles AJAX requests via wp_ajax hook (both admin and front-end events)
 *
 * @author 		AJDE
 * @category 	Core
 * @package 	EventON/Functions/AJAX
 * @version     1.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/** Frontend AJAX events **************************************************/

// Verify eventon Licenses AJAX function
function eventon_license_verification(){
	global $eventon;
	
	$key = $_POST['key'];
	$slug = $_POST['slug'];
	
	$status = $eventon->evo_updater->is_verify_license_key($slug, $key);
	
	$new_license_content='';
	if($status){
		$save_license_date = $eventon->evo_updater->save_license_key($slug, $key);
		
				
		// successfully saved new verified license
		if($save_license_date!=false){
			$status = 'success';
			
			$new_license_content ="
			<h2>{$save_license_date['name']}</h2>
			<p>Version: {$save_license_date['current_version']}</p>
			<p>Type: {$save_license_date['type']}</p>
			<p class='license_key'>{$save_license_date['key']}</p>";
		}else{
			$status='error';
		}
	}else{	$status='error'; }
	
	
	$return_content = array(
		'status'=>$status,		
		'new_content'=>$new_license_content
	);
	echo json_encode($return_content);		
	exit;
	
}
add_action('wp_ajax_eventon_verify_lic', 'eventon_license_verification');
add_action('wp_ajax_nopriv_eventon_verify_lic', 'eventon_license_verification');


/**
 * 	Primary function to load event data
 */
function evcal_ajax_callback(){
	global $eventon;
	
	$focused_month_num = (int)($_POST['next_m']);
	$focused_year = $_POST['next_y'];		
	
	// GET calendar header month year values
	$calendar_month_title = get_eventon_cal_title_month($focused_month_num, $focused_year);
	
	// validate $_POST values
	$soft_by = (isset($_POST['sort_by']))? $_POST['sort_by']: 'sort_date';
	
	
	$focus_start_date_range = mktime( 0,0,0,$focused_month_num,1,$focused_year );
	$time_string = $focused_year.'-'.$focused_month_num.'-1';		
	$focus_end_date_range = mktime(23,59,59,($focused_month_num),(date('t',(strtotime($time_string) ))), ($focused_year));
	
	
	$eve_args = array(
		'focus_start_date_range'=>$focus_start_date_range,
		'focus_end_date_range'=>$focus_end_date_range,
		'sort_by'=>$soft_by,		
		'event_count'=>$_POST['event_count'],
		'filters'=>((isset($_POST['filters']))? $_POST['filters']:null)
	);
	
	
	// Addon hook
	if(has_filter('eventon_ajax_arguments')){
		$eve_args = apply_filters('eventon_ajax_arguments',$eve_args, $_POST);
	}
	
	$content_li = $eventon->evo_generator->eventon_generate_events( $eve_args);
	
	
	// RETURN VALUES
	// Array of content for the calendar's AJAX call returned in JSON format
	$return_content = array(		
		'content'=>$content_li,
		'cal_month_title'=>$calendar_month_title
	);			
	
	
	echo json_encode($return_content);
	exit;
}
add_action('wp_ajax_the_ajax_hook', 'evcal_ajax_callback');
add_action('wp_ajax_nopriv_the_ajax_hook', 'evcal_ajax_callback');



/**
 * EventBrite API loading function for admin
 *
 * @access public
 * @return void
 */	
add_action('wp_ajax_the_post_ajax_hook_3', 'evcal_ajax_callback_3');
add_action('wp_ajax_nopriv_the_post_ajax_hook_3', 'evcal_ajax_callback_3');
function evcal_ajax_callback_3(){
	// pre
	$code = $status = $message = '';
	$evcal_opt1= get_option('evcal_options_evcal_1');
	
	$eb_event_id = $_POST['event_id'];
	$eb_api = $evcal_opt1['evcal_evb_api'];
	
	$xml =simplexml_load_file('http://www.eventbrite.com/xml/event_get?app_key='.$eb_api.'&id='.$eb_event_id );					

	if($xml->getName()!='error'):		
		$status=1;
		
		if($xml->status =='Completed'){
			$message='past';
		}
		
		// pre
		$venue = $xml->venue;
		$location = ((!empty($venue->address) )? $venue->address.', ':null ).
			$venue->city.' '.$venue->region.' '.
			$venue->postal_code;
			
		
		$code.= "<div var='title' class='evcal_data_row '>
			<p>Event Name</p>
			<p class='value'>".$xml->title."</p>
			<em class='clear'></em>
		</div>";
		
		$code.= "<div var='evcal_location' class='evcal_data_row '>
			<p>Location</p>
			<p class='value'>".$location."</p>
			<em class='clear'></em>
		</div>";
		$code.= "<div var='capacity' class='evcal_data_row '>
			<p>Event Capacity</p>
			<p class='value'>".$xml->capacity."</p>
			<em class='clear'></em>
		</div>";
		$code.= "<div var='price' class='evcal_data_row '>
			<p>Ticket Price</p>
			<p class='value'>".$xml->tickets->ticket->currency.' '.$xml->tickets->ticket->price."</p>
			<em class='clear'></em>
		</div>";		
		$code.= "<div var='url' class='evcal_data_row '>
			<p>Buy Now Ticket URL</p>
			<p class='value'>".$xml->url."</p>								
		</div><p class='clear'></p>	";
		
	else:
		$status =0;
	endif;	

	$return_content = array(
		'status'=>$status,
		'message'=>$message,
		'code'=>$code	
	);			
	echo json_encode($return_content);		
	exit;
}

/**
 * Meetup API function for admin
 *
 * @access public
 * @return void
 */	
add_action('wp_ajax_the_post_ajax_hook_2', 'evcal_ajax_callback_2');
add_action('wp_ajax_nopriv_the_post_ajax_hook_2', 'evcal_ajax_callback_2');
function evcal_ajax_callback_2(){
	
	// pre
	$code = $status = '';
	$evcal_opt1= get_option('evcal_options_evcal_1');
	$wp_time_format = get_option('time_format');
	
	$mu_event_id = $_POST['event_id'];
	$mu_api = $evcal_opt1['evcal_api_mu_key'];
	
	$xml =simplexml_load_file('http://api.meetup.com/2/event/'.
		$mu_event_id.'.xml?key='.$mu_api.'&sign=true');					

	if($xml->getName()!='error'):
		$status=1;
		// pre
		$venue = $xml->venue;
		$location = $venue->address_1.', '.
			$venue->city.' '.$venue->state.' '.
			$venue->zip;
			
		$utc_offset = substr($xml->utc_offset, 0, -3);
		$time_raw = substr($xml->time, 0, -3);
		
		$time_s = ((int)($time_raw)) + ((int)($utc_offset));
		
		
		$time_formated = date("l F j, Y",$time_s);
		$time_formated_2 = date("n/j/Y",$time_s);
		$s_hour = date("g",$time_s);
		$s_min = date("i",$time_s);
		$s_ampm = date("A",$time_s);
		//print_r( $location);
		
		
		$code.= "<div var='title' class='evcal_data_row '>
			<p>Event Name</p>
			<p class='value'>".$xml->name."</p>
			<em class='clear'></em>
		</div>";
		$code.= "<div var='evcal_location' class='evcal_data_row '>
			<p>Location</p>
			<p class='value'>".$location."</p>
			<em class='clear'></em>
		</div>";
		
		$code.= "<div var='time' class='evcal_data_row '>
			<p>Time</p>
			<p class='value' ftime='".$time_formated_2."' hr='".$s_hour."' min='".$s_min."' ampm='".$s_ampm."'>".$time_formated."</p>
		</div>";
									
		
		$code.= "<div var='url' class='evcal_data_row '>
			<p>Event URL</p>
			<p class='value'>".$xml->event_url."</p>								
		</div><p class='clear'></p>	";
		
	else:
		$status =0;
	endif;	

	$return_content = array(
		'status'=>$status,
		'code'=>$code
	);			
	echo json_encode($return_content);		
	exit;
					
}	


?>