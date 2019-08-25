<?php

class Shopware_Controllers_Frontend_PriceUpdate extends Enlight_Controller_Action
{
	public $pluginName = "itDelightPriceUpdater";
    private $path;

	public function preDispatch()
	{
		$this->path = $this->container->getParameter("delight_price_updater.plugin_dir");
	}

	public function loadAction()
	{
		$request = $this->Request();
		$params = $request->getParams();

        $this->Request()->setHeader("Content-Type", "application/json");

        $config = Shopware()->Container()->get("shopware.plugin.config_reader")->getByPluginName($this->pluginName);

        $data = [];
        $sql = "SELECT * FROM `s_price_updater_metall_data` WHERE 1";
        $charts = Shopware()->Db()->fetchAll($sql);
        if(count($charts))
        {
            foreach($charts as $chart)
            {
                $metallAppend = $config["wpu_metall_" . $chart["code"]];
                $persentage = strstr($metallAppend, "%");
                if($persentage)
                {
                    $multiplicator = (100 + (float)$metallAppend) / 100;
                    $appendix = 0;
                } else {
                    $multiplicator = 1;
                    $appendix = (float)$metallAppend;
                }

                $data[] = [
                    "type"          => $chart["code"],
                    "price"         => $chart["price"],
                    "multiplicator" => $multiplicator,
                    "appendix"      => $appendix,
                ];
            }
        }

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $this->Response()->setBody(json_encode([
            "success"   => true,
            "data"      => $data,
        ]));
	}

	public function getShopAddress($subshopID)
	{
		$sql = "
		SELECT ccv.`value` FROM `s_core_config_values` AS ccv
		LEFT JOIN `s_core_config_elements` AS cce ON cce.`id` = ccv.`element_id`
		WHERE cce.`name` = 'address' AND ccv.shop_id = {$subshopID}
		";
		$shopName = unserialize(Shopware()->Db()->fetchOne($sql));

		return $shopName;
	}
}
