<?php

/*
	AJDE Backender 
	version: 1.7
	Description: print out back end customization form set up for the plugin settings
	Date: 2013-6-10
*/

/** Store settings in this array */
global $print_ajde_customization_form;


if ( ! function_exists( 'print_ajde_customization_form' ) ) {
function print_ajde_customization_form($cutomization_pg_array, $nylon_option, $extra_tabs=''){

	$font_sizes = array('10px','11px','12px','13px','14px','16px','18px','20px');
	$font_styles = array('normal','bold','italic','bold-italic');
	
	//define variables
	$leftside=$rightside='';
	$count=1;
	
	foreach($cutomization_pg_array as $cpa=>$cpav){								
		// left side tabs with different level colors
		$ls_level_code = (isset($cpav['level']))? 'class="'.$cpav['level'].'"': null;
		
		$leftside .= "<li ".$ls_level_code."><a class='".( ($count==1)?'focused':null)."' c_id='".$cpav['id']."' title='".$cpav['tab_name']."'>".$cpav['tab_name']."</a></li>";								
		$tab_type = (isset($cpav['tab_type'] ) )? $cpav['tab_type']:'';
		if( $tab_type !='empty'){ // to not show the right side
			
			// RIGHT SIDE
			$display_default = (!empty($cpav['display']) && $cpav['display']=='show')?'':'display:none';
			
			$rightside.= "<div id='".$cpav['id']."' style='".$display_default."' class='nfer'>
				<h3 style='margin-bottom:10px' >".$cpav['name']."</h3>
				<em class='hr_line'></em>";
			
			foreach($cpav['fields'] as $field){
				switch ($field['type']){
					//IMAGE
					case 'image':
						$image = ''; 
						$meta = $nylon_option[$field['id']];
						
						$preview_img_size = (empty($field['preview_img_size']))?'medium'
							: $field['preview_img_size'];
						
						$rightside.= "<div id='pa_".$field['id']."'><p class='nylon_img'>".$field['name']."</p>";
						$rightside.= '<span class="custom_default_image" style="display:none">'.$image.'</span>';  
						
						if ($meta) { $image = wp_get_attachment_image_src($meta, $preview_img_size); $image = $image[0]; } 
						
						$img_code = (empty($image))? "<p class='custom_no_preview_img'><i>No Image Selected</i></p><img id='ev_".$field['id']."' src='' style='display:none' class='custom_preview_image' />"
							: '<p class="custom_no_preview_img" style="display:none"><i>No Image Selected</i></p><img src="'.$image.'" class="custom_preview_image" alt="" />';
						
						$rightside.= '<input name="'.$field['id'].'" type="hidden" class="custom_upload_image" value="'.$meta.'" />'.$img_code.'<br />';
							
						$display_choose = (empty($image))?'block':'none';
						$display_remove = (empty($image))?'none':'block';
						
						$rightside.='<input style="display:'.$display_choose.'" parent="pa_'.$field['id'].'" class="custom_upload_image_button button" type="button" value="Choose Image" />
							<small > <a href="#" style="display:'.$display_remove.'" class="custom_clear_image_button">Remove Image</a></small> 
							<br clear="all" /></div>';
					break;
					
					case 'subheader':
						$rightside.= "<h4 class='acus_subheader'>".$field['name']."</h4>";
					break;
					case 'note':
						$rightside.= "<p class='nylon_note'><i>".$field['name']."</i></p>";
					break;
					case 'hr': $rightside.= "<em class='hr_line'></em>"; break;
					case 'checkbox':
						$rightside.= "<p><input type='checkbox' name='".$field['id']."' value='yes' ".(($nylon_option[$field['id']]=='yes')?'checked="/checked"/':'')."/> ".$field['name']."</p>";
					break;
					case 'text':
						$this_value= (!empty($nylon_option[ $field['id']]))? $nylon_option[ $field['id']]: null;
						
						$default_value = (!empty($field['default']) )? 'placeholder="'.$field['default'].'"':null;
						
						$rightside.= "<p>".$field['name']."</p><p><span class='nfe_f_width'><input type='text' name='".$field['id']."' value='".$this_value."' ".$default_value."/></span></p>";
					break;
					case 'textarea':
						
						$textarea_value= (!empty($nylon_option[ $field['id']]))?$nylon_option[ $field['id']]:null;
						
						$rightside.= "<p>".$field['name']."</p><p><span class='nfe_f_width'><textarea name='".$field['id']."'>".$textarea_value."</textarea></span></p>";
					break;
					case 'font_size':
						$rightside.= "<p>".$field['name']." <select name='".$field['id']."'>";
								$nylon_f1_fs = $nylon_option[ $field['id'] ];
								
								foreach($font_sizes as $fs){
									$selected = ($nylon_f1_fs == $fs)?"selected='selected'":null;	
									$rightside.= "<option value='$fs' ".$selected.">$fs</option>";
								}
						$rightside.= "</select></p>";
					break;
					case 'font_style':
						$rightside.= "<p>".$field['name']." <select name='".$field['id']."'>";
								$nylon_f1_fs = $nylon_option[ $field['id'] ];
								foreach($font_styles as $fs){
									$selected = ($nylon_f1_fs == $fs)?"selected='selected'":null;	
									$rightside.= "<option value='$fs' ".$selected.">$fs</option>";
								}
						$rightside.= "</select></p>";
					break;
					case 'border_radius':
						$rightside.= "<p>".$field['name']." <select name='".$field['id']."'>";
								$nylon_f1_fs = $nylon_option[ $field['id'] ];
								$border_radius = array('0px','2px','3px','4px','5px','6px','8px','10px');
								foreach($border_radius as $br){
									$selected = ($nylon_f1_fs == $br)?"selected='selected'":null;	
									$rightside.=  "<option value='$br' ".$selected.">$br</option>";
								}
						$rightside.= "</select></p>";
					break;
					case 'color':
						$rightside.= "<p class='acus_line'>".$field['name']." <em><input name='".$field['id']."' class='backender_colorpicker' type='text' value='".$nylon_option[ $field['id'] ]."'/><span class='acus_colorp' style='background-color:#".$nylon_option[ $field['id']]."'></span></em></p>";
					break;
					case 'radio':
						$rightside.= "<p class='acus_line acus_radio'>".$field['name']."</br></br>";
						$cnt =0;
						foreach($field['options'] as $option=>$option_val){
							$this_value = (!empty($nylon_option[ $field['id'] ]))? $nylon_option[ $field['id'] ]:null;
							
							$checked_or_not = ((!empty($this_value) && ($option == $this_value) ) || (empty($this_value) && $cnt==0) )?
								'checked=\"checked\"':null;
							
							$rightside.="<em><input id='".$field['id'].$option_val."' type='radio' name='".$field['id']."' value='".$option."' "
							.  $checked_or_not  ."/><label for='".$field['id'].$option_val."'><span></span>".$option_val."</label></em>";
							
							$cnt++;
						}						
						$rightside.= "</p>";
						
					break;
					case 'dropdown':
						
						$dropdown_opt = (!empty($nylon_option[ $field['id'] ]))? $nylon_option[ $field['id'] ]:null;
						
						$rightside.= "<p class='acus_line'>".$field['name']." <select name='".$field['id']."'>";
						
						foreach($field['options'] as $option=>$option_val){
							$rightside.="<option type='radio' name='".$field['id']."' value='".$option."' "
							.  ( ($option == $dropdown_opt)? 'selected=\"selected\"':null)  ."/> ".$option_val."</option>";
						}						
						$rightside.= "</select></p>";						
					break;
					case 'checkboxes':
						
						$meta_ar = (!empty($nylon_option[ $field['id'] ]) )? $nylon_option[ $field['id'] ]: null;
						$meta_arr= $meta_ar;
						
						$rightside.= "<p class='acus_line acus_checks'>".$field['name']."<br/><br/> ";
						
						foreach($field['options'] as $option=>$option_val){
							$checked='';
							if(is_array($meta_arr)){
								$checked = (in_array($option, $meta_arr))?'checked':'';
							}
							
							$rightside.="<span><input id='".$field['id'].$option_val."' type='checkbox' name='".$field['id']."[]' value='".$option."' ".$checked."/><label for='".$field['id'].$option_val."'><span></span>".$option_val."</label></span>";							
						}						
						$rightside.= "</p>";						
					break;
					
					case 'yesno':
						
						$yesno_value = (!empty( $nylon_option[$field['id'] ]) )? 
							$nylon_option[$field['id']]:'no';
						
						$after_statement = (isset($field['afterstatement']) )?$field['afterstatement']:'';
						$legend_code = (!empty($field['legend']) )? "<em class='legend_icon'>?</em><em class='legend' style='display:none'>".$field['legend']."</em>":null;
						$rightside.= "<p class='yesno_row'>".$legend_code."<a afterstatement='".$after_statement."' class='acus_yn_btn ".(($yesno_value=='yes')?null:'btn_at_no')."'></a><input type='hidden' name='".$field['id']."' value='".(($yesno_value=='yes')?'yes':'no')."'/><span>".$field['name']."</span></p>";
					break;
					case 'begin_afterstatement': 
						
						$yesno_val = (!empty($nylon_option[$field['id']]))? $nylon_option[$field['id']]:'no';
						
						$rightside.= "<div class='backender_yn_sec' id='".$field['id']."' style='display:".(($yesno_val=='yes')?'block':'none')."'>";
					break;
					case 'end_afterstatement': $rightside.= "</div>"; break;	
				}
				if($field['type'] !='begin_afterstatement' && $field['type'] != 'end_afterstatement'){ $rightside.= "<em class='hr_line'></em>";}
				
			}		
			$rightside.= "</div>";
		}
		$count++;
	}
	
	//built out the backender section
	echo "<table id='ajde_customization'>
			<tr><td class='backender_left' valign='top'>
				<div id='acus_left'>
					<ul>".$leftside."</ul>					
				</div>
				</td><td width='100%'  valign='top'>
					<div id='acus_right' class='evo_backender_uix'>
						<p id='acus_arrow' style='top:4px'></p>
						<div class='customization_right_in'>
							".$rightside.$extra_tabs."
						</div>
					</div>
				</td></tr>
			</table>";
	
	
}
}
?>