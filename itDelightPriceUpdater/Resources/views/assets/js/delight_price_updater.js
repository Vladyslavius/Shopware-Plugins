$(document).ready(function(){
    var body = $('body');
    if($(body).hasClass('is--ctl-checkout')) {
        setInterval(explode, 15000);
    } else {
        setInterval(explode, 60000);
    }
});
function explode() {
    $.publish('plugin/swCollapseCart/onRemoveArticleFinished');

    $.ajax({
        url: window.priceUpdateConfig.checkoutUrl,
        method: 'POST',
        success: function (result) {
            var totals = $(result).find('.aggregation--list')[0];
            if(totals) {
                $('.aggregation--list').html($(totals).html());
            }
        }
    });
    
    $.ajax({
        url: window.priceUpdateConfig.loadUrl,
        dataType: 'json',
        method: 'POST',
        success: function (result) {
            $('.xmlchart-price').each(function(i, v) {
                var type            = $(v).data("type"),
                    size            = $(v).data("size"),
                    tax             = $(v).data("tax"),
                    appendix        = $(v).data("appendix"),
                    multiplicator   = $(v).data("multiplicator"),
                    price;

                if(appendix) {
                    appendix = parseFloat(appendix.replace(",", "."));
                }

                $(result.data).each(function(i, el) {
                    if(el.type == type) {
                        if(multiplicator) {
                            price = (size * el.price + appendix) * multiplicator;
                        } else {
                            price = (size * el.price + el.appendix) * el.multiplicator;
                        }

                        if($(v).hasClass('tax-weight')) {
                            price = price * (100 + tax) / 100;
                            price = price * tax / (100 + tax);
                            price = price * $(v).data("qty");
                        } else {
                            if(window.priceUpdateConfig.taxView == 1) {
                                price = price * (100 + tax) / 100;
                            }
                            if($(v).hasClass('qty')) {
                                price = price.toFixed(2) * $(v).data("qty");
                            }
                        }
                        $(v).html(price).formatCurrency({ region: "de-DE" });
                    }
                });
            });
        }
    });
}
