<?php

namespace nfxActiveCampaignSchnittstelle;

use Mpdf\Tag\P;
use Shopware\Components\Plugin;
use nfxActiveCampaignSchnittstelle\Helpers\CustomerData;
use nfxActiveCampaignSchnittstelle\Helpers\OrderData;
use nfxActiveCampaignSchnittstelle\Helpers\TagsData;
use nfxActiveCampaignSchnittstelle\ApiAuthentication\ApiAuthentication;
use nfxActiveCampaignSchnittstelle\Components\Logger;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Routing\Context;
use Shopware\Models\Customer\Customer;

class nfxActiveCampaignSchnittstelle extends Plugin {

    public static function getSubscribedEvents() {
        return [
            'Shopware_CronJob_NfxTransferActiveCampaignData' => 'cronExportData',
            'Enlight_Controller_Action_PostDispatch_Frontend' => 'extendFrontendAction',
            'Shopware_Controllers_Frontend_Address::indexAction::after" => "editActionAfter',
            'Shopware_Controllers_Frontend_Register::saveRegisterAction::after' => 'saveRegisterAfter',
            'Shopware_Controllers_Frontend_Checkout::ajaxAddArticleCartAction::after' => 'addArticleCartActionAfter',
            'Shopware_Controllers_Backend_Customer::saveAction::before' => 'saveBackendCustomerActionAfter'
        ];
    }

    public function saveBackendCustomerActionAfter(\Enlight_Hook_HookArgs $args) {
        /** @var \Shopware_Controllers_Backend_Customer $subject */
        $subject = $args->getSubject();

        $request = $subject->Request()->getParams();

        $customerId = $request['customerID'];

        if (!empty($customerId) && isset($customerId)) {
            $customerModel = Shopware()->Models()->getRepository(Customer::class);

            /** @var  $customerObject Customer */
            $customerObject = $customerModel->findOneBy(['id' => $customerId]);
            $customerObject->getAttribute()->setNfxGlobalChanged(date("Y-m-d H:i:s", strtotime('now')));
            Shopware()->Models()->flush();
        }
        $return = $args->getReturn();

        $args->setReturn($return);
    }

    public function addArticleCartActionAfter(\Enlight_Hook_HookArgs $args) {
        /** @var \Shopware_Controllers_Frontend_Register $subject */
        $subject = $args->getSubject();

        $return = $args->getReturn();

        $userRepository = Shopware()->Models()->getRepository(Customer::class);
        /**
         * @return int
         * @var  $sUserId
         */
        $sUserId = $this->container->get('session')->get('sUserId');

        if (!empty($sUserId) && isset($sUserId)) {
            /** @var  $userObject Customer */
            $userObject = $userRepository->findOneBy(['id' => $sUserId]);

            $addressChangedTime = date("Y-m-d H:i:s", strtotime('now'));
            $userObject->getAttribute()->setNfxGlobalChanged($addressChangedTime);

            Shopware()->Models()->flush();
        }

        $args->setReturn($return);
    }

    public function saveRegisterAfter(\Enlight_Hook_HookArgs $args) {
        /** @var \Shopware_Controllers_Frontend_Register $subject */
        $subject = $args->getSubject();

        $return = $args->getReturn();

        $userRepository = Shopware()->Models()->getRepository(Customer::class);
        /**
         * @return int
         * @var  $sUserId
         */
        $sUserId = $this->container->get('session')->get('sUserId');
        /** @var  $userObject Customer */
        $userObject = $userRepository->findOneBy(['id' => $sUserId]);

        $addressChangedTime = date("Y-m-d H:i:s", strtotime('now'));
        $userObject->getAttribute()->setNfxGlobalChanged($addressChangedTime);

        Shopware()->Models()->flush();

        $args->setReturn($return);
    }

    public function extendFrontendAction(\Enlight_Controller_ActionEventArgs $args) {

        $controller = $args->getSubject();
        $view = $controller->View();

        $config = Shopware()->Container()->get("shopware.plugin.config_reader")->getByPluginName('nfxActiveCampaignSchnittstelle');

        $customerEmail = null;
        $userID = Shopware()->Session()->get('sUserId');
        if (!empty($userID)) {
            /** @var $customer \Shopware\Models\Customer\Customer */
            $customer = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->findOneBy(array('id' => $userID));
            if (!empty($customer)) {
                $customerEmail = $customer->getEmail();
            }
        }

        $view->assign('nfxApiConnectorTrackingCode', $config['tracking_code']);
        $view->assign('nfxApiConnectorTrackingCodeCustomerEmail', $customerEmail);
        $view->addTemplateDir($this->getPath() . '/Resources/Views');
    }

    public function cronExportData() {
        try {
            $logger = $this->container->get("nfx_active_campaign_schnittstelle");
            $logger->debug("START cronExportData");

            $configReader = $this->container->get('shopware.plugin.cached_config_reader')->getByPluginName($this->getName());

            /* @var \Shopware\Models\Shop\Shop $shop */
            $shop = Shopware()->Models()->getRepository(\Shopware\Models\Shop\Shop::class)->getActiveById(1);
            $shop->registerResources();
            $shopContext = Context::createFromShop($shop, Shopware()->Container()->get('config'));
            Shopware()->Container()->get('router')->setContext($shopContext);

            $apiAuthentication = new ApiAuthentication();
            $customerData = new CustomerData();
            $tagsData = new TagsData();
            $allTags = $tagsData->getAll();

            $contactID = null;


            //Get config
            $config = Shopware()->Container()->get("shopware.plugin.config_reader")->getByPluginName('nfxActiveCampaignSchnittstelle');

            $configConnection = array(
                'connection' => array(
                    'service' => strtolower(str_replace(' ', '', $shop->getName())),
                    'externalid' => strtolower(str_replace(' ', '', $shop->getName())),
                    'name' => $shop->getName(),
                    'logoUrl' => $config['logo_ac'],
                    'linkUrl' => $shop->getHost(),
                )
            );

            //Update connection
            $apiResult = $apiAuthentication->put($configConnection, 'connections/' . $config['connectionID']);
            $logger->debug("put: " . print_r($apiResult, true));

            //All customers
            $customers = $customerData->getData($config);
            foreach ($customers['users'] as $item) {
                $logger->debug("customer: ", $item);
                $result = $apiAuthentication->post($item['contact'], 'contacts');
                $logger->debug("result: " . print_r($result, true));
                $result = json_decode($result, true);

                //New contact
                if (!empty($result['contact']['id']) && isset($result['contact']['id'])) {

                    $contactID = $result['contact']['id'];

                    //Create Contact-customer
                    $resultEcomCustomer = $apiAuthentication->post($item['ecomCustomer'], 'ecomCustomers/' . $contactID);
                    $logger->debug("resultEcomCustomer: " . print_r($resultEcomCustomer, true));
                    $resultEcomCustomer = json_decode($resultEcomCustomer, true);

                    //Update field contact id
                    $item['contactList']['contactList']['contact'] = $contactID;

                    //Link contact to list
                    $resultContactLists = $apiAuthentication->post($item['contactList'], 'contactLists');
                    $logger->debug("resultContactLists: " . print_r($resultContactLists, true));
                    $resultContactLists = json_decode($resultContactLists, true);

                    //Set contact info in DB
                    $apiResult = $customerData->setApiConnectorData(
                            $item['ecomCustomer']['ecomCustomer']['externalid'], $contactID, $resultEcomCustomer['ecomCustomer']['id'], date('Y-m-d H:i:s')
                    );
                    //Custom fields
                    $fieldDictionary = array(
                        'country' => $config['contact_custom_field_country'],
                        'street' => $config['contact_custom_field_street'],
                        'zipcode' => $config['contact_custom_field_zipcode'],
                        'city' => $config['contact_custom_field_city'],
                        'salutation' => $config['contact_custom_field_salutation'],
                        'bonusSystem' => $config['contact_custom_field_bonus_system'],
                        'expiringBonusPoints' => $config['contact_custom_field_next_expiring_bonus_points'],
                        'expiringBonusPointsDate' => $config['contact_custom_field_next_expiring_bonus_points_date']
                    );
                    foreach ($item['customFields'] as $customFieldKey => $customField) {
                        $logger->debug("custom: " , array(
                            'fieldValue' => array(
                                'contact' => $contactID,
                                'field' => $fieldDictionary[$customFieldKey],
                                'value' => $customField
                            )
                                ));
                        $resultCustom = $apiAuthentication->post(array(
                            'fieldValue' => array(
                                'contact' => $contactID,
                                'field' => $fieldDictionary[$customFieldKey],
                                'value' => $customField
                            )
                                ), 'fieldValues');
                        $logger->debug("resultCustom: " . print_r($resultCustom, true));
                    }

                    $counter = 1;
                    foreach ($item['recommendationProducts'] as $recommendationProduct) {
                        foreach ($recommendationProduct as $recommendationProductKey => $recommendationProductValue) {
                            $logger->debug("custom: " , array(
                                'fieldValue' => array(
                                    'contact' => $contactID,
                                    'field' => $config['contact_custom_field_pr_' . $recommendationProductKey . '_' . $counter . ''],
                                    'value' => $recommendationProductValue
                                )
                                    ));
                            $resultCustom = $apiAuthentication->post(array(
                                'fieldValue' => array(
                                    'contact' => $contactID,
                                    'field' => $config['contact_custom_field_pr_' . $recommendationProductKey . '_' . $counter . ''],
                                    'value' => $recommendationProductValue
                                )
                                    ), 'fieldValues');
                            $logger->debug("resultCustom: " . print_r($resultCustom, true));
                        }
                        $counter++;
                    }
                } else {
                    //Existing contact update
                    if (!empty($item['apiData']['contactID'] && !empty($item['apiData']['customerID']))) {
                        if ($item['changesAddress']) {
                            
                        }
                        $contactID = $item['apiData']['contactID'];
                    }
                }

                if ($contactID) {

                    //Tags
                    $tagsContact = $tagsData->getCustomer($contactID);
                    $tags = $item['apiData']['tags'];
                    $tags = explode(',', $tags);
                    foreach ($tags as $tag) {
                        $tag = trim($tag);
                        if (!empty($allTags[$tag]) && isset($allTags[$tag])) {
                            if (empty($tagsContact[$allTags[$tag]['id']])) {
                                $logger->debug("contactTags: " , array(
                                    'contactTag' => array(
                                        'contact' => $contactID,
                                        'tag' => $allTags[$tag]['id']
                                    )
                                        ));
                                $resultContactTags = $apiAuthentication->post(array(
                                    'contactTag' => array(
                                        'contact' => $contactID,
                                        'tag' => $allTags[$tag]['id']
                                    )
                                        ), 'contactTags');
                                $logger->debug("resultContactTags: " . print_r($resultContactTags, true));
                            }
                        } else {
                            $newTag = $apiAuthentication->post(array(
                                'tag' => array(
                                    'tag' => $tag,
                                    'tagType' => 'contact'
                                )
                                    ), 'tags');
                            if (!empty($newTag) && isset($newTag)) {
                                $newTag = json_decode($newTag, true);

                                $allTags[$tag] = array(
                                    'contact' => $newTag['tag']['tagType'],
                                    'tag' => $newTag['tag']['tag'],
                                    'id' => $newTag['tag']['id']
                                );
                                $logger->debug("newContactTag: ", array(
                                    'contactTag' => array(
                                        'contact' => $contactID,
                                        'tag' => $newTag['tag']['id']
                                    )
                                        ));
                                $newContactTag = $apiAuthentication->post(array(
                                    'contactTag' => array(
                                        'contact' => $contactID,
                                        'tag' => $newTag['tag']['id']
                                    )
                                        ), 'contactTags');
                                $logger->debug("resultNewContactTag: " . print_r($newContactTag, true));
                            }
                        }
                    }

                    //Search and remove old contact tags
                    $tagsContactAfterUpdate = $tagsData->getCustomer($contactID);
                    $allTagsAfterUpdate = $tagsData->getAll();
                    foreach ($tags as $tag) {
                        $tag = trim($tag);
                        if ($tagsContactAfterUpdate[$allTagsAfterUpdate[$tag]['id']]) {
                            unset($tagsContactAfterUpdate[$allTagsAfterUpdate[$tag]['id']]);
                        }
                    }
                    foreach ($tagsContactAfterUpdate as $tagsContactAfterUpdateItem) {
                        $logger->debug("delete: ", array('contactTags/' . $tagsContactAfterUpdateItem['id']));
                        $resultDelete = $apiAuthentication->delete(array(), 'contactTags/' . $tagsContactAfterUpdateItem['id']);
                        $logger->debug("resultDelete: " . print_r($resultDelete, true));
                    }
                }

                //if (!empty($item['basket']) && isset($item['basket'])) {
                //    $apiAuthentication->post($item['basket'], 'ecomOrders');
                //}
            }


            $orderData = new OrderData();

            foreach ($orderData->getData($config) as $item) {
                $logger->debug("order: ", $item);
                $resultOrder = $apiAuthentication->post($item, 'ecomOrders');
                $logger->debug("resultOrder: " . print_r($resultOrder, true));
            }
        } catch (\Exception $ex) {
            $logger->logError($ex, __METHOD__);
        }
        $logger->debug("END cronExportData");

        return true;
    }

    public function install(InstallContext $context) {

        $service = $this->container->get('shopware_attribute.crud_service');
        $service->update('s_user_attributes', 'nfx_api_connector_contact_id', 'boolean', [
            'displayInBackend' => false,
        ]);
        $service->update('s_user_attributes', 'nfx_api_connector_customer_id', 'boolean', [
            'displayInBackend' => false,
        ]);
        $service->update('s_user_attributes', 'nfx_api_connector_contact_changes', 'datetime', [
            'displayInBackend' => false,
        ]);
        $service->update('s_user_attributes', 'nfx_api_connector_contact_tags', 'text', [
            'displayInBackend' => true,
            'label' => 'nfxApiContactTags',
        ]);
        $service->update('s_user_addresses_attributes', 'nfx_api_changes', 'boolean', [
            'displayInBackend' => false,
        ]);
        $service->update('s_user_attributes', 'nfx_api_recommendation', 'multi_selection', [
            'label' => 'nfxApiContactRecommendation',
            'displayInBackend' => true,
            'entity' => 'Shopware\Models\Article\Article',
        ]);
        $service->update('s_user_attributes', 'nfx_global_changed', 'text', [
            'displayInBackend' => false,
        ]);

        $service->update('s_core_customergroups_attributes', 'nfx_api_list_id', 'integer', [
            'label' => 'nfxListID',
            'displayInBackend' => true
        ]);

        $metaDataCache = Shopware()->Models()->getConfiguration()->getMetadataCacheImpl();
        $metaDataCache->deleteAll();
        Shopware()->Models()->generateAttributeModels();
    }

    /**
     * @param \Enlight_Hook_HookArgs $args
     */
    public function editActionAfter(\Enlight_Hook_HookArgs $args) {

        /** @var \Shopware_Controllers_Frontend_Address $subject */
        $subject = $args->getSubject();
        $response = $subject->Request()->getParams();
        $userRepository = Shopware()->Models()->getRepository(Customer::class);
        /**
         * @return int
         * @var  $sUserId
         */
        $sUserId = $this->container->get('session')->get('sUserId');
        /** @var  $userObject Customer */
        $userObject = $userRepository->findOneBy(['id' => $sUserId]);
        if (!empty($response)) {
            if (array_key_exists('success', $response)) {
                if ($response['success'] == "update") {
                    $addressChangedTime = date("Y-m-d H:i:s", strtotime('now'));
                    $userObject->getAttribute()->setNfxGlobalChanged($addressChangedTime);
                }
            }
        }
        Shopware()->Models()->flush();
        $return = $args->getReturn();
        $args->setReturn($return);
    }

    public function uninstall(UninstallContext $context) {

        $service = $this->container->get('shopware_attribute.crud_service');
        $service->delete('s_user_attributes', 'nfx_global_changed');
        $service->delete('s_user_attributes', 'nfx_api_connector_contact_id');
        $service->delete('s_user_attributes', 'nfx_api_connector_customer_id');
        $service->delete('s_user_attributes', 'nfx_api_connector_contact_changes');
        $service->delete('s_user_attributes', 'nfx_api_connector_contact_tags');
        $service->delete('s_user_attributes', 'nfx_api_recommendation');
        $service->delete('s_user_addresses_attributes', 'nfx_api_changes');
        $service->delete('s_core_customergroups_attributes', 'nfx_api_list_id');
        $metaDataCache = Shopware()->Models()->getConfiguration()->getMetadataCacheImpl();
        $metaDataCache->deleteAll();
        Shopware()->Models()->generateAttributeModels();
    }

}
