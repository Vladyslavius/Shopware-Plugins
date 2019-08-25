{extends file="parent:frontend/checkout/items/product.tpl"}

{block name='frontend_checkout_cart_item_price'}
    <div class="panel--td column--unit-price is--align-right price-test">

        {if !$sBasketItem.modus}
            {block name='frontend_checkout_cart_item_unit_price_label'}
                <div class="column--label unit-price--label">
                    {s name="CartColumnPrice" namespace="frontend/checkout/cart_header"}{/s}
                </div>
            {/block}
            {if $sBasketItem.additional_details.delight_priceupdater_bool == 1
             && $sBasketItem.additional_details.delight_priceupdater_type != ''
             && $sBasketItem.additional_details.delight_priceupdater_size > 0
            }
            {if $sBasketItem.additional_details.delight_priceupdater_appendix}
                {$metallAppend = $sBasketItem.additional_details.delight_priceupdater_appendix|number}
                {$persentage = $sBasketItem.additional_details.delight_priceupdater_appendix|strpos:'%'}
                {if $persentage}
                    {$multiplicator = (100 + $metallAppend) / 100}
                    {$appendix = 0}
                {else}
                    {$multiplicator = 1}
                    {$appendix = $metallAppend|number}
                {/if}
            {else}
                {$multiplicator = ""}
                {$appendix = ""}
            {/if}
            <span class="xmlchart-price" data-type="{$sBasketItem.additional_details.delight_priceupdater_type}" data-size="{$sBasketItem.additional_details.delight_priceupdater_size}" data-tax="{$sBasketItem.additional_details.tax}" data-appendix="{$appendix}" data-multiplicator="{$multiplicator}">{/if}
            {$sBasketItem.price|currency}
            {if $sBasketItem.additional_details.delight_priceupdater_bool == 1
             && $sBasketItem.additional_details.delight_priceupdater_type != ''
             && $sBasketItem.additional_details.delight_priceupdater_size > 0
            }</span>{/if}
            {block name='frontend_checkout_cart_tax_symbol'}{s name="Star" namespace="frontend/listing/box_article"}{/s}{/block}
        {/if}
    </div>
{/block}

{block name='frontend_checkout_cart_item_total_sum'}
    <div class="panel--td column--total-price is--align-right price-test">
        {block name='frontend_checkout_cart_item_total_price_label'}
            <div class="column--label total-price--label">
                {s name="CartColumnTotal" namespace="frontend/checkout/cart_header"}{/s}
            </div>
        {/block}
        {if $sBasketItem.additional_details.delight_priceupdater_bool == 1
         && $sBasketItem.additional_details.delight_priceupdater_type != ''
         && $sBasketItem.additional_details.delight_priceupdater_size > 0
        }
            {if $sBasketItem.additional_details.delight_priceupdater_appendix}
                {$metallAppend = $sBasketItem.additional_details.delight_priceupdater_appendix|number}
                {$persentage = $sBasketItem.additional_details.delight_priceupdater_appendix|strpos:'%'}
                {if $persentage}
                    {$multiplicator = (100 + $metallAppend) / 100}
                    {$appendix = 0}
                {else}
                    {$multiplicator = 1}
                    {$appendix = $metallAppend|number}
                {/if}
            {else}
                {$multiplicator = ""}
                {$appendix = ""}
            {/if}
        <span class="xmlchart-price qty" data-type="{$sBasketItem.additional_details.delight_priceupdater_type}" data-size="{$sBasketItem.additional_details.delight_priceupdater_size}" data-tax="{$sBasketItem.additional_details.tax}" data-appendix="{$appendix}" data-multiplicator="{$multiplicator}" data-qty="{$sBasketItem.quantity}">{/if}
        {$sBasketItem.amount|currency}
        {if $sBasketItem.additional_details.delight_priceupdater_bool == 1
         && $sBasketItem.additional_details.delight_priceupdater_type != ''
         && $sBasketItem.additional_details.delight_priceupdater_size > 0
        }</span>{/if}
        {block name='frontend_checkout_cart_tax_symbol'}{s name="Star" namespace="frontend/listing/box_article"}{/s}{/block}
    </div>
{/block}

