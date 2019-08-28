<?php
namespace CheckoutQuestions;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;

use Doctrine\Common\Collections\ArrayCollection;

class CheckoutQuestions extends Plugin
{
	public $pluginName		= "CheckoutQuestions";

	/*
	 * Subscribe plugin functions to system events
	 */
	public static function getSubscribedEvents()
	{
		return
		[
			"Enlight_Controller_Action_PostDispatch_Frontend"									=> "getExtendedCheckoutQuestionStepsView",
			"Enlight_Controller_Action_Frontend_Checkout_Questions"								=> "getExtendedCheckoutQuestionView",
			"Enlight_Controller_Action_Frontend_Checkout_Interview"								=> "getExtendedCheckoutInterviewStep",
			"Shopware_Controllers_Frontend_Checkout::confirmAction::before"						=> "onCheckoutConfirmActionBefore",
			"Shopware_Modules_Order_SaveOrder_FilterAttributes"									=> "onSaveOrderFilterAttributes",
			"Enlight_Controller_Action_PostDispatchSecure_Backend_Order"						=> "onBackendOrderPostDispatch",
			"Enlight_Controller_Dispatcher_ControllerPath_Backend_CheckoutQuestions"			=> "onGetBackendController",
			"Theme_Compiler_Collect_Plugin_Less"												=> "onCollectLessFiles",
		];
	}

	/*
	 * Install plugin
	 */
	public function install(InstallContext $context)
	{
		$service = Shopware()->Container()->get("shopware_attribute.crud_service");
		$service->update("s_order_attributes", "cq_questions_form_data", "string");

		parent::install($context);
	}

	/*
	 * Uninstall plugin
	 */
	public function uninstall(UninstallContext $context)
	{
		$service = Shopware()->Container()->get("shopware_attribute.crud_service");
		$service->delete("s_order_attributes", "cq_questions_form_data", "string");

		parent::uninstall($context);
	}

	/*
	 * Append plugin less data to theme style
	 */
	public function onCollectLessFiles(\Enlight_Event_EventArgs $arguments)
	{
		$lessDir = $this->getPath() . "/Resources/assets/less/";

		$less = new \Shopware\Components\Theme\LessDefinition(
			[],
			[
				$lessDir . "cqStyle.less",
			]
		);

		return new ArrayCollection([$less]);
	}

	/*
	 * Load backend controller class
	 */
	public function onGetBackendController()
	{
		return $this->getPath() . "/Controllers/Backend/CheckoutQuestions.php";
	}

	/*
	 * Load backend controller template
	 */
	public function onBackendOrderPostDispatch(\Enlight_Event_EventArgs $arguments)
	{
		$controller = $arguments->getSubject();
		$view = $controller->View();
		$request = $controller->Request();
		$view->addTemplateDir($this->getPath() . "/Resources/Views/");
		if($request->getActionName() == "load")
		{
			$view->extendsTemplate("backend/checkout_questions/order/view/detail/window.js");
		}   
	}

	/*
	 * Append form data on order creating
	 */
	public function onSaveOrderFilterAttributes(\Enlight_Event_EventArgs $arguments)
	{
		$attributes = $arguments->getReturn();
		$session = Shopware()->Session();

		$questionsData["Gender"] = $session->get("checkoutQuestionGender");
		$session->offsetUnset("checkoutQuestionGender");

		$questionsData["DateOfBirth"] = $session->get("checkoutQuestionDateOfBirth");
		$session->offsetUnset("checkoutQuestionDateOfBirth");

		$questionsData["Size"] = $session->get("checkoutQuestionSize");
		$session->offsetUnset("checkoutQuestionSize");

		$questionsData["Weight"] = $session->get("checkoutQuestionWeight");
		$session->offsetUnset("checkoutQuestionWeight");

		$questionsData["Ethnicity"] = $session->get("checkoutQuestionEthnicity");
		$session->offsetUnset("checkoutQuestionEthnicity");

		$questionsData["DesiredWeight"] = $session->get("checkoutQuestionDesiredWeight");
		$session->offsetUnset("checkoutQuestionDesiredWeight");

		$questionsData["BasalMetabolicRateWithoutPhysicalActivity"] = $session->get("checkoutQuestionBasalMetabolicRateWithoutPhysicalActivity");
		$session->offsetUnset("checkoutQuestionBasalMetabolicRateWithoutPhysicalActivity");

		$questionsData["BasicMetabolismIncludingPhysicalActivity"] = $session->get("checkoutQuestionBasicMetabolismIncludingPhysicalActivity");
		$session->offsetUnset("checkoutQuestionBasicMetabolismIncludingPhysicalActivity");

		$questionsData["PhysicalActivityOccupationOrLeisure"] = $session->get("checkoutQuestionPhysicalActivityOccupationOrLeisure");
		$session->offsetUnset("checkoutQuestionPhysicalActivityOccupationOrLeisure");

		$attributes["cq_questions_form_data"] = json_encode($questionsData);

		$arguments->setReturn($attributes);
	}

	/*
	 * Redirect checkout process to questions form, if data input not completed
	 */
	public function onCheckoutConfirmActionBefore(\Enlight_Event_EventArgs $arguments)
	{
		$controller = $arguments->getSubject();
		$view = $controller->View();

		if(!$this->testQuestionData() && isset($view->sUserData['additional']['payment']))
		{
			$controller->redirect([
				"controller"	=> "checkout",
				"action"		=> "questions"
			]);
		}
	}

	/*
	 * Validate all questions field was completed
	 */
	public function testQuestionData()
	{
		$session = Shopware()->Session();

		$test = true;

		if(!$session->get("checkoutQuestionGender"))
		{
			$test = false;
		}
		if(!$session->get("checkoutQuestionDateOfBirth"))
		{
			$test = false;
		}
		if(!$session->get("checkoutQuestionSize"))
		{
			$test = false;
		}
		if(!$session->get("checkoutQuestionWeight"))
		{
			$test = false;
		}
		if(!$session->get("checkoutQuestionEthnicity"))
		{
			$test = false;
		}
		if(!$session->get("checkoutQuestionDesiredWeight"))
		{
		}
		if(!$session->get("checkoutQuestionBasalMetabolicRateWithoutPhysicalActivity"))
		{
		}
		if(!$session->get("checkoutQuestionBasicMetabolismIncludingPhysicalActivity"))
		{
		}
		if(!$session->get("checkoutQuestionPhysicalActivityOccupationOrLeisure"))
		{
		}

		return $test;
	}

	/*
	 * Add questions form template and step navigation template to theme view
	 */
	public function getExtendedCheckoutQuestionStepsView(\Enlight_Event_EventArgs $arguments)
	{
		$controller = $arguments->getSubject();
		$view = $controller->View();

		$view->addTemplateDir($this->getPath() . "/Resources/Views/");
		$view->extendsTemplate("frontend/checkout_questions/register/steps.tpl");
		$view->extendsTemplate("frontend/checkout_questions/checkout/confirm.tpl");
	}

	/*
	 * Add questions form input to session data or redirect to qustions form, if questions data input not completed
	 */
	public function getExtendedCheckoutInterviewStep(\Enlight_Event_EventArgs $arguments)
	{
		$controller = $arguments->getSubject();
		$view = $controller->View();

		$request = $controller->Request();
		$params = $request->getParams();

		if($this->validateForm($params))
		{
			$controller->redirect([
				"controller"	=> "checkout",
				"action"		=> "finish"
			]);
		} else {
			$controller->redirect([
				"controller"	=> "checkout",
				"action"		=> "questions"
			]);
		}
	}

	/*
	 * Test questions data input before session data set
	 */
	public function validateForm($data)
	{
		$validate = true;

		Shopware()->Session()->offsetSet("checkoutQuestionGender", $data["gender"]);
		if(!isset($data["gender"]) || ($data["gender"] != "m" && $data["gender"] != "f"))
		{
			$validate = false;
			Shopware()->Session()->offsetSet("checkoutQuestionGenderError", true);
		} else {
			Shopware()->Session()->offsetSet("checkoutQuestionGenderError", false);
		}

		Shopware()->Session()->offsetSet("checkoutQuestionDateOfBirth", $data["dateofbirth"]);
		if(!isset($data["dateofbirth"]) || !(bool)preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])\.(0[1-9]|1[0-2])\.([0-9]{4})$/", $data["dateofbirth"]))
		{
			$validate = false;
			Shopware()->Session()->offsetSet("checkoutQuestionDateOfBirthError", true);
		}

		list($d, $m, $y) = explode(".", $data["dateofbirth"]);
		if(!checkdate($m, $d, $y))
		{
			$validate = false;
			Shopware()->Session()->offsetSet("checkoutQuestionDateOfBirthError", true);
		} else {
			Shopware()->Session()->offsetSet("checkoutQuestionDateOfBirthError", false);
		}

        if((int)$data["size"] <= 0)
        {
            $data["size"] = 0;
        }
		Shopware()->Session()->offsetSet("checkoutQuestionSize", $data["size"]);
		if(!isset($data["size"]) || $data["size"] < 0)
		{
			$validate = false;
			Shopware()->Session()->offsetSet("checkoutQuestionSizeError", true);
		} else {
			Shopware()->Session()->offsetSet("checkoutQuestionSizeError", false);
		}

        if((int)$data["weight"] <= 0)
        {
            $data["weight"] = 0;
        }
		Shopware()->Session()->offsetSet("checkoutQuestionWeight", $data["weight"]);
		if(!isset($data["weight"]) || $data["weight"] < 0)
		{
			$validate = false;
			Shopware()->Session()->offsetSet("checkoutQuestionWeightError", true);
		} else {
			Shopware()->Session()->offsetSet("checkoutQuestionWeightError", false);
		}

		Shopware()->Session()->offsetSet("checkoutQuestionEthnicity", $data["ethnicity"]);
		if(!isset($data["ethnicity"]) || ($data["ethnicity"] != "eu" && $data["ethnicity"] != "af" && $data["ethnicity"] != "ar" && $data["ethnicity"] != "as" && $data["ethnicity"] != "hi" && $data["ethnicity"] != "mi"))
		{
			$validate = false;
			Shopware()->Session()->offsetSet("checkoutQuestionEthnicityError", true);
		} else {
			Shopware()->Session()->offsetSet("checkoutQuestionEthnicityError", false);
		}

        if((int)$data["desired-weight"] <= 0)
        {
            $data["desired-weight"] = 0;
        }
		Shopware()->Session()->offsetSet("checkoutQuestionDesiredWeight", $data["desired-weight"]);
		if(!isset($data["desired-weight"]) || $data["desired-weight"] < 0)
		{
		} else {
		}

        if((int)$data["bmrwpa"] <= 0)
        {
            $data["bmrwpa"] = 0;
        }
		Shopware()->Session()->offsetSet("checkoutQuestionBasalMetabolicRateWithoutPhysicalActivity", $data["bmrwpa"]);
		if(!isset($data["bmrwpa"]) || $data["bmrwpa"] < 0)
		{
		} else {
		}

        if((int)$data["bmipa"] <= 0)
        {
            $data["bmipa"] = 0;
        }
		Shopware()->Session()->offsetSet("checkoutQuestionBasicMetabolismIncludingPhysicalActivity", $data["bmipa"]);
		if(!isset($data["bmipa"]) || $data["bmipa"] < 0)
		{
		} else {
		}

		Shopware()->Session()->offsetSet("checkoutQuestionPhysicalActivityOccupationOrLeisure", $data["paol"]);
		if(!isset($data["paol"]) || ($data["paol"] != "ve" && $data["paol"] != "no" && $data["paol"] != "mo" && $data["paol"] != "ac" && $data["paol"] != "va"))
		{
		} else {
		}

		return $validate;
	}

	/*
	 * Add custom questions form action to checkout controller
	 */
	public function getExtendedCheckoutQuestionView(\Enlight_Event_EventArgs $arguments)
	{
        $arguments->setProcessed(true);
		$controller = $arguments->getSubject();
		$view = $controller->View();

		$session = Shopware()->Session();

		if(empty($view->sUserLoggedIn))
		{
			$controller->forward(
				"login",
				"account",
				null,
				["sTarget" => "checkout", "sTargetAction" => "questions", "showNoAccount" => true]
			);
		} elseif(Shopware()->Modules()->Basket()->sCountBasket() < 1) {
			$controller->forward("cart");
		}

		$view->assign("checkoutQuestionGender", $session->get("checkoutQuestionGender"));
		$view->assign("checkoutQuestionGenderError", $session->get("checkoutQuestionGenderError"));

		$view->assign("checkoutQuestionDateOfBirth", $session->get("checkoutQuestionDateOfBirth"));
		$view->assign("checkoutQuestionDateOfBirthError", $session->get("checkoutQuestionDateOfBirthError"));

		$view->assign("checkoutQuestionSize", $session->get("checkoutQuestionSize"));
		$view->assign("checkoutQuestionSizeError", $session->get("checkoutQuestionSizeError"));

		$view->assign("checkoutQuestionWeight", $session->get("checkoutQuestionWeight"));
		$view->assign("checkoutQuestionWeightError", $session->get("checkoutQuestionWeightError"));

		$view->assign("checkoutQuestionEthnicity", $session->get("checkoutQuestionEthnicity"));
		$view->assign("checkoutQuestionEthnicityError", $session->get("checkoutQuestionEthnicityError"));

		$view->assign("checkoutQuestionDesiredWeight", $session->get("checkoutQuestionDesiredWeight"));
		$view->assign("checkoutQuestionDesiredWeightError", $session->get("checkoutQuestionDesiredWeightError"));

		$view->assign("checkoutQuestionBasalMetabolicRateWithoutPhysicalActivity", $session->get("checkoutQuestionBasalMetabolicRateWithoutPhysicalActivity"));
		$view->assign("checkoutQuestionBasalMetabolicRateWithoutPhysicalActivityError", $session->get("checkoutQuestionBasalMetabolicRateWithoutPhysicalActivityError"));

		$view->assign("checkoutQuestionBasicMetabolismIncludingPhysicalActivity", $session->get("checkoutQuestionBasicMetabolismIncludingPhysicalActivity"));
		$view->assign("checkoutQuestionBasicMetabolismIncludingPhysicalActivityError", $session->get("checkoutQuestionBasicMetabolismIncludingPhysicalActivityError"));

		$view->assign("checkoutQuestionPhysicalActivityOccupationOrLeisure", $session->get("checkoutQuestionPhysicalActivityOccupationOrLeisure"));
		$view->assign("checkoutQuestionPhysicalActivityOccupationOrLeisureError", $session->get("checkoutQuestionPhysicalActivityOccupationOrLeisureError"));

		$view->addTemplateDir($this->getPath() . "/Resources/Views/");
		$view->loadTemplate("frontend/checkout_questions/checkout/questions.tpl");
	}
}
?>
