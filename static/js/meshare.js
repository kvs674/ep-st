document.addEventListener("DOMContentLoaded", function(event) { 

// Uses sharer.js 
//  https://ellisonleao.github.io/sharer.js/#twitter	
   var url = window.location.href;
   var title = document.title;
   var subject = "EachPorn Video";
   var via = "EachPorn";
   var desc = document.querySelector('meta[name="description"]')['content'];
   var tags = document.querySelector('meta[name="keywords"]')['content'];
   if (imgvk) {
    var imgvk = document.querySelector('meta[property="og:image"]')['content'];
}
   //console.log( url );
   //console.log( title );
//Whatsapp
$('#share-wa').attr('data-url', url).attr('data-title', title).attr('data-sharer', 'whatsapp');
//vk
$('#share-vk').attr('data-url', url).attr('data-title', title).attr('data-image', imgvk).attr('data-sharer', 'vk');
///Reddit
$('#share-reddit').attr('data-url', url).attr('data-title', title).attr('data-sharer', 'reddit');
//facebook
$('#share-fb').attr('data-url', url).attr('data-sharer', 'facebook');
///Blog
$('#share-gogl').attr('data-url', url).attr('data-title', title).attr('data-description', desc).attr('data-sharer', 'blogger');
//twitter
$('#share-tw').attr('data-url', url).attr('data-title', title).attr('data-via', via).attr('data-sharer', 'twitter');
///Tumbler
$('#share-tum').attr('data-url', url).attr('data-title', title).attr('data-tags', tags).attr('data-sharer', 'tumblr');
//linkedin
$('#share-li').attr('data-url', url).attr('data-sharer', 'linkedin');
  // email
  $('#share-em').attr('data-url', url).attr('data-title', title).attr('data-subject', subject).attr('data-sharer', 'email');
//Prevent basic click behavior
$( ".sharer" ).click(function() {
  event.preventDefault();
});


});
