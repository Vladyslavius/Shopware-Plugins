<?php
namespace itDelightPriceUpdater\Subscribers;

use Enlight\Event\SubscriberInterface;

class BackendArticleSubscriber implements SubscriberInterface
{
    private $path;

    public function __construct($pluginDirectory)
    {
        $this->path = $pluginDirectory;
    }

    public static function getSubscribedEvents()
    {
        return [
            "Shopware_Controllers_Backend_Article::loadStoresAction::after"                     => "onLoadStoresActionAfter",
            "Enlight_Controller_Action_PostDispatch_Backend_Article"                            => "extendBackendArticle",
            "Enlight_Controller_Action_Backend_AttributeData_saveProductMetallData"             => "onAttributeDataSaveProductMetallData",
        ];
    }

    public function onAttributeDataSaveProductMetallData(\Enlight_Event_EventArgs $arguments)
    {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();

        $subject = $arguments->getSubject();
        $request = $subject->Request();
        $view = $subject->View();

        $params = $request->getParams();

        $mainDetailId = $params["mainDetailId"];
        $setMetallType = $params["setMetallType"];
        $setMetallUse = $params["setMetallUse"];
        $setMetallAppendix = $params["setMetallAppendix"];
        if($setMetallUse == "true")
        {
            $setMetallUse = 1;
        } else {
            $setMetallUse = 0;
        }
        $setMetallWeight = (float)$params["setMetallWeight"];

        $sql = "UPDATE `s_articles_attributes` SET `delight_priceupdater_bool` = {$setMetallUse}, `delight_priceupdater_type` = '{$setMetallType}', `delight_priceupdater_size` = '{$setMetallWeight}', `delight_priceupdater_appendix` = '{$setMetallAppendix}' WHERE `articledetailsID` = {$mainDetailId}";
        Shopware()->Db()->query($sql);

        $subject->Response()->setHttpResponseCode(200);
        $subject->Response()->setBody(json_encode([
            "success"   => true,
            "data"      => $params["action"],
        ]));
    }

    public function extendBackendArticle(\Enlight_Event_EventArgs $arguments)
    {
        $subject = $arguments->getSubject();
        $request = $subject->Request();

        $subject->View()->addTemplateDir($this->path . "/Resources/views/");
        switch($request->getActionName())
        {
            case "load":
                $subject->View()->extendsTemplate("backend/article/view/detail/price_updater_fieldset.js");
                $subject->View()->extendsTemplate("backend/article/model/price_updater_detail.js");
                $subject->View()->extendsTemplate("backend/article/controller/detail_updater.js");
            default:
                break;
        }
    }

    public function onLoadStoresActionAfter(\Enlight_Hook_HookArgs $arguments)
    {
        $subject = $arguments->getSubject();
        $view = $subject->View();
        $request = $subject->Request();
        $articleId = $request->getParam("articleId");
        $data = $view->getAssign("data");
        $articles = $data["article"];

        if(count($articles))
        {
            foreach($articles as &$article)
            {
                if($articleId)
                {
                    $sql = "
                        SELECT aa.`delight_priceupdater_bool`, aa.`delight_priceupdater_type`, aa.`delight_priceupdater_size`, aa.`delight_priceupdater_appendix` FROM `s_articles_attributes` AS aa
                        WHERE aa.`articledetailsID` = {$article["mainDetailId"]}
                    ";
                    $productData = Shopware()->Db()->fetchRow($sql);
                    $article["setMetallUse"] = $productData["delight_priceupdater_bool"];
                    $article["setMetallType"] = $productData["delight_priceupdater_type"];
                    $article["setMetallWeight"] = $productData["delight_priceupdater_size"];
                    $article["setMetallAppendix"] = $productData["delight_priceupdater_appendix"];
                } else {
                    $article["setMetallUse"] = 0;
                    $article["setMetallType"] = "";
                    $article["setMetallWeight"] = "";
                    $article["setMetallAppendix"] = "";
                }
            }
        }

        $data["article"] = $articles;
        $view->assign("data", $data);
    }
}
