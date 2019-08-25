<?php

namespace itDelightShippingFreeFrom\Subscriber\Frontend;

use Doctrine\ORM\EntityManager;
use Enlight\Event\SubscriberInterface;
use Shopware\Components\Logger;
use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Dispatch\ShippingCost;
use Shopware\Models\Order\Basket;
use Shopware\Models\Shop\Currency;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Common
 * @package itDelightShippingFreeFrom\Subscriber\Frontend
 */
class Common implements SubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var
     */
    private $pluginDir;


    /**
     * Common constructor.
     * @param ContainerInterface $container
     * @param $pluginDir
     */
    public function __construct(ContainerInterface $container, EntityManager $entityManager, $pluginDir)
    {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->pluginDir = $pluginDir;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return
            [
                "Enlight_Controller_Action_PostDispatchSecure_Frontend" => 'listenAll',
                "Theme_Inheritance_Template_Directories_Collected" => "onCollectTemplateDir"
            ];
    }

    public function listenAll(\Enlight_Event_EventArgs $args)
    {

        $coreLogger = $this->container->get('corelogger')->log(200, "qweqwewqe");


        $activeCurrencyName = Shopware()->Shop()->getCurrency()->getCurrency();
        $defaultCurrencyId = Shopware()->Shop()->getCurrency()->getDefault();

        $currencyRepository = $this->entityManager->getRepository(Currency::class);
        $dispatchModel = $this->entityManager->getRepository(Dispatch::class);
        $shippingCostsModel = $this->entityManager->getRepository(ShippingCost::class);


        $subject = $args->getSubject();
        /** @var  $view \Enlight_View_Default */
        $view = $subject->View();


        $sBasketAmount = Shopware()->Modules()->Basket()->sGetAmount()["totalAmount"];


        $sDispatch = $view->getAssign('sDispatch');
        error_log(print_r($sDispatch, true));

        $sDispatchId = $sDispatch["id"];

        if (!$sDispatchId) {
            $sDispatch["id"] = 9;
            $view->assign('sDispatch', $sDispatch);
        }


        if ($sDispatchId && $sBasketAmount) {

            /** @var  $activeCurrencyObject Currency */
            $activeCurrencyObject = $currencyRepository->findOneBy(['currency' => $activeCurrencyName]);
            $activeCurrencyId = $activeCurrencyObject->getId();

            if ($activeCurrencyId !== $defaultCurrencyId) {
                $activeCurrencyPriceFactor = $activeCurrencyObject->getFactor();

                /** @var  $dispatchObject Dispatch */
                $dispatchObject = $dispatchModel->findOneBy(['id' => $sDispatchId]);
                $dispatchShippingFreeFrom = $dispatchObject->getShippingFree();


                $sBasketAmountWithPriceFactor = $sBasketAmount / $activeCurrencyPriceFactor;

                $shippingValue = Shopware()->Db()->fetchOne("SELECT `value` FROM s_premium_shippingcosts WHERE `from` <='{$sBasketAmountWithPriceFactor}' AND dispatchID = '{$sDispatchId}' ORDER BY `from` DESC ");

                $shippingValue *= $activeCurrencyPriceFactor;
                if (($sBasketAmount / $activeCurrencyPriceFactor) < $dispatchShippingFreeFrom) {
                    $recalculatedBasketAmount = $sBasketAmount + $shippingValue;

                    $view->assign("sShippingcosts", $shippingValue);
                    $view->assign("sAmount", $recalculatedBasketAmount);
                }

            }

        }
    }

    Public function onCollectTemplateDir(\Enlight_Event_EventArgs $args)
    {
        $dirs = $args->getReturn();
        $dirs[] = $this->container->getParameter('it_delight_shipping_free_from.plugin_dir') . '/Resources/views';
        $args->setReturn($dirs);
    }


}