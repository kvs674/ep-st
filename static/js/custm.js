$(document).ready(function() {
	if ($(window).width() < 560) {
$(window).scroll(function() {
        var scroll = $(window).scrollTop();
        if (scroll >= 975) {
            $(".player-holder").addClass("miniplayer").css("position", "fixed");
        } else {
            $(".player-holder").removeClass("miniplayer").css("position", "inherit");
        }
		if (scroll >= 975) {
            $(".player").css("padding-bottom", "56%");
        } else {
            $(".player").css("padding-bottom", "0");
        }
    });}
	$('.load-more').click(function(){
	$( ".load-more" ).remove();
	});
$('.valueup').click(function(){
    $('.network').toggle();
});
$('.languages').click(function(){
    $('.dropdown-content').toggle();
});
$(document).on('click', function(event) {
	if (!$(event.target).closest('.valueup, #usermobile, .languages').length) {
		$('.network').hide();
		$('.mobile-menu').hide();
		$('.dropdown-content').hide();
	}
});
$('.dropdown-content li').click(function() {
	$.cookie('kt_lang', $(this).attr('data-value'), {expires: 360, path: '/'});
	window.location.reload();
});
$('.mobile-menu').hide();
if ($(window).width() < 785) {
     $('.search').hide();
    }
$('.search-mobile-icon').click(function(){
  $('.search').insertAfter(".header-inner");
  $('.search').toggle();
});	
$('#usermobile').click(function(){
$('.mobile-menu').toggle();
});	
$("#checkbox").each(function() {
    var mycookie = $.cookie($(this).attr('name'));
    if (mycookie && mycookie == "true") {
        $(this).prop('checked', mycookie);
    }
});
$("#checkbox").change(function() {
    $.cookie($(this).attr("name"), $(this).prop('checked'), {
        path: '/',
        expires: 360
    });
});	
if ($.cookie('kt_rt_skin') != '1')
    {
$("#checkbox").click(function() {
$('#checkbox').attr('checked', true);
if($(this).is(':checked'))
  {
	$.cookie('kt_rt_skin', 'dark', {expires: 360, path: '/'});
window.location.reload();
  }
else {
	$.cookie('kt_rt_skin', 'white', {expires: 360, path: '/'});
window.location.reload();}
});
}
$(window).scroll(function () {
        if ($(this).scrollTop() > 0) {
            $('.up-arrow').fadeIn();
        } else {
            $('.up-arrow').fadeOut();
        }
    });
    $('.up-arrow').click(function () {
        $('body,html').animate({
            scrollTop: 0
        }, 400);
        return false;
    });
});
