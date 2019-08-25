var body = $('body');
var header = $('.header-main');
var pageWrap = $('.page-wrap');

if(body.hasClass('is--header-stick')) {
    body.css('padding-top', header.height());
}
if(body.hasClass('is--header-stick-animate')) {
    pageWrap.css('padding-top', header.height());
    $(document).scroll(function () {
        var scroll = $(window).scrollTop();

        if(scroll > header.height()) {
            if(scroll < header.attr('data-scroll')) {
                header.css('top', 0);
            } else {
                header.css('top', -header.height());
            }
        } else {
            header.css('top', -scroll);
        }

        header.attr('data-scroll', scroll);

    });
}