{extends file="parent:frontend/compare/col.tpl"}

{block name='frontend_compare_price_normal'}
    <span class="price--normal{if $sArticle.has_pseudoprice} price--reduced{/if}">
        {if $sArticle.priceStartingFrom}
            {s name="ComparePriceFrom"}{/s}
        {/if}

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
        {s name="Star" namespace="frontend/listing/box_article"}{/s}
    </span>
{/block}
