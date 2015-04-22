/*
	
	EventON Generate Google maps function

*/


(function($){
	$.fn.evoGenmaps = function(opt){
		
		var defaults = {
			delay:	0,
			fnt:	1,
			cal:	'',
			mapSpotId:	''
		};
		var options = $.extend({}, defaults, opt); 
		
		var geocoder;
		
		
		if(options.fnt==1){
			this.each(function(){
				var eventcard = $(this).attr('eventcard');
			
				if(eventcard=='1'){
					$(this).find('a.desc_trig').each(function(elm){
						//$(this).siblings('.event_description').slideDown();
						var obj = $(this);
						
						if(options.delay==0){
							load_googlemaps_here(obj);
						}else{
							setTimeout(load_googlemaps_here, options.delay, obj);
						}
					});
				}
			});
		}
		
		if(options.fnt==2){
			load_googlemaps_here(this);			
		}
		if(options.fnt==3){
			loadl_gmaps_in(this, options.cal, '');			
		}
		
		// gmaps on popup
		if(options.fnt==4){
			// check if gmaps should run
			if( this.attr('gmtrig')=='1' && this.attr('gmap_status')!='null'){
			
				var cal = this.closest('div.ajde_evcal_calendar ');
				loadl_gmaps_in(this, cal, options.mapSpotId);
			}			
			
		}
		
		
		// function to load google maps for eventcard
		function load_googlemaps_here(obj){
			if( obj.attr('gmstat')!= '1'){				
				obj.attr({'gmstat':'1'});
			}
			
			var cal = obj.closest('div.ajde_evcal_calendar ');
			
			if( obj.attr('gmtrig')=='1' && obj.attr('gmap_status')!='null'){
				loadl_gmaps_in(obj, cal, '');
			}			
				
		}
		
		
		// Load the google map on the object
		function loadl_gmaps_in(obj, cal, mapId){
			var mapformat = cal.attr('mapformat');				
			var ev_location = obj.find('.evcal_desc');
			
			var latlon = ev_location.attr('latlon');
			if(latlon=='1'){
				var address = ev_location.attr('latlng');
				var location_type = 'latlng';
			}else{
				var address = ev_location.attr('add_str');
				var location_type = 'add';
			}
			
			
			var map_canvas_id= (mapId!=='')?
				mapId:
				obj.siblings('.event_description').find('.evcal_gmaps').attr('id');
				
				
			var zoom = cal.attr('mapzoom');
			var zoomlevel = (typeof zoom !== 'undefined' && zoom !== false)? parseInt(zoom):12;
			
			var scroll = cal.attr('mapscroll');		
								
			//obj.siblings('.event_description').find('.evcal_gmaps').html(address);
			initialize(map_canvas_id, address, mapformat, zoomlevel, location_type, scroll);

			//console.log(map_canvas_id+' '+mapformat+' '+ location_type +' '+scroll +' '+ address);
		}
		
		
		
		//console.log(options);
		
	};
}(jQuery));