var slideLeft = new Menu({
    wrapper: '#o-wrapper',
    type: 'push-left',
    menuOpenerClass: '.c-button',
    maskId: '#c-mask'
});


$('#c-button--push-left').click(function(e) {
    e.preventDefault;
    slideLeft.open();
});

navigation();

$( window ).resize(function() {
    navigation();
})

function navigation() {
    var is_touch_device = 'ontouchstart' in document.documentElement;
    var viewport = $(window).width()
    if (viewport < 768) {

        if ($('.navigation').parents().length!=2) {
            $('body').append($('.navigation'));
            $('html').css('overflow-y','none');
            $('.c-menu__close').show();
            $('.togge-menu').height($(window).height()-50)
        }
    } else {
        if ($('.navigation').parents().length!=7) {
            $('.custom-menu').append($('.navigation'));
            $('.c-menu__close').hide();
        }
    }

    if ($('.c-button').css('display') == "inline-block") {
        $('body').append($('.navigation'));
        $('html').css('overflow','');
        $('.c-menu__close').show();
    }
    
}