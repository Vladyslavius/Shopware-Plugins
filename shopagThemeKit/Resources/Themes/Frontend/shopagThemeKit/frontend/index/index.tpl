{extends file='parent:frontend/index/index.tpl'}

{block name='frontend_index_body_classes' append}
    {if $theme.shopag_settings_header_stick}
        is--{$theme.shopag_settings_header_stick}
    {/if}
    {if $theme.shopag_home_icon_allow}
        is--main-navigation-first-icon
    {/if}
    {if $theme.shopag_settings_header_template}
        is--{$theme.shopag_settings_header_template}
    {/if}
{/block}

{* Shop header *}
{block name='frontend_index_navigation'}
    {if $theme.shopag_settings_header_template == 'header-template-ll-plus-nav' || $theme.shopag_settings_header_template == 'header-template-lc-plus-nav'}
        {include file="frontend/index/template-header-line.tpl"}
    {else}
        {include file="frontend/index/template-header-default.tpl"}
    {/if}
    {block name='frontend_index_container_ajax_cart'}
        <div class="container--ajax-cart"
             data-collapse-cart="true"{if $theme.offcanvasCart} data-displayMode="offcanvas"{/if}></div>
    {/block}
{/block}