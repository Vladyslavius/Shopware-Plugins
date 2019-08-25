{block name='frontend_index_header_javascript_inline' prepend}
    {$priceUpdateConfig = [
        "loadUrl"       => "{url controller='PriceUpdate' action='load'}",
        "checkoutUrl"   => "{url controller='Checkout' action='cart'}",
        "taxView"       => {$taxView}
    ]}

    var priceUpdateConfig = priceUpdateConfig || {$priceUpdateConfig|json_encode};
{/block}
