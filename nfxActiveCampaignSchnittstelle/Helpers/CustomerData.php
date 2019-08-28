<?php

namespace nfxActiveCampaignSchnittstelle\Helpers;

use Doctrine\DBAL\DBALException;
use nfxActiveCampaignSchnittstelle\ApiAuthentication\ApiAuthentication;
use nfxActiveCampaignSchnittstelle\Helpers\OrderData;
use Shopware\Components\Form\Field\Date;
use Shopware\Components\Plugin\ConfigReader;

class CustomerData
{

    public function setApiConnectorData($number, $idContact, $idCustomer, $date)
    {
        /** @var $customer \Shopware\Models\Customer\Customer */
        $customer = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->findOneBy(array('number' => $number));
        if (!empty($customer) && isset($customer)) {
            $customer->getAttribute()->setNfxApiConnectorContactId($idContact);
            $customer->getAttribute()->setNfxApiConnectorCustomerId($idCustomer);
            $customer->getAttribute()->setNfxApiConnectorContactChanges($date);
            Shopware()->Models()->flush();
        }
    }

    public function getData($config)
    {

        $customer = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer');
        /** @var $customers \Shopware\Models\Customer\Customer */

        $timeNow = strtotime("now");
        $configTime = $config["nfx_transfer_time"] * 60;

        $diffTime = date("Y-m-d H:i:s", $timeNow - $configTime);
        $diffTime = strtotime($diffTime);

        try {
            $customerIdsFromDate = Shopware()->Db()->fetchAll("SELECT userID FROM s_user_attributes WHERE UNIX_TIMESTAMP(nfx_global_changed) >= {$diffTime} AND userID IN (SELECT userID FROM s_order WHERE UNIX_TIMESTAMP(ordertime) >= {$diffTime})");                     
        } catch (DBALException $exception) {
            $exception->getTrace();
        }
        $convertedIds = [];

        foreach ($customerIdsFromDate as $item) {
            array_push($convertedIds, $item["userID"]);
        }

        $customers = $customer->findBy(['id' => $convertedIds]);
        $orderData = new OrderData();

        if (!empty($customers) && isset($customers)) {
            $data = array(
                'users' => array(),
                'tags' => array()
            );
            
            //get an instance of NFXBonusSystemFeatures in order to read the details about the expiring points
            $plugins = Shopware()->Models()->getRepository('Shopware\Models\Plugin\Plugin');
            $pluginNFXBonusSystemFeatures = $plugins->findOneBy(['name' => "NFXBonusSystemFeatures", 'active' => 1]);
            if($pluginNFXBonusSystemFeatures){
                $pluginNFXBonusSystemFeatures = Shopware()->Plugins()->Core()->NFXBonusSystemFeatures();
            }
            foreach ($customers as $customer) {
                if($customer->getGroup()->getAttribute()->getNfxApiListId() === 0) {
                    continue;
                }
                
                $customerBonusSystem = 0;
                $bonusSystemResult = Shopware()->Db()->fetchOne("SELECT points FROM s_core_plugins_bonus_user_points WHERE userID = {$customer->getId()}");
                if (!empty($bonusSystemResult) && isset($bonusSystemResult)) {
                    $customerBonusSystem = $bonusSystemResult;
                }
                
                //get expiring points
                $customerExpiringBonusPoints = "";
                $customerExpiringBonusPointsDate = "";
                if($pluginNFXBonusSystemFeatures) {
                    $expiringPoints = $pluginNFXBonusSystemFeatures->getExpiringPoints($customer->getId());
                    $customerExpiringBonusPoints = $expiringPoints["expiring_bonuspoints"];
                    $customerExpiringBonusPointsDate = $expiringPoints["expiring_bonuspoints_date"];
                    if($customerExpiringBonusPointsDate){
                        $customerExpiringBonusPointsDate = explode(" ", $customerExpiringBonusPointsDate);
                        $customerExpiringBonusPointsDate = $customerExpiringBonusPointsDate[0];
                    }
                }

                $recommendationProducts = array();
                $recommendationProductsArray = explode('|', $customer->getAttribute()->getNfxApiRecommendation());

                /** @var $articleDetailModel \Shopware\Models\Article\Detail */
                $articleDetailModel = Shopware()->Models()->getRepository('Shopware\Models\Article\Detail');

                $recommendationProductsCount = 0;
                foreach ($recommendationProductsArray as $product) {
                    if (!empty($product) && isset($product)) {
                        /** @var $articleDetail \Shopware\Models\Article\Detail */
                        $articleDetail = $articleDetailModel->findOneBy(array('number' => $product));
                        if (!empty($articleDetail)) {
                            $article = $articleDetail->getArticle();
                            $articleImages = Shopware()->Modules()->Articles()->sGetArticlePictures($article->getId());
                            $imageUrl = $articleImages['src']['original'];
                            $articleData = Shopware()->Modules()->Articles()->sGetArticleById($article->getId());
                            array_push($recommendationProducts, array(
                                'image' => $imageUrl,
                                'name' => $article->getName(),
                                'desc' => $article->getDescription(),
                                'price' => $articleData['price_numeric'],
                                'pseudo_price' => $articleData['pseudoprice_numeric']
                            ));
                            $recommendationProductsCount++;
                            if ($recommendationProductsCount == 5) {
                                break;
                            }
                        }
                    }
                }

                //Get customer list
                $listID = 1;
                if(!empty($customer->getGroup()->getAttribute()->getNfxApiListId())) {
                    $listID = $customer->getGroup()->getAttribute()->getNfxApiListId();
                }

                //Get customer tags & add tag list
                $tags = $customer->getAttribute()->getNfxApiConnectorContactTags();
                if(!empty($tags) && isset($tags)) {
                    $tags = explode(',', $tags);
                } else {
                    $tags = array();
                }
                $tagList = $customer->getGroupKey();
                array_push($tags, $tagList);
                $tags = implode(',', $tags);
                
                $billingAddressChanged = "";
                if($customer->getDefaultBillingAddress()->getAttribute()){
                    $billingAddressChanged = $customer->getDefaultBillingAddress()->getAttribute()->getNfxApiChanges();
                }
                
                $temp = array(
                    'contact' => array(
                        'contact' => array(
                            'email' => $customer->getEmail(),
                            'firstName' => $customer->getFirstname(),
                            'lastName' => $customer->getLastname(),
                            'phone' => $customer->getDefaultBillingAddress()->getPhone(),
                        )
                    ),
                    'ecomCustomer' => array(
                        'ecomCustomer' => array(
                            'externalid' => $customer->getNumber(),
                            'connectionid' => $config['connectionID'],
                            'email' => $customer->getEmail(),
                            'acceptsMarketing' => $customer->getNewsletter()
                        )
                    ),
                    'contactList' => array(
                        'contactList' => array(
                            'list' => $listID,
                            'contact' => null,
                            'status' => '1'
                        )
                    ),
                    'customFields' => array(
                        'country' => $customer->getDefaultBillingAddress()->getCountry()->getName(),
                        'street' => $customer->getDefaultBillingAddress()->getStreet(),
                        'zipcode' => $customer->getDefaultBillingAddress()->getZipcode(),
                        'city' => $customer->getDefaultBillingAddress()->getCity(),
                        'salutation' => $customer->getSalutation(),
                        'bonusSystem' => $customerBonusSystem,
                        'expiringBonusPoints' => $customerExpiringBonusPoints,
                        'expiringBonusPointsDate' => $customerExpiringBonusPointsDate
                    ),
                    'apiData' => array(
                        'contactID' => $customer->getAttribute()->getNfxApiConnectorContactId(),
                        'customerID' => $customer->getAttribute()->getNfxApiConnectorCustomerId(),
                        'contactChanges' => $customer->getAttribute()->getNfxApiConnectorContactChanges(),
                        'tags' => $tags
                    ),
                    'changesAddress' => $billingAddressChanged,
                    'recommendationProducts' => $recommendationProducts
                );
                array_push($data['users'], $temp);
                array_push($data['tags'], $tags);
            }

            return $data;
        }
    }

    public function getCustomer($id)
    {
        $apiAuthentication = new ApiAuthentication();
        $result = $apiAuthentication->get(array(), 'contacts/' . $id);
        return $result;
    }

}