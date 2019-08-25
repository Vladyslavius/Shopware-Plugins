<?php

namespace itDelightExtendDtgs\Components\Helpers;

use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Crud
{
    /** @var CrudService */
    private $service;

    public function __construct($crud)
    {
        $this->service = $crud;
    }

    /**
     * @throws \Exception
     */
    public function createAttributes()
    {
        try {
            $this->service->update("s_premium_dispatch_attributes", "delight_allow_override_plugin_dtsg_configuration", "boolean", [
                "label" => "Allow override basic configuration?",
                "displayInBackend" => "1",
            ]);

            $this->service->update("s_premium_dispatch_attributes", "delight_min_days_before_selected", "integer", [
                "label" => "Minimum days before delivery date can be selected",
                "displayInBackend" => "1"
            ], null, false, 0);
            $this->service->update("s_premium_dispatch_attributes", "delight_disable_earliest_day", "string", [
                "label" => "Disable earliest date if later than",
                "displayInBackend" => "1"
            ], null, false, "12:00");
            $this->service->update("s_premium_dispatch_attributes", "delight_excluded_days", "text", [
                "label" => "Excluded dates",
                "displayInBackend" => "1"
            ], null, false, "12:00");

            $this->service->update("s_premium_dispatch_attributes", "delight_allow_1", "boolean", [
                "label" => "Allow Mondays?",
                "displayInBackend" => "1"
            ], null, false, true);
            $this->service->update("s_premium_dispatch_attributes", "delight_allow_2", "boolean", [
                "label" => "Allow Tuesdays?",
                "displayInBackend" => "1"
            ], null, false, true);
            $this->service->update("s_premium_dispatch_attributes", "delight_allow_3", "boolean", [
                "label" => "Allow Wednesdays?",
                "displayInBackend" => "1"
            ], null, false, true);
            $this->service->update("s_premium_dispatch_attributes", "delight_allow_4", "boolean", [
                "label" => "Allow Thursdays?",
                "displayInBackend" => "1"
            ], null, false, true);
            $this->service->update("s_premium_dispatch_attributes", "delight_allow_5", "boolean", [
                "label" => "Allow Fridays?",
                "displayInBackend" => "1"
            ], null, false, true);
            $this->service->update("s_premium_dispatch_attributes", "delight_allow_6", "boolean", [
                "label" => "Allow Saturdays?",
                "displayInBackend" => "1"
            ], null, false, true);

            $this->service->update("s_premium_dispatch_attributes", "delight_allow_7", "boolean", [
                "label" => "Allow Sundays?",
                "displayInBackend" => "1"
            ], null, false, true);
            $metaDataCache = Shopware()->Models()->getConfiguration()->getMetadataCacheImpl();
            $metaDataCache->deleteAll();
            Shopware()->Models()->generateAttributeModels(['s_premium_dispatch_attributes']);
            Shopware()->Models()->flush();
        } catch (\Exception $exception) {
            $exception->getTraceAsString();
        }
    }

    public function deleteAttributes()
    {
        try {
            $this->service->delete("s_premium_dispatch_attributes", "delight_allow_override_plugin_dtsg_configuration");
            $this->service->delete("s_premium_dispatch_attributes", "delight_min_days_before_selected");
            $this->service->delete("s_premium_dispatch_attributes", "delight_disable_earliest_day");
            $this->service->delete("s_premium_dispatch_attributes", "delight_excluded_days");
            $this->service->delete("s_premium_dispatch_attributes", "delight_allow_1");
            $this->service->delete("s_premium_dispatch_attributes", "delight_allow_2");
            $this->service->delete("s_premium_dispatch_attributes", "delight_allow_3");
            $this->service->delete("s_premium_dispatch_attributes", "delight_allow_4");
            $this->service->delete("s_premium_dispatch_attributes", "delight_allow_5");
            $this->service->delete("s_premium_dispatch_attributes", "delight_allow_6");
            $this->service->delete("s_premium_dispatch_attributes", "delight_allow_7");
        } catch (\Exception $e) {

        }
        $metaDataCache = Shopware()->Models()->getConfiguration()->getMetadataCacheImpl();
        $metaDataCache->deleteAll();
        Shopware()->Models()->generateAttributeModels(['s_premium_dispatch_attributes']);
    }
}