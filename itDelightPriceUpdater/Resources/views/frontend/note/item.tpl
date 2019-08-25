{extends file="parent:frontend/note/item.tpl"}

{block name="frontend_note_item_price"}
    {if $sBasketItem.itemInfo}
        {$sBasketItem.itemInfo}
    {else}
        <div class="note--price">{if $sBasketItem.priceStartingFrom}{s namespace='frontend/listing/box_article' name='ListingBoxArticleStartsAt'}{/s} {/if}
        {if $sBasketItem.priceStartingFrom}{s name='ListingBoxArticleStartsAt'}{/s} {/if}
        {if $sBasketItem.delight_priceupdater_bool == 1
         && $sBasketItem.delight_priceupdater_type != ''
         && $sBasketItem.delight_priceupdater_size > 0
        }
            {if $sBasketItem.delight_priceupdater_appendix}
                {$metallAppend = $sBasketItem.delight_priceupdater_appendix|number}
                {$persentage = $sBasketItem.delight_priceupdater_appendix|strpos:'%'}
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
        <span class="xmlchart-price" data-type="{$sBasketItem.delight_priceupdater_type}" data-size="{$sBasketItem.delight_priceupdater_size}" data-tax="{$sBasketItem.tax}" data-appendix="{$appendix}" data-multiplicator="{$multiplicator}">{/if}
        {$sBasketItem.price|currency}
        {if $sBasketItem.delight_priceupdater_bool == 1
         && $sBasketItem.delight_priceupdater_type != ''
         && $sBasketItem.delight_priceupdater_size > 0
        }</span>{/if}
        {s name="Star"}{/s}
        *</div>
    {/if}
{/block}
