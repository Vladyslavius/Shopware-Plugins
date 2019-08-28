<?php

namespace itDelightCommunicationApplication\Helpers;

class ArticleData
{

    public function getArticle($orderNumber) {

        $sArticleID = Shopware()->Modules()->Articles()->sGetArticleIdByOrderNumber($orderNumber);

        if(!empty($sArticleID) && isset($sArticleID)) {

            $sArticle = Shopware()->Modules()->Articles()->sGetArticleById($sArticleID);

            return $sArticle;

        }

    }

    public function checkActiveAndCategoriesModel($article) {

        //Only article/detail model

        if($article->getActive() && $article->getArticle()->getCategories()->count() != 0) {
            return true;
        } else {
            return false;
        }

    }

    public function formatPrice($price) {

        $price = str_replace(',', '.', $price);
        return (double)number_format($price, 2);

    }

    public function getNetPrice($articleID) {

        $criteria = array(
            'articleId' => $articleID
        );

        /** @var $priceModel \Shopware\Models\Article\Price */
        $priceModel = Shopware()->Models()->getRepository('Shopware\Models\Article\Price')->findOneBy($criteria);
        if(!empty($priceModel)) {
            return $priceModel->getPrice();
        } else {
            return null;
        }

    }

}