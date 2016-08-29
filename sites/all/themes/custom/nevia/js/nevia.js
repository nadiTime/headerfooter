(function($) {

  $(document).ready(function() {

    $('.not-front .node:not(.node-page,.node-product)').after(('<div class="line"></div>'));
    $('.node-teaser:first').addClass('first-child');
    $('.view-recent-news .views-row-last').addClass('omega').removeClass('alpha');
  });


window.resizesidebar = function() {
    var windowwidth = $(window).width();
    if(windowwidth > 768) {
        var contheight = Math.max($(".eleven.floated").outerHeight(true));
        var sbheight = Math.max($("aside.sidebar").outerHeight(true));
        if(contheight<sbheight) {
            $('.eleven.floated').css('min-height',sbheight);
        }
    } else {
        $('div.sidebar').css('min-height','auto');
        $('.eleven.floated').css('min-height','auto');
    }
};
$(window).load(function() {
    window.resizesidebar();
});
$(window).resize(function () { window.resizesidebar(); });

})(jQuery);