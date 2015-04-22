// ======================================================
// AJDE Backender Section


jQuery(document).ready(function($){

	// switching between tabs
	$('#acus_left').find('a').click(function(){
		
		// class switch
		$('#acus_left').find('a').removeClass('focused');
		$(this).addClass('focused');
		
		var menu_position = $(this).position();
		var nfer_id = $(this).attr('c_id');
		$('.nfer').hide();
		$('#'+nfer_id).show();
		//var nfer_top = parseInt($(this).attr('top'));
		$('#acus_arrow').css({'top':(menu_position.top+3)+'px'}).show();
		
		//alert(menu_position.top);
		return false;
	});
	// color picker
	$('.backender_colorpicker').ColorPicker({
		color: '#206177',
		onSubmit: function(hsb, hex, rgb, el) {
			$(el).attr({'value':hex});
			$(el).siblings('.acus_colorp').css({'background-color':'#'+hex});
			$(el).ColorPickerHide();
		}
	});
	//yes no buttons in event edit page
	$('.evo_backender_uix').on('click','.acus_yn_btn', function(){
		
		if($(this).hasClass('disable')){
		
		}else{
			// yes
			if($(this).hasClass('btn_at_no')){
				$(this).removeClass('btn_at_no');
				$(this).siblings('input').val('yes');
				
				$('#'+$(this).attr('afterstatement')).fadeIn();
				
			}else{//no
				$(this).addClass('btn_at_no');
				$(this).siblings('input').val('no');
				
				$('#'+$(this).attr('afterstatement')).fadeOut();
			}
		}
		
	});
	
	//legend
	$('.legend_icon').hover(function(){
		$(this).siblings('.legend').show();
	},function(){
		$(this).siblings('.legend').hide();
	});
	
	// image
	var formfield;
	var preview;
	var the_variable;
	
  
    $('.custom_upload_image_button').click(function() {  
		formfield = $(this).siblings('.custom_upload_image');
		var parent_id = $(this).attr('parent');
		var parent = $('#'+parent_id);
		preview = parent.find('.custom_preview_image');  
        tb_show('', 'media-upload.php?type=image&from=t31os&TB_iframe=true');
		
		window.original_send_to_editor = window.send_to_editor;
		
        window.send_to_editor = function(html) {			
			if( $(html).find('img').length ){// <img is inside <a>
				the_variable = $(html).find('img');
			}else{	the_variable = $(html);	}
			
            imgurl = $(the_variable).attr('src');  
			
			//alert(imgurl);
            classes = $(the_variable).attr('class');  
            id = classes.replace(/(.*?)wp-image-/, '');  
            formfield.val(id);  
            preview.attr('src', imgurl);
			preview.show();
            tb_remove();
			parent.find('.custom_no_preview_img').hide();
			parent.find('.custom_upload_image_button ').hide();
			parent.find('.custom_clear_image_button').show();
        }  
        return false;  
    });  
  
    $('.custom_clear_image_button').click(function() {           
        $(this).parent().siblings('.custom_upload_image').val('');  
        $(this).parent().siblings('.custom_preview_image').attr('src', '').hide();
		
		$(this).parent().siblings('.custom_no_preview_img').show();
		$(this).parent().siblings('.custom_upload_image_button ').show();
		$(this).hide();
        return false;  
    });
	
// AJDE Backender Section -- END
// ======================================================

});