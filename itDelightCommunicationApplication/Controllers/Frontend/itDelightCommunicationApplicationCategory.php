<?php

use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Models\Article\Detail;
use itDelightCommunicationApplication\Helpers\JsonData;
use itDelightCommunicationApplication\Helpers\Debugger;
use itDelightCommunicationApplication\Helpers\ArticleData;

class Shopware_Controllers_Frontend_itDelightCommunicationApplicationCategory extends Enlight_Controller_Action implements CSRFWhitelistAware
{

    private $jsonHelper;
    private $debugger;
    private $article;

    public function __construct(Enlight_Controller_Request_Request $request, Enlight_Controller_Response_Response $response)
    {
        parent::__construct($request, $response);

        $this->jsonHelper = new JsonData();
        $this->debugger = new Debugger();
        $this->article = new ArticleData();

    }

    public function getWhitelistedCSRFActions()
    {

        return [
            'getBrandsList',
            'getCategoriesIDs',
            'getProductsByBrand',
            'getProductsByCategoryID',
            'getSearchResult'
        ];

    }

    private function shopRender()
    {
        $this->container->get('front')->Plugins()->ViewRenderer()->setNoRender();
    }

    public function getBrandsListAction()
    {

        $this->shopRender();

        $suppliers = array();
        $suppliersItems = Shopware()->Db()->fetchAll("SELECT * FROM s_articles_supplier");
        foreach ($suppliersItems as $suppliersItem) {

            $temp = array(
                'supplierID' => $suppliersItem['id'],
                'imgURL' => ''.(!empty($suppliersItem['img']) ? 'https://teleropa.de/'.$suppliersItem['img'].'' : '').'',
                'title' => $suppliersItem['name']
            );

            array_push($suppliers, $temp);

        }

        echo $this->jsonHelper->returnJsonData($suppliers);

    }

    public function getCategoriesIDsAction()
    {

        $this->shopRender();

        $data = array(
            'currentCategory' => array(),
            'categories'=> array()
        );

        $categoryID = $this->request->getPost('categoryID');
        if(empty($categoryID)) {
            $categoryID = 3;
        }

        $categoryCurrent = Shopware()->Modules()->Categories()->sGetCategoryContent($categoryID);

        $data['currentCategory'] = array(
            'id' => $categoryCurrent['id'],
            'name' => $categoryCurrent['name'],
            'cmsHeadline' => $categoryCurrent['cmsHeadline'],
            'cmsText' => $categoryCurrent['cmsText']
        );

        $categories = Shopware()->Modules()->Categories()->sGetWholeCategoryTree($categoryID, 2);
        foreach ($categories as $category) {

            $subStatus = false;
            if(!empty($category['sub'])) {
                $subStatus = true;
            }

            $temp = array(
                'id' => $category['id'],
                'name' => $category['name'],
                'cmsHeadline' => $category['cmsHeadline'],
                'cmsText' => $category['cmsText'],
                'haveSubCategories' => $subStatus
            );
            array_push($data['categories'], $temp);
        }

        echo $this->jsonHelper->returnJsonData($data);

        $this->debugger->insertLog($data, 'getCategoriesIDsAction');

    }

    public function getProductsByBrandAction()
    {

        $this->shopRender();

        //[..., productID, ...]

        $supplierID = $this->request->getPost('supplierID');

        $criteria = array(
            'supplier' => $supplierID
        );

        $supplier = Shopware()->Modules()->Articles()->sGetSupplierById($supplierID);

        if(!empty($supplier)) {

            /** @var $articleModel \Shopware\Models\Article\Article */
            $articleModel = Shopware()->Models()->getRepository('Shopware\Models\Article\Article')->findBy($criteria);

            /** @var $articleDetailModel Detail */
            $articleDetailModel = Shopware()->Models()->getRepository('Shopware\Models\Article\Detail');

            $data = array(
                'products' => array()
            );

            if(!empty($articleModel)) {

                foreach ($articleModel as $item) {

                    $articleDetailCriteria = array(
                        'articleId' => $item->getId()
                    );
                    $articleDetail = $articleDetailModel->findOneBy($articleDetailCriteria);
                    if(!empty($articleDetail)) {
                        if($this->article->checkActiveAndCategoriesModel($articleDetail)) {

                            array_push($data['products'], $articleDetail->getNumber());

                        }
                    }

                }

            }

            echo $this->jsonHelper->returnJsonData($data);

        } else {

            $this->jsonHelper->returnError();

        }

        $this->debugger->insertLog($data, 'getProductsByBrandAction');

    }


    public function getProductsByCategoryIDAction()
    {

        $this->shopRender();

        $categoryID = $this->request->getPost('categoryID');

        $data = array(
            'products' => array()
        );

        $category = Shopware()->Models()->getRepository('Shopware\Models\Category\Category')->find($categoryID);

        if(!empty($category)) {

            $articles = Shopware()->Db()->fetchAll("SELECT * FROM s_articles_categories WHERE categoryID = {$category->getId()}");

            if(!empty($articles)) {

                /** @var $articleDetailModel Shopware\Models\Article\Detail */
                $articleDetailModel = Shopware()->Models()->getRepository('Shopware\Models\Article\Detail');

                foreach ($articles as $item) {

                    $articleCriteria = array(
                        'articleId' => $item['articleID']
                    );
                    $articleDetail = $articleDetailModel->findOneBy($articleCriteria);
                    if (!empty($articleDetail)) {

                        if ($this->article->checkActiveAndCategoriesModel($articleDetail)) {

                            array_push($data['products'], $articleDetail->getNumber());

                        }

                    }

                }

            }

            echo $this->jsonHelper->returnJsonData($data);

        } else {

            $this->jsonHelper->returnError();

        }

        $this->debugger->insertLog($data, 'getProductsByCategoryIDAction');

    }


    public function getSearchResultAction()
    {

        $this->shopRender();

        $data = array(
            'searchResult' => array()
        );

        $searchString = $this->request->getPost('searchString');
        $this->request->setParam('sSearch', $searchString);

        $term = $searchString;

        if (!$term || strlen($term) < Shopware()->Config()->get('MinSearchLenght')) {
            return;
        }

        $context = $this->get('shopware_storefront.context_service')->getShopContext();

        $criteria = $this->get('shopware_search.store_front_criteria_factory')->createAjaxSearchCriteria($this->Request(), $context);
        $criteria->limit(999);

        $result = null;
        if (!$result || $result->getTotalCount() === 0) {
            $result = $this->get('shopware_search.product_search')->search($criteria, $context);
        }

        if($result->getTotalCount() > 0) {

            $productIDs = array();
            foreach ($result->getProducts() as $productKey => $product) {
                array_push($productIDs, $product->getNumber());
            }
            $productIDsString = implode(',', $productIDs);
            $getAllOrders = Shopware()->Db()->fetchAll("SELECT * FROM s_order_details WHERE articleordernumber IN ($productIDsString)");
            if(empty($getAllOrders)) {
                $getAllOrders = array();
            }

            foreach ($result->getProducts() as $productKey => $product) {
                $counter = 0;
                foreach ($getAllOrders as $getAllOrder) {
                    if($getAllOrder['articleordernumber'] == $product->getNumber()) {
                        $counter++;
                    }
                }
                $temp = array(
                    'productID' => $product->getNumber(),
                    'name' => $product->getName(),
                    'price' => $product->getListingPrice()->getCalculatedPrice(),
                    'popularity' => $counter
                );
                array_push($data['searchResult'], $temp);
            }

        }

        echo $this->jsonHelper->returnJsonData($data);

    }

}