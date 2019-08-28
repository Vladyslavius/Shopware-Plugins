<?php

namespace itDelightCommunicationApplication\Helpers;

class RetourenData
{

    public function getRetoure($orderID) {

        return Shopware()->Db()->fetchRow("SELECT * FROM s_dreisc_retoure WHERE orderId = {$orderID}");

    }

    public function updateRetoure($data, $messages) {

        $orderID = $data['orderID'];
        $userID = $data['userID'];

        $result = array();

        if(empty(Shopware()->Db()->fetchRow("SELECT * FROM s_dreisc_retoure WHERE orderId = {$orderID}"))) {

            $lastRetoure = Shopware()->Db()->fetchRow("SELECT * FROM s_dreisc_retoure ORDER BY id DESC LIMIT 1");

            if(empty($data['comment'])) {
                $data['comment'] = '';
            }

            Shopware()->Db()->insert('s_dreisc_retoure', array(
                'number' => $lastRetoure['number']+1,
                'userId' => $userID,
                'orderId' => $orderID,
                'statusId' => 1,
                'comment' => $data['comment'],
                'creation_date' => date('Y-m-d H:i:s')
            ));

            foreach ($data['products'] as $product) {

                if($product['returnQuantity'] > 0) {
                    Shopware()->Db()->insert('s_dreisc_retoure_selection', array(
                        'orderId' => $orderID,
                        'userId' => $userID,
                        'orderDetailId' => $product['orderDetailID'],
                        'quantity' => $product['returnQuantity'],
                        'reason' => $product['reason']
                    ));
                }

            }

            $result['status'] = $messages['success'];

        } else {

            $result['status'] = $messages['error'];

        }

        return $result;

    }

}