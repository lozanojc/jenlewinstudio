<?php
/**
 * Meta boxes for ajde_events
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	EventON/Admin/ajde_events
 * @version     1.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Init the meta boxes.
 */
function eventon_meta_boxes(){
	// ajde_events meta boxes
	add_meta_box('ajdeevcal_mb2','Event Color', 'ajde_evcal_show_box_2','ajde_events', 'side', 'core');
	add_meta_box('ajdeevcal_mb1','Event Settings', 'ajde_evcal_show_box','ajde_events', 'normal', 'high');	
	
	
	do_action('eventon_add_meta_boxes');
}
add_action( 'add_meta_boxes', 'eventon_meta_boxes' );
add_action( 'save_post', 'eventon_save_meta_data', 1, 2 );
	
	
/**
 * Event Color Meta box.
 */	
function ajde_evcal_show_box_2(){
		
		
		// Use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), 'evo_noncename_2' );
		$p_id = get_the_ID();
		$ev_vals = get_post_custom($p_id);
		
			
?>		
		<table id="meta_tb2" class="form-table meta_tb" >
		<tr>
			<td>
			<?php
				// Hex value cleaning
				$hexcolor = (!empty($ev_vals["evcal_event_color"]) )? 
					( ($ev_vals["evcal_event_color"][0][0] == '#')?
						substr( $ev_vals["evcal_event_color"][0], 1 ):
						$ev_vals["evcal_event_color"][0] )
					:null;
			?>			
			<div id='color_selector' style='border-color:<?php echo (!empty($hexcolor) )? '#'.$hexcolor: 'na'; ?>'>
				<span class='evcal_color_hex evcal_chex'  ><?php echo (!empty($hexcolor) )? $hexcolor: 'Hex code'; ?></span>
				<span class='evcal_color_selector_text evcal_chex'><?php _e('Click here to pick a color');?></span>				
			</div>
			<p style='margin-bottom:0; padding-bottom:0'><i>OR Select from other colors</i></p>
			
			<div id='evcal_colors'>
				<?php 
				
					$other_events = get_posts(array(
						'posts_per_page'=>-1,
						'post_type'=>'ajde_events',
						'meta_key' => 'evcal_event_color'
					));
					
					$other_colors='';
					
					foreach($other_events as $ev){ setup_postdata($ev);
						$this_id = $ev->ID;
						
						$hexval = get_post_meta($this_id,'evcal_event_color',true);
						$hexval_num = get_post_meta($this_id,'evcal_event_color_n',true);
						
						
						// hex color cleaning
						$hexval = ($hexval[0]=='#')? substr($hexval,1):$hexval;
						
						
						if(!empty( $hexval) && (empty($other_colors) || (is_array($other_colors) && !in_array($hexval, $other_colors)	)	)	){
							echo "<div class='evcal_color_box' style='background-color:#".$hexval."'color_n='".$hexval_num."' color='".$hexval."'></div>";
							
							$other_colors[]=$hexval;
						}
											
					}
					
				?>				
			</div>
			<div class='clear'></div>
			
			
			
			<input id='evcal_event_color' type='hidden' name='evcal_event_color' 
				value='<?php echo (!empty($ev_vals["evcal_event_color"]) )? $ev_vals["evcal_event_color"][0]: null; ?>'/>
			<input id='evcal_event_color_n' type='hidden' name='evcal_event_color_n' 
				value='<?php echo (!empty($ev_vals["evcal_event_color_n"]) )? $ev_vals["evcal_event_color_n"][0]: null ?>'/>
			</td>
		</tr>
		<?php do_action('eventon_metab2_end'); ?>
		</table>
<?php }
	
	
	
/**
 * Main meta box.
 */
	function ajde_evcal_show_box(){
		global $eventon;
		
		// repeat frequency array
		$repeat_freq= apply_filters('evo_repeat_intervals', array('weekly'=>'weeks','monthly'=>'months','yearly'=>'years') );
				
		
		$evcal_opt1= get_option('evcal_options_evcal_1');
		$evcal_opt2= get_option('evcal_options_evcal_2');
		
		// Use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), 'evo_noncename' );
		
		// The actual fields for data entry
		$p_id = get_the_ID();
		$ev_vals = get_post_custom($p_id);
		
		
		$evcal_allday = (!empty($ev_vals["evcal_allday"]))? $ev_vals["evcal_allday"][0]:null;		
		$show_style_code = ($evcal_allday=='yes') ? "style='display:none'":null;

		$select_a_arr= array('AM','PM');
		
		
		// --- TIME variations
		$evcal_date_format = eventon_get_timeNdate_format($evcal_opt1);
		$time_hour_span= ($evcal_date_format[2])?25:13;
		
		
		// GET DATE and TIME values
		$_START=(!empty($ev_vals['evcal_srow'][0]))?
			eventon_get_editevent_kaalaya($ev_vals['evcal_srow'][0],$evcal_date_format[1], $evcal_date_format[2]):false;
		$_END=(!empty($ev_vals['evcal_erow'][0]))?
			eventon_get_editevent_kaalaya($ev_vals['evcal_erow'][0],$evcal_date_format[1], $evcal_date_format[2]):false;
		
		//print_r($_START);
	?>
		
		<table id="meta_tb" class="form-table meta_tb" >
		<?php
			// (---) hook for addons
			if(has_action('eventon_post_settings_metabox_table'))
				do_action('eventon_post_settings_metabox_table');
		?>
		<tr>
			<td><label for=''><?php _e('All Day Event'); ?></label></td>
			<td>
				<a id='evcal_allday_yn_btn' allday_switch='1' class='evcal_yn_btn <?php echo ($evcal_allday=='yes')?null:'btn_at_no'?>'></a>
				<input type='hidden' name='evcal_allday' value="<?php echo ($evcal_allday=='yes')?'yes':'no';?>"/>
			</td>
		</tr>
		<?php
			// (---) hook for addons
			if(has_action('eventon_post_time_settings'))
				do_action('eventon_post_time_settings');
		?>
		<tr><td colspan='2'>
			
			<!-- date and time formats to use -->
			<input type='hidden' name='_evo_date_format' value='<?php echo $evcal_date_format[1];?>'/>
			<input type='hidden' name='_evo_time_format' value='<?php echo ($evcal_date_format[2])?'24h':'12h';?>'/>
			
			
			<div id='evcal_dates' date_format='<?php echo $evcal_date_format[0];?>'>
				
				
				<!-- START TIME-->
				<div class='evo_start_event evo_datetimes'>
					<div class='evo_date'>
						<p id='evcal_start_date_label'><?php _e('Event Start Date')?></p>
						<input class='evcal_data_picker datapicker_on' type='text' id='evcal_start_date' name='evcal_start_date' value='<?php echo ($_START)?$_START[0]:null?>' placeholder='<?php echo $evcal_date_format[1];?>'/>					
						<span>Select a Date</span>
					</div>					
					<div class='evcal_date_time switch_for_evsdate evcal_time_selector' <?php echo $show_style_code?>>
						<div class='evcal_select'>
							<select id='evcal_start_time_hour' class='evcal_date_select' name='evcal_start_time_hour'>
								<?php
									//echo "<option value=''>--</option>";
									$start_time_h = ($_START)?$_START[1]:null;						
								for($x=1; $x<$time_hour_span;$x++){									
									echo "<option value='$x'".(($start_time_h==$x)?'selected="selected"':'').">$x</option>";
								}?>
							</select>
						</div><p style='display:inline; font-size:24px;padding:4px 2px'>:</p>
						<div class='evcal_select'>
							
							<select id='evcal_start_time_min' class='evcal_date_select' name='evcal_start_time_min'>
								<?php	
									//echo "<option value=''>--</option>";
									$start_time_m = ($_START)?	$_START[2]: null;
								for($x=0; $x<12;$x++){
									$min = ($x<2)?('0'.$x*5):$x*5;
									echo "<option value='$min'".(($start_time_m==$min)?'selected="selected"':'').">$min</option>";
								}?>
							</select>
						</div>
						
						<?php if(!$evcal_date_format[2]):?>
						<div class='evcal_select evcal_ampm_sel'>
							<select name='evcal_st_ampm' id='evcal_st_ampm' >
								<?php
									$evcal_st_ampm = ($_START)?$_START[3]:null;
									foreach($select_a_arr as $sar){
										echo "<option value='".$sar."' ".(($evcal_st_ampm==$sar)?'selected="selected"':'').">".$sar."</option>";
									}
								?>								
							</select>
						</div>	
						<?php endif;?>
						<br/>
						<span><?php _e('Select a Time')?></span>
					</div><div class='clear'></div>
				</div>
				
				
				
				<!-- END TIME -->
				<?php 
					$evo_hide_endtime = (!empty($ev_vals["evo_hide_endtime"]) )? $ev_vals["evo_hide_endtime"][0]:null;
				?>
				<div class='evo_start_event evo_datetimes switch_for_evsdate'>
					<div class='evo_enddate_selection' style='<?php echo ($evo_hide_endtime=='yes')?'display:none':null;?>'>
					<div class='evo_date'>
						<p><?php _e('Event End Date','eventon')?></p>
						<input class='evcal_data_picker datapicker_on' type='text' id='evcal_end_date' name='evcal_end_date' value='<?php echo ($_END)? $_END[0]:null; ?>'/>					
						<span><?php _e('Select a Date','eventon')?></span>
						
					</div>
					<div class='evcal_date_time evcal_time_selector' <?php echo $show_style_code?>>
						<div class='evcal_select'>
							<select class='evcal_date_select' name='evcal_end_time_hour'>
								<?php	
									//echo "<option value=''>--</option>";
									$end_time_h = ($_END)?$_END[1]:null;
									for($x=1; $x<$time_hour_span;$x++){
										echo "<option value='$x'".(($end_time_h==$x)?'selected="selected"':'').">$x</option>";
									}
								?>
							</select>
						</div><p style='display:inline; font-size:24px;padding:4px'>:</p>
						<div class='evcal_select'>
							<select class='evcal_date_select' name='evcal_end_time_min'>
								<?php	
									//echo "<option value=''>--</option>";
									$end_time_m = ($_END[2])?$_END[2]:null;
									for($x=0; $x<12;$x++){
										$min = ($x<2)?('0'.$x*5):$x*5;
										echo "<option value='$min'".(($end_time_m==$min)?'selected="selected"':'').">$min</option>";
									}
								?>
							</select>
						</div>
						
						<?php if(!$evcal_date_format[2]):?>
						<div class='evcal_select evcal_ampm_sel'>
							<select name='evcal_et_ampm'>
								<?php
									$evcal_et_ampm = ($_END)?$_END[3]:null;
									
									foreach($select_a_arr as $sar){
										echo "<option value='".$sar."' ".(($evcal_et_ampm==$sar)?'selected="selected"':'').">".$sar."</option>";
									}
								?>								
							</select>
						</div>
						<?php endif;?>
						<br/>
						<span><?php _e('Select the Time','eventon')?></span>
					</div><div class='clear'></div>
					</div>
					
					
					<!-- end time yes/no option -->					
					<p class='yesno_leg_line '>
						<a id='evo_endtime' class='evcal_yn_btn <?php echo ( $evo_hide_endtime=='yes')?null:'btn_at_no'?>'></a>
						<input type='hidden' name='evo_hide_endtime' value="<?php echo ($evo_hide_endtime=='yes')?'yes':'no';?>"/>
						<label for='evo_hide_endtime'><?php _e('Hide End Time from calendar')?></label>
					</p>
					<p style='clear:both'></p>
				</div>
				<div style='clear:both'></div>
				
				<?php // Recurring events 
					$evcal_repeat = (!empty($ev_vals["evcal_repeat"]) )? $ev_vals["evcal_repeat"][0]:null;
				?>
				<div id='evcal_rep' class='evd'>
					<div class='evcalr_1'>
						<p class='yesno_leg_line '>
							<a id='evd_repeat' class='evcal_yn_btn <?php echo ( $evcal_repeat=='yes')?null:'btn_at_no'?>'></a>
							<input type='hidden' name='evcal_repeat' value="<?php echo ($evcal_repeat=='yes')?'yes':'no';?>"/>
							<label for='evcal_repeat'><?php _e('Repeating event')?></label>
						</p>
						<p style='clear:both'></p>
					</div>
					<p class='eventon_ev_post_set_line'></p>
					<?php
						$display = (!empty($ev_vals["evcal_repeat"]) && $evcal_repeat=='yes')? '':'none';
					?>
					<div class='evcalr_2' style='display:<?php echo $display ?>'>
						<p class='evcalr_2_freq evcalr_2_p'><em></em>Frequency: <select id='evcal_rep_freq' name='evcal_rep_freq'>
						<?php
							$evcal_rep_freq = (!empty($ev_vals['evcal_rep_freq']))?$ev_vals['evcal_rep_freq'][0]:null;
							foreach($repeat_freq as $refv=>$ref){
								echo "<option field='".$ref."' value='".$refv."' ".(($evcal_rep_freq==$refv)?'selected="selected"':'').">".$refv."</option>";
							}
							
						?></select></p>
						
						<?php
							$evcal_rep_gap = (!empty($ev_vals['evcal_rep_gap']) )? 
									$ev_vals['evcal_rep_gap'][0]:null;
							$freq = (!empty($ev_vals["evcal_rep_freq"]) )?
								 ($repeat_freq[ $ev_vals["evcal_rep_freq"][0] ])
								: null;
						?>						
						<p class='evcalr_2_rep evcalr_2_p'><em></em>Repeat Every: 
							<input type='number' name='evcal_rep_gap' min='1' max='100' value='<?php echo $evcal_rep_gap;?>'/>	 <span id='evcal_re'><?php echo $freq;?></span></p>
						
						<?php
							$evcal_rep_num = (!empty($ev_vals['evcal_rep_num']) )? 
									$ev_vals['evcal_rep_num'][0]:null;
						?>
						<p class='evcalr_2_numr evcalr_2_p'><em></em>Number of repeats: 
							<input type='number' name='evcal_rep_num' min='1' max='100' value='<?php echo $evcal_rep_num;?>'/>						
						</p>
					</div>
				</div>	
				
			</div>
		</td></tr>
		<tr>
		<?php
			// LOCATION
		?>
			<td colspan='2'>
				<div class='evcal_data_block_style1'>
					<p class='edb_icon evcal_edb_map'></p>
					<div class='evcal_db_data'><label for='evcal_location'><?php _e('Event Location Address')?> <i>(eg. 123 Main St. Mainland, AB 12345)</i></label><br/>			
						<input type='text' id='evcal_location' name='evcal_location' value="<?php echo (!empty($ev_vals["evcal_location"]) )? $ev_vals["evcal_location"][0]:null?>" style='width:100%'/>
						
						
						<p><?php _e('Latitude');?>: <input type='text' class='evcal_latlon' name='evcal_lat' value='<?php echo (!empty($ev_vals["evcal_lat"]) )? $ev_vals["evcal_lat"][0]:null?>'/></p>
						<p><?php _e('Longitude')?>: <input type='text' class='evcal_latlon' name='evcal_lon' value='<?php echo (!empty($ev_vals["evcal_lon"]) )? $ev_vals["evcal_lon"][0]:null?>'/></p>
						<p><i>(NOTE: Provide either address or latitude longitude(Latlon). If Latlon provided, Latlon will be used for generating google maps while address will be shown as text address.)</i></p>
						
						
						<p class='yesno_leg_line'>
							<a class='evcal_yn_btn <?php echo ($ev_vals["evcal_gmap_gen"][0]=='yes')?null:'btn_at_no'?>'></a>
							<input type='hidden' name='evcal_gmap_gen' value="<?php echo ($ev_vals["evcal_gmap_gen"][0]=='yes')?'yes':'no';?>"/>
							<label for='evcal_gmap_gen'><?php _e('Generate Google Map from the address','eventon')?></label>
						</p>
						<p style='clear:both'></p>
					</div>
				</div>
			</td>
		</tr>
		<tr class='divide'><td colspan='2'><p class='div_bar'></p></td></tr>
		<?php
			// ORGANIZER
		?>
		<tr>
			<td colspan='2'>
				<div class='evcal_data_block_style1'>
					<p class='edb_icon evcal_edb_organizer'></p>
					<div class='evcal_db_data'><label for='evcal_organizer'><?php _e('Event Organizer')?></label><br/>			
						<input type='text' id='evcal_organizer' name='evcal_organizer' value="<?php echo (!empty($ev_vals["evcal_organizer"]) )? $ev_vals["evcal_organizer"][0]:null?>" style='width:100%'/><br/>						
					</div>
				</div>
			</td>			
		</tr>
		<?php 
		// Event brite
		if($evcal_opt1['evcal_evb_events']=='yes'
			&& !empty($evcal_opt1['evcal_evb_api']) ):?>
			<tr class='divide'><td colspan='2'><p class='div_bar'></p></td></tr>
			<tr>
				<td colspan='2'>
				<div class='evcal_data_block_style1'>
					<p class='edb_icon'><img src='<?php echo AJDE_EVCAL_URL ?>/assets/images/backend_post/eventbrite_icon.png'/></p>
					
					<p class='evcal_db_data'>
					<?php
						if(!empty($ev_vals["evcal_evb_id"]) ){
							echo "<span id='evcal_eb5'>Currently Connected to <b id='evcal_eb2'>".$ev_vals["evcal_evb_id"][0]."</b><br/></span>";
							$html_eb2 = "  <input type='button' class='button' id='evcal_eventb_btn_dis' value='Disconnect this'/>";
						}else{
							$html_eb2='';
						}
						$html_eb1 = 'Connect to Eventbrite Event';
					?>	
					<input type='button' class='button' id='evcal_eventb_btn' value='<?php echo $html_eb1?>'/><?php echo $html_eb2?></p>
					
					<input type='hidden' name='evcal_evb_id' id='evcal_eventb_ev_d2' value='<?php echo (!empty($ev_vals["evcal_evb_id"]))? $ev_vals["evcal_evb_id"][0]: null; ?>'/>
					<input type='hidden' name='evcal_eventb_data_set' id='evcal_eventb_ev_d1' value='<?php echo (!empty($ev_vals["evcal_eventb_data_set"]))? $ev_vals["evcal_eventb_data_set"][0]: null; ?>'/>
				</div>	
				</td>
			</tr>
			<?php
				// URL
				$display = (!empty($ev_vals["evcal_eventb_url"]) )? '':'none';
			?>
			<tr class='divide evcal_eb_url evcal_eb_r' style='display:<?php echo $display ?>'>
				<td colspan='2'><p class='div_bar div_bar_sm'></p></td></tr>
			<tr class='evcal_eb_url evcal_eb_r' style='display:<?php echo $display?>'>
				<td colspan='2'>
					<p style='margin-bottom:2px'>Eventbrite Buy Ticket URL</p>
					<input style='width:100%' id='evcal_ebv_url' type='text' name='evcal_eventb_url' value='<?php echo (!empty($ev_vals["evcal_eventb_url"]))? $ev_vals["evcal_eventb_url"][0]: null; ?>' />
				</td>
			</tr>
			<?php
				// CAPACITY
				$display = (!empty($ev_vals["evcal_eventb_capacity"]) )? '':'none';
			?>
			<tr class='divide evcal_eb_capacity evcal_eb_r' style='display:<?php echo $display ?>'>
				<td colspan='2'><p class='div_bar div_bar_sm'></p></td></tr>
			<tr class='evcal_eb_capacity evcal_eb_r' style='display:<?php echo $display?>'>
				<td colspan='2'>
					<?php $evcal_eventb_capacity = (!empty($ev_vals["evcal_eventb_capacity"]))? $ev_vals["evcal_eventb_capacity"][0]: null; ?>
					<p style='margin-bottom:2px'>Eventbrite Event Capacity: <b id='evcal_eb3'><?php echo $evcal_eventb_capacity?></b></p>
					<input id='evcal_ebv_capacity' type='hidden' name='evcal_eventb_capacity' value='<?php echo $evcal_eventb_capacity?>' />
				</td>
			</tr>
			<?php
				// TICKET PRICE
				$display = (!empty($ev_vals["evcal_eventb_tprice"]) )? '':'none';
			?>
			<tr class='divide evcal_eb_price evcal_eb_r' style='display:<?php echo $display ?>'>
				<td colspan='2'><p class='div_bar div_bar_sm'></p></td></tr>
			<tr class='evcal_eb_price evcal_eb_r' style='display:<?php echo $display?>'>
				<td colspan='2'>
					<?php $evcal_eventb_tprice = (!empty($ev_vals["evcal_eventb_tprice"]))? $ev_vals["evcal_eventb_tprice"][0]: null; ?>
					<p style='margin-bottom:2px'>Eventbrite Ticket Price: <b id='evcal_eb4'><?php echo $evcal_eventb_tprice?></b></p>
					<input id='evcal_ebv_price' type='hidden' name='evcal_eventb_tprice' value='<?php echo $evcal_eventb_tprice?>' />
				</td>
			</tr>
			
			<tr id='evcal_eventb_data' style='display:none'><td colspan='2'>
				<div class='evcal_row_dark' >
					<p id='evcal_eventb_msg' class='event_api_msg' style='display:none'>Message</p>
					<div class='col50'>
						<p><input type='text' id='evcal_eventb_ev_id' value='' style='width:100%'/></p>
						<p class='legend_mf'>Enter Eventbrite Event ID</p>
					</div>
					<div class='col50'>
						<div class='padl20'>
							<p><input id='evcal_eventb_btn_2' style='margin-left:10px'type='button' class='button' value='Get Event Data from Eventbrite'/></p>
						</div>
					</div>			
					
					<p class='clear'></p>					
					<p class='divider'></p>					
					<div id='evcal_eventb_s1' style='display:none'>
						<h5 class='mu_ev_id'>Retrived Event Data for: <b id='evcal_eb1'>321786</b></h5>
						<p class='legend_mf'>Click on each eventbrite event data section to connect to this event.</p>
						
						<div id='evcal_eventb_data_tb'></div>
					</div>
				</div>
			</td></tr>
		
		
		<?php endif;?>
		
		<?php 
			// MEETUP
			
			if($evcal_opt1['evcal_api_meetup']=='yes' 
				&& !empty($evcal_opt1['evcal_api_mu_key']) ):
		?>
			<tr class='divide'><td colspan='2'><p class='div_bar'></p></td></tr>
			<tr>
				<td colspan='2'>
				<div class='evcal_data_block_style1'>
					<p class='edb_icon'><img src='<?php echo AJDE_EVCAL_URL ?>/assets/images/backend_post/meetup_icon.png'/></p>
					
					<p class='evcal_db_data'>
					<?php
						if(!empty($ev_vals["evcal_meetup_ev_id"]) ){
							echo "<span id='evcal_mu2'>Currently Connected to <b id='evcal_002'>".$ev_vals["evcal_meetup_ev_id"][0]."</b><br/></span>";
							$html_mu2 = "  <input type='button' class='button' id='evcal_meetup_btn_dis' value='Disconnect this'/>";
						}else{
							$html_mu2 ='';
						}
						$html_mu1 = 'Connect to Meetup Event';
					?>	
					<input type='button' class='button' id='evcal_meetup_btn' value='<?php echo $html_mu1?>'/><?php echo $html_mu2?></p>
					
					<input type='hidden' name='evcal_meetup_data_set' id='evcal_meetup_ev_d1' value='<?php echo (!empty($ev_vals["evcal_meetup_data_set"]))? $ev_vals["evcal_meetup_data_set"][0]: null; ?>'/>
					<input type='hidden' name='evcal_meetup_ev_id' id='evcal_meetup_ev_d2' value='<?php echo (!empty($ev_vals["evcal_meetup_ev_id"]))? $ev_vals["evcal_meetup_ev_id"][0]: null; ?>'/>
				</div>	
				</td>
			</tr>
			
			
			<tr id='evcal_meetup_data' style='display:none'><td colspan='2'>
				<div class='evcal_row_dark' >
					<p id='evcal_meetup_msg' class='event_api_msg' style='display:none'>Message</p>
					<div class='col50'>
						<p><input type='text' id='evcal_meetup_ev_id' value='' style='width:100%'/></p>
						<p class='legend_mf'>Enter Meetup Event ID</p>
					</div>
					<div class='col50'>
						<div class='padl20'>
							<p><input id='evcal_meetup_btn_2' style='margin-left:10px'type='button' class='button' value='Get Event Data from Meetup'/></p>
						</div>
					</div>			
					
					<p class='clear'></p>					
					<p class='divider'></p>					
					<div id='evcal_meetup_s1' style='display:none'>
						<h5 class='mu_ev_id'>Retrived Event Data for: <b id='evcal_001'>321786</b></h5>
						<p class='legend_mf'>Click on each meetup event data section to populate this event with meetup event information.</p>						
						<div id='evcal_meetup_data_tb'></div>
					</div>
				</div>
			</td></tr>
		<?php endif; ?>
		
		<?php
			// PAYPAL
			if($evcal_opt1['evcal_paypal_pay']=='yes'):
		?>
		<tr class='divide'><td colspan='2'><p class='div_bar'></p></td></tr>
		<tr>
			<td colspan='2'>
				<div class='evcal_data_block_style1'>
					<p class='edb_icon evcal_edb_paypal'></p>
					<p class='evcal_db_data'><label for='evcal_paypal_link'><?php _e('Paypal Link to purchase event tickets.')?></label><br/>			
						<input type='text' id='evcal_paypal_link' name='evcal_paypal_link' value='<?php echo (!empty($ev_vals["evcal_paypal_link"]) )? $ev_vals["evcal_paypal_link"][0]:null?>' style='width:100%'/>
					</p>
				</div>				
			</td>			
		</tr>
		<?php endif;?>
		<?php
			// Learn More Link 
		?>
		<tr class='divide'><td colspan='2'><p class='div_bar'></p></td></tr>
		<tr>
			<td colspan='2'>
				<div class='evcal_data_block_style1'>
					<p class='edb_icon evcal_edb_exlink'></p>
					<div class='evcal_db_data'>
						<label for='evcal_lmlink'><?php _e('Learn More About Event Link','eventon')?> <i>(This will create a learn more link in the event card)</i></label><br/>		
						<input type='text' id='evcal_lmlink' name='evcal_lmlink' value='<?php echo (!empty($ev_vals["evcal_lmlink"]) )? $ev_vals["evcal_lmlink"][0]:null?>' style='width:100%'/><br/>
						<input type='checkbox' name='evcal_lmlink_target' value='yes' <?php echo (!empty($ev_vals["evcal_lmlink_target"]) && $ev_vals["evcal_lmlink_target"][0]=='yes')? 'checked="checked"':null?>/> <?php _e('Open in New window','eventon'); ?>
					</div>
				</div>
			</td>			
		</tr>
		<?php
			// External Link
		?>
		<tr class='divide'><td colspan='2'><p class='div_bar'></p></td></tr>
		<tr id='eventon_event_settings_externallink_metaboxrow'>
			<td colspan='2'>
				<div class='evcal_data_block_style1'>
					<p class='edb_icon evcal_edb_useri'></p>
					<div class='evcal_db_data'>
						<label for='evcal_exlink'><?php _e('User Interaction for click on event')?></label><br/>
						
						<?php
							$exlink_option = (!empty($ev_vals["_evcal_exlink_option"]))? $ev_vals["_evcal_exlink_option"][0]:1;
							$exlink_target = (!empty($ev_vals["_evcal_exlink_target"]) && $ev_vals["_evcal_exlink_target"][0]=='yes')?
								$ev_vals["_evcal_exlink_target"][0]:null;
						?>
						
						<input id='evcal_exlink_option' type='hidden' name='_evcal_exlink_option' value='<?php echo $exlink_option; ?>'/>
						
						<input id='evcal_exlink_target' type='hidden' name='_evcal_exlink_target' value='<?php echo ($exlink_target) ?>'/>
						
						<?php
							$display_link_input = (!empty($ev_vals["_evcal_exlink_option"]) && $ev_vals["_evcal_exlink_option"][0]!='1')? 'display:block':'display:none';
					
						?>
						<p <?php echo ($exlink_option=='1')?"style='display:none'":null;?> id='evo_new_window_io' class='<?php echo ($exlink_target=='yes')?'selected':null;?>'><span></span> <?php _e('Open in new window','eventon');?></p>
						
						<!-- external link field-->
						<input id='evcal_exlink' placeholder='<?php _e('Type the URL address','eventon');?>' type='text' name='evcal_exlink' value='<?php echo (!empty($ev_vals["evcal_exlink"]) )? $ev_vals["evcal_exlink"][0]:null?>' style='width:100%; <?php echo $display_link_input;?>'/>
						
						<div class='evcal_db_uis'>
							<a link='no'  class='evcal_db_ui evcal_db_ui_1 <?php echo ($exlink_option=='1')?'selected':null;?>' title='Slide Down Event Card' value='1'></a>
							
							<!-- open as link-->
							<a link='yes' class='evcal_db_ui evcal_db_ui_2 <?php echo ($exlink_option=='2')?'selected':null;?>' title='External Link' value='2'></a>	
							
							<!-- open as popup -->
							<a link='yes' class='evcal_db_ui evcal_db_ui_3 <?php echo ($exlink_option=='3')?'selected':null;?>' title='Popup Window' value='3'></a>
							
							<?php
								// (-- addon --)
								if(has_action('evcal_ui_click_additions')){
									do_action('evcal_ui_click_additions');
								}
							?>							
							<div class='clear'></div>
						</div>
					</div>
				</div>
			</td>			
		</tr>
		
		<?php
			/** custom fields **/
			
			for($x =1; $x<3; $x++){			
				
				
				if(!empty($evcal_opt1['evcal_ec_f'.$x.'a1']) && !empty($evcal_opt1['evcal_ec_f'.$x.'a2'])){
					
					$img = wp_get_attachment_image_src($evcal_opt1['evcal_ec_f'.$x.'a2'], 'full');
					
					echo "<tr class='divide'><td colspan='2'><p class='div_bar'></p></td></tr>
					<tr>
						<td colspan='2'>
							<div class='evcal_data_block_style1'>
								<p class='edb_icon evcal_metaf_icon'><img src='".$img[0]."'/></p>
								<div class='evcal_db_data'>
									<label for='_evcal_ec_f".$x."a1_cus'>".$evcal_opt1['evcal_ec_f'.$x.'a1']."</label><br/>		
									<input type='text' id='_evcal_ec_f".$x."a1_cus' name='_evcal_ec_f".$x."a1_cus' ";
									
									echo 'value="'. ((!empty($ev_vals["_evcal_ec_f".$x."a1_cus"]) )? $ev_vals["_evcal_ec_f".$x."a1_cus"][0]:null ).'"';
									
									echo "style='width:100%'/>
								</div>
							</div>
						</td>			
					</tr>";
				}
			}
			
		?>
		
		<?php 
			// (---) hook for addons
			if(has_action('eventon_metab1_end'))
				do_action('eventon_metab1_end');
		?>
	</table>
	

<?php }
	
	
/**
 * Save the Event data meta box.
 **/
function eventon_save_meta_data($post_id, $post){
	if($post->post_type!='ajde_events')
		return;
		
	// Stop WP from clearing custom fields on autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return;

	// Prevent quick edit from clearing custom fields
	if (defined('DOING_AJAX') && DOING_AJAX)
		return;

	
	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if( isset($_POST['evo_noncename']) ){
		if ( !wp_verify_nonce( $_POST['evo_noncename'], plugin_basename( __FILE__ ) ) ){
			return;
		}
	}
	// Check permissions
	if ( !current_user_can( 'edit_post', $post_id ) )
		return;	

		
	
	
	//save the post meta values
	$fields_ar =apply_filters('eventon_event_metafields', array(
		'evcal_allday','evcal_event_color','evcal_event_color_n',
		'evcal_location','evcal_organizer','evcal_exlink','evcal_lmlink',
		'evcal_gmap_gen','evcal_mu_id','evcal_paypal_link',
		'evcal_eventb_data_set','evcal_evb_id','evcal_eventb_url','evcal_eventb_capacity','evcal_eventb_tprice',
		'evcal_meetup_data_set','evcal_meetup_url','evcal_meetup_ev_id',
		'evcal_repeat','evcal_rep_freq','evcal_rep_gap','evcal_rep_num',
		'evcal_lmlink_target','_evcal_exlink_target','_evcal_exlink_option',
		'evo_hide_endtime',
		
		'_evcal_ec_f1a1_cus','_evcal_ec_f2a1_cus',
		'evcal_lat','evcal_lon',
	));
	
	
	
	// field names that pertains only to event date information
	$fields_sub_ar = apply_filters('eventon_event_date_metafields', array(
		'evcal_start_date','evcal_end_date', 'evcal_start_time_hour','evcal_start_time_min','evcal_st_ampm',
		'evcal_end_time_hour','evcal_end_time_min','evcal_et_ampm'
		)
	);
	
	
	// DATE and TIME data
	$date_POST_values='';
	foreach($fields_sub_ar as $ff){
		
		// end date value fix for -- hide end date
		if($ff=='evcal_end_date' && $_POST['evo_hide_endtime']=='yes'){
			$date_POST_values['evcal_end_date']=$_POST['evcal_start_date'];
		}else{
			$date_POST_values[$ff]=$_POST[$ff];
		}
			
		
		// remove these values from previously saved
		delete_post_meta($post_id, $ff);
	}
	
	// convert the post times into proper unix time stamps
	$proper_time = eventon_get_unix_time($date_POST_values, $_POST['_evo_date_format'], $_POST['_evo_time_format']);
	
	
	// run through all the custom meta fields
	foreach($fields_ar as $f_val){
		
		if(!empty ($_POST[$f_val])){
			
			$post_value = ( $_POST[$f_val]);
			update_post_meta( $post_id, $f_val,$post_value);		
			
		}else{
			if(defined('DOING_AUTOSAVE') && !DOING_AUTOSAVE){
				// if the meta value is set to empty, then delete that meta value
				delete_post_meta($post_id, $f_val);
			}
			delete_post_meta($post_id, $f_val);
		}
		
	}
	
	
	// full time converted to unix time stamp
	if ( !empty($proper_time['unix_start']) )
		update_post_meta( $post_id, 'evcal_srow', $proper_time['unix_start']);
	
	if ( !empty($proper_time['unix_end']) )
		update_post_meta( $post_id, 'evcal_erow', $proper_time['unix_end']);
	
	//set event color code to 1 for none select colors
	if ( !isset( $_POST['evcal_event_color_n'] ) )
		update_post_meta( $post_id, 'evcal_event_color_n',1);
	
	
	
	
	// (---) hook for addons
	do_action('eventon_save_meta', $fields_ar, $post_id);	
		
}
	
	
	

?>