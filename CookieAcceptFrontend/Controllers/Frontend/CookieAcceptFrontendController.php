<?php
class Shopware_Controllers_Frontend_CookieAcceptFrontendController extends \Enlight_Controller_Action
{
	public function setCookieAction() {
		$host = Shopware()->Shop()->getHost();
		$search = array("https://", "http://", ".", "/", "www");
		$host = str_replace($search, "", $host);
		$code = "check_info_box_" . $host;
		setcookie($code, 1, time() + (86400 * 365 * 100), "/"); // 86400 = 1 day

		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();

		echo json_encode(array("complete" => 1));
	}
}

