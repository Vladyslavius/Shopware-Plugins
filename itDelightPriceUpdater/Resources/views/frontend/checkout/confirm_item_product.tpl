{extends file="parent:frontend/checkout/confirm_item_product.tpl"}

{block name='frontend_checkout_cart_item_tax_price'}
    <div class="panel--td column--tax-price block is--align-right">
        {block name='frontend_checkout_cart_item_tax_label'}
            <div class="column--label tax-price--label">
                {if $sUserData.additional.charge_vat && !$sUserData.additional.show_net}
                    {s name='CheckoutColumnExcludeTax' namespace="frontend/checkout/confirm_header"}{/s}
                {elseif $sUserData.additional.charge_vat}
                    {s name='CheckoutColumnTax' namespace="frontend/checkout/confirm_header"}{/s}
                {/if}
            </div>
        {/block}

        {if $sUserData.additional.charge_vat}
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
            <span class="xmlchart-price tax-weight" data-type="{$sBasketItem.additional_details.delight_priceupdater_type}" data-size="{$sBasketItem.additional_details.delight_priceupdater_size}" data-tax="{$sBasketItem.additional_details.tax}" data-appendix="{$appendix}" data-multiplicator="{$multiplicator}" data-qty="{$sBasketItem.quantity}">{/if}
        {$sBasketItem.tax|currency}
        {if $sBasketItem.additional_details.delight_priceupdater_bool == 1
         && $sBasketItem.additional_details.delight_priceupdater_type != ''
         && $sBasketItem.additional_details.delight_priceupdater_size > 0
        }</span>{/if}
        {else}&nbsp;{/if}
    </div>
{/block}
