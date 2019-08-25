<?php


namespace itDelightExtendDtgs\Subscribers\Frontend;


use DtgsDeliveryDate\Components\Helpers\DateCalculation;
use DtgsDeliveryDate\Subscriber\Frontend\Checkout;
use Enlight_Controller_Action;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Dispatch\Dispatch;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CheckoutSubscriber extends Checkout
{

    /** @var ContainerInterface */
    private $container;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct($this->container);
    }

    public function onPostDispatchFrontend(\Enlight_Event_EventArgs $args)
    {
        /** @var  $subject Enlight_Controller_Action */
        $subject = $args->getSubject();
        parent::onPostDispatchFrontend($args); // TODO: Change the autogenerated stub

        $helper = new DateCalculation($this->container);

        $view = $subject->View();
        $sDispatch = $view->getAssign("sDispatch");
        $sDispatch = Shopware()->Session()->offsetGet('sDispatch');
        $dispatchModel = Shopware()->Models()->getRepository(Dispatch::class);
        /** @var  $sDispatchObject Dispatch */
        $sDispatchObject = $dispatchModel->findOneBy(["id" => $sDispatch]);
        $sDispatchObjectAllowOverrideConfiguration = $sDispatchObject->getAttribute()->getDelightAllowOverridePluginDtsgConfiguration();
        if ($sDispatchObjectAllowOverrideConfiguration) {
            $arr = [];
            $excludedDays = $sDispatchObject->getAttribute()->getDelightExcludedDays();
            $excludedDays = $excludedDays != '' ? preg_split('/\R/', $excludedDays) : array();

            foreach ($excludedDays as $excludedDay) {
                $arr[] = date('d.m.Y', strtotime($excludedDay));
            }
            $included_days = array();
            if ($sDispatchObject->getAttribute()->getDelightAllow_1()) {
                array_push($included_days, 1);
            }
            if ($sDispatchObject->getAttribute()->getDelightAllow_2()) {
                array_push($included_days, 2);
            }
            if ($sDispatchObject->getAttribute()->getDelightAllow_3()) {
                array_push($included_days, 3);
            }
            if ($sDispatchObject->getAttribute()->getDelightAllow_4()) {
                array_push($included_days, 4);
            }
            if ($sDispatchObject->getAttribute()->getDelightAllow_5()) {
                array_push($included_days, 5);
            }
            if ($sDispatchObject->getAttribute()->getDelightAllow_6()) {
                array_push($included_days, 6);
            }
            if ($sDispatchObject->getAttribute()->getDelightAllow_7()) {
                array_push($included_days, 7);
            }

            if (strtotime(date('H:i')) > strtotime(date($sDispatchObject->getAttribute()->getDelightDisableEarliestDay()))) {
                array_push($arr, date('d.m.Y'));
            }
            $i = 0;
            $days_from_now = $sDispatchObject->getAttribute()->getDelightMinDaysBeforeSelected();
            while ($i < $days_from_now) {
                $i++;
                $nextDay = strtotime('today +' . $i . 'days');
                $date = date('d.m.Y', $nextDay);
                if (in_array($date, $arr)) {
                    $days_from_now++;
                    continue;
                } else {
                    array_push($arr, $date);
                }
            }
            $view->assign('dtgsDeliveryDate_excluded_dates', json_encode($arr));
            $view->assign('dtgsDeliveryDate_included_days', json_encode($included_days));

        }
    }


}