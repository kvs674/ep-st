var $hs = $('.list-sort');
var $sLeft = 0;
var $hsw = $hs.outerWidth(true);

$( window ).resize(function() {
  $hsw = $hs.outerWidth(true);
});

function scrollMap($sLeft) {
  $hs.scrollLeft($sLeft);
  //$('.js-scroll').animate( { scrollLeft: $sLeft }, 10); // animate
}

$hs.on('mousewheel', function(e) {
  
  var $max = $hsw * 2 + (-e.originalEvent.wheelDeltaY);
  
  if ($sLeft > -1){
    $sLeft = $sLeft + (-e.originalEvent.wheelDeltaY);
  } else {
    $sLeft = 0;
  }
  //
  if ($sLeft > $max) {
    $sLeft = $max;
  }
  
  if(($sLeft > 0) && ($sLeft < $max)) {
    e.preventDefault();
    e.stopPropagation(); 
  }
  scrollMap($sLeft);
});