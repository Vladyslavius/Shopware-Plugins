{extends file="parent:frontend/detail/data.tpl"}

{block name='frontend_detail_data_price_configurator_starting_from_content'}
    <span class="price--content content--starting-from">
        {s name="DetailDataInfoFrom"}{/s}
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
            {$sArticle.priceStartingFrom|currency}
        {if $sArticle.delight_priceupdater_bool == 1
         && $sArticle.delight_priceupdater_type != ''
         && $sArticle.delight_priceupdater_size > 0
        }</span>{/if}
        {s name="Star" namespace="frontend/listing/box_article"}{/s}
    </span>
{/block}

{block name='frontend_detail_data_price_default'}
    <span class="price--content content--default">
        <meta itemprop="price" content="{$sArticle.price|replace:',':'.'}">
        {if $sArticle.priceStartingFrom}{s name='ListingBoxArticleStartsAt' namespace="frontend/listing/box_article"}{/s} {/if}
        {if $sArticle.delight_priceupdater_bool == 1
         && $sArticle.delight_priceupdater_type != ''
         && $sArticle.delight_priceupdater_size > 0
        }
data.tpl
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
