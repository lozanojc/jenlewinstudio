<?php
	// EventON Settings tab - Addons and licenses
	// version: 0.2
?>
<div id="evcal_4" class="postbox evcal_admin_meta">	
	
	
	<div class="inside">
		
	<?php		
		
		$evo_installed_addons ='';
		$count =1;
		$eventon_addons_opt = get_option('eventon_addons');
		
		
		echo "<a href='http://www.myeventon.com/addons/' target='_blank'><img src='".AJDE_EVCAL_URL."/assets/images/eventon_addon_badge.png'/></a>";
		
		//delete_option('eventon_addons');
		//print_r($eventon_addons_opt);
		/*
		foreach($eventon_addons_opt  as $ff=>$tt){
			echo $ff.'<br/>';
		}*/
		
		// GET EVENTON ADDONS
		$evo_addons_url = "http://update.myeventon.com/eventon_addons.xml";
		$remote_addon_data_status = false;
		
		if(ini_get('allow_url_fopen')){
			// file get contents
			$xmlstr = file_get_contents($evo_addons_url);
			$remote_addon_data_status=true;
		
		}else if(function_exists('curl_init') ){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $evo_addons_url);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,5);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			
			$xmlstr = curl_exec($ch);
			curl_close($ch);
			$remote_addon_data_status=true;
		}
		
		
		if($remote_addon_data_status){
			$xmlcont = new SimpleXMLElement($xmlstr);			
			
			if(!empty($eventon_addons_opt) and count($eventon_addons_opt)>0 ){
				foreach($eventon_addons_opt as $tt=>$yy){
					$evo_installed_addons[]=$tt;
				}
			}else{	$evo_installed_addons=false;	}
			//print_r($evo_installed_addons);
			echo "<div class='evo_addons_list'>";
			
			// FOR EACH ADDON
			foreach($xmlcont as $addons){
				
				// Icon Image for the addon
				$img = ($addons->iconty == 'local')? AJDE_EVCAL_URL.'/'.$addons->icon: $addons->icon;
				
				
				// Check if addon is installed in the website
				$_has_addon = ($evo_installed_addons && in_array($addons->slug, $evo_installed_addons))?true:false;
				if($_has_addon){
					$_addon_options_array = $eventon_addons_opt[(string)$addons->slug];				
				}
				
				
				$guide = ($_has_addon && !empty($_addon_options_array['guide_file']) )? "<span class='evo_admin_btn btn_prime eventon_guide_btn eventon_popup_trig' ajax_url='{$_addon_options_array['guide_file']}'>Guide</span>":null;
				
				$_this_version = ($_has_addon)? "<span class='evoa_ver' title='My Version'>".$_addon_options_array['version']."</span>": null;
				
				$_hasthis_btn = ($_has_addon)? "  <span class='evo_admin_btn btn_triad'>You have this</span>":null;
				
				?>
				<div class='evoaddon_box'>
					<div class='evoa_boxe'>
					<div class='evoaddon_box_in'>	
						<div class='evoa_content'>
							<h5 style='background-image:url(<?php echo $img;?>)'><?php echo $addons->name.' '.$_this_version;?></h5>
							<p><?php echo $addons->desc;?></p>						
						</div>
						<div class='clear'></div>
						<a class='evo_admin_btn btn_prime' target='_blank' href='<?php echo $addons->link;?>'>Learn more</a>  <?php echo $guide;?><?php echo $_hasthis_btn;?>
						<?php if(!$_has_addon):?> <a class='evo_admin_btn btn_secondary' target='_blank' href='<?php echo $addons->download;?>'>Download</a><?php endif;?>
					</div>
					</div>
				</div>
				<?php			
					echo ($count%2==0)?"<div class='clear'></div>":null;
				$count++;
			}
			
			echo "<div class='clear'></div>
			</div>";
		
		}else{
			echo "<p>".__('Can not get remote eventON addon information', 'eventon')."</p>";
		}
		
		echo $eventon->output_eventon_pop_window("<p>Loading</p>",'');
		
		
	?>
		<hr></hr>	
	</div>
	
	
	
	<?php
		/*
			
			LICENSES Section
			
		*/	
	?>
	<div class='licenses_list' id='eventon_licenses'>
		<h2 class='evo_license_h2'><?php _e('EventON License','eventon');?></h2>
		<?php
			
			$show_license_msg = true;
			//delete_option('_evo_licenses');
			$evo_licenses = get_option('_evo_licenses');
			//print_r( get_option('_evo_licenses') );
			//echo AJDE_EVCAL_BASENAME;
			
			// running for the first time
			if(empty($evo_licenses)){
				
				$lice = array(
					'eventon'=>array(
						'name'=>'EventON',
						'current_version'=>$eventon->version,
						'type'=>'plugin',
						'status'=>'inactive',
						'key'=>'',
					));
				update_option('_evo_licenses', $lice);
				
				$evo_licenses = get_option('_evo_licenses');				
			}
			
			// render existing licenses
			if(!empty($evo_licenses) && count($evo_licenses)>0){
				foreach($evo_licenses as $slug=>$evl){
					if($evl['status']=='active'){
						
						$new_update_text = (!empty($evl['has_new_update']) && $evl['has_new_update'])?
							"New Version: ".$evl['remote_version']:"You got the latest version";
						$new_update_details_btn = (!empty($evl['has_new_update']) && $evl['has_new_update'])?
							"<p><a class='button-primary thickbox' href='".BACKEND_URL."plugin-install.php?tab=plugin-information&plugin=eventon&section=changelog&TB_iframe=true&width=600&height=400'>Version Details</a></p>":null;
						
						echo "
						<div class='license activated' id='license_{$evl['name']}'>
							<div class='license_msg'><p>{$new_update_text}</p></div>
							<div class='license_in'>
								<h2>{$evl['name']}</h2>
								<h3>{$evl['current_version']}</h3>
								<p>Your version</p>
								<p class='license_key'>{$evl['key']}</p>".$new_update_details_btn."	
							</div>
						</div>";
						
						$show_license_msg = false;
					}else{
						
						$new_update_text = (!empty($evl['has_new_update']) && $evl['has_new_update'])?
							"New Version: ".$evl['remote_version']:"You got the latest version";
							
						echo "
						<div class='license' id='license_{$evl['name']}'>
							<div class='license_msg'><p>{$new_update_text}</p></div>
							<div class='license_in'>
								<h2>{$evl['name']}</h2>
								<h3>{$evl['current_version']}</h3>
								<p>Your version</p>
								<a class='button eventon_popup_trig' dynamic_c='1' content_id='eventon_pop_content_001'>Activate Now</a>
							</div>
						</div>
						<div id='eventon_pop_content_001' class='evo_hide_this'>
							<h2>Activate License</h2><p>Product: <strong>{$evl['name']}</strong></p><p>License Key <br/>
							<input class='eventon_license_key_val' type='text' style='width:100%'/>
							<input class='eventon_slug' type='hidden' value='{$slug}' /></p>
							<input class='eventon_license_div' type='hidden' value='license_{$evl['name']}' /></p>
							<p><a class='button eventon_submit_license'>Activate Now</a></p>
						</div>";
						
					}
				}
			}
		?>
		
	
		<?php /*<div class='license_blank'><p>+</p></div>*/?>
		<div class='clear'></div>
		
		<?php if($show_license_msg):?>
		<p><?php _e('Activate your copy of EventON to get free automatic plugin updates direct to your site!'); ?></p>
		<?php endif;?>
		
		<?php
			// Throw the output popup box html into this page
			echo $eventon->output_eventon_pop_window('Loading...');
		?>
	</div>
</div>