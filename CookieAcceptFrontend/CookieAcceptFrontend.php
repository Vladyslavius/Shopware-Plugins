<?php
namespace CookieAcceptFrontend;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware\Components\Model\ModelManager;

use CookieAcceptFrontend\Controller\CookieAcceptFrontendController;

class CookieAcceptFrontend extends Plugin
{
	const pluginName = "CookieAcceptFrontend";

	public function getLabel()
	{
		return "Cookie accept";
	}

	public static function getSubscribedEvents()
	{
		return
		[
			"Enlight_Controller_Action_PostDispatch_Frontend_Account"					=> "TemplateCorrect",
			"Enlight_Controller_Action_PostDispatch_Frontend_Blog"						=> "TemplateCorrect",
			"Enlight_Controller_Action_PostDispatch_Frontend_Campaign"					=> "TemplateCorrect",
			"Enlight_Controller_Action_PostDispatch_Frontend_Checkout"					=> "TemplateCorrect",
			"Enlight_Controller_Action_PostDispatch_Frontend_Compare"					=> "TemplateCorrect",
			"Enlight_Controller_Action_PostDispatch_Frontend_Custom"					=> "TemplateCorrect",
			"Enlight_Controller_Action_PostDispatch_Frontend_Detail"					=> "TemplateCorrect",
			"Enlight_Controller_Action_PostDispatch_Frontend_Error"						=> "TemplateCorrect",
			"Enlight_Controller_Action_PostDispatch_Frontend_Forms"						=> "TemplateCorrect",
			"Enlight_Controller_Action_PostDispatch_Frontend_Home"						=> "TemplateCorrect",
			"Enlight_Controller_Action_PostDispatch_Frontend_Index"						=> "TemplateCorrect",
			"Enlight_Controller_Action_PostDispatch_Frontend_Listing"					=> "TemplateCorrect",
			"Enlight_Controller_Action_PostDispatch_Frontend_Newsletter"				=> "TemplateCorrect",
			"Enlight_Controller_Action_PostDispatch_Frontend_Note"						=> "TemplateCorrect",
			"Enlight_Controller_Action_PostDispatch_Frontend_Paypal"					=> "TemplateCorrect",
			"Enlight_Controller_Action_PostDispatch_Frontend_Plugins"					=> "TemplateCorrect",
			"Enlight_Controller_Action_PostDispatch_Frontend_Register"					=> "TemplateCorrect",
			"Enlight_Controller_Action_PostDispatch_Frontend_Search"					=> "TemplateCorrect",
			"Enlight_Controller_Action_PostDispatch_Frontend_Sitemap"					=> "TemplateCorrect",
			"Enlight_Controller_Action_PostDispatch_Frontend_Tellafriend"				=> "TemplateCorrect",

			"Enlight_Controller_Dispatcher_ControllerPath_Frontend_CookieFrontend"		=> "onGetFrontendController",
        ];
    }

	public function install(InstallContext $context)
	{
		parent::install($context);
	}

	public function update(UpdateContext $context)
	{
		parent::update($context);
    }

	public function uninstall(UninstallContext $context)
	{
		parent::uninstall($context);
	}

	public function deactivate(DeactivateContext $context)
	{
		parent::deactivate($context);
	}

	public function activate(ActivateContext $context)
	{
		parent::activate($context);
	}

	private function getClasses(ModelManager $modelManager)
	{
		return [
			$modelManager->getClassMetadata(Translate::class)
		];
	}

	public function TemplateCorrect(\Enlight_Controller_ActionEventArgs $arguments)
	{
		$controller = $arguments->getSubject();
		$view = $controller->View();
        $request = Shopware()->Container()->get("front")->Request();

		$host = Shopware()->Shop()->getHost();
		$search = array("https://", "http://", ".", "/", "www");
		$host = str_replace($search, "", $host);
		try {
			$code = "check_info_box_" . $host;
			if(!isset($_COOKIE[$code]) || $_COOKIE[$code] != 1) {
				$view->assign("hide_cookie_code_box", 0);
				$config = $this->container->get("shopware.plugin.config_reader")->getByPluginName("CookieAcceptFrontend");
				$view->assign("infoBox", $config["infoBox"]);

				$view->addTemplateDir($this->getPath() . "/Views/");
				$view->extendsTemplate("frontend/index/cookie_frontend_index.tpl");
			} else {
				$view->assign("hide_cookie_code_box", 1);
			}
		} catch (Exception $e) {
		}
	}

	public function onGetFrontendController()
	{
		return __DIR__ . "/Controllers/Frontend/CookieAcceptFrontendController.php";
	}
}
?>
