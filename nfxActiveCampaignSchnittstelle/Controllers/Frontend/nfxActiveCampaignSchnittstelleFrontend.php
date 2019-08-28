<?php

use Shopware\Components\CSRFWhitelistAware;
use nfxActiveCampaignSchnittstelle\Helpers\CustomerData;
use nfxActiveCampaignSchnittstelle\Helpers\OrderData;
use nfxActiveCampaignSchnittstelle\Helpers\TagsData;
use nfxActiveCampaignSchnittstelle\ApiAuthentication\ApiAuthentication;


class Shopware_Controllers_Frontend_nfxActiveCampaignSchnittstelleFrontend extends Enlight_Controller_Action implements CSRFWhitelistAware
{
    public function getWhitelistedCSRFActions()
    {

        return [
            'start',
            'deleteAll'
        ];

    }

    private function shopRender()
    {
        $this->container->get('front')->Plugins()->ViewRenderer()->setNoRender();
    }


    public function startAction()
    {

        $this->shopRender();

        $apiAuthentication = new ApiAuthentication();
        $customerData = new CustomerData();
        $tagsData = new TagsData();
        $orderData = new OrderData();
        $allTags = $tagsData->getAll();

        $config = Shopware()->Container()->get("shopware.plugin.config_reader")->getByPluginName('nfxActiveCampaignSchnittstelle');

        error_log(print_r($orderData->getData($config), true));

    }


    public function deleteAllAction()
    {

        $this->shopRender();

        $apiAuthentication = new ApiAuthentication();

        $data = $apiAuthentication->get(array(), 'contacts');
        $data = json_decode($data, true);
        foreach ($data['contacts'] as $dataItem) {
            $apiAuthentication->delete(array(), 'contacts/' . $dataItem['id']);
        }

        $data = $apiAuthentication->get(array(), 'ecomCustomers');
        $data = json_decode($data, true);
        foreach ($data['ecomCustomers'] as $dataItem) {
            $apiAuthentication->delete(array(), 'ecomCustomers/' . $dataItem['id']);
        }

    }

}