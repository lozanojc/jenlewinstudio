/*
	Javascript code that is associated with the front end of the calendar
	version: 1.9
*/
jQuery(document).ready(function($){
	
	$('.ajde_evcal_calendar').evoGenmaps();
	
	
	// popup
	var popupcode = "<div class='evo_popup' style='display:none'><div class='evo_popin'><a class='evopopclose'>X</a><div class='evo_pop_body evcal_eventcard'></div></div></div><div class='evo_popbg' style='display:none'></div>";
	$('body').append(popupcode);
	
		// close popup
		$('.evopopclose').click(function(){
			$(this).closest('.evo_popup').fadeOut().siblings('.evo_popbg').fadeOut();
		});
		// close with click outside popup box when pop is shown
		$(document).mouseup(function (e){
			var container=$('.evo_pop_body');
			
				if (!container.is(e.target) // if the target of the click isn't the container...
				&& container.has(e.target).length === 0) // ... nor a descendant of the container
				{
					$('.evo_popup').fadeOut(300);
					$('.evo_popbg').fadeOut(300);
				}
			
		});
	
		
	//event full description
	$('.eventon_events_list').on('click','a.desc_trig', function(){
		var obj = $(this);
		
		var exlk = obj.attr('exlk');
		
		
		// open as popup
		if(obj.attr('ux_val')=='3'){
			
			var cur_window_top = parseInt($(window).scrollTop()) + 50;
			$('.evo_popin').css({'margin-top':cur_window_top});
			
			$('.evo_pop_body').html('');
			var content = obj.siblings('.event_description').html();
			var content_front = obj.html();
			
			var _content = $(content).not('.evcal_close');
			
		
			$('.evo_pop_body').append('<div class="evopop_top">'+content_front+'</div>').append(_content);
			
			var this_map = $('.evo_pop_body').find('.evcal_gmaps');
			var idd = this_map.attr('id');
			this_map.attr({'id':idd+'_evop'});
			
			$('.evo_popup').fadeIn(300);
			$('.evo_popbg').fadeIn(300);
			//$('html,body').animate({ scrollTop: 0 }, 'slow');
			
			
			obj.evoGenmaps({
				'fnt':4,
				'mapSpotId':idd+'_evop'
			});
			
			
			return false;
		}else{
		
			if(exlk=='1' ){
				return;
			}else{
				$(this).siblings('.event_description').slideToggle();
				
				// This will make sure markers and gmaps run once and not multiples			
				if( obj.attr('gmstat')!= '1'){				
					obj.attr({'gmstat':'1'});
					//load_googlemaps(obj);
					
					obj.evoGenmaps({'fnt':2});
				}
						
				return false;
			}
		}
	});
	
	
	
	
	
	// close event card
	$('.eventon_events_list').on('click','.evcal_close',function(){
		$(this).parent().slideUp();
	});
	
	
	
	// change IDs for map section for eventon widgets
	if( $('.ajde_evcal_calendar').hasClass('evcal_widget')){
		cal.find('.evcal_gmaps').each(function(){
			var gmap_id = obj.attr('id');
			var new_gmal_id =gmap_id+'_widget';
			obj.attr({'id':new_gmal_id})
		});
	}			
	
	
	
	//===============================
	// SORT BAR SECTION
	// ==============================
	
	// display sort section
	$('.evo_sort_btn').click(function(){
		$(this).siblings('.eventon_sorting_section').find('.evo_sortOpt').slideToggle('fast');
	});	
	
	// sorting section	
	$('.sorting_set_val').click(function(){		
		$('.eventon_sortbar_selection').fadeToggle();
	});
	
		// update calendar based on the sorting selection
		$('.eventon_sortbar_selection').on('click','.evs_btn',function(){		
				
			var cal_head = $(this).closest('.eventon_sorting_section').siblings('.calendar_header');
			var cur_m = parseInt( cal_head.attr('cur_m'));
			var cur_y = parseInt( cal_head.attr('cur_y'));	
			var sort_by = $(this).attr('val');
			var new_sorting_name = $(this).html();
			var cal_id = cal_head.parent().attr('id');	
						
			ajax_post_content(cur_m,cur_y,sort_by,cal_id);
			
			// update new values everywhere
			cal_head.attr({'sort_by':sort_by});		
			$(this).parent().find('p').removeClass('evs_hide');
			$(this).addClass('evs_hide');		
			$(this).parent().siblings('.eventon_sf_cur_val').find('p').html(new_sorting_name);
			$(this).parent().hide();
		});
	
	
	// filtering section
	$('.filtering_set_val').click(function(){
		$(this).siblings('.eventon_filter_dropdown').fadeIn();
	});	
	
		// selection on filter dropdown list
		$('.eventon_filter_dropdown').on('click','p',function(){
			var new_filter_val = $(this).attr('filter_val');
			var filter = $(this).closest('.eventon_filter');
			var filter_current_set_val = filter.attr('filter_val');
			
			if(filter_current_set_val == new_filter_val){
				$(this).parent().fadeOut();
			}else{
				// set new filtering changes				
				var cal_head = $(this).closest('.eventon_sorting_section').siblings('.calendar_header');
				var cur_m = parseInt( cal_head.attr('cur_m'));
				var cur_y = parseInt( cal_head.attr('cur_y'));	
				var sort_by = cal_head.attr('sort_by');
				var cal_id = cal_head.parent().attr('id');				
				
				// make changes
				filter.attr({'filter_val':new_filter_val});	
				cal_head.attr({'filters_on':'true'});
				
				ajax_post_content(cur_m,cur_y,sort_by,cal_id);
				
				// reset the new values				
				var new_filter_name = $(this).html();
				$(this).parent().fadeOut();
				$(this).parent().siblings('.filtering_set_val').html(new_filter_name);
			}
		});
	

	
	// previous month
	$('.evcal_btn_prev').click(function(){
		var cal_head = $(this).parents('.calendar_header');
		var parent_bar = cal_head.siblings('div.evcal_sort');
				
		var sort_by=parent_bar.attr('sort_by');
		var cur_m = parseInt( cal_head.attr('cur_m'));
		var cur_y = parseInt( cal_head.attr('cur_y'));
		var new_m = (cur_m==1)?12:cur_m-1;
		var new_y = (cur_m==1)?cur_y-1:cur_y;
		
		cal_id = $(this).closest('.ajde_evcal_calendar').attr('id');
		
		ajax_post_content(new_m,new_y,sort_by,cal_id);
		
	});
	
	// next month
	$('.evcal_btn_next').click(function(){	
		
		var cal_head = $(this).parents('.calendar_header');
		var parent_bar = cal_head.siblings('div.evcal_sort');
		
		var sort_by=parent_bar.attr('sort_by');
		var cur_m = parseInt(cal_head.attr('cur_m'));
		var cur_y = parseInt(cal_head.attr('cur_y'));
		var new_m = (cur_m==12)?1: cur_m+ 1 ;
		var new_y = (cur_m==12)? cur_y+1 : cur_y;
		
		cal_id = $(this).closest('.ajde_evcal_calendar').attr('id');
		
		ajax_post_content(new_m,new_y,sort_by, cal_id);
		
	});
	
	
	/*
		PRIMARY hook to get content
	*/
	function ajax_post_content(new_m, new_y,sort_by,cal_id){
		
		// identify the calendar and its elements.
		var ev_cal = $('#'+cal_id); 
		var cal_head = ev_cal.find('.calendar_header');	
		var filters_on = ( cal_head.attr('filters_on')=='true')?'true':'false';
		
		// creat the filtering data array if exist
		if(filters_on =='true'){
			var filter_section = ev_cal.find('.eventon_filter_line');
			var filter_array = [];
			
			filter_section.find('.eventon_filter').each(function(index){
				var filter_val = $(this).attr('filter_val');
				
				if(filter_val !='all'){
					var filter_ar = {};
					filter_ar['filter_type'] = $(this).attr('filter_type');
					filter_ar['filter_name'] = $(this).attr('filter_field');
					filter_ar['filter_val'] = filter_val;
					filter_array.push(filter_ar);
				}
			});			
		}else{
			var filter_array ='';
		}
		
			
		
		// category filtering for the calendar
		var cat = ev_cal.find('.evcal_sort').attr('cat');
		var event_count = parseInt(cal_head.attr('ev_cnt'));
		
		var data_arg = {
			action:'the_ajax_hook',
			next_m:new_m,	
			next_y:new_y,	
			sort_by:sort_by, 
			event_count:event_count,
			filters:filter_array
		};
		
		cal_head.find('.eventon_other_vals').each(function(){
			if($(this).val()!=''){
				data_arg[$(this).attr('name')] = $(this).val();
			}
		});
		
		
		$.ajax({
			beforeSend: function(){
				ev_cal.find('.eventon_events_list').slideUp('fast');
				ev_cal.find('#eventon_loadbar').show().css({width:'0%'}).animate({width:'100%'});
			},
			type: 'POST',
			url:the_ajax_script.ajaxurl,
			data: data_arg,
			dataType:'json',
			success:function(data){
				//alert(data);
				ev_cal.find('.eventon_events_list').html(data.content);
				animate_month_switch(data.cal_month_title, ev_cal.find('#evcal_cur'));
				
				ev_cal.find('#evcal_head').attr({'cur_m':new_m,'cur_y':new_y});
			},complete:function(){
				ev_cal.find('#eventon_loadbar').css({width:'100%'}).fadeOut();
				ev_cal.find('.eventon_events_list').delay(300).slideDown('slow');
				ev_cal.evoGenmaps({'delay':400});
			}
		});
		
	}
	
	
	function animate_month_switch(new_data, title_element){
		
		var current_text = title_element.html();
		var hei = title_element.height();
		var wid= title_element.width();
		
		title_element.html("<span style='position:absolute; width:"+wid+"; height:"+hei+" ;'>"+current_text+"</span><span style='position:absolute; display:none;'>"+new_data+"</span>").width(wid);
		
		
		title_element.find('span:first-child').fadeOut(800); 
		title_element.find('span:last-child').fadeIn(800, function(){
			title_element.html(new_data).width('');
		});
		
		
	}
	
	
	// show more and less of event details
	$('.eventon_events_list').on('click','.eventon_shad_p',function(){		
		control_more_less( $(this) );		
	});
	
	$('.evo_pop_body').on('click','.eventon_shad_p',function(){		
		control_more_less( $(this));		
	});	
	
	// actual animation/function for more/less button
	function control_more_less(obj){
		
		
		var content = obj.attr('content');
		var current_text = obj.find('.ev_more_text').html();
		var changeTo_text = obj.find('.ev_more_text').attr('txt');
		
		if(content =='less'){			
			
			var hei = obj.parent().siblings('.eventon_full_description').height();
			var orhei = obj.closest('.evcal_evdata_cell').height();
			
			obj.closest('.evcal_evdata_cell').attr({'orhei':orhei}).animate({height: (parseInt(hei)+40) });
			
			obj.attr({'content':'more'});
			obj.find('.ev_more_arrow').addClass('less');
			obj.find('.ev_more_text').attr({'txt':current_text}).html(changeTo_text);
			
		}else{
			var orhei = parseInt(obj.closest('.evcal_evdata_cell').attr('orhei'));
			
			obj.closest('.evcal_evdata_cell').animate({height: orhei });
			
			obj.attr({'content':'less'});
			obj.find('.ev_more_arrow').removeClass('less');
			obj.find('.ev_more_text').attr({'txt':current_text}).html(changeTo_text);
		}
	}
	
	
	// expand and shrink featured image
	$('.ajde_evcal_calendar').on('click','.evcal_evdata_img',function(){
		var img_status = $(this).attr('status');
		
		if(img_status=='open'){
			$(this).attr({'status':'close'}).css({'height':''});
		}else{
			var imgh = $(this).attr('imgheight');
			$(this).css({'height':imgh}).attr({'status':'open'});
		}
	});
	
})