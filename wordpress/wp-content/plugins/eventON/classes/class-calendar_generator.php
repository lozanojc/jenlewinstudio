<?php
/**
 * EVO_generator class.
 *
 * @class 		EVO_generator
 * @version		1.11
 * @package		EventON/Classes
 * @category	Class
 * @author 		AJDE
 */

class EVO_generator {
	
	public $google_maps_load, 
		$is_eventcard_open,				
		$evopt1, 
		$evopt2, 
		$evcal_hide_sort;
	
	public $is_upcoming_list = false;
	public $is_eventcard_hide_forcer = false;
	public $_sc_hide_past = false; // shortcode hide past
		
	public $wp_arguments='';
	public $shortcode_args;
	public $filters;
	
	private $lang_array=array();
	
	private $_hide_mult_occur = false;
	private	$events_processed = array();
	
	private $__apply_scheme_SEO = false;
	
	
	/**
	 *	Construction function
	 */
	public function __construct(){
		
		/** set class wide variables **/
		$this->evopt1= get_option('evcal_options_evcal_1');
		$this->evopt2= get_option('evcal_options_evcal_2');		
		
		$this->is_eventcard_open = (!empty($this->evopt1['evo_opencard']) && $this->evopt1['evo_opencard']=='yes')? true:false;
		
		// set reused values
		$this->evcal_hide_sort = (!empty($this->evopt1['evcal_hide_sort']))? $this->evopt1['evcal_hide_sort']:null;
		
		// load google maps api only on frontend
		add_action( 'init', array( $this, 'load_google_maps_api' ) );
		
		$this->google_maps_load = get_option('evcal_gmap_load');		
	}
	
	
	function load_google_maps_api(){
		// google maps loading conditional statement
		if( !empty($this->evopt1['evcal_cal_gmap_api']) && ($this->evopt1['evcal_cal_gmap_api']=='yes') 	){
			if(!empty($this->evopt1['evcal_gmap_disable_section']) && $this->evopt1['evcal_gmap_disable_section']=='complete'){
				
				
				update_option('evcal_gmap_load',false);
				
				wp_enqueue_script( 'eventon_init_gmaps', AJDE_EVCAL_URL. '/assets/js/eventon_init_gmap_blank.js', array('jquery'),'1.0',true ); // load a blank initiate gmap javascript
			}else{
				
				
				update_option('evcal_gmap_load',true);
				wp_register_script('eventon_init_gmaps', AJDE_EVCAL_URL. '/assets/js/eventon_init_gmap.js', array('jquery'),'1.0',true );
				wp_enqueue_script( 'eventon_init_gmaps');
			}
			
		}else {
			
			
			update_option('evcal_gmap_load',true);
			wp_register_script( 'evcal_gmaps', 'https://maps.googleapis.com/maps/api/js?sensor=false', array('jquery'),'1.0',true);
			wp_register_script('eventon_init_gmaps', AJDE_EVCAL_URL. '/assets/js/eventon_init_gmap.js', array('jquery'),'1.0',true );
			wp_enqueue_script( 'evcal_gmaps');
			wp_enqueue_script( 'eventon_init_gmaps');
		}
	}
	
	
	/*
		Process the eventON variable arguments
	*/
	function process_arguments($args='', $own_defaults=false){
		
		$default_arguments = array(			
			'cal_id'=>'1',
			'event_count'=>0,
			'month_incre'=>0,
			'show_upcoming'=>0,
			'number_of_months'=>3,
			'number_of_events'=>5,
			'event_type'=> 'all',
			'event_type_2'=> 'all',
			'focus_start_date_range'=>'',
			'focus_end_date_range'=>'',
			'filters'=>'',
			'fixed_month'=>0,
			'fixed_year'=>0,
			'hide_past'=>'no',
			'hide_mult_occur'=>'no'
		);
		
		if(!empty($args)){
		
			// merge default values of shortcode
			if(!$own_defaults)
				$args = shortcode_atts($default_arguments, $args);

			if(!empty($args['event_type']) && $args['event_type']!='all'){
				$filters['filters'][]=array(
					'filter_type'=>'tax',
					'filter_name'=>'event_type',
					'filter_val'=>$args['event_type']
				);
				$args = array_merge($args,$filters);
			}
			if(!empty($args['event_type_2']) && $args['event_type_2']!='all'){
				$filters['filters'][]=array(
					'filter_type'=>'tax',
					'filter_name'=>'event_type_2',
					'filter_val'=>$args['event_type_2']
				);
				$args = array_merge($args,$filters);
			}

				
			$this->shortcode_args=$args; // set global arguments
		}else{
			$this->shortcode_args=$default_arguments; // set global arguments
			$args = $default_arguments;
		}
		
		// check for possible filters
		$this->filters = (!empty($args['filters']))? 'true':'false';
		
		return $args;
	}
	
	
	/**
	 * function to build the entire event calendar
	 */
	public function eventon_generate_calendar($args){
		global $EventON, $wpdb;		
		
		// extract the variable values 
		$args__ = $this->process_arguments($args);
		extract($args__);
		
		
		
		// action specific to a shortcode variable
		if(has_action('eventon_cal_variable_action'))		
			do_action('eventon_cal_variable_action', $args);			
		
		
		
		// Set hide past value for shortcode hide past event variation
		$this->_sc_hide_past = ($hide_past=='yes')? true:false;
		
		// If settings set to hide calendar
		if($this->evopt1['evcal_cal_hide']=='no'||$this->evopt1['evcal_cal_hide']==''):			
						
			// check if upcoming list calendar view
			if($show_upcoming==1 && $number_of_months>0){				

				$this->is_upcoming_list= true;
				$this->is_eventcard_open = false;
				wp_dequeue_script('eventon_geocal_script');
				
			}
			
			
			$evcal_plugin_url= AJDE_EVCAL_URL;			
			$content = $content_li='';	
			
			// Check for empty month_incre values
			$month_incre = (!empty($month_incre))? $month_incre:0;
			
			// current focus month calculation
			$current_timestamp =  current_time('timestamp');
			$focused_month_num_raw = date('n',$current_timestamp);	
			
			
			// *** GET STARTING month and year 
			if($fixed_month!=0 && $fixed_year!=0){
				$focused_month_num = $fixed_month;
				$focused_year = $fixed_year;
			}else{
			// fixed month year not set
				$focused_month_num = date('n', strtotime($month_incre.' month', $current_timestamp) );
				$focused_year = date('Y', strtotime($month_incre.' month', $current_timestamp) );
			}
			
			
			$cal_version =  get_option('eventon_plugin_version');			
			
			//BASE settings to pass to calendar
			$evcal_gmap_format = ($this->evopt1['evcal_gmap_format']!='')?$this->evopt1['evcal_gmap_format']:'roadmap';	
			$evcal_gmap_zooml = ($this->evopt1['evcal_gmap_zoomlevel']!='')?$this->evopt1['evcal_gmap_zoomlevel']:'12';	
			
			$eventcard_open = ($this->is_eventcard_open)? 'eventcard="1"':null;		
			$evcal_gmap_scrollw = (!empty($this->evopt1['evcal_gmap_scroll']) && $this->evopt1['evcal_gmap_scroll']=='yes')?'false':'true';		
			
			
			// Calendar SHELL
			$content.="<div id='evcal_calendar_".$cal_id."' class='ajde_evcal_calendar' cal_ver='".$cal_version."' mapscroll='".$evcal_gmap_scrollw."' mapformat='".$evcal_gmap_format."' mapzoom='".$evcal_gmap_zooml."'  ".$eventcard_open." cur_m='".$focused_month_num."' cur_y='".$focused_year."'>";
			
			
			// ========================================
			// HEADER with month and year name	- for NONE upcoming list events
			if($show_upcoming==0){			
				
				$cal_header_title = get_eventon_cal_title_month($focused_month_num, $focused_year);
				
				
				$hide_arrows_check = ($this->evopt1['evcal_arrow_hide']=='yes')?"style='display:none'":null;
				$sort_class = ($this->evcal_hide_sort=='yes')?'evcal_nosort':null;
				
				// HTML 
				$content.="<div id='evcal_head' class='calendar_header ".$sort_class."' cur_m='".$focused_month_num."' cur_y='".$focused_year."' ev_cnt='".$event_count."' sort_by='sort_date' filters_on='{$this->filters}'>					
					<a id='evcal_prev' class='evcal_arrows evcal_btn_prev' ".$hide_arrows_check."></a>
					<p id='evcal_cur'> ".$cal_header_title."</p>
					<a id='evcal_next' class='evcal_arrows evcal_btn_next' ".$hide_arrows_check."></a>";	
				
				// (---) Hook for addon
				if(has_action('eventon_calendar_header_content')){
					ob_start();
					do_action('eventon_calendar_header_content', $content);
					$content.= ob_get_clean();
				}
				$content.="<div class='clear'></div></div>";
				
				// SORT BAR
				$content.= $this->eventon_get_cal_sortbar($event_type, $event_type_2);
			}
			
						
			
			// upcoming events display format
			// check repeating months
			$number_of_months = ($show_upcoming==1)?$number_of_months:1;
			$defined_date_ranges = ( empty($focus_start_date_range) && empty($focus_end_date_range) )?1:0;
			
			// -- HIDE or show multiple occurance of events in upcoming list
			$this->_hide_mult_occur= ($this->evopt1['evo_hide_mult_occur']=='yes' || $hide_mult_occur=='yes')?true:false;
			
			// for each month
			for($x=0; $x<$number_of_months; $x++){				
				
				
				// check if date ranges present
				if( $defined_date_ranges==1){	
				
					// default start end date range -- for month view
					$get_new_monthyear = eventon_get_new_monthyear($focused_month_num, $focused_year,$x);
					$active_month_name = eventon_returnmonth_name_by_num($get_new_monthyear['month']);
					
					// check settings to see if year should be shown or not
					$active_year = ($this->evopt1['evo_show_yr_ulist']=='yes')?
						$get_new_monthyear['year']:null;
					
					$focus_start_date_range = mktime( 0,0,0,$get_new_monthyear['month'],1,$get_new_monthyear['year'] );
					$time_string = $get_new_monthyear['year'].'-'.$get_new_monthyear['month'].'-1';		
					$focus_end_date_range = mktime(23,59,59,($get_new_monthyear['month']),(date('t',(strtotime($time_string) ))), ($get_new_monthyear['year']));
				}
				
				
				
				// generate events within the focused date range
				$eve_args = array(
					'focus_start_date_range'=>$focus_start_date_range,
					'focus_end_date_range'=>$focus_end_date_range,
					'sort_by'=>'sort_date', // by default sort events by start date					
					'event_count'=>$event_count,
					'ev_type'=>$event_type,
					'ev_type_2'=>$event_type_2,
					'filters'=>$filters,
					'number_months'=>$number_of_months // to determine empty label 
				);
				$content_li = $this->eventon_generate_events($eve_args);	
				
				
				// Construct months exterior 
				if($show_upcoming==1 && $content_li != 'empty'){
					$content.= "<div class='evcal_month_line'><p>".$active_month_name.' '.$active_year."</p></div>";
				}		
				if($content_li != 'empty'){
					// ## Eventon Calendar events list
					$content.="<div id='evcal_list' class='eventon_events_list'>";
				}
				
				if($content_li != 'empty'){		
					$content.=$content_li;
				}
				if($content_li != 'empty'){
					$content.="</div>"; 
				}
				
				// empty months with upcoming events set to hidden
				if($number_of_months==1 && $content_li=='empty'){
					$content.="<div id='evcal_list' class='eventon_events_list'><div class='eventon_list_event'><p class='no_events'>".$this->lang_array['no_event']."</p></div></div>";
				}
			}
			
			
			$content.="<div class='clear'></div></div>";
				
			return  $content;			
		
		endif;
	}
	
	
	
	/**
	 * MAIN function to generate individual events.
	 *
	 * @access public
	 * @return void
	 /*
		possible values
		array(
			'focus_start_date_range'
			'focus_end_date_range'
			'sort_by'=>sort_date,sort_title,sort_color,event_type, event_type_2
			'ev_type'
			'ev_type_2'
			'event_count'
			'number_months'
			'filters'
		)
	*/	 
	public function eventon_generate_events($args){
		
		global $EventON;
		
		$this->lang_array['no_event'] = (!empty($this->evopt2['evcal_lang_noeve']))? $this->evopt2['evcal_lang_noeve']:"No Events";
		
		
		// Default array values for event generation
		$defaults = array(
			'sort_by'=>'sort_date',
			'event_count'=>0,
			'ev_type'=>'all',
			'ev_type_2'=>'all',
		);
		$ecv = array_merge($defaults,$args);//event calendar values = ecv
		
		
		// ===========================
		// WPQUery Arguments
		$wp_arguments = array (
			'post_type' 		=> 'ajde_events' ,
			'posts_per_page'	=>-1 ,
			'order'				=>'ASC',					
		);
		
		// apply other filters to wp argument
		$wp_arguments = $this->apply_evo_filters_to_wp_argument($wp_arguments, $ecv['filters'],$ecv['ev_type'],$ecv['ev_type_2']);
		
		
		//print_r($wp_arguments);
				
		// -----------------------------
		// hook for addons
		if(has_filter('eventon_wp_query_args')){
			$wp_arguments = apply_filters('eventon_wp_query_args',$wp_arguments);
		}
		
		$this->wp_arguments = $wp_arguments;
		
		// ========================		
		$event_list_array = $this->wp_query_event_cycle($wp_arguments, $ecv['focus_start_date_range'], $ecv['focus_end_date_range']);
		
		
		//print_r($wp_arguments);
		
		
		
		/*
			primary sorting mechanism
		*/
		if(is_array($event_list_array)){			
			switch($ecv['sort_by']){
				case has_action("eventon_event_sorting_{$ecv['sort_by']}"):
					do_action("eventon_event_sorting_{$ecv['sort_by']}", $event_list_array);
					
				break;
				case 'sort_date':
					usort($event_list_array, 'cmp_esort_startdate' );
				break;case 'sort_title':
					usort($event_list_array, 'cmp_esort_title' );
				break; case 'sort_color':
					usort($event_list_array, 'cmp_esort_color' );
				break;
				
			}
		}
		//print_r($event_list_array);
		
		$months_event_array = $this->generate_event_data( apply_filters('eventon_sorted_dates',$event_list_array), $ecv['focus_start_date_range']);
		//print_r($months_event_array);
		
		
		// ========================
		// RETURN VALUES
		$content_li='';
		if( is_array($months_event_array) && count($months_event_array)>0){
			if($ecv['event_count']==0 ){
				foreach($months_event_array as $event){
					$content_li.= $event['content'];
				}
				
			}else if($ecv['event_count']>0){
				for($x=0; $x<$ecv['event_count']; $x++){
					$content_li.= $months_event_array[$x]['content'];
				}
			}
		}else{	
			$evcal_hide_empty_um = $this->evopt1['evcal_hide_empty_um']; // settings val - hide empty upcoming months
			
			// if its upcoming events list, no events 
			if(!empty($evcal_hide_empty_um) && $evcal_hide_empty_um=='yes' && !empty($ecv['number_months']) && $ecv['number_months']>0){
				$content_li = "empty";
				
			}else{
				$content_li = "<div class='eventon_list_event'><p class='no_events'>".$this->lang_array['no_event']."</p></div>";
			}
			
		}
		return $content_li;
		
	}// END evcal_generate_events()
	
	
	
	/**
	 * WP_Query function to generate relavent events for a given month
	 * return events list within start - end date range for WP_Query arg.
	 * return array
	 */
	public function wp_query_event_cycle($wp_arguments, $focus_month_beg_range, $focus_month_end_range){
		
		//echo $focus_month_beg_range.' '. $focus_month_end_range.'ff';
		
		
		$event_list_array='';
		$wp_arguments= (!empty($wp_arguments))?$wp_arguments: $this->wp_arguments;
		//print_r($wp_arguments);
		
		
		// check if multiple occurance of events b/w months allowed
		$__run_occurance_check = ($this->is_upcoming_list && $this->_hide_mult_occur)? true:false;
		
		/** RUN through all events **/
		$events = new WP_Query( $wp_arguments);
		if ( $events->have_posts() ) :
			
			// Define option values for the front-end
			$cur_time_basis = (!empty($this->evopt1['evcal_past_ev']) )? $this->evopt1['evcal_past_ev'] : null;
			$evcal_cal_hide_past= ($this->_sc_hide_past)? 'yes': $this->evopt1['evcal_cal_hide_past'];
			
			//date_default_timezone_set($tzstring);	
			if($evcal_cal_hide_past=='yes' && $cur_time_basis=='today_date'){
				// this is based on local time
				$current_time = strtotime( date_i18n("m/j/Y") );	
			}else{
				// this is based on UTC time zone
				$current_time = current_time('timestamp');		
			}
			
						
			// Pre variables
			$content_li='';
			
			while( $events->have_posts()): $events->the_post();
				
				$p_id = get_the_ID();
				$ev_vals = get_post_custom($p_id);
				
				$is_recurring_event = (!empty($ev_vals['evcal_repeat']) )? $ev_vals['evcal_repeat'][0]: null;
				//$__is_all_day_event = (!empty($ev_vals['evcal_allday']) && $ev_vals['evcal_allday'][0]=='yes')?true:false;
				
				// initial event start and end UNIX
				$row_start = (!empty($ev_vals['evcal_srow']))? 
					$ev_vals['evcal_srow'][0] :null;
				$row_end = ( !empty($ev_vals['evcal_erow']) )? 
					$ev_vals['evcal_erow'][0]:$row_start;
				
				$evcal_event_color_n= (!empty($ev_vals['evcal_event_color_n']))?$ev_vals['evcal_event_color_n'][0]:'0';
				
				// check for recurring event 
				if($is_recurring_event=='yes'){
					$frequency = $ev_vals['evcal_rep_freq'][0];
					$repeat_gap_num = $ev_vals['evcal_rep_gap'][0];
					$repeat_num = (int)$ev_vals['evcal_rep_num'][0];
					
					
					// each repeating instance	
					$monthly_row_start = $row_start;
					for($x=0; $x<=($repeat_num); $x++){
												
						$repeat_multiplier = ((int)$repeat_gap_num) * $x;
						//$multiply_term = '+'.$repeat_multiplier.' '.$term;
						
						// Get repeat terms for different frequencies
						switch($frequency){
							// Additional frequency filters
							case has_filter("eventon_event_frequency_{$frequency}"):
								$terms = apply_filters("eventon_event_frequency_{$frequency}", $repeat_multiplier);								
								$term = $terms['term'];
								$term_ar = $terms['term_ar'];
							break;
							case 'yearly':
								$term = 'year';	$term_ar = 'ry';
							break;
							case 'monthly':
								$term = 'month';	$term_ar = 'rm';
							break; 
							case 'weekly':
								$term = 'week';	$term_ar = 'rw';
							break;							
							default: $term = $term_ar = ''; break;
						}
						
						
						$E_start_unix = strtotime('+'.$repeat_multiplier.' '.$term, $row_start);
						$E_end_unix = strtotime('+'.$repeat_multiplier.' '.$term, $row_end);
									
						
						$fe = eventon_is_future_event($current_time, $E_end_unix, $evcal_cal_hide_past);
						$me = eventon_is_event_in_daterange($E_start_unix,$E_end_unix, $focus_month_beg_range,$focus_month_end_range);
						
						if($fe && $me){
							$event_list_array[] = array(
								'event_id' => $p_id,
								'event_start_unix'=>$E_start_unix,
								'event_end_unix'=>$E_end_unix,
								'event_title'=>get_the_title(),
								'event_color'=>$evcal_event_color_n,
								'event_type'=>$term_ar
							);
						}						
					}	
					
				}else{
				// Non recurring event
					$fe = eventon_is_future_event($current_time, $row_end, $evcal_cal_hide_past);
					$me = eventon_is_event_in_daterange($row_start,$row_end, $focus_month_beg_range,$focus_month_end_range);
					
					if($fe && $me){
						
						if($__run_occurance_check && !in_array($p_id, $this->events_processed) ||!$__run_occurance_check){
						
							$event_list_array[] = array(
								'event_id' => $p_id,
								'event_start_unix'=>$row_start,
								'event_end_unix'=>$row_end,
								'event_title'=>get_the_title(),
								'event_color'=>$evcal_event_color_n,
								'event_type'=>'nr'
							);	
								
							$this->events_processed[]=$p_id;
						}
					}		
				}
				
				
			endwhile;
			
			
		endif;
		wp_reset_query();
		
		return $event_list_array;
	}
	
	
	/**
	 *	output single event data
	 */
	public function get_single_event_data($event_id){
		
		//echo 'ff'.$this->is_eventcard_hide_forcer;
		$this->is_eventcard_open= ($this->is_eventcard_hide_forcer)?false:true;
		
		$emv = get_post_custom($event_id);
		
		$event_array[] = array(
			'event_id' => $event_id,
			'event_start_unix'=>$emv['evcal_srow'][0],
			'event_end_unix'=>$emv['evcal_erow'][0],
			'event_title'=>get_the_title($event_id),
			'event_color'=>$emv['evcal_event_color_n'][0],
			'event_type'=>'nr'
		);
		
		$month_int = date('n', time() );
		
		return $this->generate_event_data($event_array, '', $month_int);
	}
	
	
	/**
	 * GENERATE individual event data
	 */
	public function generate_event_data($event_list_array, $focus_month_beg_range='', $FOCUS_month_int='', $FOCUS_year_int=''){
		
		
		$months_event_array='';
		
		// Initial variables
		$wp_time_format = get_option('time_format');
		$default_event_color = (!empty($this->evopt1['evcal_hexcode']))?$this->evopt1['evcal_hexcode']:'#ffa800';
		
				
		// EVENT CARD open by default variables		
		$eventcard_styles = ($this->is_eventcard_open)? null:"style='display:none'";
		$eventcard_script_class = ($this->is_eventcard_open)? "gmaponload":null;
		
		
		// GET: Event Type's custom names
		$evt_name = (!empty($this->evopt1['evcal_eventt']))?$this->evopt1['evcal_eventt']:'Event Type';
		$evt_name2 = (!empty($this->evopt1['evcal_eventt2']))?$this->evopt1['evcal_eventt2']:'Event Type 2';
		
		
		$CURRENT_month_INT = (!empty($FOCUS_month_int))?$FOCUS_month_int: date('n', $focus_month_beg_range ); // 
		
		
		// GET EventTop fields - v2.1.17
		$eventop_fields = (!empty($this->evopt1['evcal_top_fields']))?$this->evopt1['evcal_top_fields']:null;
		
		// EACH EVENT
		if(is_array($event_list_array) ){
		foreach($event_list_array as $event):
			
			$event_id = $event['event_id'];
			$event_start_unix = $event['event_start_unix'];
			$event_end_unix = $event['event_end_unix'];
			$event_type = $event['event_type'];
			
			$event = get_post($event_id);
			$ev_vals = get_post_custom($event_id);
			
			
			// define variables
			$ev_other_data = $ev_other_data_top = $html_event_type_info= $_event_date_HTML=$_event_datarow='';	
			$_is_end_date=true;
			
			$DATE_start_val=eventon_get_formatted_time($event_start_unix);
			if(empty($event_end_unix)){
				$_is_end_date=false;
				$DATE_end_val= $DATE_start_val;
			}else{
				$DATE_end_val=eventon_get_formatted_time($event_end_unix);
			}
			
			// Unique ID generation
			$unique_varied_id = 'evc'.$event_start_unix.(uniqid()).$event_id;
			$unique_id = 'evc_'.$event_start_unix.$event_id;
			
			// All day event variables
			$_is_allday = (!empty($ev_vals['evcal_allday']) && $ev_vals['evcal_allday'][0]=='yes')? true:false;
			$_hide_endtime = (!empty($ev_vals['evo_hide_endtime']) && $ev_vals['evo_hide_endtime'][0]=='yes')? true:false;
			$evcal_lang_allday = (!empty($this->evopt2['evcal_lang_allday']) )?
					$this->evopt2['evcal_lang_allday']: 'All Day';
			
			/*
				evo_hide_endtime
				NOTE: if its set to hide end time, meaning end time and date would be empty on wp-admin, which will fall into same start end month category.
			*/
				
			/** EVENT TYPE = start and end in SAME MONTH **/
			if($DATE_start_val['n'] == $DATE_end_val['n']){
				
				/** EVENT TYPE = start and end in SAME DAY **/
				if($DATE_start_val['j'] == $DATE_end_val['j']){
					
					// check all days event
					if($_is_allday){					
						$__from_to ="<em class='evcal_alldayevent_text'>(".$evcal_lang_allday.": ".$DATE_start_val['l'].")</em>";
						$__prettytime = $evcal_lang_allday.' ('.$DATE_start_val['l'].')';
					}else{
						
						$__from_to = ($_hide_endtime)?
							date($wp_time_format,($event_start_unix)):
							date($wp_time_format,($event_start_unix)).' - '. date($wp_time_format,($event_end_unix));
						
						$__prettytime ='('.$DATE_start_val['l'].') '.$__from_to;
					}
					
					$_event_date_HTML = array(
						'html_date'=>$DATE_start_val['j'],
						'html_fromto'=>$__from_to,
						'html_prettytime'=> $__prettytime,
						'class_daylength'=>"sin_val"
					);	
					
				}else{
					// different start and end date
					
					// check all days event
					if($_is_allday){
						$__from_to ="<em class='evcal_alldayevent_text'>(".$evcal_lang_allday.")</em>";
						$__prettytime = $DATE_start_val['j'].' ('.$DATE_start_val['l'].') - '.$DATE_end_val['j'].' ('.$DATE_end_val['l'].')';
					}else{
						$__from_to = date($wp_time_format,($event_start_unix)).' - '.date($wp_time_format,($event_end_unix)). ' ('.$DATE_end_val['j'].')';
						$__prettytime =$DATE_start_val['j'].' ('.$DATE_start_val['l'].') '.date($wp_time_format,($event_start_unix)).' - '.$DATE_end_val['j'].' ('.$DATE_end_val['l'].') '.date($wp_time_format,($event_end_unix));
					}
					
					$_event_date_HTML = array(							
						'html_date'=>$DATE_start_val['j'].'<span> - '.$DATE_end_val['j'].'</span>',
						'html_fromto'=>$__from_to,
						'html_prettytime'=> $__prettytime,
						'class_daylength'=>"mul_val"
					);	
				}					
			}else{
				/** EVENT TYPE = different start and end months **/
				
				/** EVENT TYPE = start month is before current month **/
				if($CURRENT_month_INT != $DATE_start_val['n']){
					// check all days event
					if($_is_allday){
						$__from_to ="<em class='evcal_alldayevent_text'>(".$evcal_lang_allday.")</em>";						
					}else{
						$__from_to = 
							'('.$DATE_start_val['F'].' '.$DATE_start_val['j'].') '.date($wp_time_format,($event_start_unix)).' - ('.$DATE_end_val['F'].' '.$DATE_end_val['j'].') '.date($wp_time_format,($event_end_unix));
					}
										
											
				}else{
					/** EVENT TYPE = start month is current month **/
					// check all days event
					if($_is_allday){
						$__from_to ="<em class='evcal_alldayevent_text'>(".$evcal_lang_allday.")</em>";						
					}else{
						$__from_to =
							date($wp_time_format,($event_start_unix)).' - ('.$DATE_end_val['F'].' '.$DATE_end_val['j'].') '.date($wp_time_format,($event_end_unix));	
					}
				}
				
				
				// check all days event
				if($_is_allday){
					$__prettytime = $DATE_start_val['F'].' '.$DATE_start_val['j'].' ('.$DATE_start_val['l'].') - '.$DATE_end_val['F'].' '.$DATE_end_val['j'].' ('.$DATE_end_val['l'].')';
				}else{
					$__prettytime = 
						$DATE_start_val['F'].' '.$DATE_start_val['j'].' ('.$DATE_start_val['l'].') '.date($wp_time_format,($event_start_unix)).' - '.$DATE_end_val['F'].' '.$DATE_end_val['j'].' ('.$DATE_end_val['l'].') '.date($wp_time_format,($event_end_unix));	
				}
				
				
				$_event_date_HTML = apply_filters('evo_eventcard_dif_SEM', array(
					'html_date'=>$DATE_start_val['j'].'<span> - '.$DATE_end_val['j'].'</span>',
					'html_fromto'=>$__from_to,
					'html_prettytime'=>$__prettytime,
					'class_daylength'=>"mul_val"
				));
			}
			
			
		
			// (---) hook for addons
			if(has_filter('eventon_eventcard_date_html'))
				apply_filters('eventon_eventcard_date_html', $_event_date_HTML, $event_id);
		
			
			
			// EVENT FEATURES IMAGE
			$img_id =get_post_thumbnail_id($event_id);
			if($img_id!=''){				
				$img_src = wp_get_attachment_image_src($img_id,'full');
				$ev_img_code = "<div imgheight='".$img_src[2]."' imgwidth='".$img_src[1]."' class='evcal_evdata_img evo_metarow_fimg' style='background-image: url(".$img_src[0].")'></div>";				
				$_event_datarow['fimage'] = $ev_img_code;
			}
			
			// EVENT DESCRIPTION
			$evcal_event_content =apply_filters('the_content', $event->post_content);
			
			if(!empty($evcal_event_content) ){
				$event_full_description = $evcal_event_content;
			}else{
				// event description compatibility from older versions.
				$event_full_description =(!empty($ev_vals['evcal_description']))?$ev_vals['evcal_description'][0]:null;
			}			
			if(!empty($event_full_description) ){
				
				// check if character length of description is longer than X size
				if( $this->evopt1['evo_morelass']!='yes' && (strlen($event_full_description) )>600 ){
					$more_code = "<div class='eventon_details_shading_bot'>
								<p class='eventon_shad_p' content='less'><span class='ev_more_text' txt='".eventon_get_custom_language($this->evopt2, 'evcal_lang_less','less')."'>".eventon_get_custom_language($this->evopt2, 'evcal_lang_more','more')."</span><span class='ev_more_arrow'></span></p>
							</div>";
					$evo_more_active_class = 'shorter_desc';
				}else{$more_code=''; $evo_more_active_class = '';}
				
				
				$except = $event->post_excerpt;
				$event_excerpt = eventon_get_event_excerpt($event_full_description, 30, $except);
				
				
				$_event_datarow['description'] ="<div class='evcal_evdata_row bordb evcal_event_details'>
						".$event_excerpt."
						<span class='evcal_evdata_icons evcalicon_1'></span>
						<div class='evcal_evdata_cell ".$evo_more_active_class."'>".$more_code."<div class='eventon_full_description'>
								<h3 class='padb5 evo_h3'>".eventon_get_custom_language($this->evopt2, 'evcal_evcard_details','Event Details')."</h3><div class='eventon_desc_in' itemprop='description'>
								".apply_filters('the_content',$event_full_description)."</div><div class='clear'></div>
							</div>
						</div>
					</div>";
			}
			
			
			// EVENT TIME
			$_event_datarow['time'] =  
				"<div class='evcal_evdata_row bordb evcal_evrow_sm evo_metarow_time'>
					<span class='evcal_evdata_icons evcalicon_6'></span>
					<div class='evcal_evdata_cell'>							
						<h3 class='evo_h3'>".eventon_get_custom_language($this->evopt2, 'evcal_lang_time','Time')."</h3><p>".$_event_date_HTML['html_prettytime']."</p>
					</div>
				</div>";	
			
			
			// EVENT LOCATION
			$lonlat = (!empty($ev_vals['evcal_lat']) && !empty($ev_vals['evcal_lon']) )?
					'latlon="1" latlng="'.$ev_vals['evcal_lat'][0].','.$ev_vals['evcal_lon'][0].'" ': null;
			
			if(!empty($ev_vals['evcal_location'])){				
				
				$_event_datarow['location']=
					"<div class='evcal_evdata_row bordb evcal_evrow_sm evo_metarow_location'>
						<span class='evcal_evdata_icons evcalicon_7'></span>
						<div class='evcal_evdata_cell'>							
							<h3 class='evo_h3'>".eventon_get_custom_language($this->evopt2, 'evcal_lang_location','Location')."</h3><p>".$ev_vals['evcal_location'][0]."</p>
						</div>
					</div>";
			}
			
			
			// GOOGLE maps			
			if( ($this->google_maps_load) && !empty($ev_vals['evcal_location']) && ($ev_vals['evcal_gmap_gen'][0]=='yes') ){
				$_event_datarow['gmap']="<div class='evcal_gmaps bordb evo_metarow_gmap' id='".$unique_varied_id."_gmap'></div>";
				$gmap_api_status='';
			}else{	$gmap_api_status = 'gmap_status="null"';	}
			
			
			
			// EVENT BRITE
			// check if eventbrite actually used in this event
			if(!empty($ev_vals['evcal_eventb_data_set'] ) && $ev_vals['evcal_eventb_data_set'][0]=='yes'){			
				// Event brite capacity
				if( 
					!empty($ev_vals['evcal_eventb_tprice'] ) &&				
					!empty($ev_vals['evcal_eventb_url'] ) )
				{					
					
					// GET Custom language text
					$evcal_tx_1 = eventon_get_custom_language($this->evopt2, 'evcal_evcard_tix2','Ticket for the event');
					$evcal_tx_2 = eventon_get_custom_language($this->evopt2, 'evcal_evcard_btn2','Buy Now');
					$evcal_tx_3 = eventon_get_custom_language($this->evopt2, 'evcal_evcard_cap','Event Capacity');
					
					
					// EVENTBRITE with event capacity
					if(!empty($ev_vals['evcal_eventb_capacity'] )){
						$_event_datarow['eventbrite'] = "<div class='bordb'>
						<div class='evcal_col50'>
							<div class='evcal_evdata_row bordr '>
								<span class='evcal_evdata_icons evcalicon_3'></span>
								<div class='evcal_evdata_cell'>
									<h2 class='bash'>".$ev_vals['evcal_eventb_tprice'][0]."</h2>
									<p>".$evcal_tx_1."</p>
									<a href='".$ev_vals['evcal_eventb_url'][0]."' class='evcal_btn'>".$evcal_tx_2."</a>
								</div>
							</div>
						</div><div class='evcal_col50'>
							<div class='evcal_evdata_row'>
								<span class='evcal_evdata_icons evcalicon_4'></span>
								<div class='evcal_evdata_cell'>
									<h2 class='bash'>".$ev_vals['evcal_eventb_capacity'][0]."</h2>
									<p>".$evcal_tx_3."</p>
								</div>
							</div>
						</div><div class='clear'></div>
						</div>";
					}else{	
						// No event capacity
						$_event_datarow['eventbrite']= "<div class='bordb'>
							<div class='evcal_evdata_row bordr '>
								<span class='evcal_evdata_icons evcalicon_3'></span>
								<div class='evcal_evdata_cell'>
									<h2 class='bash'>".$ev_vals['evcal_eventb_tprice'][0]."</h2>
									<p>".$evcal_tx_1."</p>
									<a href='".$ev_vals['evcal_eventb_url'][0]."' class='evcal_btn'>".$evcal_tx_2."</a>
								</div>
							</div>
						<div class='clear'></div>
						</div>";
					}
				}				
			}
			
			
			// MEETUP & Learn More Link
			// check for learn more link
			if(!empty($ev_vals['evcal_lmlink'] ) ){
								
				// target
				$target = (!empty($ev_vals['evcal_lmlink_target'])  && $ev_vals['evcal_lmlink_target'][0]=='yes')? 'target="_blank"':null;
				$_event_datarow['learn_more'] = "<div class='evcal_evdata_row bordb evcal_evrow_sm'>
					<span class='evcal_evdata_icons evcalicon_5'></span>
					<div class='evcal_evdata_cell'>
						<p style='padding-top:4px'>".eventon_get_custom_language($this->evopt2, 'evcal_evcard_learnmore','Learn more about this event')." <a ".$target." href='".$ev_vals['evcal_lmlink'][0]."'>".eventon_get_custom_language($this->evopt2, 'evcal_evcard_learnmore2','Learn More')."</a></p>
					</div>
				</div>";
			}
			
				
			
			// PAYPAL Code
			if(!empty($ev_vals['evcal_paypal_link'][0]) && $this->evopt1['evcal_paypal_pay']=='yes'){
							
				$_event_datarow['paypal'] = "<div class='evcal_evdata_row bordb'>
						<span class='evcal_evdata_icons evcalicon_3'></span>
						<div class='evcal_evdata_cell'>
							<p>".eventon_get_custom_language($this->evopt2, 'evcal_evcard_tix1','Buy ticket via Paypal')."</p>
							<a href='".$ev_vals['evcal_paypal_link'][0]."' class='evcal_btn'>".eventon_get_custom_language($this->evopt2, 'evcal_evcard_btn1','Buy Now')."</a>
						</div>
					</div>";
			}
			
			
			// Event Organizer
			if(!empty($ev_vals['evcal_organizer'] )){
				$evcal_evcard_org = eventon_get_custom_language($this->evopt2, 'evcal_evcard_org','Organizer');
				$_event_datarow['organizer']=
					"<div class='evcal_evdata_row bordb evcal_evrow_sm evo_metarow_organizer'>
						<span class='evcal_evdata_icons evcalicon_2'></span>
						<div class='evcal_evdata_cell'>							
							<h3 class='evo_h3'>".$evcal_evcard_org."</h3><p>".$ev_vals['evcal_organizer'][0]."</p>
						</div>
					</div>";
			}
			
			
			// Custom fields
			for($x =1; $x<3; $x++){
				if( !empty($this->evopt1['evcal_ec_f'.$x.'a1']) && !empty($this->evopt1['evcal_ec_f'.$x.'a2'])	&& !empty($ev_vals["_evcal_ec_f".$x."a1_cus"])	){
					$img = wp_get_attachment_image_src($this->evopt1['evcal_ec_f'.$x.'a2'], 'full');
					
					$_event_datarow['evcal_ec_f'.$x.'a1']=
					"<div class='evcal_evdata_row bordb evcal_evrow_sm '>
						<span class='evcal_evdata_custometa_icons'><img src='".$img[0]."'/></span>
						<div class='evcal_evdata_cell'>							
							<h3 class='evo_h3'>".$this->evopt1['evcal_ec_f'.$x.'a1']."</h3><p>".$ev_vals["_evcal_ec_f".$x."a1_cus"][0]."</p>
						</div>
					</div>";
				}
			}
			
			
			// =======================
			/** CONSTRUCT the EVENT CARD	 **/		
			if(!empty($_event_datarow) && count($_event_datarow)>0){
								
				$__eventcard_data = implode('',$_event_datarow);
				
				ob_start();
			
				echo "<div class='event_description evcal_eventcard' ".$eventcard_styles.">";
				echo $__eventcard_data;
				
				// (---) hook for addons
				if(has_action('eventon_eventcard_additions')){
					do_action('eventon_eventcard_additions', $event_id);
				}
			
				echo "<div class='evcal_evdata_row bordb evcal_close'><p>".eventon_get_custom_language($this->evopt2, 'evcal_lang_close','Close')."</p></div></div>";
				
				$html_event_detail_card = ob_get_clean();
				
				
			}else{
				$html_event_detail_card=null;
			}
			
			
			
			/** Trigger attributes **/
			$event_description_trigger = (!empty($html_event_detail_card))? "desc_trig":null;
			$gmap_trigger = ($ev_vals['evcal_gmap_gen'][0]=='yes')? 'gmtrig="1"':'gmtrig="0"';
			
			
			//event color			
			$event_color = (!empty($ev_vals['evcal_event_color']) )?
				(($ev_vals["evcal_event_color"][0][0] == '#')?
						$ev_vals["evcal_event_color"][0]:
						'#'.$ev_vals["evcal_event_color"][0] )
					: $default_event_color;
				
			//event type taxonomies #1
			$evcal_terms = wp_get_post_terms($event_id,'event_type');
				if($evcal_terms){					
					$html_event_type_info .="<span class='evcal_event_types'><em><i>".$evt_name.":</i></em>";
					foreach($evcal_terms as $termA):
						$html_event_type_info .="<em>".$termA->name."</em>";
					endforeach; $html_event_type_info .="</span>";
				}
			
			
			
			// event ex link
			$exlink_option = (!empty($ev_vals['_evcal_exlink_option']) )?$ev_vals['_evcal_exlink_option'][0]:1;
			$event_permalink = get_permalink($event_id);
			
			$href = (!empty($ev_vals['evcal_exlink']) && $exlink_option!='1' )? 
				'exlk="1" href="'.$ev_vals['evcal_exlink'][0].'"': 'exlk="0"';
			// target
			$target_ex = (!empty($ev_vals['_evcal_exlink_target'])  && $ev_vals['_evcal_exlink_target'][0]=='yes')?
				'target="_blank"':null;
			
			
			
			// EVENT LOCATION
			if(!empty($ev_vals['evcal_location'])){
				$event_location_variables = ((!empty($lonlat))? $lonlat:null ). ' add_str="'.$ev_vals['evcal_location'][0].'" ';
				
				$__scheme_data_location = '
					<item style="display:none" itemprop="location" itemscope itemtype="http://schema.org/Place">
						<span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
							<item itemprop="streetAddress">'.$ev_vals['evcal_location'][0].'</item>
						</span>
					</item>';
					
				$ev_location =				
					'<em class="evcal_location" '.( (!empty($lonlat))? $lonlat:null ).' add_str="'.$ev_vals['evcal_location'][0].'">'.$ev_vals['evcal_location'][0].'</em>';
			}else{
				$ev_location = $event_location_variables= $__scheme_data_location= null;
			}
			
			
			/* -------------------
			// 	HTML		
			// 	EventTop - building of the eventTop section
			-------------*/
			$eventtop_html='';
			$eventtop_html[]="<p class='evcal_cblock' style='bgcolor='".$event_color."' smon='".$DATE_start_val['F']."' syr='".$DATE_start_val['Y']."'><em class='evo_date'>".$_event_date_HTML['html_date'].'</em>';
			
			// CHECK for event top fields array
			$eventop_fields_ = (is_array($eventop_fields) )? true:false;
			
			if($eventop_fields_ && in_array('monthname',$eventop_fields))
				$eventtop_html[]="<em class='evo_month' mo='".$DATE_start_val['M']."'>".$DATE_start_val['M']."</em>";
			
			if($eventop_fields_ && in_array('dayname',$eventop_fields))
				$eventtop_html[]="<em class='evo_day' >".$DATE_start_val['D']."</em>";
			
			$eventtop_html[]="<em class='clear'></em></p>";
			
						
			$eventtop_html[]= "<p class='evcal_desc' {$event_location_variables}><span class='evcal_desc2 evcal_event_title' itemprop='name'>".$event->post_title."</span>";
			
			$eventtop_html[]= "<span class='evcal_desc_info' >";
			
			// time
			if($eventop_fields_ && in_array('time',$eventop_fields))
				$eventtop_html[]= "<em class='evcal_time'>".$_event_date_HTML['html_fromto']."</em> ";
			
			// location
			if($eventop_fields_ && in_array('location',$eventop_fields))
				$eventtop_html[]= $ev_location;
			
			$eventtop_html[]= "</span><span class='evcal_desc3'>";
			
			if($eventop_fields_ && in_array('organizer',$eventop_fields) && !empty($ev_vals['evcal_organizer']))
				$eventtop_html[]= "<em class='evcal_oganizer'><i>".$evcal_evcard_org.':</i> '.$ev_vals['evcal_organizer'][0]."</em> ";
				
			if($eventop_fields_ && in_array('eventtype',$eventop_fields))
				$eventtop_html[]= $html_event_type_info;
			
			$eventtop_html[]= "</p>";
			
			
			// --
			
			
			// Combine the event top individual sections
			$html_info_line = implode('', $eventtop_html);
			
			
			
			// (---) hook for addons
			if(has_filter('eventon_event_cal_short_info_line') ){
				$html_info_line = apply_filters('eventon_event_cal_short_info_line', $html_info_line);
			}
			
			
			// SCHEME SEO
			$__scheme_data = 
				'<div class="evo_event_schema" style="display:none" >
				<item href="'.$event_permalink.'" itemprop="url"></item>				
				<time itemprop="startDate" datetime="'.$DATE_start_val['Y'].'-'.$DATE_start_val['n'].'-'.$DATE_start_val['j'].'"></time>'.
				$__scheme_data_location.
				'</div>'
			
			;
			
			
			// ## Eventon Calendar events list -- single event
			$event_html_code="<div class='eventon_list_event' itemscope itemtype='http://schema.org/Event'>".$__scheme_data."
			<a id='".$unique_id."' ".$href." ".$target_ex." style='border-left-color: ".$event_color."' class='evcal_list_a ".$event_description_trigger." ".$_event_date_HTML['class_daylength']." ".(($event_type!='nr')?'event_repeat':null)." " . $eventcard_script_class ."' ".$gmap_trigger." ".(!empty($gmap_api_status)?$gmap_api_status:null)." ux_val='{$exlink_option}'>{$html_info_line}</a>".$html_event_detail_card."<div class='clear'></div></div>";	
			
			
			
			// prepare output
			$months_event_array[]=array(
				'srow'=>$event_start_unix,
				'erow'=>$event_end_unix,
				'content'=>$event_html_code
			);
			
			
		endforeach;
		
		}else{
			$months_event_array;
		}
		
		return $months_event_array;
	}
	
	/**
	 *	 Add other filters to wp_query argument
	 */
	public function apply_evo_filters_to_wp_argument($wp_arguments, $filters='', $ev_type='', $ev_type_2=''){
		// -----------------------------
		// FILTERING events	
		
		// values from filtering events
		if(!empty($filters)){			
			
			// build out the proper format for filtering with WP_Query
			$cnt =0;
			$filter_tax['relation']='AND';
			foreach($filters as $filter){
				if($filter['filter_type']=='tax'){					
					
					$filter_val = explode(',', $filter['filter_val']);
					$filter_tax[] = array(
						'taxonomy'=>$filter['filter_name'],
						'field'=>'id',
						'terms'=>$filter_val						
					);
					$cnt++;
				}else{				
					$filter_meta[] = array(
						'key'=>$filter['filter_name'],				
						'value'=>$filter['filter_val'],				
					);
				}				
			}
			
			
			if(!empty($filter_tax)){
				
				// for multiple taxonomy filtering
				if($cnt>1){					
					$filters_tax_wp_argument = array(
						'tax_query'=>$filter_tax
					);
				}else{
					$filters_tax_wp_argument = array(
						'tax_query'=>$filter_tax
					);
				}
				$wp_arguments = array_merge($wp_arguments, $filters_tax_wp_argument);
			}
			if(!empty($filter_meta)){
				$filters_meta_wp_argument = array(
					'meta_query'=>$filter_meta
				);
				$wp_arguments = array_merge($wp_arguments, $filters_meta_wp_argument);
			}		
		}else{
			
			
			// to support event_type and event_type_2 variables from older version
			if(!empty($ev_type) && $ev_type !='all'){
				$ev_type = explode(',', $ev_type);
				$ev_type_ar = array(
						'tax_query'=>array( 
						array('taxonomy'=>'event_type','field'=>'id','terms'=>$ev_type) )	
					);
				
				$wp_arguments = array_merge($wp_arguments, $ev_type_ar);
			}
			
			//event type 2
			if(!empty($ev_type_2) && $ev_type_2 !='all'){
				$ev_type_2 = explode(',', $ev_type_2);
				$ev_type_ar_2 = array(
						'tax_query'=>array( 
						array('taxonomy'=>'event_type_2','field'=>'id','terms'=>$ev_type_2) )	
					);
				$wp_arguments = array_merge($wp_arguments, $ev_type_ar_2);
			}
			
			
		}
		
		//print_r($wp_arguments);
		return $wp_arguments;
	}
	
	/**
	 *	 out put just the sort bar for the calendar
	 */
	public function eventon_get_cal_sortbar($default_event_type='all', $default_event_type_2='all'){
		
		// define variable values
		$evt_name = (!empty($this->evopt1['evcal_eventt']))?$this->evopt1['evcal_eventt']:'Event Type';
		$evt_name2 = (!empty($this->evopt1['evcal_eventt2']))?$this->evopt1['evcal_eventt2']:'Event Type 2';		
		$sorting_options = (!empty($this->evopt1['evcal_sort_options']))?$this->evopt1['evcal_sort_options']:null;
		$filtering_options = (!empty($this->evopt1['evcal_filter_options']))?$this->evopt1['evcal_filter_options']:array();
		$content='';
			
		ob_start();
		
		echo ( $this->evcal_hide_sort!='yes' )? "<a class='evo_sort_btn'>".eventon_get_custom_language($this->evopt2, 'evcal_lang_sopt','Sort Options')."</a>":null;
		
		echo "<div class='eventon_sorting_section' >";
		if( $this->evcal_hide_sort!='yes' ){ // if sort bar is set to show	
		
		// sorting section
			echo "
			<div class='eventon_sort_line evo_sortOpt' style='display:none'>
				<div class='eventon_sf_field'>
					<p>".eventon_get_custom_language($this->evopt2, 'evcal_lang_sort','Sort By').":</p>
				</div>
				<div class='eventon_sf_cur_val evs'>
					<p class='sorting_set_val'>".eventon_get_custom_language($this->evopt2, 'evcal_lang_sdate','Date')."</p>
				</div>
				<div class='eventon_sortbar_selection evs_3 evs' style='display:none'>
					<p val='sort_date' type='date' class='evs_btn evs_hide'>".eventon_get_custom_language($this->evopt2, 'evcal_lang_sdate','Date')."</p>";
				
				$evsa1 = array(	'title'=>'Title','color'=>'Color');
				$cnt =1;
				if(is_array($sorting_options) ){
					foreach($evsa1 as $so=>$sov){
						if(in_array($so, $sorting_options) ){	
															
							echo "<p val='sort_".$so."' type='".$so."' class='evs_btn' >"
								.eventon_get_custom_language($this->evopt2, 'evcal_lang_s'.$so,$sov)
								."</p>";						
						}
						$cnt++;
					}
				}
			echo "</div><div class='clear'></div></div>";
		}
		
		
		// filtering section
		echo "
			<div class='eventon_filter_line'>";
			
			// event_type line
			if(in_array('event_type', $filtering_options) && $default_event_type=='all'){				
				
				echo "
				<div class='eventon_filter evo_sortOpt' filter_field='event_type' filter_val='all' filter_type='tax' style='display:none'>
					<div class='eventon_sf_field'><p>".$evt_name.":</p></div>				
				
					<div class='eventon_filter_selection'>
						<p class='filtering_set_val' opts='evs4_in'>All</p>
						<div class='eventon_filter_dropdown' style='display:none'>";
					
						$cats = get_categories(array( 'taxonomy'=>'event_type'));
						echo "<p filter_val='all'>All</p>";
						foreach($cats as $ct){
							echo "<p filter_val='".$ct->term_id."' filter_slug='".$ct->slug."'>".$ct->name."</p>";
						}				
					echo "</div>
					</div><div class='clear'></div>
				</div>";
			}else if($default_event_type!='all'){
				echo "<div class='eventon_filter' filter_field='event_type' filter_val='{$default_event_type}' filter_type='tax'></div>";
			}
			
			// event_type_2 line
			if(in_array('event_type_2', $filtering_options) && $default_event_type_2=='all'){
				echo "
				<div class='eventon_filter evo_sortOpt' filter_field='event_type_2' filter_val='all' filter_type='tax' style='display:none'>
					<div class='eventon_sf_field'><p>".$evt_name2.":</p></div>				
				
					<div class='eventon_filter_selection'>
						<p class='filtering_set_val' opts='evs4_in'>All</p>
						<div class='eventon_filter_dropdown' style='display:none'>";
					
						$cats = get_categories(array( 'taxonomy'=>'event_type_2'));
						echo "<p filter_val='all'>All</p>";
						foreach($cats as $ct){
							echo "<p filter_val='".$ct->term_id."' filter_slug='".$ct->slug."'>".$ct->name."</p>";
						}				
					echo "</div>
					</div><div class='clear'></div>
				</div>";
			}else if($default_event_type_2!='all'){
				echo "<div class='eventon_filter' filter_field='event_type_2' filter_val='{$default_event_type_2}' filter_type='tax'></div>";
			}
			
			// (---) Hook for addon
			if(has_action('eventon_sorting_filters')){
				echo  do_action('eventon_sorting_filters', $content);
			}
				
			echo "</div>"; // #eventon_filter_line
		
		echo "</div>"; // #eventon_sorting_section
		
		// (---) Hook for addon
		if(has_action('eventon_below_sorts')){
			echo  do_action('eventon_below_sorts', $content);
		}
		
		// load bar for calendar
		echo "<div id='eventon_loadbar_section'><div id='eventon_loadbar'></div></div>";				
		
		
		return ob_get_clean();
	}
	
	
	
} // class EVO_generator


?>