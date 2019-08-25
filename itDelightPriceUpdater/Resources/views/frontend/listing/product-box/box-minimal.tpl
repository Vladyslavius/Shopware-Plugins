{extends file="parent:frontend/listing/product-box/box-basic.tpl"}

{block name='frontend_listing_box_article_price_default'}
    <span class="price--default is--nowrap{if $sArticle.has_pseudoprice} is--discount{/if}">
        {if $sArticle.priceStartingFrom}{s name='ListingBoxArticleStartsAt'}{/s} {/if}

        {if $sArticle.delight_priceupdater_bool == 1
         && $sArticle.delight_priceupdater_type != ''
         && $sArticle.delight_priceupdater_size > 0
        }
            {if $sArticle.delight_priceupdater_appendix}
                {$metallAppend = $sArticle.delight_priceupdater_appendix|number}
                {$persentage = $sArticle.delight_priceupdater_appendix|strpos:'%'}
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
        <span class="xmlchart-price" data-type="{$sArticle.delight_priceupdater_type}" data-size="{$sArticle.delight_priceupdater_size}" data-tax="{$sArticle.tax}" data-appendix="{$appendix}" data-multiplicator="{$multiplicator}">{/if}
        {$sArticle.price|currency}
        {if $sArticle.delight_priceupdater_bool == 1
         && $sArticle.delight_priceupdater_type != ''
         && $sArticle.delight_priceupdater_size > 0
        }</span>{/if}
        {s name="Star"}{/s}
    </span>
{/block}
