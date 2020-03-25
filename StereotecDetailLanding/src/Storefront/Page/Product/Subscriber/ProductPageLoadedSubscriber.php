<?php

namespace Stereotec\DetailExtension\Storefront\Page\Product\Subscriber;

use Enqueue\Dbal\JSON;
use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailCollection;
use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity;
use Shopware\Core\Content\Media\DataAbstractionLayer\MediaRepositoryDecorator;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Storefront\Page\PageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductPageLoadedSubscriber implements EventSubscriberInterface
{
    private $connection;
    private $decorator;

    public function __construct(\Doctrine\DBAL\Connection $connection, MediaRepositoryDecorator $decorator)
    {
        $this->connection = $connection;
        $this->decorator = $decorator;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware\Storefront\Page\Product\ProductPageLoadedEvent' => 'pageLoaded'
        ];
    }

    public function pageLoaded(PageLoadedEvent $event)
    {
        $variables = $event->getPage()->getVars();
        $product = $variables['product'];
        /** @var SalesChannelProductEntity $customFields */
        $customFields = $product->getCustomFields();
        $itDelightAttributes = $customFields['it_delight_field'];
        $blogData = [];
        foreach ($itDelightAttributes as $attribute) {
            array_push($blogData, $this->connection->fetchAssoc("SELECT cms_page_translation.name,cms_page.preview_media_id FROM cms_page_translation
INNER JOIN cms_page ON cms_page.id = cms_page_translation.cms_page_id  WHERE cms_page.id = UNHEX('{$attribute}')
"));

        }

        $landingPages = [];
        foreach ($blogData as $blog) {
            if (!empty($blog)) {
                $mediaId = Uuid::fromBytesToHex($blog['preview_media_id']);
                if (!empty($mediaId)) {
                    $thumbnails = $this->connection->fetchAssoc("SELECT thumbnails_ro FROM media WHERE id = UNHEX('{$mediaId}')");

                    if (!empty($thumbnails)) {
                        /** @var MediaThumbnailCollection $json */
                        $json = unserialize($thumbnails['thumbnails_ro']);
                        $elements = $json->getVars()['elements'];
                        /** @var MediaThumbnailEntity $firstValue */
                        $firstValue = $elements[array_key_first($elements)];
                    }

                }
                array_push($landingPages, ['media' => $firstValue->getUrl(), 'urlName' => $blog['name'], 'url' => '']);
            }
        }
        $event->getPage()->assign(['blogs' => $landingPages]);


    }
}