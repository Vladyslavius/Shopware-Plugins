{extends file="parent:frontend/index/header.tpl"}

{block name="frontend_index_header_css_print" append}
    {if $theme.shopag_google_webfonts_links}
        {$theme.shopag_google_webfonts_links}
    {/if}
{/block}