jQuery(document).ready(function ($) {
    
    //Social
    $(".deluxe-social a").hover(function () {
        $(this).animate({
            opacity: 0.85
          }, 200);
    }, function(){
        $(this).animate({
            opacity: 1
          }, 100);
    });
});