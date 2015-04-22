<?php
/**
 * EventON Core Functions
 *
 * Functions available on both the front-end and admin.
 *
 * @author 		AJDE
 * @category 	Core
 * @package 	EventON/Functions
 * @version     1.3
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
	
function eventon_is_future_event($current_time, $row_end_unix, $evcal_cal_hide_past){
	$future_event = ($row_end_unix >= $current_time )? true:false;
	
	if( 
		( ($evcal_cal_hide_past=='yes' ) && $future_event )
		|| ( ($evcal_cal_hide_past=='no' ) || ($evcal_cal_hide_past=='' ))
	){
		return true;
	}else{
		return false;
	}
}

function eventon_is_event_in_daterange($Estart_unix, $Eend_unix, $Mstart_unix, $Mend_unix){		
	if(
		($Estart_unix<=$Mstart_unix && $Eend_unix>=$Mstart_unix) ||
		($Estart_unix<=$Mend_unix && $Eend_unix>=$Mend_unix) ||
		($Mstart_unix<=$Estart_unix && $Estart_unix<=$Mend_unix && $Eend_unix=='') ||		
		($Mstart_unix<=$Estart_unix && $Estart_unix<=$Mend_unix && $Eend_unix==$Estart_unix) 	||
		($Mstart_unix<=$Estart_unix && $Estart_unix<=$Mend_unix && $Eend_unix!=$Estart_unix)
	){
		return true;
	}else{
		return false;
	}
}


// RETURN: formatted event time in multiple formats
function eventon_get_formatted_time($row_unix){
	/*
		D = Mon - Sun
		j = 1-31
		l = Sunday - Saturday
		N - day of week 1-7
		S - st, nd rd
		n - month 1-12
		F - January - Decemer
		t - number of days in month
		z - day of the year
		Y - 2000
		g = hours
		i = minute
		a = am/pm
		M = Jan - Dec
	*/
	$key = array('D','j','l','N','S','n','F','t','z','Y','g','i','a','M');
	$date = date('D-j-l-N-S-n-F-t-z-Y-g-i-a-M',$row_unix);
	$date = explode('-',$date);
	
	
	foreach($date as $da=>$dv){
		if($da==6){
			$output[$key[$da]]= eventon_returnmonth_name_by_num($date[5]); 
		}else if($da==2){
			$output[$key[$da]]= eventon_get_event_day_name($date[3]); 
		}else if($da==0){
			$output[$key[$da]]= substr(eventon_get_event_day_name($date[3]), 0 ,3); 
		}else{
			$output[$key[$da]]= $dv;
		}
	}
	
	return $output;
}

/*
	return date value and time values from unix timestamp
*/
function eventon_get_editevent_kaalaya($unix, $dateformat='', $timeformat24=''){
	
	
	// in case of empty date format provided
	// find it within system
	if(empty($dateformat)){
		$dateformat = eventon_get_timeNdate_format();
		$dateformat = $dateformat[1];
	}
	
	$date = date($dateformat, $unix);
	
	
	$timestring = ($timeformat24)? 'H-i': 'g-i-A';
	$times_val = date($timestring, $unix);
	$time_data = explode('-',$times_val);
	
	
	$output = array_merge( array($date), $time_data);
	
	return $output;
}

/*
	GET event UNIX time from date and time format $_POST values
*/
function eventon_get_unix_time($data, $date_format='', $time_format=''){
	
	
	// END DATE
	$__evo_end_date =(empty($data['evcal_end_date']))?
		$data['evcal_start_date']: $data['evcal_end_date'];
	
	
	$_wp_date_format = (!empty($date_format))? $date_format: get_option('date_format');
	
	$_is_24h = (!empty($time_format) && $time_format=='24h')? true:false; // get default site-wide date format
	//$_wp_date_str = split("[\s|.|,|/|-]",$_wp_date_format);
	
	// ---
	// START UNIX
	if( !empty($data['evcal_start_time_hour'])  && !empty($data['evcal_start_date']) ){
		
		//get hours minutes am/pm 
		$time_string = $data['evcal_start_time_hour']
			.':'.$data['evcal_start_time_min'].$data['evcal_st_ampm'];
		
		// event start time string
		$date = $data['evcal_start_date'].' '.$time_string;
		
		// parse string to array by time format
		$__ti = ($_is_24h)?
			date_parse_from_format($_wp_date_format.' H:i', $date):
			date_parse_from_format($_wp_date_format.' g:ia', $date);
				
		// GENERATE unix time
		$unix_start = mktime($__ti['hour'], $__ti['minute'],0, $__ti['month'], $__ti['day'], $__ti['year'] );
		
				
	}else{ $unix_start =0; }
	
	
	// ---
	// END TIME UNIX
	if( !empty($data['evcal_end_time_hour'])  && !empty($data['evcal_end_date']) ){
		
		//get hours minutes am/pm 
		$time_string = $data['evcal_end_time_hour']
			.':'.$data['evcal_end_time_min'].$data['evcal_et_ampm'];
		
		// event start time string
		$date = $__evo_end_date.' '.$time_string;
				
		
		// parse string to array by time format
		$__ti = ($_is_24h)?
			date_parse_from_format($_wp_date_format.' H:i', $date):
			date_parse_from_format($_wp_date_format.' g:ia', $date);
				
		// GENERATE unix time
		$unix_end = mktime($__ti['hour'], $__ti['minute'],0, $__ti['month'], $__ti['day'], $__ti['year'] );		
				
		
	}else{ $unix_end =0; }
		
		
	$unix_end =(!empty($unix_end) )?$unix_end:$unix_start;
	
	// output the unix timestamp
	$output = array(
		'unix_start'=>$unix_start,
		'unix_end'=>$unix_end
	);
	
	return $output;
}

/*
	return jquery and HTML UNIVERSAL date format for the site
	added: version 2.1.19
*/
function eventon_get_timeNdate_format($evcal_opt=''){
	
	if(empty($evcal_opt))
		$evcal_opt = get_option('evcal_options_evcal_1');
	
	if(!empty($evcal_opt) && $evcal_opt['evo_usewpdateformat']=='yes'){
				
		/** get date formate and convert to JQ datepicker format**/				
		$wp_date_format = get_option('date_format');
		$format_str = str_split($wp_date_format);
		
		foreach($format_str as $str){
			switch($str){							
				case 'j': $nstr = 'd'; break;
				case 'd': $nstr = 'dd'; break;	
				case 'D': $nstr = 'D'; break;	
				case 'l': $nstr = 'DD'; break;	
				case 'm': $nstr = 'mm'; break;
				case 'M': $nstr = 'M'; break;
				case 'n': $nstr = 'm'; break;
				case 'F': $nstr = 'MM'; break;							
				case 'Y': $nstr = 'yy'; break;
				case 'y': $nstr = 'y'; break;
										
				default :  $nstr = ''; break;							
			}
			$jq_date_format[] = (!empty($nstr))?$nstr:$str;
			
		}
		$jq_date_format = implode('',$jq_date_format);
		$evo_date_format = $wp_date_format;
	}else{
		$jq_date_format ='yy/mm/dd';
		$evo_date_format = 'Y/m/d';
	}
	
	
	// time format
	$wp_time_format = get_option('time_format');
	
	$hr24 = (strpos($wp_time_format, 'H')!==false)?true:false;
	
	return array(
		$jq_date_format, 
		$evo_date_format,
		$hr24
	);
}


/*
	RETURN calendar header with month and year data
	string - should be m, Y if empty
*/
function get_eventon_cal_title_month($month_number, $year_number){
	
	$evopt = get_option('evcal_options_evcal_1');
	
	$string = ($evopt['evcal_header_format']!='')?$evopt['evcal_header_format']:'m, Y';

	$str = str_split($string, 1);
	$new_str = '';
	
	
	
	foreach($str as $st){
		switch($st){
			case 'm':
				$new_str.= eventon_returnmonth_name_by_num($month_number);
			break;
			case 'Y':
				$new_str.= $year_number;
			break;
			case 'y':
				$new_str.= substr($year_number, -2);
			break;
			default:
				$new_str.= $st;
			break;
		}
	}
	
	return $new_str;
}

function eventon_get_event_day_name($day_number){
	//event day array
	$evcal_opt2= get_option('evcal_options_evcal_2');
	$custom_day_names = $evcal_opt2['evcal_cal_day_cus'];			
	if($custom_day_names == '' || $custom_day_names=='no'){
		$evcal_day_is= array(1=>'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
	}else{
		for($x=1; $x<8; $x++){
			$evcal_day_is[$x] = $evcal_opt2['evcal_lang_day'.$x];
		}
	}
	
	return $evcal_day_is[$day_number];
}

function eventon_get_new_monthyear($current_month_number, $current_year, $difference){
	$month_num = $current_month_number + $difference;
	if($month_num>12){
		$next_m_n = $month_num-12;
		$next_y = $current_year+1;
	}else{
		$next_m_n =$month_num;
		$next_y = $current_year;
	}
	
	$ra = array(
		'month'=>$next_m_n, 'year'=>$next_y
	);
	return $ra;
}

/** return custom language text saved in settings **/
function eventon_get_custom_language($evo_lang_opts, $field, $default_val){
	$new_lang_val = (!empty($evo_lang_opts[$field]) )?
		$evo_lang_opts[$field]: $default_val;
	return $new_lang_val;
}


/** SORTING arrangement functions **/
function cmp_esort_startdate($a, $b){
	return $a["event_start_unix"] - $b["event_start_unix"];
}
function cmp_esort_title($a, $b){
	return strcmp($a["event_title"], $b["event_title"]);
}
function cmp_esort_color($a, $b){
	return strcmp($a["event_color"], $b["event_color"]);
}


// GET EVENT
function get_event($the_event){
	global $eventon;
}


// Returns a proper form of labeling for custom post type
/**
 * Function that returns an array containing the IDs of the products that are on sale.
 */
if( !function_exists ('eventon_get_proper_labels')){
	function eventon_get_proper_labels($sin, $plu){
		return array(
		'name' => _x($plu, 'post type general name'),
		'singular_name' => _x($sin, 'post type singular name'),
		'add_new' => _x('Add New', $sin),
		'add_new_item' => __('Add New '.$sin),
		'edit_item' => __('Edit '.$sin),
		'new_item' => __('New '.$sin),
		'all_items' => __('All '.$plu),
		'view_item' => __('View '.$sin),
		'search_items' => __('Search '.$plu),
		'not_found' =>  __('No '.$plu.' found'),
		'not_found_in_trash' => __('No '.$plu.' found in Trash'), 
		'parent_item_colon' => '',
		'menu_name' => $plu
	  );
	}
}
// Return formatted time 
if( !function_exists ('ajde_evcal_formate_date')){
	function ajde_evcal_formate_date($date,$return_var){	
		$srt = strtotime($date);
		$f_date = date($return_var,$srt);
		return $f_date;
	}
}

if( !function_exists ('returnmonth')){
	function returnmonth($n){
		$timestamp = mktime(0,0,0,$n,1,2013);
		return date('F',$timestamp);
	}
}
if( !function_exists ('eventon_returnmonth_name_by_num')){
	function eventon_returnmonth_name_by_num($n){
		$evcal_val2= get_option('evcal_options_evcal_2');
		
		//get custom month names
		$default_month_names = array(1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December');
		
		$month_field_ar = array(1=>'evcal_lang_jan',2=>'evcal_lang_feb',3=>'evcal_lang_mar',4=>'evcal_lang_apr',5=>'evcal_lang_may',6=>'evcal_lang_jun',7=>'evcal_lang_jul',8=>'evcal_lang_aug',9=>'evcal_lang_sep',10=>'evcal_lang_oct',11=>'evcal_lang_nov',12=>'evcal_lang_dec');
		
		$cus_month_name = $evcal_val2[$month_field_ar[$n]];
		$cus_month_name =($cus_month_name!='')?$cus_month_name: $default_month_names[$n];
		
		return $cus_month_name;
	}
}




// Return a excerpt of the event details
function eventon_get_event_excerpt($text, $excerpt_length, $default_excerpt='', $title=true){
	global $eventon;
	
	$content='';
	
	if(empty($default_excerpt) ){
	
		$words = explode(' ', $text, $excerpt_length + 1);
		if(count($words) > $excerpt_length) :
			array_pop($words);
			array_push($words, '[...]');
			$content = implode(' ', $words);
		endif;
		$content = strip_shortcodes($content);
		$content = str_replace(']]>', ']]&gt;', $content);
		$content = strip_tags($content);
	}else{
		$content = $default_excerpt;
	}
	
	
	$titletx = ($title)? '<h3>' . eventon_get_custom_language($eventon->evo_generator->evopt2, 'evcal_evcard_details','Event Details').'</h3>':null;
	
	$content = '<div class="event_excerpt" style="display:none">'.$titletx.'<p>'. $content . '</p></div>';
	
	return $content;
}



/**
 * eventon Term Meta API - Get term meta
 */
function get_eventon_term_meta( $term_id, $key, $single = true ) {
	return get_metadata( 'eventon_term', $term_id, $key, $single );
}

/**
 * Get template part (for templates like the event-loop).
 */
function eventon_get_template_part( $slug, $name = '' , $preurl='') {
	global $eventon;
	$template = '';
	
	
	if($preurl){
		$template =$preurl."/{$slug}-{$name}.php";
	}else{
		// Look in yourtheme/slug-name.php and yourtheme/eventon/slug-name.php
		if ( $name )
			$template = locate_template( array ( "{$slug}-{$name}.php", "{$eventon->template_url}{$slug}-{$name}.php" ) );

		// Get default slug-name.php
		if ( !$template && $name && file_exists( AJDE_EVCAL_PATH . "/templates/{$slug}-{$name}.php" ) )
			$template = AJDE_EVCAL_PATH . "/templates/{$slug}-{$name}.php";

		// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/eventon/slug.php
		if ( !$template )
			$template = locate_template( array ( "{$slug}.php", "{$eventon->template_url}{$slug}.php" ) );

		
	}
	
	if ( $template )
		load_template( $template, false );
}


/** get base currency code **/
function get_eventon_currency() {
	return apply_filters( 'eventon_currency', get_option('eventon_currency') );
}
/** get full list of currency codes **/
function get_eventon_currencies() {
	return array_unique(
		apply_filters( 'eventon_currencies',
			array(
				'AUD' => __( 'Australian Dollars', 'eventon' ),
				'BRL' => __( 'Brazilian Real', 'eventon' ),
				'CAD' => __( 'Canadian Dollars', 'eventon' ),
				'RMB' => __( 'Chinese Yuan', 'eventon' ),
				'CZK' => __( 'Czech Koruna', 'eventon' ),
				'DKK' => __( 'Danish Krone', 'eventon' ),
				'EUR' => __( 'Euros', 'eventon' ),
				'HKD' => __( 'Hong Kong Dollar', 'eventon' ),
				'HUF' => __( 'Hungarian Forint', 'eventon' ),
				'IDR' => __( 'Indonesia Rupiah', 'eventon' ),
				'INR' => __( 'Indian Rupee', 'eventon' ),
				'ILS' => __( 'Israeli Shekel', 'eventon' ),
				'JPY' => __( 'Japanese Yen', 'eventon' ),
				'MYR' => __( 'Malaysian Ringgits', 'eventon' ),
				'MXN' => __( 'Mexican Peso', 'eventon' ),
				'NOK' => __( 'Norwegian Krone', 'eventon' ),
				'NZD' => __( 'New Zealand Dollar', 'eventon' ),
				'PHP' => __( 'Philippine Pesos', 'eventon' ),
				'PLN' => __( 'Polish Zloty', 'eventon' ),
				'GBP' => __( 'Pounds Sterling', 'eventon' ),
				'RON' => __( 'Romanian Leu', 'eventon' ),
				'SGD' => __( 'Singapore Dollar', 'eventon' ),
				'ZAR' => __( 'South African rand', 'eventon' ),
				'SEK' => __( 'Swedish Krona', 'eventon' ),
				'CHF' => __( 'Swiss Franc', 'eventon' ),
				'TWD' => __( 'Taiwan New Dollars', 'eventon' ),
				'THB' => __( 'Thai Baht', 'eventon' ),
				'TRY' => __( 'Turkish Lira', 'eventon' ),
				'USD' => __( 'US Dollars', 'eventon' ),
			)
		)
	);
}
/**
 * Get Currency symbol.
 */
function get_eventon_currency_symbol( $currency = '' ) {
	if ( ! $currency )
		$currency = get_eventon_currency();

	switch ( $currency ) {
		case 'BRL' :
			$currency_symbol = '&#82;&#36;';
			break;
		case 'AUD' :
		case 'CAD' :
		case 'MXN' :
		case 'NZD' :
		case 'HKD' :
		case 'SGD' :
		case 'USD' :
			$currency_symbol = '&#36;';
			break;
		case 'EUR' :
			$currency_symbol = '&euro;';
			break;
		case 'CNY' :
		case 'RMB' :
		case 'JPY' :
			$currency_symbol = '&yen;';
			break;
		case 'TRY' : $currency_symbol = '&#84;&#76;'; break;
		case 'NOK' : $currency_symbol = '&#107;&#114;'; break;
		case 'ZAR' : $currency_symbol = '&#82;'; break;
		case 'CZK' : $currency_symbol = '&#75;&#269;'; break;
		case 'MYR' : $currency_symbol = '&#82;&#77;'; break;
		case 'DKK' : $currency_symbol = '&#107;&#114;'; break;
		case 'HUF' : $currency_symbol = '&#70;&#116;'; break;
		case 'IDR' : $currency_symbol = 'Rp'; break;
		case 'INR' : $currency_symbol = '&#8377;'; break;
		case 'ILS' : $currency_symbol = '&#8362;'; break;
		case 'PHP' : $currency_symbol = '&#8369;'; break;
		case 'PLN' : $currency_symbol = '&#122;&#322;'; break;
		case 'SEK' : $currency_symbol = '&#107;&#114;'; break;
		case 'CHF' : $currency_symbol = '&#67;&#72;&#70;'; break;
		case 'TWD' : $currency_symbol = '&#78;&#84;&#36;'; break;
		case 'THB' : $currency_symbol = '&#3647;'; break;
		case 'GBP' : $currency_symbol = '&pound;'; break;
		case 'RON' : $currency_symbol = 'lei'; break;
		default    : $currency_symbol = ''; break;
	}

	return apply_filters( 'eventon_currency_symbol', $currency_symbol, $currency );
}


if(!function_exists('date_parse_from_format')){
	function date_parse_from_format($_wp_format, $date){
		
		$date_pcs = preg_split('/ (?!.* )/',$_wp_format);
		$time_pcs = preg_split('/ (?!.* )/',$date);
		
		$_wp_date_str = preg_split("/[\s . , \: \- \/ ]/",$date_pcs[0]);
		$_ev_date_str = preg_split("/[\s . , \: \- \/ ]/",$time_pcs[0]);
		
		$check_array = array(
			'Y'=>'year',
			'y'=>'year',
			'm'=>'month',
			'n'=>'month',
			'M'=>'month',
			'F'=>'month',
			'd'=>'day',
			'j'=>'day',
			'D'=>'day',
			'l'=>'day',
		);
		
		foreach($_wp_date_str as $strk=>$str){
			
			if($str=='M' || $str=='F' ){
				$str_value = date('n', strtotime($_ev_date_str[$strk]));
			}else{
				$str_value=$_ev_date_str[$strk];
			}
			
			if(!empty($str) )
				$ar[ $check_array[$str] ]=$str_value;		
			
		}
		
		$ar['hour']= date('H', strtotime($time_pcs[1]));
		$ar['minute']= date('i', strtotime($time_pcs[1]));
		
		
		return $ar;
	}
}



if( !function_exists('date_parse_from_format') ){
	function date_parse_from_format($format, $date) {
	  $dMask = array(
		'H'=>'hour',
		'i'=>'minute',
		's'=>'second',
		'y'=>'year',
		'm'=>'month',
		'd'=>'day'
	  );
	  $format = preg_split('//', $format, -1, PREG_SPLIT_NO_EMPTY); 
	  $date = preg_split('//', $date, -1, PREG_SPLIT_NO_EMPTY); 
	  foreach ($date as $k => $v) {
		if ($dMask[$format[$k]]) $dt[$dMask[$format[$k]]] .= $v;
	  }
	  return $dt;
	}
}

/** Return integer value for a hex color code **/
function eventon_get_hex_val($color){
    if ($color[0] == '#')
        $color = substr($color, 1);

    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
        return false;

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    $val = (int)(($r+$g+$b)/3);
	
    return $val;
}



/**
 * Get capabilities for Eventon - these are assigned to admin during installation or reset
 */
function eventon_get_core_capabilities(){
	$capabilities = array();

	$capabilities['core'] = apply_filters('eventon_core_capabilities',array(
		"manage_eventon"
	));
	
	
	
	$capability_types = array( 'eventon' );

	foreach( $capability_types as $capability_type ) {

		$capabilities[ $capability_type ] = array(

			// Post type
			"publish_{$capability_type}",
			"edit_{$capability_type}",
			"read_{$capability_type}",
			"delete_{$capability_type}",
			"edit_{$capability_type}s",
			"edit_others_{$capability_type}s",
			"publish_{$capability_type}s",
			"read_private_{$capability_type}s",
			"delete_{$capability_type}s",
			"delete_private_{$capability_type}s",
			"delete_published_{$capability_type}s",
			"delete_others_{$capability_type}s",
			"edit_private_{$capability_type}s",
			"edit_published_{$capability_type}s",

			// Terms
			"manage_{$capability_type}_terms",
			"edit_{$capability_type}_terms",
			"delete_{$capability_type}_terms",
			"assign_{$capability_type}_terms"
		);
	}

	return $capabilities;
}


/* Initiate capabilities for eventON */
function eventon_init_caps(){
	global $wp_roles;
	
	if ( class_exists('WP_Roles') )
		if ( ! isset( $wp_roles ) )
			$wp_roles = new WP_Roles();
	
	$capabilities = eventon_get_core_capabilities();
	
	foreach( $capabilities as $cap_group ) {
		foreach( $cap_group as $cap ) {
			$wp_roles->add_cap( 'administrator', $cap );
		}
	}
}


// for style values
function eventon_styles($default, $field, $options){	
	return (!empty($options[$field]))? $options[$field]:$default;
}


?>