<?php
namespace itDelightPriceUpdater\Subscribers;

use Enlight\Event\SubscriberInterface;

class ArticleSubscriber implements SubscriberInterface
{
    private $path;

    public function __construct($pluginDirectory)
    {
        $this->path = $pluginDirectory;
    }

    public static function getSubscribedEvents()
    {
        return [
            "sArticles::sGetArticleById::after"                         => "onArticlesGetArticleByIdAfter",
            "Legacy_Struct_Converter_Convert_List_Product"              => "onProductConverter",
            "Enlight_Controller_Action_PostDispatchSecure_Frontend"     => "onPostDispatch",
            "Enlight_Controller_Action_PostDispatch_Widgets"            => "onPostDispatch",
        ];
    }

    public function onPostDispatch(\Enlight_Event_EventArgs $arguments)
    {
		$view = $arguments->getSubject()->View();

        $tax = $this->getTaxView(Shopware()->Modules()->Articles()->sSYSTEM->sUSERGROUP);

        $request = Shopware()->Container()->get("front")->Request();
        $params = $request->getParams();
        if($params["controller"] == "checkout" && $params["action"] == "finish")
        {
            $sBasket = $view->getAssign("sBasket");
            if(count($sBasket["content"]))
            {
                foreach($sBasket["content"] as &$item)
                {
                    unset($item["additional_details"]["delight_priceupdater_bool"]);
                    unset($item["additional_details"]["delight_priceupdater_type"]);
                    unset($item["additional_details"]["delight_priceupdater_size"]);
                    unset($item["additional_details"]["delight_priceupdater_appendix"]);
                }
            }

            $view->assign("sBasket", $sBasket);
        }

		$view->addTemplateDir($this->path . "/Resources/views/");
		$view->extendsTemplate("frontend/index/header-updater.tpl");
		$view->extendsTemplate("frontend/compare/col.tpl");

        $view->assign("taxView", $tax);
    }

    public function onProductConverter(\Enlight_Event_EventArgs $arguments)
    {
        $product = $arguments->getReturn();

        $config = Shopware()->Container()->get("shopware.plugin.config_reader")->getByPluginName("itDelightPriceUpdater");
        if($product["delight_priceupdater_appendix"])
        {
            $metallAppend = $product["delight_priceupdater_appendix"];
        } else {
            $metallAppend = $config["wpu_metall_" . $product["delight_priceupdater_type"]];
        }

        $price = $this->getChartPrice($product["price_numeric"], $product["delight_priceupdater_bool"], $product["delight_priceupdater_type"], $product["delight_priceupdater_size"], $product["pricegroup"], $product["tax"], $metallAppend);
        $product["price"] = $price;
        $product["price_numeric"] = $price;

        if($product["delight_priceupdater_bool"] == 1)
        {
            unset($product["sBlockPrices"]);
            unset($product["priceStartingFrom"]);
        }

        $arguments->setReturn($product);
    }

    private function getChartPrice($currentPrice, $priceupdaterBool, $priceupdaterType, $priceupdaterSize, $pricegroup, $taxSize, $metallAppend)
    {
        if($priceupdaterBool == 1)
        {
            $sql = "SELECT `price` FROM `s_price_updater_metall_data` WHERE `code` = '{$priceupdaterType}'";
            $chartPrice = Shopware()->Db()->fetchOne($sql);
            if($chartPrice)
            {
                $currentPrice = $chartPrice * $priceupdaterSize;
                $persentage = strstr($metallAppend, "%");
                if($persentage)
                {
                    $currentPrice = $currentPrice * (100 + (float)$metallAppend) / 100;
                } else {
                    $currentPrice = $currentPrice + (float)$metallAppend;
                }

                $tax = $this->getTaxView($pricegroup);
                if($tax)
                {
                    $currentPrice = $currentPrice * (100 + $taxSize) / 100;
                }
            }
        }

        return $currentPrice;
    }

    public function getTaxView($pricegroup)
    {
        $sql = "SELECT `tax` FROM `s_core_customergroups` WHERE `groupkey` = '{$pricegroup}'";
        $tax = Shopware()->Db()->fetchOne($sql);

        return $tax;
    }

    public function onArticlesGetArticleByIdAfter(\Enlight_Event_EventArgs $arguments)
    {
        $data = $arguments->getReturn();
        $subject = $arguments->getSubject();
        $request = Shopware()->Container()->get("front")->Request();
        $params = $request->getParams();

        $config = Shopware()->Container()->get("shopware.plugin.config_reader")->getByPluginName("itDelightPriceUpdater");
        if($data["delight_priceupdater_appendix"])
        {
            $metallAppend = $data["delight_priceupdater_appendix"];
        } else {
            $metallAppend = $config["wpu_metall_" . $data["delight_priceupdater_type"]];
        }

        $price = $this->getChartPrice($data["price_numeric"], $data["delight_priceupdater_bool"], $data["delight_priceupdater_type"], $data["delight_priceupdater_size"], $data["pricegroup"], $data["tax"], $metallAppend);

        $data["price"] = $price;
        $data["price_numeric"] = $price;

        if($data["delight_priceupdater_bool"] == 1)
        {
            unset($data["sBlockPrices"]);
            unset($data["priceStartingFrom"]);
        }

        $arguments->setReturn($data);
    }
}
