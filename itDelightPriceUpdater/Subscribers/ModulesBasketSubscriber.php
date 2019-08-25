<?php

namespace itDelightPriceUpdater\Subscribers;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;

class ModulesBasketSubscriber implements SubscriberInterface
{
    private $path;

    public function __construct($pluginDirectory)
    {
        $this->path = $pluginDirectory;
    }

    public static function getSubscribedEvents()
    {
        return [
            "Shopware_Modules_Basket_getArticleForAddArticle_FilterArticle" => "getArticleForAddArticleFilterArticle",
            "Shopware_Modules_Basket_getPriceForUpdateArticle_FilterPrice" => "getPriceForUpdateArticleFilterPrice",
        ];
    }

    public function getArticleForAddArticleFilterArticle(\Enlight_Event_EventArgs $arguments)
    {
        $article = $arguments->getReturn();
        $request = Shopware()->Container()->get("front")->Request();

        if($this->testMetall($article["articledetailsID"]))
        {
            $article["free"] = true;
        }

        return $article;
    }

    private function testMetall($articledetailsID)
    {
        $sql = "SELECT `delight_priceupdater_bool`, `delight_priceupdater_type`, `delight_priceupdater_size` FROM `s_articles_attributes` WHERE `articledetailsID` = {$articledetailsID}";
        $data = Shopware()->Db()->fetchRow($sql);

        if($data["delight_priceupdater_bool"] == 1 && $data["delight_priceupdater_size"] > 0)
        {
            $sql = "SELECT `price` FROM `s_price_updater_metall_data` WHERE `code` = '{$data["delight_priceupdater_type"]}'";
            $chartPrice = Shopware()->Db()->fetchOne($sql);
            if($chartPrice)
            {
                return true;
            }
        }

        return false;
    }

    public function getPriceForUpdateArticleFilterPrice(\Enlight_Event_EventArgs $arguments)
    {
        $queryNewPrice = $arguments->getReturn();
        $id = $arguments->getId();
        $quantity = $arguments->getQuantity();

        $config = Shopware()->Container()->get("shopware.plugin.config_reader")->getByPluginName("itDelightPriceUpdater");

        $request = Shopware()->Container()->get("front")->Request();
        $action = $request->getParam("action", "");
        $params = $request->getParams();

        if($id)
        {
            $sql = "SELECT `articleID` FROM `s_order_basket` WHERE `id` = {$id}";
            $articleId = Shopware()->Db()->fetchOne($sql);

            $product = Shopware()->Modules()->Articles()->sGetArticleById($articleId);

            if($product["delight_priceupdater_bool"] == 1)
            {
                $sql = "SELECT `price` FROM `s_price_updater_metall_data` WHERE `code` = '{$product["delight_priceupdater_type"]}'";
                $chartPrice = Shopware()->Db()->fetchOne($sql);
                if($chartPrice)
                {
                    $currentPrice = $chartPrice * $product["delight_priceupdater_size"];

                    if($product["delight_priceupdater_appendix"])
                    {
                        $metallAppend = $product["delight_priceupdater_appendix"];
                    } else {
                        $metallAppend = $config["wpu_metall_" . $product["delight_priceupdater_type"]];
                    }

                    $persentage = strstr($metallAppend, "%");
                    if($persentage)
                    {
                        $currentPrice = $currentPrice * (100 + (float)$metallAppend) / 100;
                    } else {
                        $currentPrice = $currentPrice + (float)$metallAppend;
                    }
                    $sql = "SELECT `tax` FROM `s_core_customergroups` WHERE `groupkey` = '{$product["pricegroup"]}'";
                    $tax = Shopware()->Db()->fetchOne($sql);
                    if($tax)
                    {
                        $currentPrice = $currentPrice * (100 + $taxSize) / 100;
                    }
                    $queryNewPrice["price"] = $currentPrice;
                }
            }
        }

        return $queryNewPrice;
    }
}
