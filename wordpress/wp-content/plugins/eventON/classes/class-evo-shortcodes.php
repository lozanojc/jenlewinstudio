<?php
/**
 * EVO_Shortcodes class.
 *
 * @class 		EVO_Shortcodes
 * @version		1.0.0
 * @package		EventON/Classes
 * @category	Class
 * @author 		AJDE
 */

class EVO_Shortcodes {
	public function __construct(){
		// regular shortcodes
		add_shortcode('add_ajde_evcal',array($this,'eventon_show_calendar'));	// for eventon ver < 2.0.8	
		add_shortcode('add_eventon',array($this,'eventon_show_calendar'));
		//add_filter('widget_text', 'do_shortcode' );		// This will make the shortcodes work even on widgets
	}
	
	
	/**
	 * Show calendar shortcode
	 */
	public function eventon_show_calendar($atts){
		global $eventon;
		
		$supported_defaults = array(
			'cal_id'=>'1',
			'event_count'=>0,
			'month_incre'=>0,
			'show_upcoming'=>0,
			'number_of_months'=>0,
			'event_type'=> 'all',
			'event_type_2'=> 'all',
			'fixed_month'=>0,
			'fixed_year'=>0,
			'hide_past'=>'no',
		);
		
		
		// Hook for addons
		if(has_filter('eventon_shortcode_default_values') ){
			$supported_defaults = apply_filters('eventon_shortcode_default_values', $supported_defaults);
		}				
		
		$args = shortcode_atts( $supported_defaults, $atts ) ;		
		
		// to support event_type and event_type_2 variables from older version
		// event_type filter		
		if($args['event_type']!='all'){
			$filters['filters'][]=array(
				'filter_type'=>'tax',
				'filter_name'=>'event_type',
				'filter_val'=>$args['event_type']
			);
			$args = array_merge($args,$filters);
		}
		if($args['event_type_2']!='all'){
			$filters['filters'][]=array(
				'filter_type'=>'tax',
				'filter_name'=>'event_type_2',
				'filter_val'=>$args['event_type_2']
			);
			$args = array_merge($args,$filters);
		}
		
		
		// (---) hook for addons
		if(has_filter('eventon_shortcode_argument_update') ){
			$args = apply_filters('eventon_shortcode_argument_update', $args);
		}
		
		
		// OUT PUT
		
		ob_start();
			
		echo $eventon->evo_generator->eventon_generate_calendar($args);
		
		return ob_get_clean();
	}
}
?>