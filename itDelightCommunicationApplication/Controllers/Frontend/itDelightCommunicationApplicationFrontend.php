<?php

use Shopware\Components\CSRFWhitelistAware;
use itDelightCommunicationApplication\Helpers\JsonData;
use itDelightCommunicationApplication\Helpers\Debugger;
use itDelightCommunicationApplication\Helpers\ArticleData;
use itDelightCommunicationApplication\Helpers\CurlData;


class Shopware_Controllers_Frontend_itDelightCommunicationApplicationFrontend extends Enlight_Controller_Action implements CSRFWhitelistAware
{

    private $jsonHelper;
    private $debugger;
    private $article;
    private $curl;

    public function __construct(Enlight_Controller_Request_Request $request, Enlight_Controller_Response_Response $response)
    {
        parent::__construct($request, $response);

        $this->jsonHelper = new JsonData();
        $this->debugger = new Debugger();
        $this->article = new ArticleData();
        $this->curl = new CurlData();

    }

    public function getWhitelistedCSRFActions()
    {

        return [
            'getBannerImage',
            'getPaymentAndDeliveryConditions',
            'getReturnConditions',
            'getPrivacyPolicy',
            'getContacts',
            'getCareer',
            'getPopularCategories',
            'askQuestion',
            'addFeedback',
            'partnerRequest',
            'subscribeToMail',
            'subscribeToProduct'
        ];

    }

    private function shopRender()
    {
        $this->container->get('front')->Plugins()->ViewRenderer()->setNoRender();
    }


    public function getBannerImageAction()
    {

        $this->shopRender();

        $config = $this->container->get("shopware.plugin.config_reader")->getByPluginName(\itDelightCommunicationApplication\itDelightCommunicationApplication::$pluginName);

        $data = array(
            'indexTopBanner' => null
        );

        if (!empty($config['indexTopBanner']) && isset($config['indexTopBanner'])) {
            $data['indexTopBanner'] = $config['indexTopBanner'];
        }

        echo $this->jsonHelper->returnJsonData($data);

    }

    private function getCmsStatic($id)
    {

        $data = array();
        $page = Shopware()->Modules()->Cms()->sGetStaticPage($id);

        if (!empty($page) && isset($page)) {

            $data['cmsStatic'] = array(
                'title' => $page['description'],
                'content' => $page['html']
            );

        }

        return $data;

    }

    public function getPaymentAndDeliveryConditionsAction()
    {

        $this->shopRender();
        $config = $this->container->get("shopware.plugin.config_reader")->getByPluginName(\itDelightCommunicationApplication\itDelightCommunicationApplication::$pluginName);
        $id = $config['cmsStaticPaymentAndDeliveryConditions'];
        $data = $this->getCmsStatic($id);
        echo $this->jsonHelper->returnJsonData($data);

    }

    public function getReturnConditionsAction()
    {

        $this->shopRender();
        $config = $this->container->get("shopware.plugin.config_reader")->getByPluginName(\itDelightCommunicationApplication\itDelightCommunicationApplication::$pluginName);
        $id = $config['cmsStaticReturnConditions'];
        $data = $this->getCmsStatic($id);
        echo $this->jsonHelper->returnJsonData($data);

    }

    public function getPrivacyPolicyAction()
    {

        $this->shopRender();
        $config = $this->container->get("shopware.plugin.config_reader")->getByPluginName(\itDelightCommunicationApplication\itDelightCommunicationApplication::$pluginName);
        $id = $config['cmsStaticPrivacyPolicy'];
        $data = $this->getCmsStatic($id);
        echo $this->jsonHelper->returnJsonData($data);

    }

    public function getContactsAction()
    {

        $this->shopRender();
        $config = $this->container->get("shopware.plugin.config_reader")->getByPluginName(\itDelightCommunicationApplication\itDelightCommunicationApplication::$pluginName);
        $id = $config['cmsStaticContacts'];
        $data = $this->getCmsStatic($id);
        echo $this->jsonHelper->returnJsonData($data);

    }

    public function getCareerAction()
    {

        $this->shopRender();
        $config = $this->container->get("shopware.plugin.config_reader")->getByPluginName(\itDelightCommunicationApplication\itDelightCommunicationApplication::$pluginName);
        $id = $config['cmsStaticCareer'];
        $data = $this->getCmsStatic($id);
        echo $this->jsonHelper->returnJsonData($data);

    }

    public function getPopularCategoriesAction()
    {

        $this->shopRender();

        $data = array(
            'categories' => array()
        );

        $categories = Shopware()->Db()->fetchAll("SELECT * FROM s_categories_attributes WHERE itdelight_wca_category_popular = 1");

        foreach ($categories as $category) {

            $cat = Shopware()->Db()->fetchRow("SELECT * FROM s_categories WHERE id = {$category['categoryID']}");

            if (!empty($category['itdelight_wca_category_popular_image']) && isset($category['itdelight_wca_category_popular_image'])) {

                $media = Shopware()->Db()->fetchRow("SELECT * FROM s_media WHERE id = {$category['itdelight_wca_category_popular_image']}");
                $mediaPath = 'https://teleropa.de/' . $media['path'];

            } else {

                $mediaPath = null;

            }

            $temp = array(
                'categoryImageURL' => $mediaPath,
                'categoryName' => $cat['description'],
                'categoryID' => $cat['id']
            );

            array_push($data['categories'], $temp);

        }

        echo $this->jsonHelper->returnJsonData($data);

    }

    public function askQuestionAction()
    {

        $this->shopRender();

        $messages = array(
            'success' => array(
                'code' => 'success',
                'text' => 'Ihre Nachricht wurde erfolgreich gesendet.'
            ),
            'error' => array(
                'code' => 'error',
                'text' => 'Leider konnte Ihre Nachricht nicht gesendet werden.'
            ),
            'error_email' => array(
                'code' => 'error_email',
                'text' => 'Die E-Mail-Adresse ist falsch'
            ),
            'error_required' => array(
                'code' => 'error_required',
                'text' => 'Die mit * markierten Felder sind Pflicht. Bitte füllen Sie das Feld aus und probieren Sie neu.'
            )
        );

        $productID = $this->request->getPost('productID');
        $name = $this->request->getPost('name'); //*
        $surname = $this->request->getPost('surname'); //*
        $gender = $this->request->getPost('gender');
        $phone = $this->request->getPost('phone');
        $email = $this->request->getPost('email');
        $questionText = $this->request->getPost('questionText'); //*

        $data = array();
        $flag = true;

        $adminEmail = 'baranik.vlad@gmail.com'; //service@teleropa-ticket.de
        $subtitle = 'Ihre Kontaktanfrage im teleropa Onlineshop ' . date('d/m/Y H:i');

        //SETTING EMAIL SENDER
        $headers = "From: Teleropa <info@teleropa.de>\r\n";
        $headers .= "Content-type: text/html; charset=\"utf8\"\r\n";
        $headers .= "MIME_Version: 1.0\r\n";
        $headers .= "Date: " . date('D, d M Y H:i:s O') . "\r\n";

        //Check email address
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $flag = false;
            $data['status'] = $messages['error_email'];
        }
        if(strlen($name) == 0) { $flag = false; $data['status'] = $messages['error_required']; }
        if(strlen($surname) == 0) { $flag = false; $data['status'] = $messages['error_required']; }
        if(strlen($questionText) == 0) { $flag = false; $data['status'] = $messages['error_required']; }

        if ($flag) {

            $message = 'Kontaktformular teleropa Onlineshop<br>
                Anrede: ' . $gender . '<br> 
                Vorname: ' . $name . '<br>
                Nachname: ' . $surname . '<br>
                E-Mail: ' . $email . '<br>
                Telefon: ' . $phone . '<br>
                Betreff: <br>
                ' . $questionText . '
            ';

            $result = mail($adminEmail, $subtitle, $message, $headers);

            if ($result) {
                $data['status'] = $messages['success'];
            } else {
                $data['status'] = $messages['error'];
            }

        }

        echo $this->jsonHelper->returnJsonData($data);

    }

    public function addFeedbackAction()
    {

        $this->shopRender();

        $messages = array(
            'success' => array(
                'code' => 'success',
                'text' => 'Ihre Nachricht wurde erfolgreich gesendet.'
            ),
            'error' => array(
                'code' => 'error',
                'text' => 'Leider konnte Ihre Nachricht nicht gesendet werden.'
            ),
            'error_email' => array(
                'code' => 'error_email',
                'text' => 'Die E-Mail-Adresse ist falsch'
            ),
            'error_required' => array(
                'code' => 'error_required',
                'text' => 'Die mit * markierten Felder sind Pflicht. Bitte füllen Sie das Feld aus und probieren Sie neu.'
            )
        );

        $gender = $this->request->getPost('gender');
        $name = $this->request->getPost('name'); //*
        $surname = $this->request->getPost('surname'); //*
        $email = $this->request->getPost('email');
        $mailSubject = $this->request->getPost('mailSubject');
        $mailText = $this->request->getPost('mailText'); //*

        $data = array();
        $flag = true;

        $adminEmail = 'baranik.vlad@gmail.com'; //service@teleropa-ticket.de
        $subtitle = 'Ihre Kontaktanfrage im teleropa Onlineshop ' . date('d/m/Y H:i');

        //SETTING EMAIL SENDER
        $headers = "From: Teleropa <info@teleropa.de>\r\n";
        $headers .= "Content-type: text/html; charset=\"utf8\"\r\n";
        $headers .= "MIME_Version: 1.0\r\n";
        $headers .= "Date: " . date('D, d M Y H:i:s O') . "\r\n";

        //Check email address
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $flag = false;
            $data['status'] = $messages['error_email'];
        }
        if(strlen($name) == 0) { $flag = false; $data['status'] = $messages['error_required']; }
        if(strlen($surname) == 0) { $flag = false; $data['status'] = $messages['error_required']; }
        if(strlen($mailText) == 0) { $flag = false; $data['status'] = $messages['error_required']; }

        if ($flag) {

            $message = 'Kontaktformular teleropa Onlineshop<br>
                Anrede: ' . $gender . '<br>
                Vorname: ' . $name . '<br>
                Nachname: ' . $surname . '<br>
                E-Mail: ' . $email . '<br>
                Subject: '.$mailSubject.'<br>
                Text: <br>'.$mailText.'<br>
            ';

            $result = mail($adminEmail, $subtitle, $message, $headers);

            if ($result) {
                $data['status'] = $messages['success'];
            } else {
                $data['status'] = $messages['error'];
            }

        }

        echo $this->jsonHelper->returnJsonData($data);

    }

    public function partnerRequestAction()
    {

        $this->shopRender();

        $messages = array(
            'success' => array(
                'code' => 'success',
                'text' => 'Ihre Nachricht wurde erfolgreich gesendet.'
            ),
            'error' => array(
                'code' => 'error',
                'text' => 'Leider konnte Ihre Nachricht nicht gesendet werden.'
            ),
            'error_email' => array(
                'code' => 'error_email',
                'text' => 'Die E-Mail-Adresse ist falsch'
            ),
            'error_required' => array(
                'code' => 'error_required',
                'text' => 'Die mit * markierten Felder sind Pflicht. Bitte füllen Sie das Feld aus und probieren Sie neu.'
            )
        );


        $companyName = $this->request->getPost('companyName'); //*
        $name = $this->request->getPost('name'); //*
        $address = $this->request->getPost('address');  //*
        $city = $this->request->getPost('city');  //*
        $post = $this->request->getPost('post');  //*
        $phone = $this->request->getPost('phone');  //*
        $fax = $this->request->getPost('fax');
        $email = $this->request->getPost('email');  //*
        $site = $this->request->getPost('site');  //*
        $comment = $this->request->getPost('comment');
        $companyDescription = $this->request->getPost('companyDescription');  //*

        $data = array();
        $flag = true;

        $adminEmail = 'baranik.vlad@gmail.com'; //service@teleropa-ticket.de
        $subtitle = 'Ihre Partnerformular im teleropa Onlineshop ' . date('d/m/Y H:i');

        //SETTING EMAIL SENDER
        $headers = "From: Teleropa <info@teleropa.de>\r\n";
        $headers .= "Content-type: text/html; charset=\"utf8\"\r\n";
        $headers .= "MIME_Version: 1.0\r\n";
        $headers .= "Date: " . date('D, d M Y H:i:s O') . "\r\n";

        //Check email address
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $flag = false; $data['status'] = $messages['error_email']; }
        if(strlen($companyName) == 0) { $flag = false; $data['status'] = $messages['error_required']; }
        if(strlen($name) == 0) { $flag = false; $data['status'] = $messages['error_required']; }
        if(strlen($address) == 0) { $flag = false; $data['status'] = $messages['error_required']; }
        if(strlen($post) == 0) { $flag = false; $data['status'] = $messages['error_required']; }
        if(strlen($phone) == 0) { $flag = false; $data['status'] = $messages['error_required']; }
        if(strlen($site) == 0) { $flag = false; $data['status'] = $messages['error_required']; }
        if(strlen($companyDescription) == 0) { $flag = false; $data['status'] = $messages['error_required']; }
        if(strlen($city) == 0) { $flag = false; $data['status'] = $messages['error_required']; }


        if ($flag) {

            $message = 'Partnerformular teleropa Onlineshop<br>
                Firma: ' . $companyName . '<br>
                Vorname: ' . $name . '<br>
                Straße & Hausnummer*: ' . $address . '<br>
                PLZ: ' . $post . '<br>
                Ort: ' . $city . '<br>
                Telefon: '.$phone.'<br>
                Fax: '.$fax.'<br>
                E-Mail: '.$email.'<br>
                Webseite: '.$site.'<br>
                Kommentar: '.$comment.'<br>
                Firmenprofil: <br>'.$companyDescription.'<br>
            ';

            $result = mail($adminEmail, $subtitle, $message, $headers);

            if ($result) {
                $data['status'] = $messages['success'];
            } else {
                $data['status'] = $messages['error'];
            }

        }

        echo $this->jsonHelper->returnJsonData($data);

    }

    public function subscribeToMailAction() {

        $this->shopRender();

        $messages = array(
            'success' => array(
                'code' => 'success',
                'text' => 'Sie haben sich für unseren Newsletter efolgreich angemeldet.'
            ),
            'error' => array(
                'code' => 'error',
                'text' => 'Leider ist die Newsletter-Anmeldung fehlgeschlagen.'
            ),
            'error_email' => array(
                'code' => 'error_email',
                'text' => 'Bitte geben Sie die gültige E-Mail an.'
            )
        );

        $email = $this->request->getPost('email');
        $data = array();
        $flag = true;

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $flag = false;
            $data['status'] = $messages['error_email'];
        }

        if($flag) {

            $fieldData = array (
                'subscribeToNewsletter' => 1,
                'newsletter' => $email,
                'privacy-checkbox' => 1,
                '__csrf_token' => 'mGmbgcoulS9FmRprEhDXnJKZmf9HTs',
            );

            $resultInsert = Shopware()->Db()->insert('s_core_optin', array(
                'type' => 'swNewsletter',
                'datum' => date('Y-m-d H:i:s'),
                'hash' => md5(date('YmdHis').''.$email),
                'data' => serialize($fieldData)
            ));

            if($resultInsert) {
                $data['status'] = $messages['success'];
            } else {
                $data['status'] = $messages['error'];
            }

        }

        echo $this->jsonHelper->returnJsonData($data);

    }


    public function subscribeToProductAction() {

        $this->shopRender();

        $messages = array(
            'success' => array(
                'code' => 'success',
                'text' => 'Sie haben sich für unseren Newsletter efolgreich angemeldet.'
            ),
            'error' => array(
                'code' => 'error',
                'text' => 'Leider ist die Newsletter-Anmeldung fehlgeschlagen.'
            ),
            'error_email' => array(
                'code' => 'error_email',
                'text' => 'Bitte geben Sie die gültige E-Mail an.'
            )
        );

        $email = $this->request->getPost('email');
        $stock = $this->request->getPost('stock');
        $price = $this->request->getPost('price');
        $pseudo = $this->request->getPost('pseudo');
        $productID = $this->request->getPost('productID');

        $data = array();
        $flag = true;

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $flag = false;
            $data['status'] = $messages['error_email'];
        }

        if($flag) {

            $typesStock = 0;
            $typesPrice = 0;
            $typesPseudo = 0;

            if($stock == true) { $typesStock = 1; }
            if($price == true) { $typesPrice = 1; }
            if($pseudo == true) { $typesPseudo = 1; }

            $types = array(
                array('type' => 'stock', 'checked' => $typesStock),
                array('type' => 'price', 'checked' => $typesPrice),
                array('type' => 'pseudo', 'checked' => $typesPseudo)
            );

            $params = array(
                'email' => $email,
                'number' => $productID,
                'types' => json_encode($types)
            );

            $result = $this->curl->postDvsn($params);

            if(!empty($result)) {
                $data['status'] = $messages['success'];
            } else {
                $data['status'] = $messages['error'];
            }

        }

        echo $this->jsonHelper->returnJsonData($data);

    }

}