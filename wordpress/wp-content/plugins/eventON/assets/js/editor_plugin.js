/*
	Shortcode control
*/

jQuery(document).ready(function($){
		
	tinymce.create('tinymce.plugins.EventONShortcodes', {
	  init : function(ed, url) {
		 ed.addButton('EventONShortcodes', {
			title : 'Add EventON Calendar',
			onclick : function() {
			   
				var eventon_guide_obj =$('#eventon_popup');
				eventon_guide_obj.show().animate({'margin-top':'0px','opacity':1}).fadeIn().addClass('active');
			   
				//add shortcode to the editor
				eventon_guide_obj.find('.eventon_shortcode_btn').unbind('click').click(function(){
					var shortcode = $(this).attr('scode');
					ed.execCommand('mceInsertContent', false, shortcode);
					eventon_guide_obj.animate({'margin-top':'70px','opacity':0}).fadeOut();
				});
			   
			}
		 });
	  },
	  createControl : function(n, cm) {
		 return null;
	  },
	  getInfo : function() {
		 return {
			longname : "EventON Shortcode",
			author : 'Ashan Jay',
			authorurl : 'http://www.ashanjay.com',
			version : "1.0"
		 };
	  }
   });
   tinymce.PluginManager.add('EventONShortcodes', tinymce.plugins.EventONShortcodes);
});