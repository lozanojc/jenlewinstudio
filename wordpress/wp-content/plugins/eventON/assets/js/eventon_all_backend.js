/*
	Script that runs on all over the backend pages
	ver: 1.3
*/
jQuery(document).ready(function($){
	
	// ----------
	// EventON Sitewide POPUP
	// ----------
	// hide
		
	$('#eventon_popup').on('click','.eventon_close_pop_btn', function(){
		var obj = $(this);
		hide_popupwindowbox( obj);
	});
	
	$('.eventon_popup_text').on('click',' .evo_close_pop_trig',function(){
		var obj = $(this).parent();
		hide_popupwindowbox( obj);
	});
	
	$(document).mouseup(function (e){
		var container=$('#eventon_popup');
		
		if(container.hasClass('active')){
			if (!container.is(e.target) // if the target of the click isn't the container...
			&& container.has(e.target).length === 0) // ... nor a descendant of the container
			{
				container.animate({'margin-top':'70px','opacity':0}).fadeOut().removeClass('active');
			}
		}
	});
	
	// function to hide popup that can be assign to click actions
	function hide_popupwindowbox(obj){
		
		var container=obj.parent();
		var clear_content = container.attr('clear');
		
		if(container.hasClass('active')){
			container.animate({'margin-top':'70px','opacity':0},300).fadeOut().
				removeClass('active')
				.delay(300)
				.queue(function(n){
					if(clear_content=='true')					
						$(this).find('.eventon_popup_text').html('');
						
					n();
				})				
				
		}
	}
	
	
	
	/*
		DISPLAY Eventon in-window popup box
		Usage: <a class='button eventon_popup_trig' content_id='is_for_content' dynamic_c='yes'>Click</a>
	*/
	$('.eventon_popup_trig').click(function(){
		
		// dynamic content within the site
		var dynamic_c = $(this).attr('dynamic_c');
		if(typeof dynamic_c !== 'undefined' && dynamic_c !== false){
			
			var content_id = $(this).attr('content_id');
			var content = $('#'+content_id).html();
			
			$('#eventon_popup').find('.eventon_popup_text').html( content);
		}
		
		// if content coming from a AJAX file
		var attr_ajax_url = $(this).attr('ajax_url');
		
		if(typeof attr_ajax_url !== 'undefined' && attr_ajax_url !== false){
			
			$.ajax({
				beforeSend: function(){
					show_pop_loading();
				},
				url:attr_ajax_url,
				success:function(data){
					$('#eventon_popup').find('.eventon_popup_text').html( data);			
					
				},complete:function(){
					hide_pop_loading();
				}
			});
		}
		
		$('#eventon_popup').find('.message').removeClass('bad good').hide();
		$('#eventon_popup').addClass('active').show().animate({'margin-top':'0px','opacity':1}).fadeIn();
	});
	
	
	// licenses verification and saving
	$('#eventon_popup').on('click','.eventon_submit_license',function(){
		
		$('#eventon_popup').find('.message').removeClass('bad good');
		
		var parent_pop_form = $(this).parent().parent();
		var license_key = parent_pop_form.find('.eventon_license_key_val').val();
		
		if(license_key==''){
			show_pop_bad_msg('License key can not be blank! Please try again.');
		}else{
			
			var slug = parent_pop_form.find('.eventon_slug').val();
			
			var data_arg = {
				action:'eventon_verify_lic',
				key:license_key,
				slug:slug
			};					
			
			$.ajax({
				beforeSend: function(){
					show_pop_loading();
				},
				type: 'POST',
				url:the_ajax_script.ajaxurl,
				data: data_arg,
				dataType:'json',
				success:function(data){
					if(data.status=='success'){
						var lic_div = parent_pop_form.find('.eventon_license_div').val();
						$('#'+lic_div).addClass('activated').find('.license_in').html(data.new_content);
						
						show_pop_good_msg('License key verified and saved.');
						$('#eventon_popup').delay(3000).queue(function(n){
							$(this).animate({'margin-top':'70px','opacity':0}).fadeOut();
							n();
						});
						
					}else{
						show_pop_bad_msg('Could not verify the License key. Please try again.');
					}					
					
				},complete:function(){
					hide_pop_loading();
				}
			});
		}
	});
	
	function show_pop_bad_msg(msg){
		$('#eventon_popup').find('.message').removeClass('bad good').addClass('bad').html(msg).fadeIn();
	}
	function show_pop_good_msg(msg){
		$('#eventon_popup').find('.message').removeClass('bad good').addClass('good').html(msg).fadeIn();
	}
	
	function show_pop_loading(){
		$('.eventon_popup_text').css({'opacity':0.3});
		$('#eventon_loading').fadeIn();
	}
	function hide_pop_loading(){
		$('.eventon_popup_text').css({'opacity':1});
		$('#eventon_loading').fadeOut(20);
	}
	
	
	
	// widget
	$('.widget-content').on('click','.evowig_chbx', function(){
		
		if($(this).hasClass('selected')){
			$(this).removeClass('selected');
			
			$(this).siblings('input').val('no');
			$(this).parent().siblings('.evo_wug_hid').slideUp('fast');
		}else{
			$(this).addClass('selected');
			
			$(this).siblings('input').val('yes');
			$(this).parent().siblings('.evo_wug_hid').slideDown('fast');
		}
		
		
	});
	
	$('.widget-content').on('click','.legend_icon', function(){
		$(this).siblings('.legend').toggle();
	});
});