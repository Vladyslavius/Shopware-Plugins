<?php

use Shopware\Components\CSRFWhitelistAware;
use itDelightCommunicationApplication\Helpers\JsonData;
use itDelightCommunicationApplication\Helpers\Debugger;
use itDelightCommunicationApplication\Helpers\ArticleData;

class Shopware_Controllers_Frontend_itDelightCommunicationApplicationArticle extends Enlight_Controller_Action implements CSRFWhitelistAware
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
            'getFullProductData',
            'getPreviewProductData',
            'getInfo',
            'getPreviewImage',
            'getFullSizeImages',
            'getSimilarProducts',
            'getReviews',
            'getDescription',
            'getDetails',
            'getPackage',
            'getVideo'
        ];

    }

    private function shopRender()
    {
        $this->container->get('front')->Plugins()->ViewRenderer()->setNoRender();
    }


    public function getInfoAction()
    {

        $this->shopRender();

        $sArticle = $this->article->getArticle($this->request->getPost('productID'));

        if (!empty($sArticle)) {

            $companyPrice = $this->article->getNetPrice($sArticle['articleID']);
            if(!isset($companyPrice)) {
                $companyPrice = $sArticle['price'];
            }

            $data = array(
                'productName' => $sArticle['articleName'],
                'productSalePercent' => $sArticle['pseudopricePercent'],
                'stock' => $sArticle['instock'],
                'salePrice' => $this->article->formatPrice($sArticle['pseudoprice']),
                'price' => $this->article->formatPrice($sArticle['price']),
                'companyPrice' => $this->article->formatPrice($companyPrice),
                'rate' => $sArticle['sVoteAverage']['average'],
                'siteURL' => $sArticle['linkDetails']
            );

            echo $this->jsonHelper->returnJsonData($data);

        } else {

            $this->jsonHelper->returnError();

        }

    }


    public function getPreviewImageAction()
    {

        $this->shopRender();

        //+ previewImgURL

        $sArticle = $this->article->getArticle($this->request->getPost('productID'));

        if (!empty($sArticle)) {

            $data = array(
                'previewImgURL' => $sArticle['image']['thumbnails'][0]['source']
            );

            echo $this->jsonHelper->returnJsonData($data);

        } else {

            $this->jsonHelper->returnError();

        }

        $this->debugger->insertLog($data, 'getPreviewProductImageAction');

    }


    public function getFullSizeImagesAction()
    {

        $this->shopRender();

        //+ imgURL

        $sArticle = $this->article->getArticle($this->request->getPost('productID'));

        if (!empty($sArticle)) {

            $images = array();

            if (!empty($sArticle['image']['source'])) {
                array_push($images, $sArticle['image']['source']);
            }

            if (!empty($sArticle['images'])) {

                foreach ($sArticle['images'] as $imagesItem) {

                    array_push($images, $imagesItem['source']);

                }

            }

            $data = array(
                'imgURLs' => $images
            );

            echo $this->jsonHelper->returnJsonData($data);

        } else {

            $this->jsonHelper->returnError();

        }

        $this->debugger->insertLog($data, 'getFullSizeProductImagesAction');

    }


    public function getSimilarProductsAction()
    {

        $this->shopRender();

        //+ productID

        $sArticle = $this->article->getArticle($this->request->getPost('productID'));

        if (!empty($sArticle)) {

            $productIDs = array();

            if (!empty($sArticle['sSimilarArticles'])) {

                $count = 1;

                foreach ($sArticle['sSimilarArticles'] as $similarArticle) {

                    $article = $this->article->getArticle($similarArticle['ordernumber']);

                    if ($article['instock'] > 0 && $article['isAvailable'] && $count <= 6) {

                        array_push($productIDs, $similarArticle['ordernumber']);
                        $count++;

                    }

                }

            }

            $data = array(
                'productIDs' => $productIDs
            );

            echo $this->jsonHelper->returnJsonData($data);

        } else {

            $this->jsonHelper->returnError();

        }

        $this->debugger->insertLog($data, 'getSimilarProductsAction');

    }


    public function getReviewsAction()
    {

        $this->shopRender();

        //[…, { user(Имя пользователя, который оставил отзыв), rate(оценка), date(дата отзыва), text(текст отзыва) }, …]

        $sArticle = $this->article->getArticle($this->request->getPost('productID'));

        if (!empty($sArticle)) {

            $reviewsItems = array();

            if (!empty($sArticle['sVoteComments'])) {

                foreach ($sArticle['sVoteComments'] as $sVoteComment) {

                    array_push($reviewsItems, array(
                        'user' => $sVoteComment['name'],
                        'rate' => $sVoteComment['points'],
                        'date' => $sVoteComment['datum'],
                        'text' => $sVoteComment['comment']
                    ));

                }

            }

            $data = array(
                'reviewsItems' => $reviewsItems
            );

            echo $this->jsonHelper->returnJsonData($data);

        } else {

            $this->jsonHelper->returnError();

        }

        $this->debugger->insertLog($data, 'getReviewsAction');

    }

    public function getDescriptionAction()
    {

        $this->shopRender();

        //text

        $sArticle = $this->article->getArticle($this->request->getPost('productID'));

        if (!empty($sArticle)) {

            if (!empty($sArticle['description_long']) && isset($sArticle['description_long'])) {

                $data = array(
                    'text' => $sArticle['description_long']
                );

                echo $this->jsonHelper->returnJsonData($data);

            } else {

                $this->jsonHelper->returnError();

            }

        } else {

            $this->jsonHelper->returnError();

        }

        $this->debugger->insertLog($data, 'getDescriptionAction');

    }

    public function getDetailsAction()
    {

        $this->shopRender();

        //data

        $sArticle = $this->article->getArticle($this->request->getPost('productID'));

        if (!empty($sArticle)) {

            if (!empty($sArticle['plenty_connector_technical_description']) && isset($sArticle['plenty_connector_technical_description'])) {

                $data = array(
                    'data' => $sArticle['plenty_connector_technical_description']
                );

                echo $this->jsonHelper->returnJsonData($data);

            } else {

                $this->jsonHelper->returnError();

            }

        } else {

            $this->jsonHelper->returnError();

        }

        $this->debugger->insertLog($data, 'getDetailsAction');

    }

    public function getPackageAction()
    {

        $this->shopRender();

        //data

        $sArticle = $this->article->getArticle($this->request->getPost('productID'));

        if (!empty($sArticle)) {

            if (!empty($sArticle['plenty_connector_free1']) && isset($sArticle['plenty_connector_free1'])) {

                $data = array(
                    'data' => $sArticle['plenty_connector_free1']
                );

                echo $this->jsonHelper->returnJsonData($data);

            } else {

                $this->jsonHelper->returnError();

            }

        } else {

            $this->jsonHelper->returnError();

        }

        $this->debugger->insertLog($data, 'getPackageAction');

    }

    public function getVideoAction()
    {

        $this->shopRender();

        //data

        $sArticle = $this->article->getArticle($this->request->getPost('productID'));

        if (!empty($sArticle)) {

            if (!empty($sArticle['plenty_connector_free3']) && isset($sArticle['plenty_connector_free3'])) {

                $data = array(
                    'data' => $sArticle['plenty_connector_free3']
                );

                echo $this->jsonHelper->returnJsonData($data);

            } else {

                $this->jsonHelper->returnError();

            }

        } else {

            $this->jsonHelper->returnError();

        }

        $this->debugger->insertLog($data, 'getVideo');

    }


    public function getFullProductDataAction()
    {

        $this->shopRender();

        $productID = $this->request->getPost('productID');
        $sArticle = $this->article->getArticle($productID);

        if (!empty($sArticle)) {

            $data = array();

            $companyPrice = $this->article->getNetPrice($sArticle['articleID']);
            if(!isset($companyPrice)) {
                $companyPrice = $sArticle['price'];
            }

            //Global info
            $data['productName'] = $sArticle['articleName'];
            $data['productSalePercent'] = $sArticle['pseudopricePercent'];
            $data['stock'] = $sArticle['instock'];
            $data['salePrice'] = $this->article->formatPrice($sArticle['pseudoprice']);
            $data['price'] = $this->article->formatPrice($sArticle['price']);
            $data['companyPrice'] = $this->article->formatPrice($companyPrice);
            $data['rate'] = $sArticle['sVoteAverage']['average'];
            $data['siteURL'] = $sArticle['linkDetails'];

            //Preview images
            $data['previewImgURL'] = $sArticle['image']['thumbnails'][0]['source'];

            //Images
            $images = array();
            if (!empty($sArticle['image']['source'])) {
                array_push($images, $sArticle['image']['source']);
            }
            if (!empty($sArticle['images'])) {
                foreach ($sArticle['images'] as $imagesItem) {
                    array_push($images, $imagesItem['source']);
                }
            }
            $data['imgURLs'] = $images;

            //Similar products
            $similarProductIDs = array();
            if (!empty($sArticle['sSimilarArticles'])) {
                $count = 1;
                foreach ($sArticle['sSimilarArticles'] as $similarArticle) {
                    if ($similarArticle['instock'] > 0 && $similarArticle['isAvailable']) {
                        array_push($similarProductIDs, $similarArticle['ordernumber']);
                        $count++;
                    }
                    if($count > 6) {
                        break;
                    }
                }
            }

            $data['similarProductIDs'] = $similarProductIDs;

            //Product description
            $data['description_long'] = $sArticle['description_long'];

            //Product details
            $data['description_details'] = $sArticle['plenty_connector_technical_description'];

            //Product package
            $data['description_package'] = $sArticle['plenty_connector_free1'];

            //Product video
            $data['description_video'] = $sArticle['plenty_connector_free3'];

            //Product reviews
            $reviewsItems = array();
            if (!empty($sArticle['sVoteComments'])) {
                foreach ($sArticle['sVoteComments'] as $sVoteComment) {
                    array_push($reviewsItems, array(
                        'user' => $sVoteComment['name'],
                        'rate' => $sVoteComment['points'],
                        'date' => $sVoteComment['datum'],
                        'text' => $sVoteComment['comment']
                    ));
                }
            }
            $data['reviews'] = $reviewsItems;

            echo $this->jsonHelper->returnJsonData($data);

        } else {

            $this->jsonHelper->returnError();

        }

        $this->debugger->insertLog($data, 'getFullProductDataAction');

    }

    public function getPreviewProductDataAction() {

        $this->shopRender();

        $productID = $this->request->getPost('productID');
        $sArticle = $this->article->getArticle($productID);

        if (!empty($sArticle)) {

            $companyPrice = $this->article->getNetPrice($sArticle['articleID']);
            if(!isset($companyPrice)) {
                $companyPrice = $sArticle['price'];
            }

            $data = array(
                'productName' => $sArticle['articleName'],
                'productSalePercent' => $sArticle['pseudopricePercent'],
                'stock' => $sArticle['instock'],
                'salePrice' => $this->article->formatPrice($sArticle['pseudoprice']),
                'price' => $this->article->formatPrice($sArticle['price']),
                'companyPrice' => $this->article->formatPrice($companyPrice),
                'rate' => $sArticle['sVoteAverage']['average'],
                'siteURL' => $sArticle['linkDetails'],
                'previewImgURL' => $sArticle['image']['thumbnails'][0]['source']
            );

            echo $this->jsonHelper->returnJsonData($data);

        } else {

            $this->jsonHelper->returnError();

        }

        $this->debugger->insertLog($data, 'getPreviewProductDataAction');

    }


}