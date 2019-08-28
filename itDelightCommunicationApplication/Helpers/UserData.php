<?php

namespace itDelightCommunicationApplication\Helpers;

use Elasticsearch\Endpoints\Cat\Plugins;

class UserData
{

    public function changeAddress($userID, $userInfo, $type)
    {

        //$type = billing / shipping

        $messages = array(
            'success' => array(
                'code' => 'success',
                'text' => 'Information erfolgreich aktualisiert'
            ),
            'error' => array(
                'code' => 'error',
                'text' => 'Information konnte nicht aktualisiert werden'
            )
        );

        /** @var $userModel \Shopware\Models\Customer\Customer */
        $userModel = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->findOneBy(array('id' => $userID));

        /** @var $countryModel \Shopware\Models\Country\Country */
        $countryModel = Shopware()->Models()->getRepository('Shopware\Models\Country\Country')->findOneBy(array('id' => $userInfo['country']));

        if (!empty($userModel)) {

            $userAddressID = null;

            if ($type == 'billing') {

                $userAddressID = $userModel->getDefaultBillingAddress()->getId();

            } elseif ($type == 'shipping') {

                $userAddressID = $userModel->getDefaultShippingAddress()->getId();
                if ($userAddressID == $userModel->getDefaultBillingAddress()->getId()) {

                    $shippingAddress = array(
                        'salutation' => ' ',
                        'firstname' => ' ',
                        'lastname' => ' ',
                        'street' => ' ',
                        'zipcode' => ' ',
                        'city' => ' ',
                    );

                    $shippingAddress['country'] = $countryModel;
                    $address = new \Shopware\Models\Customer\Address();
                    $address->fromArray($shippingAddress);
                    $address->setCountry($countryModel);

                    Shopware()->Container()->get('shopware_account.address_service')->create($address, $userModel);
                    Shopware()->Container()->get('shopware_account.address_service')->setDefaultShippingAddress($address);
                    $userAddressID = $userModel->getDefaultShippingAddress()->getId();

                }

            }

            /** @var $userModelAddress \Shopware\Models\Customer\Address */
            $userModelAddress = Shopware()->Models()->getRepository('Shopware\Models\Customer\Address')->findOneBy(array('id' => $userAddressID));

            if (!empty($userModelAddress)) {

                $userModelAddress->setSalutation($userInfo['salutation']);
                $userModelAddress->setFirstname($userInfo['firstname']);
                $userModelAddress->setLastname($userInfo['lastname']);
                $userModelAddress->setStreet($userInfo['street']);
                $userModelAddress->setZipcode($userInfo['zipcode']);
                $userModelAddress->setCity($userInfo['city']);
                $userModelAddress->setPhone($userInfo['phone']);

                if (!empty($countryModel)) {
                    $userModelAddress->setCountry($countryModel);
                }

                if ($userInfo['customer_type'] == 'business') {
                    $userModelAddress->setCompany($userInfo['company']);
                    $userModelAddress->setDepartment($userInfo['department']);
                    $userModelAddress->setVatId($userInfo['vatId']);
                } else {
                    $userModelAddress->setCompany(null);
                    $userModelAddress->setDepartment(null);
                    $userModelAddress->setVatId(null);
                }

                try {

                    Shopware()->Models()->flush($userModelAddress);
                    return $messages['success'];

                } catch (\Doctrine\ORM\ORMException $exception) {

                    $exception->getTrace();

                }

            } else {

                return $messages['error'];

            }

        } else {

            return $messages['error'];

        }

    }

    public function favorites($userID, $type, $productID)
    {
        //Type: delete, deleteAll, add

        $messages = array(
            'product_add' => array(
                'code' => 'product_add',
                'text' => 'Produkt zu der Wunschliste hinzugefügt'
            ),
            'product_add_error' => array(
                'code' => 'product_add_error',
                'text' => 'Produkt konnten nicht zu der Wunschliste hinzugefügt werden'
            ),
            'product_delete' => array(
                'code' => 'product_delete',
                'text' => 'Produkt aus der Wunschliste entfernt'
            ),
            'product_delete_error' => array(
                'code' => 'product_delete_error',
                'text' => 'Produkt konnte nicht aus der Wunschliste entfernt werden'
            ),
            'product_delete_all' => array(
                'code' => 'product_delete_all',
                'text' => 'Alle Produkte aus der Wunschliste entfernt'
            ),
            'product_delete_all_error' => array(
                'code' => 'product_delete_all_error',
                'text' => 'Es konnten nicht alle Produkte aus der Wunschliste entfernt werden'
            )
        );

        if ($type == 'delete') {

            if (!empty($userID) && isset($userID) && !empty($productID) && isset($productID)) {
                $result = Shopware()->Db()->delete('s_order_notes', "userID = {$userID} AND ordernumber = {$productID}");
                if (!empty($result)) {
                    $result = $messages['product_delete'];
                } else {
                    $result = $messages['product_delete_error'];
                }
            } else {
                $result = $messages['product_delete_error'];
            }

        } elseif ($type == 'deleteAll') {

            if (!empty($userID) && isset($userID)) {
                $resultDelete = Shopware()->Db()->delete('s_order_notes', "userID = '{$userID}'");
                if (!empty($resultDelete)) {
                    $result = $messages['product_delete_all'];
                } else {
                    $result = $messages['product_delete_all_error'];
                }
            } else {
                $result = $messages['product_delete_all_error'];
            }

        } elseif ($type == 'add') {

            if (!empty($userID) && isset($userID) && !empty($productID) && isset($productID)) {

                $checkArticle = Shopware()->Db()->fetchAll("SELECT * FROM s_order_notes WHERE userID = {$userID} AND ordernumber = {$productID}");

                if (!empty($checkArticle) && isset($checkArticle)) {

                    $result = $messages['product_add'];

                } else {

                    $sArticleID = Shopware()->Modules()->Articles()->sGetArticleIdByOrderNumber($productID);
                    $sArticleName = Shopware()->Modules()->Articles()->sGetArticleNameByArticleId($sArticleID);

                    $resultInsert = Shopware()->Db()->insert('s_order_notes', array(
                        'sUniqueID' => md5($userID . '' . date('YmdHis')),
                        'userID' => $userID,
                        'articlename' => $sArticleName,
                        'articleID' => $sArticleID,
                        'ordernumber' => $productID,
                        'datum' => date('Y-m-d H:i:s')
                    ));

                    if ($resultInsert) {
                        $result = $messages['product_add'];
                    } else {
                        $result = $messages['product_add_error'];
                    }

                }

            } else {

                $result = $messages['product_add_error'];

            }

        } else {

            $result = false;

        }

        return $result;

    }

    public function changePaymentOption($userID, $paymentID)
    {

        $messages = array(
            'success' => array(
                'code' => 'success',
                'text' => 'Information erfolgreich aktualisiert'
            ),
            'error' => array(
                'code' => 'error',
                'text' => 'Information konnte nicht aktualisiert werden'
            )
        );

        /** @var $userModel \Shopware\Models\Customer\Customer */
        $userModel = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->findOneBy(array('id' => $userID));

        if (!empty($userModel)) {

            $userModel->setPaymentId($paymentID);

            try {

                Shopware()->Models()->flush($userModel);
                return $messages['success'];

            } catch (\Doctrine\ORM\ORMException $exception) {

                $exception->getTrace();

            }

        } else {

            return $messages['error'];

        }

    }

    public function resetPassword($email)
    {

        $snippets = Shopware()->Snippets()->getNamespace('frontend/account/password');

        if (empty($email)) {
            return 'error';
        }

        $userID = Shopware()->Modules()->Admin()->sGetUserByMail($email);
        if (empty($userID)) {
            return [];
        }

        $hash = \Shopware\Components\Random::getAlphanumericString(32);

        $context = [
            'sUrlReset' => Shopware()->Front()->Router()->assemble(['controller' => 'account', 'action' => 'resetPassword', 'hash' => $hash]),
            'sUrl' => Shopware()->Front()->Router()->assemble(['controller' => 'account', 'action' => 'resetPassword']),
            'sKey' => $hash,
        ];

        $sql = 'SELECT 
              s_user.accountmode,
              s_user.active,
              s_user.affiliate,
              s_user.birthday,
              s_user.confirmationkey,
              s_user.customergroup,
              s_user.customernumber,
              s_user.email,
              s_user.failedlogins,
              s_user.firstlogin,
              s_user.lastlogin,
              s_user.language,
              s_user.internalcomment,
              s_user.lockeduntil,
              s_user.subshopID,
              s_user.title,
              s_user.salutation,
              s_user.firstname,
              s_user.lastname,
              s_user.lastlogin,
              s_user.newsletter
              FROM s_user
              WHERE id = ?';

        $user = Shopware()->Container()->get('dbal_connection')->fetchAssoc($sql, [$userID]);
        $email = $user['email'];
        $user['attributes'] = Shopware()->Container()->get('dbal_connection')->fetchAssoc('SELECT * FROM s_user_attributes WHERE userID = ?', [$userID]);

        $context['user'] = $user;

        // Send mail
        $mail = Shopware()->TemplateMail()->createMail('sCONFIRMPASSWORDCHANGE', $context);
        $mail->addTo($email);
        $mail->send();

        // Add the hash to the optin table
        $sql = "INSERT INTO `s_core_optin` (`type`, `datum`, `hash`, `data`) VALUES ('swPassword', NOW(), ?, ?)";
        Shopware()->Db()->query($sql, [$hash, $userID]);

    }

    public function getPromocode($promocode)
    {

        $messages = array(
            'success' => array(
                'code' => 'success',
                'text' => 'Der Gutscheincode wurde erfolgreich eingelöst.'
            ),
            'error' => array(
                'code' => 'error',
                'text' => 'Der Gutscheincode konnte leider nicht eingelöst werden.'
            ),
            'error_limit' => array(
                'code' => 'error_limit',
                'text' => 'Der Gutscheincode ist nicht mehr verfügbar.'
            ),
            'error_date' => array(
                'code' => 'error_date',
                'text' => 'Der Gutscheincode ist abgelaufen.'
            ),
            'error_date_start' => array(
                'code' => 'error_date_start',
                'text' => 'Der eingegebene Gutscheincode ist noch nicht aktiv'
            )
        );

        $flag = true;
        $result = array();

        /** @var $promocodeModel \Shopware\Models\Voucher\Voucher */
        $promocodeModel = Shopware()->Models()->getRepository('Shopware\Models\Voucher\Voucher')->findOneBy(array('voucherCode' => $promocode));

        if (!empty($promocodeModel)) {

            $valid_from = '';
            $valid_to = '';

            if (!empty($promocodeModel->getValidFrom())) {
                $valid_from = date('Y-m-d', strtotime($promocodeModel->getValidFrom()->format(DATE_W3C)));
            }
            if (!empty($promocodeModel->getValidTo())) {
                $valid_to = date('Y-m-d', strtotime($promocodeModel->getValidTo()->format(DATE_W3C)));
            }

            $ordercode = $promocodeModel->getOrderCode();
            $numberofunits = $promocodeModel->getNumberOfUnits();

            if ($promocodeModel->getPercental()) {
                $percental = true;
            } else {
                $percental = false;
            }

            $result['id'] = $promocodeModel->getId();
            $result['description'] = $promocodeModel->getDescription();
            $result['percental'] = $percental;
            $result['value'] = $promocodeModel->getValue();
            $result['minimumcharge'] = $promocodeModel->getMinimumCharge();
            $result['ordercode'] = $ordercode;
            $result['numberofunits'] = $numberofunits;

            //Check dete > promocode
            if (date('Y-m-d') > $valid_to && !empty($valid_to)) {
                $flag = false;
                $result['status'] = $messages['error_date'];
            }

            //Check date < promocode
            if (date('Y-m-d') < $valid_from && !empty($valid_from)) {
                $flag = false;
                $result['status'] = $messages['error_date_start'];
            }

            //Check count order promocode
            $countOrdersPromocode = Shopware()->Db()->fetchAll("SELECT * FROM s_order_details WHERE articleordernumber = '{$ordercode}'");
            if (!empty($countOrdersPromocode)) {
                $countOrdersPromocode = count($countOrdersPromocode);
            } else {
                $countOrdersPromocode = 0;
            }
            if ($countOrdersPromocode >= $numberofunits) {
                $flag = false;
                $result['status'] = $messages['error_limit'];
            }

            if ($flag) {
                $result['status'] = $messages['success'];
            }

        } else {

            $result['status'] = $messages['error'];

        }

        return $result;

    }

    public function getDeliverySuppliers()
    {
        $result = array();
        /** @var $deliverySuppliers  \Shopware\Models\Dispatch\Dispatch */
        $deliverySuppliers = Shopware()->Models()->getRepository('Shopware\Models\Dispatch\Dispatch')->findAll();

        /** @var $deliverySupplierCost \Shopware\Models\Dispatch\ShippingCost */
        $deliverySupplierCost = Shopware()->Models()->getRepository('Shopware\Models\Dispatch\ShippingCost');

        if (!empty($deliverySuppliers)) {

            foreach ($deliverySuppliers as $deliverySupplier) {

                if ($deliverySupplier->getActive()) {

                    $criteriaCost = array(
                        'dispatch' => $deliverySupplier->getId()
                    );
                    $supplierCosts = $deliverySupplierCost->findBy($criteriaCost);
                    if (!empty($supplierCosts)) {
                        $costs = array();
                        foreach ($supplierCosts as $supplierCost) {
                            $temp = array(
                                'from' => (float)$supplierCost->getFrom(),
                                'value' => (float)$supplierCost->getValue(),
                            );
                            array_push($costs, $temp);
                        }
                    } else {
                        $costs = null;
                    }

                    $temp = array(
                        'id' => $deliverySupplier->getId(),
                        'description' => $deliverySupplier->getDescription(),
                        'name' => $deliverySupplier->getName(),
                        'shippingFree' => (float)$deliverySupplier->getShippingFree(),
                        'costs' => $costs
                    );
                    array_push($result, $temp);

                }

            }

        }
        return $result;

    }

}