<?php
/**
 * Created by PhpStorm.
 * User: vlad
 * Date: 08.05.18
 * Time: 13:23
 */

namespace ImportBooks;


use Enlight_Controller_Request_Request;
use Enlight_Controller_Response_Response;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware_Controllers_Backend_Article;

class Connector extends Shopware_Controllers_Backend_Article
{


    public function saveArticle($data, $article)
    {
        $data = $this->prepareAssociatedData($data, $article);
        $article->fromArray($data);
        Shopware()->Models()->persist($article);
        Shopware()->Models()->flush();
        if (empty($data['id']) && !empty($data['autoNumber'])) {
            $this->increaseAutoNumber($data['autoNumber'], $article->getMainDetail()->getNumber());
        }
        $savedArticle = $this->getArticle($article->getId());
        return $savedArticle;
    }

    public function getArticleRelatedProductStreams($articleId)
    {
        $result = Shopware()->Models()->getRepository(\Shopware\Models\Article\Article::class)
            ->getArticleRelatedProductStreamsQuery($articleId)
            ->getArrayResult();

        return $result ?: [];
    }

    public function createConfiguratorVariants($articleId, $groups, $offset = 0, $limit = 50, $mergeType = 1)
    {
        /** @var $article Article */
        $article = $this->getRepository()->find($articleId);
        $generatorData = $this->prepareGeneratorData($groups, $offset, $limit);
        $detailData = $this->getDetailDataForVariantGeneration($article);

        if ($offset === 0 && $mergeType === 1) {
            $this->removeAllConfiguratorVariants($articleId);
        } elseif ($offset === 0 && $mergeType === 2) {
            $this->deleteVariantsForAllDeactivatedOptions($article, $generatorData['allOptions']);
        }

        Shopware()->Models()->clear();
        $article = $this->getRepository()->find($articleId);

        $detailData = $this->setDetailDataReferences($detailData, $article);

        $configuratorSet = $article->getConfiguratorSet();

        $dependencies = $this->getRepository()->getConfiguratorDependenciesQuery($configuratorSet->getId())->getArrayResult();

        $priceVariations = $this->getRepository()->getConfiguratorPriceVariationsQuery($configuratorSet->getId())->getArrayResult();
        if (empty($generatorData)) {
            return;
        }

        $sql = $generatorData['sql'];
        $originals = $generatorData['originals'];
        $variants = Shopware()->Db()->fetchAll($sql);

        $counter = 1;
        if ($mergeType === 1) {
            $counter = $offset;
        }
        $allOptions = $this->getRepository()->getAllConfiguratorOptionsIndexedByIdQuery()->getResult();

        // Iterate all selected variants to insert them into the database
        foreach ($variants as $variant) {
            $variantData = $this->prepareVariantData($variant, $detailData, $counter, $dependencies, $priceVariations,
                $allOptions, $originals, $article, $mergeType);
            if ($variantData === false) {
                continue;
            }

            // Merge the data with the original main detail data
            $data = array_merge($detailData, $variantData);

            //use only the main detail of the article as base object, if the merge type is set to "Override" and the current variant is the first generated variant.
            if ($offset === 0 && $mergeType === 1) {
                $detail = $article->getMainDetail();
            } else {
                $detail = new Detail();
                Shopware()->Models()->persist($detail);
            }

            $detail->fromArray($data);
            $detail->setArticle($article);
            Shopware()->Models()->flush();

            $this->copyConfigurationTemplateTranslations($detailData, $detail);
            ++$offset;
        }

        Shopware()->Models()->clear();

    }

    private function setDetailDataReferences($detailData, $article)
    {
        foreach ($detailData['prices'] as &$price) {
            $price['article'] = $article;
            unset($price['id']);
            $price['customerGroup'] = Shopware()->Models()->find(\Shopware\Models\Customer\Group::class,
                $price['customerGroup']['id']);
        }
        if ($detailData['unitId']) {
            $detailData['unit'] = Shopware()->Models()->find(\Shopware\Models\Article\Unit::class,
                $detailData['unitId']);
        }

        return $detailData;
    }

    protected function getTranslationComponent()
    {
        if ($this->translation === null) {
            $this->translation = Shopware()->Container()->get('translation');
        }

        return $this->translation;
    }


}