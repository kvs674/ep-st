if($(window).width()>1280){
$('<span class="preloader"></span').insertAfter('.timer-thumbnail');
$(".js_show_porn_videos,#porn_videos_panel").hover(function(){
    $('#porn_videos_panel').show();
    $('.slimScrollDiv').css('overflow-y','hidden');
},function(){  
    $('#porn_videos_panel').hide();
    $('.slimScrollDiv').css('overflow-y','scroll');
});
$(".js_show_categories,#categories_panel").hover(function(){
    $('#categories_panel').show();
$('.slimScrollDiv').css('overflow-y','hidden');
},function(){
    $('#categories_panel').hide();
$('.slimScrollDiv').css('overflow-y','scroll');
});
$(".js_show_pornstars,#pornstars_panel").hover(function(){
    $('#pornstars_panel').show();
$('.slimScrollDiv').css('overflow-y','hidden');
},function(){
    $('#pornstars_panel').hide();
$('.slimScrollDiv').css('overflow-y','scroll');
});
$(".js_show_channels,#channels_panel").hover(function(){
    $('#channels_panel').show();
$('.slimScrollDiv').css('overflow-y','hidden');
},function(){
    $('#channels_panel').hide();
$('.slimScrollDiv').css('overflow-y','scroll');
});
}
$('.menu_toggle').click(function(){
  $('#sidemenu_wrap').slideToggle(10);
  $('body').toggleClass('menu_hide', '');
if($(window).width()>=1280){
if ($('.menu_hide').length <= 0) {
       $('.navigation, .content').css({'margin-left':'300px'});
       $('#footer_wrapper').css({'margin-left':'170px'});
       $('html').css('overflow-x','hidden');  
   }else{
       $('.navigation, .content').css({'margin':'0 auto','margin-left':'auto'});
       $('#footer_wrapper').css({'margin':'0 auto', 'margin-left':'auto'});
 }
}
});
$('.list-videos .img').hover(function(){
     $('.list-videos .img .pc').removeClass('pc');
     $(this).addClass('pc');
     $('.pc .watchLaterIcon').css('display','block');
});
$('.list-videos .img').mouseleave(function(){
     $(this).removeClass('pc');
     $('.pc .watchLaterIcon').css('display','none');
     $('.watchLaterIcon').css('display','none');
});
$('.menu_min_list .menu_min_elem').hover(function(){
  $('.menu_min_list .menu_min_elem .active').removeClass('active');
  $(this).addClass('active');
  $('.active .side_menu_triangle').css('color','#fff');
    $('.menu_min_list .menu_min_elem').mouseleave(function(){
      $(this).removeClass('active');
      $('.side_menu_triangle').css('color','');
    });
});
$('.js_upgrade_modal').bind("click",function(){
  $('#signup').click();
});
$('#upgrade_star_icon').bind("click",function(){
  $('#signup').click();
});
$('#submenu_library_submit').bind("click",function(){
  $('#login').click();
});
$('#upload_btn').bind("click",function(){
  $('#upd_btn-click').click();
});
$('.upload_button').bind("click",function(){
  $('#upd_btn-click').click();
});
if(window.location.href=='{{$config.statics_url}}/members/'){
$('.sidebar').css('display','block');
$('.content').css({'max-width':'973px'})
}
if (window.location.href.indexOf("videos") > -1){
$('.adv-main').remove();
$('.related-videos, .related-albums').css({'padding': '0 1.2%',
    'width': '973px',
    'background-color': 'rgb(26, 26, 26)',
    'margin-bottom': '15px',
    'box-sizing': 'border-box'})
}
if("ontouchstart" in document){
$('.item').on("touchstart", function(){
   $('.item').removeClass('active');
   $(this).addClass('active');
})
}
$('#quick_link_upgrade').click(function(){
$('#signup').click();
});
$('#search_icon_wrapper').click(function(){
$('.search').css('display','block');
})
$('#search_form_close').click(function(){
$('.search').css('display','none');
})
$('.login_tab').click(function(){
$('#login').click();
});
$('.signup_tab').click(function(){
$('#signup').click();
});
let display = false;
$('.language-changer').click(function(){
  $('.lang-sub').toggle();
  $('.rt_Round_Ended_Arrow_Up_Down').toggleClass("arrowLang"," " );
});
$('.item').hover(function(){
   $('.item').removeClass('active');
   $(this).addClass('active');
})
$('#header_nav_avatar').click(function(){
 $('#user_menu').slideToggle(10);
});
$('.user_menu').click(function(){
 $('.submenu').slideToggle(10);
});
if($(window).width()<=1280){
$('.js_show_porn_videos').click(function(){
   $('.pornvideo_submenu_mobile').toggle();
})
$("#main-video-mobile-selector").removeAttr("href");
}
$('.js_expend_btn1').click(function(){
$('#footer_connect_links').slideToggle(10);
});
$('.js_expend_btn2').click(function(){
$('#footer_work_links').slideToggle(10);
});
$('.js_expend_btn3').click(function(){
$('#footer_info_links').slideToggle(10);
});
const lang = $.cookie("kt_lang");
if(lang=='ru'){
  $('.language-selected strong, .language-changer .js_expend_menu .menu_elem_text').text('Русский');
}
if(lang=='en'){
  $('.language-selected strong, .language-changer .js_expend_menu .menu_elem_text').text('English');
}
if(lang=='de'){
  $('.language-selected strong, .language-changer .js_expend_menu .menu_elem_text').text('Deutsch');
}
if(lang=='fr'){
  $('.language-selected strong, .language-changer .js_expend_menu .menu_elem_text').text('Français');
}
if(lang=='it'){
  $('.language-selected strong, .language-changer .js_expend_menu .menu_elem_text').text('Italiano');
}
if(lang=='es'){
  $('.language-selected strong, .language-changer .js_expend_menu .menu_elem_text').text('Español');
}
if(lang=='pl'){
  $('.language-selected strong, .language-changer .js_expend_menu .menu_elem_text').text('Polski');
}
if(lang=='pt'){
  $('.language-selected strong, .language-changer .js_expend_menu .menu_elem_text').text('Português');
}
if(lang=='nl'){
  $('.language-selected strong, .language-changer .js_expend_menu .menu_elem_text').text('Nederlands');
}
if(lang=='ja'){
  $('.language-selected strong, .language-changer .js_expend_menu .menu_elem_text').text('日本語');
}
if (window.location.href.indexOf("most-popular") > -1 || window.location.href.indexOf("top-rated") > -1 || window.location.href.indexOf("newest") > -1){
$('.headline h1').css({'font-weight':'700','font-size':'23px'});
}
if (window.location.href.indexOf("videos") > -1){
$('#tab_comments').insertAfter('.related-videos')
$('#footer-textcloud').css('display','none')
}
if (window.location.href.indexOf("recommended") > -1){
$('.headline h1').hover(function(){
   $('.headline h1').css('text-decoration', 'none')
});
}
//popup
$('#cboxClose').click(function(){
$('#colorbox').css('display','none');
})
$('#tab_downloads').click(function(){
$('#colorbox').css('display','block');
})
//tags list scroll
let stepScroll = 50;
$('.next_icon').click(function(){
   stepScroll+=250;
   $('.list-tags').animate({scrollLeft:stepScroll},500);
});
$('.prev_icon').click(function(){
   stepScroll-=250;
   $('.list-tags').animate({scrollLeft:stepScroll},500);
});
$("a:contains('...')").each(function(){
    $(this).text($(this).text().replace('...',''));
});
$(".fade-models").hide(0).delay(2000).fadeIn(2500)