<?php

namespace itDelightCommunicationApplication;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;


class itDelightCommunicationApplication extends Plugin
{
    public static $pluginName = "itDelightCommunicationApplication";

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatch_Frontend_Detail' => 'testDemo',
            'Shopware_Controllers_Backend_Blog::saveBlogArticleAction::after' => 'saveBlogArticle'
        ];
    }

    public function saveBlogArticle(\Enlight_Hook_HookArgs $args)
    {
        $subject = $args->getSubject();
        $params = $subject->Request()->getParams();
        $data = [
            'title' => $params['title'],
            'shortDescription' => $params['shortDescription'],
            'icon' => '',
            'click_action' => '',
        ];
        $url = 'https://fcm.googleapis.com/fcm/send';
        $YOUR_API_KEY = 'AIzaSyDOcRI-bqi4KxuxLR6HkjFhAc3J2L4-i30'; // Server key
        $tokens = [];
        $tokensDB = Shopware()->Db()->fetchAll("SELECT * FROM s_user_push_token");
        if(!empty($tokensDB)) {
            foreach ($tokensDB as $item) {
                array_push($tokens, $item['token']);
            }
        }
        $YOUR_TOKEN_ID = $tokens;
        $request_body = [
            'registration_ids' => $YOUR_TOKEN_ID,
            'notification' => [
                'title' => $data["title"],
                'body' => $data["shortDescription"],
                'icon' => $data['icon'],
                'click_action' => $data['click_action']
            ],
        ];
        $fields = json_encode($request_body);
        $request_headers = [
            'Content-Type: application/json',
            'Authorization: key=' . $YOUR_API_KEY,
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        curl_close($ch);
    }

    public function install(InstallContext $context)
    {

        $service = $this->container->get('shopware_attribute.crud_service');
        $service->update('s_categories_attributes', 'itdelight_wca_category_popular', 'boolean', [
            'label' => 'itDelight WCA popular category',
            'supportText' => 'This category displays on homepage in application',
            'displayInBackend' => true,
            'arrayStore' => [
                ['key' => '1', 'value' => 'first value'],
                ['key' => '2', 'value' => 'second value']
            ],
        ]);

        $service->update('s_categories_attributes', 'itDelight_wca_category_popular_image', 'single_selection', [
            'label' => 'itdelight WCA popular category image',
            'displayInBackend' => true,
            'entity' => 'Shopware\Models\Media\Media',
        ]);

        //Create new table for push tokens
        $sql = "CREATE TABLE s_user_push_token(
            id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
            token TEXT
        )";
        Shopware()->Db()->query($sql);

    }

    public function uninstall(UninstallContext $context)
    {

        $service = $this->container->get('shopware_attribute.crud_service');
        $service->delete('s_categories_attributes', 'itdelight_wca_category_popular');
        $service->delete('s_categories_attributes', 'itdelight_wca_category_popular_image');

        $sql = "DROP TABLE s_user_push_token";
        Shopware()->Db()->query($sql);

    }


    public function testDemo(\Enlight_Controller_ActionEventArgs $args)
    {

        $controller = $args->getSubject();
        $view = $controller->View();

    }

}