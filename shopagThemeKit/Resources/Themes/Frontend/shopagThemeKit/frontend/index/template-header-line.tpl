<header data-scroll="0" class="header-main">
    {if $theme.shopag_settings_header_advantages_position == 'top' && $theme.shopag_settings_header_advantages_allow}
        {include file='frontend/index/header-advantages.tpl'}
    {/if}
    {block name='frontend_index_header_navigation'}
        <div class="container header--navigation">

            {* Logo container *}
            {block name='frontend_index_logo_container'}
                {include file="frontend/index/logo-container.tpl"}
            {/block}

            {* Maincategories navigation top *}
            {block name='frontend_index_navigation_categories_top'}
                <nav class="navigation-main">
                    {block name="frontend_index_navigation_categories_top_include"}
                        {include file='frontend/index/main-navigation.tpl'}
                    {/block}
                </nav>
            {/block}

            {* Shop navigation *}
            {block name='frontend_index_shop_navigation'}
                {include file="frontend/index/shop-navigation.tpl"}
            {/block}

        </div>
    {/block}
    {if $theme.shopag_settings_header_advantages_position == 'bottom' && $theme.shopag_settings_header_advantages_allow}
        {include file='frontend/index/header-advantages.tpl'}
    {/if}
</header>