<?php

namespace itDelightPriceUpdater;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Config\Definition\Exception\Exception;
use itDelightPriceUpdater\Models\Metalldata;

class itDelightPriceUpdater extends Plugin
{
    public $pluginName = "itDelightPriceUpdater";

    public static function getSubscribedEvents()
    {
        return [
            "Shopware_CronJob_UpdateXmlChart"                                                   => "onUpdateXmlChartCronRun",
			"Theme_Compiler_Collect_Javascript_Files_FilterResult"								=> "collectJavascriptFiles",
			"Enlight_Controller_Dispatcher_ControllerPath_Frontend_PriceUpdate"                 => "onGetFrontendPriceUpdateController",
        ];
    }

    public function install(InstallContext $context)
    {
        try {
            $service = Shopware()->Container()->get("shopware_attribute.crud_service");
            $service->update("s_articles_attributes", "delight_priceupdater_bool", "boolean", ["position" => 996, "displayInBackend"  => 0,]);
            $service->update("s_articles_attributes", "delight_priceupdater_type", "text", ["position" => 997, "displayInBackend"  => 0,]);
            $service->update("s_articles_attributes", "delight_priceupdater_size", "number", ["position" => 998, "displayInBackend"  => 0,]);
            $service->update("s_articles_attributes", "delight_priceupdater_appendix", "number", ["position" => 999, "displayInBackend"  => 0,]);

            $this->updateSchema();

            $this->addCron("updateXmlChart");

            parent::install($context);
        } catch (Exception $exception) {
            $exception->getTrace();
        }
    }

	public function addCron($cronName)
	{
		$connection = $this->container->get("dbal_connection");
		$connection->insert(
			"s_crontab",
			[
				"`name`"        => $cronName,
				"`action`"      => $cronName,
				"`next`"        => new \DateTime(),
				"`start`"       => null,
				"`interval`"    => "300",
				"`active`"      => 1,
				"`end`"         => new \DateTime(),
				"`pluginID`"    => null
			],
			[
				"`next`"        => "datetime",
                "`end`"         => "datetime",
			]
		);
	}

	public function removeCron($cronName)
	{
		try {
			$this->container->get("dbal_connection")->executeQuery("DELETE FROM `s_crontab` WHERE `name` = ?", [
				$cronName
			]);
		} catch (DBALException $exception) {
			error_log(print_r($exception->getLine(), true));
		}
	}

    public function uninstall(UninstallContext $context)
    {
        $service = Shopware()->Container()->get("shopware_attribute.crud_service");

        $service->delete("s_articles_attributes", "delight_priceupdater_bool");
        $service->delete("s_articles_attributes", "delight_priceupdater_type");
        $service->delete("s_articles_attributes", "delight_priceupdater_size");
        $service->delete("s_articles_attributes", "delight_priceupdater_appendix");

        $this->removeCron("updateXmlChart");

        $tool = new SchemaTool($this->container->get("models"));
        $classes = $this->getModelMetaData();
        $tool->dropSchema($classes);

        parent::uninstall($context);
    }

    private function updateSchema()
    {
        $tool = new SchemaTool($this->container->get("models"));
        $classes = $this->getModelMetaData();

        try {
            $tool->dropSchema($classes);
        } catch (\Exception $e) {
        }

        $tool->createSchema($classes);
    }

    private function getModelMetaData()
    {
        return [
            $this->container->get("models")->getClassMetadata(Models\Metalldata::class),
        ];
    }

    public function onUpdateXmlChartCronRun(\Shopware_Components_Cron_CronJob $job)
	{
        $content = file_get_contents("http://www.xmlcharts.com/live/precious-metals.php?format=json&currency=EUR");
        $prices = json_decode($content, true);

        if(count($prices))
        {
            foreach($prices as $type => $price)
            {
                $sql = "SELECT * FROM `s_price_updater_metall_data` WHERE `code` = '{$type}'";
                $chart = Shopware()->Db()->fetchOne($sql);
                if($chart)
                {
                    $sql = "UPDATE `s_price_updater_metall_data` SET `price` = {$price}, `changetime` = NOW() WHERE `code` = '{$type}'";
                    Shopware()->Db()->query($sql);
                } else {
                    $sql = "INSERT INTO `s_price_updater_metall_data`(`code`, `price`, `changetime`) VALUES ('{$type}', {$price}, NOW())";
                    Shopware()->Db()->query($sql);
                }
            }
        }
    
        $this->refreshBasketOrders();

		return true;
	}

    private function refreshBasketOrders()
    {
        $config = Shopware()->Container()->get("shopware.plugin.config_reader")->getByPluginName("itDelightPriceUpdater");

        $sql = "SELECT ob.`id`, ob.`quantity`, ob.`articleID`, ob.`ordernumber`, ob.`tax_rate`, aa.`delight_priceupdater_type`, aa.`delight_priceupdater_size`, aa.`delight_priceupdater_appendix`, pumd.`price`
            FROM `s_order_basket` AS ob
            LEFT JOIN `s_articles_details` AS ad ON ad.`articleID` = ob.`articleID` AND ad.`ordernumber` = ob.`ordernumber`
            LEFT JOIN `s_articles_attributes` AS aa ON aa.`articledetailsID` = ad.`id`
            LEFT JOIN `s_price_updater_metall_data` AS pumd ON pumd.`code` = aa.`delight_priceupdater_type`
            WHERE aa.`delight_priceupdater_bool` = 1";
        $basketItems = Shopware()->Db()->fetchAll($sql);

        $netPrice = 0;
        $taxPrice = 0;
        if(count($basketItems))
        {
            foreach($basketItems as $basketItem)
            {
                $currentPrice = $basketItem["price"] * $basketItem["delight_priceupdater_size"];
                if($basketItem["delight_priceupdater_appendix"])
                {
                    $metallAppend = $basketItem["delight_priceupdater_appendix"];
                } else {
                    $metallAppend = $config["wpu_metall_" . $basketItem["delight_priceupdater_type"]];
                }
                $persentage = strstr($metallAppend, "%");
                if($persentage)
                {
                    $currentPrice = $currentPrice * (100 + (float)$metallAppend) / 100;
                } else {
                    $currentPrice = $currentPrice + (float)$metallAppend;
                }

                $netPrice = $currentPrice;
                $taxPrice = $currentPrice * (100 + $basketItem["tax_rate"]) / 100;

                $sql = "UPDATE `s_order_basket` SET `price` = " . Shopware()->Db()->quote($taxPrice) . ", `netprice` = " . Shopware()->Db()->quote($netPrice) . " WHERE `id` = {$basketItem["id"]}";
                Shopware()->Db()->query($sql);
            }
        }
    }

	public function onGetFrontendPriceUpdateController(\Enlight_Event_EventArgs $arguments)
	{
		return $this->getPath() . "/Controllers/Frontend/PriceUpdate.php";
	}

    public function collectJavascriptFiles(\Enlight_Event_EventArgs $arguments)
    {
        $files = $arguments->getReturn();
        $files[] = $this->getPath() . "/Resources/views/assets/js/delight_price_updater.js";
        $files[] = $this->getPath() . "/Resources/views/assets/js/jquery.formatCurrency-1.4.0.min.js";
        $files[] = $this->getPath() . "/Resources/views/assets/js/jquery.formatCurrency.all.js";

        $arguments->setReturn($files);
    }
}
