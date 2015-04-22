<?php
/**
 * Functions for the settings page in admin.
 *
 * The settings page contains options for the EventON plugin - this file contains functions to display
 * and save the list of options.
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	EventON/Admin/Settings
 * @version     1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/** Store settings in this array */
global $eventon_settings;

if ( ! function_exists( 'eventon_settings' ) ) {
	
	
	
	/**
	 * Settings page.
	 *
	 * Handles the display of the main EventON settings page in admin.
	 *
	 * @access public
	 * @return void
	 */
	function eventon_settings() {
		global $eventon;
		
		
		//echo "<a class='thickbox' href='http://dev.myeventon.com/wp-admin/plugin-install.php?tab=plugin-information&plugin=eventon&section=changelog&TB_iframe=true&width=600&height=800'>Test</a>";
		/////
		do_action('eventon_settings_start');
		
		
		// Settings Tabs array
		$evcal_tabs = apply_filters('eventon_settings_tabs',array(
			'evcal_1'=>__('Settings'), 
			'evcal_2'=>__('Language'),
			'evcal_3'=>__('Styles'),
			'evcal_4'=>__('Addons & Licenses')
		));
		
		
		// Get current tab/section
		$focus_tab = (isset($_GET['tab']) )? sanitize_text_field( urldecode($_GET['tab'])):'evcal_1';	
		$current_section = (isset($_GET['section']) )? sanitize_text_field( urldecode($_GET['section'])):'';	
		
		//browse for skins
		/*
		$path = AJDE_EVCAL_DIR.'/eventON/themes';	
		$skin_dirs = scandir($path);
		foreach ($skin_dirs as $skin_dir) {
			if ($skin_dir === '.' or $skin_dir === '..') continue;
			if (is_dir($path . '/' . $skin_dir)) {
				$evcal_skins[]=  $skin_dir;
			}
		}
		*/
		$evcal_skins[]=  'slick';
		
		// Update or add options
		if( isset($_POST['evcal_noncename']) && isset( $_POST ) ){
			if ( wp_verify_nonce( $_POST['evcal_noncename'], AJDE_EVCAL_BASENAME ) ){
				
				foreach($_POST as $pf=>$pv){
					if( ($pf!='evcal_styles' && $focus_tab!='evcal_4') || $pf!='evcal_sort_options'){
						
						$pv = (is_array($pv))? $pv: strip_tags(stripslashes ($pv) );
						$evcal_options[$pf] = $pv;
					}
					if($pf=='evcal_sort_options'){
						$evcal_options[$pf] =$pv;
					}
				}
				
				
				update_option('evcal_options_'.$focus_tab, $evcal_options);
				
				if( isset($_POST['evcal_styles']) )
					update_option('evcal_styles', strip_tags(stripslashes($_POST['evcal_styles'])) );
				
				$_POST['settings-updated']='true';			
			}else{
				die( __( 'Action failed. Please refresh the page and retry.', 'eventon' ) );
			}	
		}
		
		// Load eventon settings values for current tab
		$current_tab_number = substr($focus_tab, -1);		
		if(!is_numeric($current_tab_number)){ // if the tab last character is not numeric then get the whole tab name as the variable name for the options 
			$current_tab_number = $focus_tab;
		}
		
		$evcal_opt[$current_tab_number] = get_option('evcal_options_'.$focus_tab);			
			
?>
<div class="wrap" id='evcal_settings'>
	<div id='eventon'><div id="icon-themes" class="icon32"></div></div>
	<h2>EventON Settings (ver <?php echo get_option('eventon_plugin_version');?>) <?php do_action('eventon_updates_in_settings');?></h2>
	<h2 class='nav-tab-wrapper' id='meta_tabs'>
		<?php					
			foreach($evcal_tabs as $nt=>$ntv){
				$evo_notification='';
				
				echo "<a href='?page=eventon&tab=".$nt."' class='nav-tab ".( ($focus_tab == $nt)? 'nav-tab-active':null)."' evcal_meta='evcal_1'>".$ntv.$evo_notification."</a>";
			}			
		?>
		
	</h2>	
<div class='metabox-holder'>		
<?php
	
switch ($focus_tab):
	
	case "evcal_1":
		// Event type custom taxonomy
		$evt_name = (!empty($evcal_opt[1]['evcal_eventt']))?$evcal_opt[1]['evcal_eventt']:'Event Type';
		$evt_name2 = (!empty($evcal_opt[1]['evcal_eventt2']))?$evcal_opt[1]['evcal_eventt2']:'Event Type 2';
	?>
	<form method="post" action=""><?php settings_fields('evcal_field_group'); 
		wp_nonce_field( AJDE_EVCAL_BASENAME, 'evcal_noncename' );
	?>
	<div id="evcal_1" class=" evcal_admin_meta evcal_focus">		
		<div class='postbox'>
		<div class="inside">
			<?php
					
				require_once('includes/settings_settings_tab.php');
				
				// hook into addons
				if(has_filter('eventon_settings_tab1_arr_content')){
					$cutomization_pg_array = apply_filters('eventon_settings_tab1_arr_content', $cutomization_pg_array);
					
				}
				
				
				
				$updated_code = (isset($_POST['settings-updated']) && $_POST['settings-updated']=='true')? '<div class="updated fade"><p>Settings Saved</p></div>':null;
				echo $updated_code;
				
				$eventon->load_ajde_backender();		
				
				print_ajde_customization_form($cutomization_pg_array, $evcal_opt[1]);
				
			?>
			
		</div>
		</div>		
	</div>
	<div class='evo_diag'><a target='_blank' href='http://www.myeventon.com/documentation/'><img src='<?php echo AJDE_EVCAL_URL;?>/assets/images/myeventon_resources.jpg'/></a></div>
	<input type="submit" class="evo_admin_btn btn_prime" value="<?php _e('Save Changes') ?>" />
	</form>
	
	
<?php  
	break;
	
	
	// LANGUAGE TAB
	case "evcal_2":
		
		$evcal_lang_names_1 = array('evcal_lang_jan'=>'January', 'evcal_lang_feb'=>'February', 'evcal_lang_mar'=>'March',
			'evcal_lang_apr'=>'April', 'evcal_lang_may'=>'May','evcal_lang_jun'=>'June', 'evcal_lang_jul'=>'July', 'evcal_lang_aug'=>'August',
			'evcal_lang_sep'=>'September','evcal_lang_oct'=>'October','evcal_lang_nov'=>'November','evcal_lang_dec'=>'December'
		);
		$evcal_lang_names_2 = array('evcal_lang_day1'=>'Monday','evcal_lang_day2'=>'Tuesday','evcal_lang_day3'=>'Wednesday','evcal_lang_day4'=>'Thursday','evcal_lang_day5'=>'Friday',
			'evcal_lang_day6'=>'Saturday','evcal_lang_day7'=>'Sunday');
		
		
?>
<form method="post" action=""><?php settings_fields('evcal_field_group'); 
	wp_nonce_field( AJDE_EVCAL_BASENAME, 'evcal_noncename' );
?>
<div id="evcal_2" class="postbox evcal_admin_meta">	
	<div class="inside">
		<h2><?php _e('Type in custom language text','eventon');?></h2>
		<p><i><?php _e('Please use the below fields to type in custom language text that will be used to replace the default language text on the front-end of the calendar.','eventon')?></i></p>
		
		<div class='full_width_dark evcal_lang_box'>
			<?php
				foreach($evcal_lang_names_1 as $evlf=>$evlv){
					echo "<p class='evcal_lang_p'><input type='text' name='".$evlf."' class='evcal_lang' value='";
					echo ($evcal_opt[2][$evlf]!='')?  $evcal_opt[2][$evlf]: $evlv; echo "'/></p>";
				}
			?><p style='clear:both'></p>			
		</div>
		<table width='100%'>				
			<tr><td width='195px' valign='top'><p>Enable custom day names?</p></td>
			<td>
				<a class='evcal_yn_btn <?php echo ($evcal_opt[2]['evcal_cal_day_cus']=='yes')?null:'btn_at_no'?>' afterstatement='evcal_lang_days'></a>
				<input type='hidden' name='evcal_cal_day_cus' value="<?php echo ($evcal_opt[2]['evcal_cal_day_cus']=='yes')?'yes':'no';?>"/>
			</td></tr>
			<tr><td colspan='2' id='evcal_lang_days' <?php echo ($evcal_opt[2]['evcal_cal_day_cus']!='yes')?"style='display:none'":null ?>>
				<p><i>(You may not leave these blank)</i></p>
				<div class='full_width_dark evcal_lang_box' style='margin:0 -14px'>
					<?php
						foreach($evcal_lang_names_2 as $evlf=>$evlv){
							echo "<p class='evcal_lang_p'><input type='text' name='".$evlf."' class='evcal_lang' value='";
							echo ($evcal_opt[2][$evlf]!='')?  $evcal_opt[2][$evlf]: $evlv; echo "'/></p>";
						}
					?><p style='clear:both'></p>
				</div>
			</td></tr>			
			<tr><td colspan ='2'><hr/></td></tr>			
		</table>
		
		
		
		<div class='eventon_custom_lang_lines'>
		<?php
			
			require_once('includes/settings_language_tab.php');
			
			// hook into addons
			if(has_filter('eventon_settings_lang_tab_content')){
				$eventon_custom_language_array = apply_filters('eventon_settings_lang_tab_content', $eventon_custom_language_array);			
			}
			
			foreach($eventon_custom_language_array as $cl){
				$val = ($evcal_opt[2][$cl['name']]!='')?  $evcal_opt[2][$cl['name']]: '';
				echo "
					<div class='eventon_custom_lang_line'>
						<div class='eventon_cl_label_out'>
							<p class='eventon_cl_label'>{$cl['label']}</p>
						</div>
						<input class='eventon_cl_input' type='text' name='{$cl['name']}' value='{$val}'/>
						<div class='clear'></div>
					</div>";
				echo (!empty($cl['legend']))? "<p class='eventon_cl_legend'>{$cl['legend']}</p>":null;				
			}
		?>		
		</div><!-- .eventon_custom_lang_lines -->
		
		
		
		
					
	</div>
</div>
<input type="submit" class="evo_admin_btn btn_prime" value="<?php _e('Save Changes') ?>" />
</form>
<?php	
	break;
	
	// STYLES TAB
	case "evcal_3":
		
		echo '<form method="post" action="">';
		
		//settings_fields('evcal_field_group'); 
		wp_nonce_field( AJDE_EVCAL_BASENAME, 'evcal_noncename' );
				
		// styles settings tab content
		require_once('includes/settings_styles_tab.php');
	
	break;
	
	// ADDON TAB
	case "evcal_4":
		
		// Addons settings tab content
		require_once('includes/settings_addons_tab.php');

	
	break;
	
	
		
	
	case "extra":
	
	// advanced tab content
	require_once('includes/settings_advanced_tab.php');		
	
	break;
	
		default:
			do_action('eventon_settings_tabs_'.$focus_tab);
		break;
		endswitch;
		
		echo "</div>";
	}
} // * function exists 

?>