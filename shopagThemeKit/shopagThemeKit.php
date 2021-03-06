<?php

namespace shopagThemeKit;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware\Components\Theme\LessDefinition;

class shopagThemeKit extends Plugin
{
    private $pluginName = 'shopagThemeKit';

    public static function getSubscribedEvents()
    {
        return [
            'Theme_Compiler_Collect_Plugin_Less' => 'onCollectLess'
        ];
    }

    public function install(\Shopware\Components\Plugin\Context\InstallContext $context)
    {
        parent::install($context); // TODO: Change the autogenerated stub
    }

    public function update(\Shopware\Components\Plugin\Context\UpdateContext $context)
    {
        $context->scheduleClearCache(UpdateContext::CACHE_LIST_ALL);

    }

    public function activate(\Shopware\Components\Plugin\Context\ActivateContext $context)
    {
        $context->scheduleClearCache(ActivateContext::CACHE_LIST_ALL);
    }

    public function deactivate(\Shopware\Components\Plugin\Context\DeactivateContext $context)
    {
        parent::deactivate($context); // TODO: Change the autogenerated stub
    }

    public function uninstall(\Shopware\Components\Plugin\Context\UninstallContext $context)
    {
        parent::uninstall($context); // TODO: Change the autogenerated stub

    }


    public function onCollectLess()
    {
        $file = $this->getPath() . '/Resources/Views/frontend/_public/src/less/style.less';
        if (!is_file($file)) {
            return null;
        }

        $shop = false;
        if ($this->container->initialized('shop')) {
            $shop = $this->container->get('shop');
        }

        if (!$shop) {
            $shop = $this->container->get('models')->getRepository(\Shopware\Models\Shop\Shop::class)->getActiveDefault();
        }

        $config = $this->container->get('shopware.plugin.cached_config_reader')->getByPluginName($this->pluginName, $shop);

        $themeSettings = array(
            'ts-header-background' => $config['ts_header_backgroud'],

            'ts-main-navigation-background' => $config['ts_main_navigation_background'],
            'ts-main-navigation-margin' => $config['ts_main_navigation_margin'],
            'ts-main-navigation-padding' => $config['ts_main_navigation_padding'],

            'ts-body-background' => $config['ts_body_background'],

            'ts-footer-background' => $config['ts_footer_background'],

            'ts-product-box-background' => $config['ts_product_box_background'],
            'ts-product-box-margin' => $config['ts_product_box_margin'],
            'ts-product-box-padding' => $config['ts_product_box_padding'],
        );

        return new LessDefinition(
            $themeSettings,
            [$file]
        );


    }

}