{extends file='parent:frontend/index/index.tpl'}

{block name='frontend_index_javascript_async_ready' append}
    {if $nfxApiConnectorTrackingCode}
        {literal}
        <script type="text/javascript">
            (function(e,t,o,n,p,r,i){e.visitorGlobalObjectAlias=n;e[e.visitorGlobalObjectAlias]=e[e.visitorGlobalObjectAlias]||function(){(e[e.visitorGlobalObjectAlias].q=e[e.visitorGlobalObjectAlias].q||[]).push(arguments)};e[e.visitorGlobalObjectAlias].l=(new Date).getTime();r=t.createElement("script");r.src=o;r.async=true;i=t.getElementsByTagName("script")[0];i.parentNode.insertBefore(r,i)})(window,document,"https://diffuser-cdn.app-us1.com/diffuser/diffuser.js","vgo");
            vgo('setAccount', '{/literal}{$nfxApiConnectorTrackingCode}{literal}');
            vgo('setTrackByDefault', true);
            {/literal}{if $nfxApiConnectorTrackingCodeCustomerEmail}{literal}
                vgo('setEmail', '{/literal}{$nfxApiConnectorTrackingCodeCustomerEmail}{literal}');
            {/literal}{/if}{literal}
            vgo('process');
        </script>
        {/literal}
    {/if}
{/block}