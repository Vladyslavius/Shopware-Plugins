<?php


namespace itDelightExtendDtgs;


use Psr\Log\LogLevel;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use itDelightExtendDtgs\Components\Helpers\Crud;

class itDelightExtendDtgs extends Plugin
{
    /**
     * @throws \Exception
     */
    public function install(InstallContext $context)
    {
        $crudService = new Crud($this->container->get("shopware_attribute.crud_service"));
        $crudService->createAttributes();
    }

    /**
     * @throws \Exception
     */
    public function uninstall(UninstallContext $context)
    {
        $crudService = new Crud($this->container->get("shopware_attribute.crud_service"));
        $crudService->deleteAttributes();
    }

}