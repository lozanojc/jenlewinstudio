<?php
/**
 * EventON Widget
 *
 * @author 		AJDE
 * @category 	Widget
 * @package 	EventON/Classes
 * @version     1.2
 */
class EvcalWidget extends WP_Widget{
	
	function EvcalWidget(){
		$widget_ops = array('classname' => 'EvcalWidget', 
			'description' => 'EventON basic or upcoming list Event Calendar widget.' );
		$this->WP_Widget('EvcalWidget', 'eventON Calendar', $widget_ops);
	}
	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'ev_count' => '','ev_type' =>'', 'ev_title'=>'' ) );
		$ev_count = $instance['ev_count'];
		$ev_type = $instance['ev_type'];
		$ev_title = $instance['ev_title'];
		$ev_upcomingevents = (!empty($instance['ev_upcomingevents']) )?$instance['ev_upcomingevents']:null;
		$ev_hidepastev = (!empty($instance['ev_hidepastev']) )?$instance['ev_hidepastev']:null;
		$hide_mult_occur = (!empty($instance['hide_mult_occur']) )?$instance['hide_mult_occur']:null;
		$ev_month_count = (!empty($instance['ev_month_count']))? $instance['ev_month_count'] : null;
		
		$_is_fixed_time = (!empty($instance['_is_fixed_time']))? $instance['_is_fixed_time'] : null;
		$fixed_month = (!empty($instance['fixed_month']))? $instance['fixed_month'] : null;
		$fixed_year = (!empty($instance['fixed_year']))? $instance['fixed_year'] : null;
		
		// HTML
		
		?>
		<div id='eventon_widget_settings'>
			<div class='eventon_widget_top'><p></p></div>
			
			<div class='evo_widget_outter evowig'>
				<div class='evo_wig_item'>
					
					<input id="<?php echo $this->get_field_id('ev_title'); ?>" name="<?php echo $this->get_field_name('ev_title'); ?>" type="text" 
					value="<?php echo attribute_escape($ev_title); ?>" placeholder='Widget Title' title='Widget Title'/>					
				</div>
				<div class='evo_wig_item'>
					<span class='legend_icon'>?</span>
					<span class='legend' style='display:none'>If left blank - will display all events for that month.</span>
					
					<input id="<?php echo $this->get_field_id('ev_count'); ?>" 
					name="<?php echo $this->get_field_name('ev_count'); ?>" type="text" 
					value="<?php echo attribute_escape($ev_count); ?>" placeholder='Event Count' title='Event Count'/>
					
				</div>
				<div class='evo_wig_item' connection=''>
					<input id="<?php echo $this->get_field_id('ev_hidepastev'); ?>" type='hidden' name='<?php echo $this->get_field_name('ev_hidepastev'); ?>' value='<?php echo attribute_escape($ev_hidepastev); ?>'/>
					<p class='evowig_chbx <?php echo ($ev_hidepastev=='yes')?'selected':null; ?>'></p>
					<p>Hide past events</p>
					<div class='clear'></div>
				</div>				
			</div>
			
			<p class='divider'></p>
			<div class='evo_widget_outter evowig'>
				<div class='evo_wig_item' connection=''>

					<input id="<?php echo $this->get_field_id('ev_upcomingevents'); ?>" type='hidden' name='<?php echo $this->get_field_name('ev_upcomingevents'); ?>' value='<?php echo attribute_escape($ev_upcomingevents); ?>'/>
					<p class='evowig_chbx <?php echo ($ev_upcomingevents=='yes')?'selected':null; ?>'></p>
					<p>Show upcoming events</p>
					<div class='clear'></div>
				</div>
				
				<div class='evo_wug_hid' <?php echo ($ev_upcomingevents=='yes')?'style="display:block"':null; ?>>
					<div class='evo_wig_item'>
						<span class='legend_icon'>?</span>
						<span class='legend' style='display:none'>Use this field to set the number of upcoming months to show</span>
						
						<input id="<?php echo $this->get_field_id('ev_month_count'); ?>" name="<?php echo $this->get_field_name('ev_month_count'); ?>" type="text" 
						value="<?php echo attribute_escape($ev_month_count); ?>" placeholder='Number of Months' title='Number of Months'/>
						
					</div>
					<div class='evo_wig_item' connection=''>
						<input id="<?php echo $this->get_field_id('hide_mult_occur'); ?>" type='hidden' name='<?php echo $this->get_field_name('hide_mult_occur'); ?>' value='<?php echo attribute_escape($hide_mult_occur); ?>'/>
						<p class='evowig_chbx <?php echo ($hide_mult_occur=='yes')?'selected':null; ?>'></p>
						<p>Hide Multiple Occurance</p>
						<div class='clear'></div>
					</div>
				</div>
			</div>	
			
			
			<p class='divider'></p>
			<div class='evo_widget_outter evowig'>
				<div class='evo_wig_item' connection=''>					
					
					<input id="<?php echo $this->get_field_id('_is_fixed_time'); ?>" type='hidden' name='<?php echo $this->get_field_name('_is_fixed_time'); ?>' value='<?php echo attribute_escape($_is_fixed_time); ?>'/>
					<p class='evowig_chbx <?php echo ($_is_fixed_time=='yes')?'selected':null; ?>'></p>
					<p>Set fixed month/year</p>
					<div class='clear'></div>
				</div>
				
				<div class='evo_wug_hid' <?php echo ($_is_fixed_time=='yes')?'style="display:block"':null; ?>>
					<div class='evo_wig_item'>
						<input id="<?php echo $this->get_field_id('fixed_month'); ?>" name="<?php echo $this->get_field_name('fixed_month'); ?>" type="text" 
						value="<?php echo attribute_escape($fixed_month); ?>" placeholder='Fixed month number' title='Fixed month number'/>					
					</div><div class='evo_wig_item'>
						<input id="<?php echo $this->get_field_id('fixed_year'); ?>" name="<?php echo $this->get_field_name('fixed_year'); ?>" type="text" 
						value="<?php echo attribute_escape($fixed_year); ?>" placeholder='Fixed year number' title='Fixed year number'/>					
					</div>
				</div>
			</div>
			 
			<p class='divider'></p>
			 
			<div class='evo_widget_outter evowig'>
				<div class='evo_wig_item'>
					<input id="<?php echo $this->get_field_id('ev_type'); ?>" name="<?php echo $this->get_field_name('ev_type'); ?>" type="text" 
					value="<?php echo attribute_escape($ev_type); ?>" placeholder='Event Types' title='Event Types'/>
					<em>Leave blank for all event types, else type <a href='edit-tags.php?taxonomy=event_type&post_type=ajde_events'>event type ID</a> separated by commas)</em>
				</div>						
			</div>
			
			
		</div>
		<?php
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['ev_title'] = strip_tags($new_instance['ev_title']);
		$instance['ev_count'] = strip_tags($new_instance['ev_count']);
		$instance['ev_type'] = strip_tags($new_instance['ev_type']);
		$instance['ev_upcomingevents'] = strip_tags($new_instance['ev_upcomingevents']);
		$instance['ev_hidepastev'] = strip_tags($new_instance['ev_hidepastev']);
		$instance['hide_mult_occur'] = strip_tags($new_instance['hide_mult_occur']);
		$instance['ev_month_count'] = strip_tags($new_instance['ev_month_count']);
		$instance['_is_fixed_time'] = strip_tags($new_instance['_is_fixed_time']);
		$instance['fixed_month'] = strip_tags($new_instance['fixed_month']);
		$instance['fixed_year'] = strip_tags($new_instance['fixed_year']);
		return $instance;
	}
	
	/**
	 * The actuval widget
	 */
	public function widget($args, $instance) {
		global $eventon;
		
		// DEFAULTS
		$fixed_month = $fixed_year = 0;
		
		
		extract($args, EXTR_SKIP);		
		
		$event_count = empty($instance['ev_count']) ? '0' : $instance['ev_count'];
		$event_type = empty($instance['ev_type']) ? 'all' : $instance['ev_type'];
		$ev_month_count = empty($instance['ev_month_count']) ? 'all' : $instance['ev_month_count'];
		$ev_hidepastev = empty($instance['ev_hidepastev']) ? 'no' : $instance['ev_hidepastev'];
		$hide_mult_occur = empty($instance['hide_mult_occur']) ? 'no' : $instance['hide_mult_occur'];
		$event_type_2 ='all';
		
		// Upcoming months
		$show_upcoming= (!empty($instance['ev_upcomingevents']) && $instance['ev_upcomingevents']=='yes' && !empty($instance['ev_month_count'])) ?1:0;
			
		
		// Fixed month year
		if(!empty($instance['_is_fixed_time']) && $instance['_is_fixed_time']=='yes'){
			$fixed_month = (!empty($instance['fixed_month']))? $instance['fixed_month']:0;
			$fixed_year = (!empty($instance['fixed_year']))? $instance['fixed_year']:0;
		}
		
		// CALENDAR ARGUMENTS
		$args = array(
			'cal_id'=>'eventon_widget',
			'event_count'=>$event_count,
			'show_upcoming'=>$show_upcoming,
			'number_of_months'=>$ev_month_count,
			'event_type'=> $event_type,
			'event_type_2'=> 'all',
			'fixed_month'=>$fixed_month,
			'fixed_year'=>$fixed_year,
			'hide_past'=>$ev_hidepastev,
			'hide_mult_occur'=>$hide_mult_occur
		);
		
		// Check for event type filterings called for from widget settings
		if($event_type!='all'){
			$filters['filters'][]=array(
				'filter_type'=>'tax',
				'filter_name'=>'event_type',
				'filter_val'=>$args['event_type']
			);
			$args = array_merge($args,$filters);
		}
		if($event_type_2!='all'){
			$filters['filters'][]=array(
				'filter_type'=>'tax',
				'filter_name'=>'event_type_2',
				'filter_val'=>$args['event_type_2']
			);
			$args = array_merge($args,$filters);
		}
		
		
		
		echo $before_widget;
		
		// widget title
		if(!empty($instance['ev_title']) ){
			echo "<h3 class='widget-title'>".$instance['ev_title']."</h3>";
		}
		
		
		$content =$eventon->evo_generator->eventon_generate_calendar($args);
		echo "<div id='evcal_widget'>".$content."</div>";
		
		
		echo $after_widget;
		
	}
}