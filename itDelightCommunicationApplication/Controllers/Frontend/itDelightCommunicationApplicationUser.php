<?php

use Shopware\Components\CSRFWhitelistAware;
use Shopware\Models\Article\Detail;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Order\Billing;
use Shopware\Models\Order\Detail as OrderDetail;
use Shopware\Models\Order\DetailStatus;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Shipping;
use Shopware\Models\Order\Status;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Shop\Shop;
use itDelightCommunicationApplication\Helpers\JsonData;
use itDelightCommunicationApplication\Helpers\Debugger;
use itDelightCommunicationApplication\Helpers\ArticleData;
use itDelightCommunicationApplication\Helpers\UserData;
use itDelightCommunicationApplication\Helpers\apiConnector;
use itDelightCommunicationApplication\Helpers\CurlData;
use itDelightCommunicationApplication\Helpers\RetourenData;

class Shopware_Controllers_Frontend_itDelightCommunicationApplicationUser extends Enlight_Controller_Action implements CSRFWhitelistAware
{

    private $jsonHelper;
    private $debugger;
    private $article;
    private $user;
    private $curl;
    private $retouren;

    public function __construct(Enlight_Controller_Request_Request $request, Enlight_Controller_Response_Response $response)
    {
        parent::__construct($request, $response);

        $this->jsonHelper = new JsonData();
        $this->debugger = new Debugger();
        $this->article = new ArticleData();
        $this->user = new UserData();
        $this->curl = new CurlData();
        $this->retouren = new RetourenData();

    }

    public function getWhitelistedCSRFActions()
    {

        return [
            'getInfo',
            'login',
            'getAddressInfo',
            'getPaymentMethod',
            'getFavourites',
            'getCurrentOrders',
            'getDoneOrders',
            'getDeniedOrders',
            'getOrderDetails',
            'getReturnOrders',
            'register',
            'getCountries',
            'changeUserInfo',
            'changeUserBillingAddress',
            'changeUserShippingAddress',
            'addToFavourite',
            'deleteAllFavourite',
            'deleteFavourite',
            'changeUserPassword',
            'getPaymentOptions',
            'changeUserPaymentOption',
            'addReview',
            'resetPassword',
            'getReturnData',
            'makeReturn',
            'getPromocodeData',
            'getDeliverySuppliers',
            'changeUserPushNotificationToken',
            'createOrderAction'
        ];

    }

    private function shopRender()
    {
        $this->container->get('front')->Plugins()->ViewRenderer()->setNoRender();
    }

    public function getInfoAction()
    {

        $this->shopRender();

        $userID = $this->request->getPost('userID');

        if (!empty($userID)) {

            /** @var $userModel \Shopware\Models\Customer\Customer */
            $userModel = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->findOneBy(array('id' => $userID));

            if (!empty($userModel)) {

                $companyInfo = null;
                if ($userModel->getGroupKey() == 'H') {
                    $companyInfo = array(
                        'company' => $userModel->getDefaultBillingAddress()->getCompany(),
                        'department' => $userModel->getDefaultBillingAddress()->getDepartment(),
                        'ustid' => $userModel->getDefaultBillingAddress()->getVatId()
                    );
                }
                //$userModel->getBirthday()
                $data = array(
                    'name' => $userModel->getFirstname(),
                    'surname' => $userModel->getLastname(),
                    'email' => $userModel->getEmail(),
                    'phone' => $userModel->getDefaultBillingAddress()->getPhone(),
                    'country' => $userModel->getDefaultBillingAddress()->getCountry()->getName(),
                    'street' => $userModel->getDefaultBillingAddress()->getStreet(),
                    'city' => $userModel->getDefaultBillingAddress()->getCity(),
                    'post' => $userModel->getDefaultBillingAddress()->getZipcode(),
                    'gender' => $userModel->getSalutation(),
                    'userType' => $userModel->getGroupKey(),
                    'points' => 0,
                    'companyInfo' => $companyInfo
                );
                if (!empty($userModel->getBirthday())) {
                    $data['birthDate'] = date('Y-m-d', strtotime($userModel->getBirthday()->format(DATE_W3C)));
                } else {
                    $data['birthDate'] = "";
                }

                echo $this->jsonHelper->returnJsonData($data);

            } else {

                $this->jsonHelper->returnError();

            }

        } else {

            $this->jsonHelper->returnError();

        }

    }


    public function loginAction()
    {

        $this->shopRender();

        $data = array();

        $email = $this->request->getPost('email');

        $checkUser = Shopware()->Modules()->Admin()->sLogin();

        $data['system'] = $checkUser;

        if (empty($checkUser['sErrorFlag']) && empty($checkUser['sErrorMessages'])) {

            $userID = Shopware()->Modules()->Admin()->sGetUserByMail($email);

            $data['userID'] = $userID;

        }

        echo $this->jsonHelper->returnJsonData($data);

    }

    public function getAddressInfoAction()
    {

        $this->shopRender();

        //$type: shipping/billing

        $userID = $this->request->getPost('userID');
        $type = $this->request->getPost('type');

        /** @var $userModel \Shopware\Models\Customer\Customer */
        $userModel = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->findOneBy(array('id' => $userID));

        if (!empty($userModel)) {

            $data = array(
                'userID' => $userID,
            );

            if ($type == 'shipping' || $type == 'billing') {

                switch ($type) {
                    case 'shipping':
                        $userModelAddress = $userModel->getDefaultShippingAddress();
                        break;
                    case 'billing':
                        $userModelAddress = $userModel->getDefaultBillingAddress();
                        break;
                }

                $data['company'] = $userModelAddress->getCompany();
                $data['department'] = $userModelAddress->getDepartment();
                $data['vatId'] = $userModelAddress->getVatId();
                $data['salutation'] = $userModelAddress->getSalutation();
                $data['firstname'] = $userModelAddress->getFirstname();
                $data['lastname'] = $userModelAddress->getLastname();
                $data['street'] = $userModelAddress->getStreet();
                $data['zipcode'] = $userModelAddress->getZipcode();
                $data['city'] = $userModelAddress->getCity();
                $data['country'] = $userModelAddress->getCountry()->getName();
                $data['phone'] = $userModelAddress->getPhone();

                echo $this->jsonHelper->returnJsonData($data);

            } else {

                $this->jsonHelper->returnError();

            }

        } else {

            $this->jsonHelper->returnError();

        }

    }

    public function getPaymentMethodAction()
    {

        $this->shopRender();

        $userID = $this->request->getPost('userID');

        $criteriaUser = array(
            'id' => $userID
        );
        /** @var $userModel \Shopware\Models\Customer\Customer */
        $userModel = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->findOneBy($criteriaUser);


        $criteriaPayment = array(
            'id' => $userModel->getPaymentId()
        );
        /** @var $paymentModel \Shopware\Models\Payment\Payment */
        $paymentModel = Shopware()->Models()->getRepository('Shopware\Models\Payment\Payment')->findOneBy($criteriaPayment);

        if (!empty($userModel)) {

            $data = array(
                'paymentMethod' => array(
                    'id' => $userModel->getPaymentId(),
                    'name' => $paymentModel->getDescription()
                )
            );

            echo $this->jsonHelper->returnJsonData($data);

        } else {

            $this->jsonHelper->returnError();

        }

    }

    public function getFavouritesAction()
    {

        $this->shopRender();

        $userID = $this->request->getPost('userID');

        /** @var $userModel \Shopware\Models\Customer\Customer */
        $userModel = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->findOneBy(array('id' => $userID));

        if (!empty($userModel)) {

            $data = array(
                'productIDs' => array()
            );

            $articles = Shopware()->Db()->fetchAll("SELECT * FROM s_order_notes WHERE userID = {$userID}");

            if (!empty($articles)) {

                foreach ($articles as $article) {
                    array_push($data['productIDs'], $article['ordernumber']);
                }

            }

            echo $this->jsonHelper->returnJsonData($data);

        } else {

            $this->jsonHelper->returnError();

        }

    }

    function checkOrdersType($orders, $type)
    {

        //$type (return = return, default = detault)

        foreach ($orders as $key => &$order) {

            $orderRetoure = Shopware()->Db()->fetchOne("SELECT id FROM s_dreisc_retoure WHERE orderId = {$order['orderID']}");

            if ($type == 'return') {
                if (empty($orderRetoure)) {
                    unset($orders[$key]);
                }
            } elseif ($type == 'default') {
                if (!empty($orderRetoure)) {
                    unset($orders[$key]);
                }
            }

        }

        $orders = array_values($orders);

        return $orders;

    }

    private function getOrders($criteria)
    {

        $data = array();

        /** @var $ordersModel \Shopware\Models\Order\Order */
        $ordersModel = Shopware()->Models()->getRepository('Shopware\Models\Order\Order')->findBy($criteria);

        if (!empty($ordersModel)) {

            foreach ($ordersModel as $item) {

                switch ($item->getOrderStatus()->getName()) {
                    case 'open' :
                        $status = 'Offen';
                        break;
                    case 'in_process' :
                        $status = 'In Bearbeitung';
                        break;
                    case 'completed' :
                        $status = 'Abgeschlossen';
                        break;
                    case 'partially_completed' :
                        $status = 'Teilweise bearbeitet';
                        break;
                    case 'cancelled_rejected' :
                        $status = 'Storniert';
                        break;
                    case 'ready_for_delivery' :
                        $status = 'Lieferbereit';
                        break;
                    case 'partially_delivered' :
                        $status = 'Teilweise versendet';
                        break;
                    case 'completely_delivered' :
                        $status = 'Versendet';
                        break;
                    case 'clarification_required' :
                        $status = 'Klärung benötigt';
                        break;
                    default :
                        $status = 'Status nicht definiert';
                }

                $deliveryAddress = $item->getShipping()->getCountry()->getName();
                $deliveryAddress .= ', ';
                $deliveryAddress .= $item->getShipping()->getCity();
                $deliveryAddress .= ', ';
                $deliveryAddress .= $item->getShipping()->getStreet();
                if ($item->getShipping()->getAdditionalAddressLine1()) {
                    $deliveryAddress .= ', ';
                    $deliveryAddress .= $item->getShipping()->getAdditionalAddressLine1();
                }
                if ($item->getShipping()->getAdditionalAddressLine2()) {
                    $deliveryAddress .= ', ';
                    $deliveryAddress .= $item->getShipping()->getAdditionalAddressLine2();
                }

                $deliveryService = $item->getDispatch()->getName();

                array_push($data, array(
                    'orderID' => $item->getId(),
                    'date' => $item->getOrderTime()->format('Y-m-d H:i:s'),
                    'status' => $status,
                    'deliveryAddress' => $deliveryAddress,
                    'deliveryService' => $deliveryService
                ));

            }

        }

        return $data;

    }

    public function getCurrentOrdersAction()
    {

        $this->shopRender();

        //[…, {orderID, date, delivery, status}, …]

        $userID = $this->request->getPost('userID');

        /** @var $userModel \Shopware\Models\Customer\Customer */
        $userModel = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->findOneBy(array('id' => $userID));


        if (!empty($userModel)) {

            $criteria = array(
                'customer' => $userID
            );

            $data = array(
                'orders' => $this->checkOrdersType($this->getOrders($criteria), 'default')
            );

            echo $this->jsonHelper->returnJsonData($data);

        } else {

            $this->jsonHelper->returnError();

        }

    }


    public function getDoneOrdersAction()
    {

        $this->shopRender();

        //[…, {orderID, date, delivery, status}, …]

        $userID = $this->request->getPost('userID');

        /** @var $userModel \Shopware\Models\Customer\Customer */
        $userModel = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->findOneBy(array('id' => $userID));

        if (!empty($userModel)) {

            $orderStatus = 2;

            $criteria = array(
                'customer' => $userID,
                'orderStatus' => $orderStatus
            );

            $data = array(
                'orders' => $this->checkOrdersType($this->getOrders($criteria), 'default')
            );

            echo $this->jsonHelper->returnJsonData($data);

        } else {

            $this->jsonHelper->returnError();

        }

    }

    public function getDeniedOrdersAction()
    {

        $this->shopRender();

        //[…, {orderID, date, delivery, status}, …]

        $userID = $this->request->getPost('userID');

        /** @var $userModel \Shopware\Models\Customer\Customer */
        $userModel = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->findOneBy(array('id' => $userID));

        if (!empty($userModel)) {

            $orderStatus = 4;

            $criteria = array(
                'customer' => $userID,
                'orderStatus' => $orderStatus
            );

            $data = array(
                'orders' => $this->checkOrdersType($this->getOrders($criteria), 'default')
            );

            echo $this->jsonHelper->returnJsonData($data);

        } else {

            $this->jsonHelper->returnError();

        }

    }

    public function getReturnOrdersAction()
    {

        $this->shopRender();
        $userID = $this->request->getPost('userID');

        /** @var $userModel \Shopware\Models\Customer\Customer */
        $userModel = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->findOneBy(array('id' => $userID));

        if (!empty($userModel)) {

            $criteria = array(
                'customer' => $userID
            );

            $orders = $this->checkOrdersType($this->getOrders($criteria), 'return');

            $data = array(
                'orders' => $orders
            );

            echo $this->jsonHelper->returnJsonData($data);

        } else {

            $this->jsonHelper->returnError();

        }

    }


    public function getOrderDetailsAction()
    {

        $this->shopRender();

        $orderID = $this->request->getPost('orderID');

        $data = array(
            'products' => array(),
            'pseudoProducts' => array()
        );

        /** @var $orderModel \Shopware\Models\Order\Order */
        $orderModel = Shopware()->Models()->getRepository('Shopware\Models\Order\Order')->findOneBy(array('id' => $orderID));

        if (!empty($orderModel)) {

            /** @var $orderDetailModel \Shopware\Models\Order\Detail */
            $orderDetailModel = Shopware()->Models()->getRepository('Shopware\Models\Order\Detail')->findBy(array('order' => $orderID));

            if (!empty($orderDetailModel)) {

                foreach ($orderDetailModel as $item) {

                    $sArticle = $this->article->getArticle($item->getArticleNumber());
                    $returnData = null;

                    $product = array(
                        'productID' => $item->getArticleNumber(),
                        'productName' => $item->getArticleName(),
                        'productCount' => $item->getQuantity(),
                        'productPrice' => $item->getPrice(),
                        'previewImageURL' => $sArticle['image']['thumbnails'][0]['source'],
                        'returnData' => null
                    );

                    $productReturn = Shopware()->Db()->fetchRow("SELECT * FROM s_dreisc_retoure_selection WHERE orderId = {$orderID} AND orderDetailId = {$item->getId()}");
                    if (!empty($productReturn)) {
                        $product['returnData'] = array(
                            'count' => $productReturn['quantity'],
                            'reason' => $productReturn['reason']
                        );
                    }

                    if ($item->getPrice() > 0) {
                        array_push($data['products'], $product);
                    } else {
                        array_push($data['pseudoProducts'], $product);
                    }

                }

                $data['orderTotalPrice'] = $orderModel->getInvoiceAmountNet();
                $data['orderDeliveryPrice'] = $orderModel->getInvoiceShipping();
                $data['orderVAT'] = $orderModel->getInvoiceAmount();

                echo $this->jsonHelper->returnJsonData($data);

            } else {

                $this->jsonHelper->returnError();

            }

        } else {

            $this->jsonHelper->returnError();

        }

    }

    public function registerAction()
    {

        $this->shopRender();

        $messages = array(
            'success' => array(
                'code' => 'success',
                'text' => 'Ihre Anmeldung war erfolgreich'
            ),
            'error_password' => array(
                'code' => 'error_password',
                'text' => 'Passwort stimmt nicht überein. Die Mindestzeichenlänge muss 8 Symbole sein.'
            ),
            'error_email' => array(
                'code' => 'error_email',
                'text' => 'Die E-Mail-Adresse ist ungültig.'
            ),
            'error_exists_user' => array(
                'code' => 'error_exists_user',
                'text' => 'Der Benutzer mit der angegebenen E-Mail-Adresse existiert schon.'
            )
        );
        $result = array();
        $flag = false;
        $client = new apiConnector();

        $customerType = $this->request->getPost('customerType');
        $salutation = $this->request->getPost('salutation');
        $firstname = $this->request->getPost('firstname');
        $lastname = $this->request->getPost('lastname');
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $phone = $this->request->getPost('phone');
        $birthday = $this->request->getPost('birthday');

        $billingStreet = $this->request->getPost('billingStreet');
        $billingZipcode = $this->request->getPost('billingZipcode');
        $billingCity = $this->request->getPost('billingCity');
        $billingCountry = $this->request->getPost('billingCountry');
        $newsletter = $this->request->getPost('newsletterSubscribe');

        //if $customerType = business
        $billingCompany = $this->request->getPost('billingCompany');
        $billingDepartment = $this->request->getPost('billingDepartment');
        $billingVatId = $this->request->getPost('billingVatId');

        if (Shopware()->Modules()->Admin()->sGetUserByMail($email)) {
            $flag = true;
            $result['status'] = $messages['error_exists_user'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $flag = true;
            $result['status'] = $messages['error_email'];
        }
        if (strlen($password) < 8) {
            $flag = true;
            $result['status'] = $messages['error_password'];
        }
//        if(empty($newsletter)) {
//            $newsletter = 0;
//        }

        if (!$flag) {

            $customerInfo = array();

            if (!empty($birthday) && $birthday != 'null') {
                $birthday = date('Y-m-d', strtotime($birthday));
                $customerInfo['birthday'] = $birthday;
            } else {
                $customerInfo['birthday'] = null;
            }

            $customerInfo['firstname'] = $firstname;
            $customerInfo['lastname'] = $lastname;
            $customerInfo['email'] = $email;
            $customerInfo['password'] = $password;
            $customerInfo['salutation'] = $salutation;
            $customerInfo['newsletter'] = $newsletter;
            $customerInfo['billing'] = array(
                'firstname' => $firstname,
                'lastname' => $lastname,
                'salutation' => $salutation,
                'street' => $billingStreet,
                'city' => $billingCity,
                'zipcode' => $billingZipcode,
                'country' => $billingCountry,
                'phone' => $phone
            );

            if ($customerType == 'business') {

                $customerInfo['billing']['company'] = $billingCompany;
                $customerInfo['billing']['department'] = $billingDepartment;
                $customerInfo['billing']['vatId'] = $billingVatId;

            }

            $customer = $client->post('customers', $customerInfo);
            $result['result'] = $customer;
            $result['status'] = $messages['success'];

            if(!empty($result['result']['data']['id']) && $customerType == 'business' && !empty($_FILES['photo'])) {

                $data = file_get_contents($_FILES['photo']['tmp_name']);
                $size = filesize($_FILES['photo']['tmp_name']);
                $fileName = $_FILES['photo']['name'];
                $type = $_FILES['photo']['type'];

                Shopware()->Db()->query('INSERT INTO ant_registration_upload VALUES (NULL,?,?,?,?,?)', [
                    $result['result']['data']['id'],
                    $fileName,
                    $type,
                    $size,
                    $data,
                ]);
            }

        }

        error_log(print_r('---------------------------------- start', true));
        error_log(print_r($result, true));
        error_log(print_r($_FILES, true));
        error_log(print_r($this->request->getPost(), true));
        error_log(print_r('---------------------------------- end', true));

        echo $this->jsonHelper->returnJsonData($result);

    }

    public function getCountriesAction()
    {

        $this->shopRender();

        $data = array(
            'countries' => array()
        );

        /** @var $countriesModel \Shopware\Models\Country\Country */
        $countriesModel = Shopware()->Models()->getRepository('Shopware\Models\Country\Country');

        $criteriaCountries = array(
            'active' => true
        );

        $countries = $countriesModel->findBy($criteriaCountries);

        foreach ($countries as $country) {

            array_push($data['countries'], array(
                'id' => $country->getId(),
                'name' => $country->getName(),
            ));

        }

        echo $this->jsonHelper->returnJsonData($data);

    }

    public function changeUserInfoAction()
    {

        $this->shopRender();
        $userID = $this->request->getPost('userID');

        $messages = array(
            'success' => array(
                'code' => 'success',
                'text' => 'Sie haben Ihre persönlichen Daten erfolgreich aktualisiert.'
            ),
            'error' => array(
                'code' => 'error',
                'text' => 'Leider konnten wir Ihre persönlichen Daten nicht aktualisieren.'
            )
        );

        $data = array();

        /** @var $userModel \Shopware\Models\Customer\Customer */
        $userModel = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->findOneBy(array('id' => $userID));

        if (!empty($userModel)) {

            $name = $this->request->getPost('name');
            $surname = $this->request->getPost('surname');
            $birthday = $this->request->getPost('birthday');
            $gender = $this->request->getPost('gender');

            $flagChangeInfoOK = true;

            if (!empty($name) && strlen($name) > 0) {
                $userModel->setFirstname($name);
            } else {
                $flagChangeInfoOK = false;
            }

            if (!empty($surname) && strlen($surname) > 0) {
                $userModel->setLastname($surname);
            } else {
                $flagChangeInfoOK = false;
            }

            if (!empty($birthday) && strlen($birthday) > 0) {
                $birthday = date('Y-m-d', strtotime($birthday));
                $userModel->setBirthday(new DateTime($birthday));
            }

            if (!empty($gender) && $gender == 'Herr' || !empty($gender) && $gender == 'Frau') {
                $userModel->setSalutation($gender);
            } else {
                $flagChangeInfoOK = false;
            }

            if ($flagChangeInfoOK) {

                try {
                    Shopware()->Models()->flush($userModel);
                    $data['status'] = $messages['success'];
                } catch (\Doctrine\ORM\ORMException $exception) {
                    $exception->getTrace();
                    $data['status'] = $messages['error'];
                }

            } else {

                $data['status'] = $messages['error'];

            }

            echo $this->jsonHelper->returnJsonData($data);

        } else {

            $this->jsonHelper->returnError();

        }

    }

    public function changeUserBillingAddressAction()
    {

        $this->shopRender();
        $userID = $this->request->getPost('userID');
        $userInfo = array(
            'customer_type' => $this->request->getPost('customer_type'),
            'company' => $this->request->getPost('company'),
            'department' => $this->request->getPost('department'),
            'vatId' => $this->request->getPost('vatId'),
            'salutation' => $this->request->getPost('salutation'),
            'firstname' => $this->request->getPost('firstname'),
            'lastname' => $this->request->getPost('lastname'),
            'street' => $this->request->getPost('street'),
            'zipcode' => $this->request->getPost('zipcode'),
            'city' => $this->request->getPost('city'),
            'country' => $this->request->getPost('country'),
            'phone' => $this->request->getPost('phone')
        );
        $result = array(
            'status' => $this->user->changeAddress($userID, $userInfo, 'billing')
        );
        echo $this->jsonHelper->returnJsonData($result);

    }

    public function changeUserShippingAddressAction()
    {

        $this->shopRender();
        $userID = $this->request->getPost('userID');
        $userInfo = array(
            'customer_type' => $this->request->getPost('customer_type'),
            'company' => $this->request->getPost('company'),
            'department' => $this->request->getPost('department'),
            'vatId' => $this->request->getPost('vatId'),
            'salutation' => $this->request->getPost('salutation'),
            'firstname' => $this->request->getPost('firstname'),
            'lastname' => $this->request->getPost('lastname'),
            'street' => $this->request->getPost('street'),
            'zipcode' => $this->request->getPost('zipcode'),
            'city' => $this->request->getPost('city'),
            'country' => $this->request->getPost('country'),
            'phone' => $this->request->getPost('phone')
        );
        $result = array(
            'status' => $this->user->changeAddress($userID, $userInfo, 'shipping')
        );
        echo $this->jsonHelper->returnJsonData($result);

    }

    public function addToFavouriteAction()
    {

        $this->shopRender();

        $userID = $this->request->getPost('userID');
        $productID = $this->request->getPost('productID');

        $status = $this->user->favorites($userID, 'add', $productID);

        $data = array(
            'status' => $status
        );

        echo $this->jsonHelper->returnJsonData($data);

    }

    public function deleteAllFavouriteAction()
    {

        $this->shopRender();

        $userID = $this->request->getPost('userID');

        $status = $this->user->favorites($userID, 'deleteAll', null);

        $data = array(
            'status' => $status
        );

        echo $this->jsonHelper->returnJsonData($data);

    }

    public function deleteFavouriteAction()
    {

        $this->shopRender();

        $userID = $this->request->getPost('userID');
        $productID = $this->request->getPost('productID');

        $status = $this->user->favorites($userID, 'delete', $productID);

        $data = array(
            'status' => $status
        );

        echo $this->jsonHelper->returnJsonData($data);

    }

    public function changeUserPasswordAction()
    {

        $this->shopRender();

        $userID = $this->request->getPost('userID');
        $password = $this->request->getPost('password');

        $messages = array(
            'success' => array(
                'code' => 'success',
                'text' => 'Ihr Passwort wurde erfolgreich aktualisiert.'
            ),
            'error' => array(
                'code' => 'error',
                'text' => 'Wir konnten leider Ihr Passwort nicht ändern. Die Mindestzeichenlänge muss 8 Symbole betragen. Bitte geben Sie ein korrektes Passwort und versuchen Sie es neu.'
            )
        );

        $result = array();

        if (strlen($password) >= 8) {

            $client = new apiConnector();
            $client->put('customers/' . $userID . '', array(
                'password' => $password
            ));
            $result['status'] = $messages['success'];

        } else {

            $result['status'] = $messages['error'];

        }

        echo $this->jsonHelper->returnJsonData($result);

    }


    public function getPaymentOptionsAction()
    {

        $this->shopRender();

        $data = array(
            'paymentOptions' => array()
        );

        /** @var $paymentsModel \Shopware\Models\Payment\Payment */
        $paymentsModel = Shopware()->Models()->getRepository('Shopware\Models\Payment\Payment')->findAll();
        if (!empty($paymentsModel)) {

            foreach ($paymentsModel as $payment) {

                if ($payment->getActive()) {

                    $temp = array(
                        'id' => $payment->getId(),
                        'name' => $payment->getName(),
                        'description' => $payment->getDescription(),
                        'additionalDescription' => $payment->getAdditionalDescription()
                    );

                    array_push($data['paymentOptions'], $temp);

                }

            }

        }

        echo $this->jsonHelper->returnJsonData($data);

    }

    public function changeUserPaymentOptionAction()
    {

        $this->shopRender();

        $userID = $this->request->getPost('userID');
        $paymentID = $this->request->getPost('paymentID');

        $result = $this->user->changePaymentOption($userID, $paymentID);

        echo $this->jsonHelper->returnJsonData($result);

    }

    public function addReviewAction()
    {

        $this->shopRender();

        $productID = $this->request->getPost('productID');

        $product = Shopware()->Modules()->Articles()->sGetArticleIdByOrderNumber($productID);
        $result = Shopware()->Modules()->Articles()->sSaveComment($product);

        $data = array(
            'status' => array(
                'code' => 'success',
                'text' => 'Ihre Bewertung erfolgreich eingefügt'
            )
        );

        echo $this->jsonHelper->returnJsonData($data);

    }

    public function resetPasswordAction()
    {

        $this->shopRender();

        $messages = array(
            'success' => array(
                'code' => 'success',
                'text' => 'Wir haben Ihnend as Passwort erfolgreich per E-Mail gesendet.'
            ),
            'error_email' => array(
                'code' => 'error_email',
                'text' => 'Bitte geben Sie die gültige E-Mail an.'
            ),
            'error' => array(
                'code' => 'error',
                'text' => 'Leider konnten wir Ihnen kein neues Passwort per E-Mail senden. Bitte wiederholen Sie es neu.'
            )
        );

        $email = $this->request->getPost('email');
        $flag = true;
        $data = array();

        /** @var $userModel \Shopware\Models\Customer\Customer */
        $userModel = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->findOneBy(array('email' => $email));

        if (empty($userModel)) {
            $flag = false;
            $data['status'] = $messages['error'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $flag = false;
            $data['status'] = $messages['error_email'];
        }

        if ($flag) {
            $this->user->resetPassword($email);
            $data['status'] = $messages['success'];
        }

        echo $this->jsonHelper->returnJsonData($data);

    }

    public function getReturnDataAction()
    {

        $this->shopRender();
        $orderID = $this->request->getPost('orderID');
        $data = array();
        $editable = true;

        if(!empty($orderID)) {
            $orderDetails = Shopware()->Db()->fetchAll("SELECT * FROM s_order_details WHERE orderID = {$orderID}");
        }

        if(!empty($orderDetails)) {

            $returnOrder = $this->retouren->getRetoure($orderID);
            $returnOrderSelections = Shopware()->Db()->fetchAll("SELECT * FROM s_dreisc_retoure_selection WHERE orderId = {$orderID}");

            $returnOrderProducts = array();
            if(!empty($returnOrder)) {
                foreach ($returnOrderSelections as $returnOrderSelection) {
                    $returnOrderProducts[$returnOrderSelection['orderDetailId']] = $returnOrderSelection;
                }
                $editable = false;
            }

            $data['returnNumber'] = $returnOrder['number'];
            $data['comment'] = $returnOrder['comment'];
            $data['creationDate'] = $returnOrder['creation_date'];
            $data['editable'] = $editable;
            $data['reasons'] = array(
                'Artikel falsch geliefert',
                'Defekt/Verschmutzt',
                'Preis/Leistung',
                'Anders als beschrieben',
                'Anders als abgebildet',
                'Zur Auswahl bestellt',
                'Artikel unvollständig',
                'Zu spät geliefert',
                'Schlechte Qualität',
                'Sonstiges'
            );
            $data['products'] = array();

            foreach ($orderDetails as $orderDetail) {

                if(!empty($returnOrderProducts[$orderDetail['id']])) {
                    $quantity = $returnOrderProducts[$orderDetail['id']]['quantity'];
                    $reason = $returnOrderProducts[$orderDetail['id']]['reason'];
                } else {
                    $quantity = 0;
                    $reason = '';
                }
                $sArticle = $this->article->getArticle($orderDetail['articleordernumber']);
                array_push($data['products'], array(
                    'orderDetailID' => (int)$orderDetail['id'],
                    'productID' => $sArticle['ordernumber'],
                    'title' => $sArticle['articleName'],
                    'price' => $this->article->formatPrice($orderDetail['price']),
                    'quantity' => (int)$orderDetail['quantity'],
                    'returnQuantity' => (int)$quantity,
                    'reason' => $reason
                ));

            }

            echo $this->jsonHelper->returnJsonData($data);

        } else {

            $this->jsonHelper->returnError();

        }

    }

    public function makeReturnAction() {

        $this->shopRender();
        $data = json_decode($this->request->getPost('returnData'), true);
        $retoure = $this->retouren->getRetoure($data['orderID']);

        $messages = array(
            'success' => array(
                'code' => 'success',
                'text' => 'Die Rücksendung wurde erfolgreich erstellt.'
            ),
            'error' => array(
                'code' => 'error',
                'text' => 'Die Rücksendung konnte nicht erstellt werden.'
            ),
            'error_exist_retouren' => array(
                'code' => 'error_exist_retouren',
                'text' => 'Die Rücksendung wurde bereits erstellt. Sie können sie weder bearbeiten noch ändern.'
            )
        );

        if(!empty($data)) {

            if(empty($retoure)) {
                $result = $this->retouren->updateRetoure($data, $messages);
            } else {
                $result = array(
                    'status' => $messages['error_exist_retouren']
                );
            }
            echo $this->jsonHelper->returnJsonData($result);

        } else {
            $this->jsonHelper->returnError();
        }

    }

    public function getPromocodeDataAction()
    {
        $this->shopRender();
        $promocode = $this->request->getPost('promocode');

        if(!empty($promocode)) {
            $result = $this->user->getPromocode($promocode);
            echo $this->jsonHelper->returnJsonData($result);
        } else {
            $this->jsonHelper->returnError();
        }

    }

    public function getDeliverySuppliersAction()
    {

        $this->shopRender();
        $result = $this->user->getDeliverySuppliers();
        if(!empty($result)) {
            echo $this->jsonHelper->returnJsonData($result);
        } else {
            $this->jsonHelper->returnError();
        }

    }

    public function changeUserPushNotificationTokenAction()
    {
        $this->shopRender();
        $token = $this->request->getPost('pushToken');
        if(!empty($token)) {
            $checkToken = Shopware()->Db()->fetchAll("SELECT * FROM s_user_push_token WHERE token='$token'");
            if(empty($checkToken)) {
                Shopware()->Db()->query("INSERT INTO s_user_push_token (token) VALUES('$token')");
            }
        }
    }


    public function createOrderAction()
    {
        $this->shopRender();
        //Generate ordernumber
        $lastOrder = Shopware()->Db()->fetchAll("SELECT * FROM s_order WHERE status!='-1' ORDER BY id DESC LIMIT 1");
        $orderNumber = $lastOrder[0]['ordernumber'] + 1;
        $customerID = $this->request->getPost('customerID');
        $paymentID = $this->request->getPost('paymentID');
        $dispatchID = $this->request->getPost('dispatchID');
        $products = $this->request->getPost('products');
        $orderStatusID = 0;
        $paymentStatusID = 17;
        $subshopID = 1;
        /** @var Customer $customer */
        $articleModel = Shopware()->Models()->getRepository(Detail::class); //Article
        $detailStatusModel = Shopware()->Models()->getRepository(DetailStatus::class); //Detail status
        $customerModel = Shopware()->Models()->getRepository(Customer::class); //Customer
        $customer = $customerModel->findOneBy(['id' => $customerID]);
        $orderStatusModel = Shopware()->Models()->getRepository(Status::class); //OrderStatus
        $orderStatus = $orderStatusModel->findOneBy(['id' => $orderStatusID]);
        $paymentStatusModel = Shopware()->Models()->getRepository(Status::class); //PaymentStatus
        $paymentStatus = $orderStatusModel->findOneBy(['id' => $paymentStatusID]);
        $paymentModel = Shopware()->Models()->getRepository(Payment::class); //Payment
        $payment = $paymentModel->findOneBy(['id' => $paymentID]);
        $dispatchModel = Shopware()->Models()->getRepository(Dispatch::class); //Dispatch
        /** @var Dispatch $dispatch */
        $dispatch = $dispatchModel->findOneBy(['id' => $dispatchID]);
        $shopModel = Shopware()->Models()->getRepository(Shop::class); //Shop
        $shop = $shopModel->findOneBy(['id' => $subshopID]);

        $invoiceAmount = 0;
        $invoiceAmountNet = 0;
        /** @var Detail $article */
        foreach ($products as $product) {
            $article = $articleModel->findOneBy(['number' => $product['id']]);
            $tax = $article->getArticle()->getTax()->getTax();
            $priceDb = $article->getPrices()->first()->getPrice();
            $price = (floatval($priceDb / 100) * $tax) + floatval($priceDb);
            $invoiceAmount += $price * floatval($product['quantity']);
            $invoiceAmountNet += $article->getPrices()->first()->getPrice() * $product['quantity'];
        }
        $dispatchPrice = $dispatch->getCostsMatrix()->first()->getValue();
        $dispatchPriceNet = $dispatchPrice / 1.19;
        $invoiceAmount += $dispatchPrice;
        $invoiceAmountNet += $dispatchPriceNet;

        $order = new Order(); //Create new order
        $order->setNumber($orderNumber);
        $order->setOrderTime(new DateTime('now'));
        $order->setCustomer($customer);
        $order->setOrderStatus($orderStatus);
        $order->setPaymentStatus($paymentStatus);
        $order->setPayment($payment);
        $order->setDispatch($dispatch);
        $order->setShop($shop);
        $order->setInvoiceAmount(round($invoiceAmount, 2));
        $order->setInvoiceAmountNet(round($invoiceAmountNet, 2));
        $order->setInvoiceShipping(round($dispatchPrice, 2));
        $order->setInvoiceShippingNet(round($dispatchPriceNet, 2));
        $order->setInvoiceShippingTaxRate(19);
        $order->setTransactionId('');
        $order->setComment('');
        $order->setCustomerComment('');
        $order->setInternalComment('');
        $order->setNet(0);
        $order->setTaxFree(0);
        $order->setTemporaryId('');
        $order->setReferer('');
        $order->setTrackingCode('');
        $order->setLanguageIso(1);
        $order->setCurrency('EUR');
        $order->setCurrencyFactor(1);
        try {
            Shopware()->Models()->persist($order); //Save new order
        } catch (\Doctrine\ORM\ORMException $e) {
            $e->getTraceAsString();
        }

        $details = [];
        foreach ($products as $product) {
            $article = $articleModel->findOneBy(['number' => $product['id']]);
            $orderDetails = new OrderDetail();
            $orderDetails->setNumber($order->getNumber());
            $orderDetails->setOrder($order);
            $orderDetails->setArticleId($article->getArticle()->getId());
            $orderDetails->setTaxRate($article->getArticle()->getTax()->getTax());
            $orderDetails->setStatus(Shopware()->Models()->getRepository(DetailStatus::class)->findOneBy(['id' => 0]));
            $orderDetails->setArticleNumber($article->getNumber());
            $orderDetails->setPrice($article->getPrices()->first()->getPrice() * 1.19);
            $orderDetails->setQuantity($product['quantity']);
            $orderDetails->setArticleName($article->getArticle()->getName());
            $orderDetails->setTax(Shopware()->Models()->getRepository(\Shopware\Models\Tax\Tax::class)->findOneBy(['id' => 1]));
            $orderDetails->setUnit($article->getUnit()->getName());
            $orderDetails->setPackUnit($article->getPackUnit());
            $orderDetails->setArticleDetail($article);
            array_push($details, $orderDetails);
        }
        $order->setDetails($details); //Save order details

        $billingAddressObject = $customer->getDefaultBillingAddress();
        $billingAddress = new Billing(); //Billing address
        $billingAddress->setOrder($order);
        $billingAddress->setCompany((!empty($billingAddressObject->getCompany()) ? $billingAddressObject->getCompany() : ''));
        $billingAddress->setDepartment((!empty($billingAddressObject->getDepartment()) ? $billingAddressObject->getDepartment() : ''));
        $billingAddress->setSalutation($customer->getSalutation());
        $billingAddress->setCustomer($customer);
        $billingAddress->setNumber($customer->getNumber());
        $billingAddress->setFirstName($customer->getFirstname());
        $billingAddress->setLastName($customer->getLastname());
        $billingAddress->setStreet((!empty($billingAddressObject->getStreet()) ? $billingAddressObject->getStreet() : ''));
        $billingAddress->setZipCode((!empty($billingAddressObject->getZipcode()) ? $billingAddressObject->getZipcode() : ''));
        $billingAddress->setCity((!empty($billingAddressObject->getCity()) ? $billingAddressObject->getCity() : ''));
        $billingAddress->setPhone((!empty($billingAddressObject->getPhone()) ? $billingAddressObject->getPhone() : ''));
        $billingAddress->setCountry($customer->getDefaultBillingAddress()->getCountry());
        $order->setBilling($billingAddress);

        $shippingAddressObject = $customer->getDefaultShippingAddress();
        $shippingAddress = new Shipping();//Shipping address
        $shippingAddress->setOrder($order);
        $shippingAddress->setCompany((!empty($shippingAddressObject->getCompany()) ? $shippingAddressObject->getCompany() : ''));
        $shippingAddress->setDepartment((!empty($shippingAddressObject->getDepartment()) ? $shippingAddressObject->getDepartment() : ''));
        $shippingAddress->setSalutation($customer->getSalutation());
        $shippingAddress->setCustomer($customer);
        $shippingAddress->setFirstName($customer->getFirstname());
        $shippingAddress->setLastName($customer->getLastname());
        $shippingAddress->setStreet((!empty($shippingAddressObject->getStreet()) ? $shippingAddressObject->getStreet() : ''));
        $shippingAddress->setZipCode((!empty($shippingAddressObject->getZipcode()) ? $shippingAddressObject->getZipcode() : ''));
        $shippingAddress->setCity((!empty($shippingAddressObject->getCity()) ? $shippingAddressObject->getCity() : ''));
        $shippingAddress->setPhone((!empty($shippingAddressObject->getPhone()) ? $shippingAddressObject->getPhone() : ''));
        $shippingAddress->setCountry($customer->getDefaultShippingAddress()->getCountry());
        $order->setShipping($shippingAddress);
        Shopware()->Models()->flush();

        //After add order & details & billing & shipping
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
        /** @var Order $orderResult */
        $orderResult = Shopware()->Models()->getRepository(Order::class)->findOneBy(['number' => $orderNumber]);
        if($orderResult) {
            $messages['success']['data']['orderNumber'] = $orderResult->getNumber();
            $data = $messages['success'];
        } else {
            $data = $messages['error'];
        }
        echo $this->jsonHelper->returnJsonData($data);

    }


}