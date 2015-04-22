(function($) {

	$(document).ready(function(){

    $("body").css('opacity', 1);
 
    $("a.transition").click(function(event){
        event.preventDefault();
        linkLocation = this.href;
        $("body").fadeOut(500, redirectPage);
    });

     $(".transition a").click(function(event){
        event.preventDefault();
        linkLocation = this.href;
        $("body").fadeOut(500, redirectPage);
    });

     $(".menufication-menu-level-0 li a").click(function(event){
        event.preventDefault();
        linkLocation = this.href;
        $("body").fadeOut(500, redirectPage);
    });
         
    function redirectPage() {
        window.location = linkLocation;
    }

    $('.tabs').append('<div class="team-tab"><a class="transition" href="http://jenlewinstudio.com/contact/jen-lewin-studio-team/"><span>+</span>The Team</a></div>');

	
	

});

function resizeMenu(){
	var sliderHeight = $('.master-slider-parent').height() + 20;

	$('#site-navigation').css('height', sliderHeight).addClass('opened').removeClass('closed');
}

//function openMenu(
//}



function closeMenu(){
	$('#site-navigation').addClass('closed').removeClass('opened');
}

function menuSlideUpIn() {
	$('#site-navigation').velocity("transition.slideDownBigIn", 750)
      .delay(250)
      .velocity({ opacity: 1 });
}

function menuSlideDownOut() {
	$('#site-navigation').velocity("transition.slideUpBigOut", 750)
      .delay(250)
      .velocity({ opacity: 0 });
}


$('.project-trigger a').on('click', function(){

	if($('#site-navigation').hasClass('closed')) {
		resizeMenu();
		menuSlideUpIn();
		$('.project-navigation').addClass('opened');
		return false;

	} else if($('#site-navigation').hasClass('opened'))  {
		menuSlideDownOut();
		closeMenu();
		$('.project-navigation').removeClass('opened');
		return false;

	}
	

	
});




})(jQuery);
