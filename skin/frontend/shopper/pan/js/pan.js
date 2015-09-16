jQuery(function($){

    // Mobile menu animations
    $('.mobile-menu').before('<div class="nav-top-title"><div class="icon"><span></span><span></span><span></span></div><a href="javascript:void(0);">Navigation</a></div>');

    $('.mobile-menu li.menu-item-parent').append('<a href="javascript:void(0);" class="arrow">+</a>')

    $('.em_nav .nav-top-title').click(function() {
        $('.mobile-menu').slideToggle();
    });

    $('.mobile-menu a.arrow').click(function() {
        $(this).siblings('ul').slideToggle();

        if ($(this).text() == "+") {
            $(this).text("-");
        } else {
            $(this).text("+");
        }
    });
});
