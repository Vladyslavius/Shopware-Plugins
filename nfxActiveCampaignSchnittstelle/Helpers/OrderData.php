<?php

namespace nfxActiveCampaignSchnittstelle\Helpers;

use Doctrine\DBAL\DBALException;

class OrderData
{

    private $currency = '';

    public function __construct()
    {

        $this->currency = Shopware()->Shop()->getCurrency()->getCurrency();

    }

    public function getData($config)
    {
        $order = Shopware()->Models()->getRepository('Shopware\Models\Order\Order');
        $orderDetail = Shopware()->Models()->getRepository('Shopware\Models\Order\Detail');
        $articles = Shopware()->Models()->getRepository('Shopware\Models\Article\Article');
        $user = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer');

        $timeNow = strtotime("now");
        $configTime = $config["nfx_transfer_time"] * 60;

        $diffTime = date("Y-m-d H:i:s", $timeNow - $configTime);
        $diffTime = strtotime($diffTime);

        try {
            $orderIdsFromDate = Shopware()->Db()->fetchAll("SELECT id FROM s_order WHERE UNIX_TIMESTAMP(ordertime) >= {$diffTime}");           
        } catch (DBALException $exception) {
            $exception->getTrace();
        }
        $convertedIds = [];

        foreach ($orderIdsFromDate as $item) {
            array_push($convertedIds, $item['id']);
        }

        /** @var $orders \Shopware\Models\Order\Order */
        $orders = $order->findBy(['id' => $convertedIds]);

        if (!empty($orders) && isset($orders)) {

            $data = array();

            foreach ($orders as $order) {
                /** @var $orderCustomer \Shopware\Models\Customer\Customer */
                $orderCustomer = $order->getCustomer();
                /** @var $orderItems \Shopware\Models\Order\Detail */
                $orderItems = $orderDetail->findBy(array('orderId' => $order->getId()));

                /** @var $orderDispatch \Shopware\Models\Dispatch\Dispatch */
                $orderDispatch = $order->getDispatch();
                $dispatchName = "";//open carts have no dispatch
                if($orderDispatch){
                    if($orderDispatch->getId()){
                        $dispatchName = $orderDispatch->getName();
                    }
                }


                $orderProducts = array();
                foreach ($orderItems as $orderItem) {                    
                    if($orderItem->getMode() != 0){
                        continue;//do not export e.g. Vouchers
                    }
                    /** @var $article \Shopware\Models\Article\Article */
                    $article = $articles->findOneBy(array('id' => $orderItem->getArticleId()));
                    if(!$article){
                        continue;//do not export removed articles
                    }

                    /** @var $categories \Shopware\Models\Category\Category */
                    $categories = $article->getCategories();
                    $category = array();
                    foreach ($categories as $cat) {
                        array_push($category, $cat->getName());
                    }

                    $articleImages = Shopware()->Modules()->Articles()->sGetArticlePictures($orderItem->getArticleId());
                    $productUrl = Shopware()->Front()->Router()->assemble(['sViewport' => 'detail', 'sArticle' => $orderItem->getId()]);
                    $imageUrl = $articleImages['src']['original'];

                    array_push($orderProducts, array(
                        'externalid' => $orderItem->getArticleNumber(),
                        'name' => $orderItem->getArticleName(),
                        'price' => $orderItem->getQuantity() * $orderItem->getPrice() * 100,
                        'quantity' => $orderItem->getQuantity(),
                        'category' => implode(', ', $category),
                        'productUrl' => $productUrl,
                        'imageUrl' => $imageUrl
                    ));

                }

                $customerID = $orderCustomer->getAttribute()->getNfxApiConnectorCustomerId();
                if(!$customerID){
                    continue;
                }

                //Find global user account accountMode = 0 && email = customer
                $users = $user->findBy(array('email' => $orderCustomer->getEmail(), 'accountMode' => 0));
                if (!empty($users)) {
                    /** @var $userItem \Shopware\Models\Customer\Customer */
                    foreach ($users as $userItem) {
                        if (!empty($userItem->getAttribute()->getNfxApiConnectorCustomerId())) {
                            $customerID = $userItem->getAttribute()->getNfxApiConnectorCustomerId();
                        }
                    }
                }


                //Check canceled orders (status = -1)
                if($order->getOrderStatus()->getId() === -1) {

                    $temp = array(
                        'ecomOrder' => array(
                            'externalcheckoutid' => $order->getId(),
                            'source' => '1',
                            'email' => $orderCustomer->getEmail(),
                            'orderProducts' => $orderProducts,
                            'externalCreatedDate' => $order->getOrderTime()->format(DATE_W3C),
                            'abandonedDate' => $order->getOrderTime()->format(DATE_W3C),
                            'totalPrice' => $order->getInvoiceAmount() * 100,
                            'currency' => $this->currency,
                            'connectionid' => $config['connectionID'],
                            'customerid' => $customerID
                        )
                    );

                } else {

                    $temp = array(
                        'ecomOrder' => array(
                            'externalid' => $order->getNumber(),
                            'source' => '0',
                            'email' => $orderCustomer->getEmail(),
                            'orderNumber' => $order->getNumber(),
                            'orderProducts' => $orderProducts,
                            'orderDate' => $order->getOrderTime()->format(DATE_W3C),
                            'totalPrice' => $order->getInvoiceAmount() * 100,
                            'currency' => $this->currency,
                            'shippingMethod' => $dispatchName,
                            'connectionid' => $config['connectionID'],
                            'customerid' => $customerID
                        )
                    );

                }

                array_push($data, $temp);
            }

            return $data;

        }
    }

}