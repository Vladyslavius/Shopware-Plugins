<?php

namespace ImportBooks;

use Doctrine\DBAL\DBALException;
use PHPExcel_Reader_CSV;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Models\Article\Article;
use Shopware\Models\Attribute\User;
use Shopware\Models\Category\Category;

require_once "Classes/PHPExcel.php";

class ImportBooks extends Plugin
{
    public $increment = 0;
    private $optionDictionary =
        [
            "gebraucht; neu" => "Neu",
            "gebraucht; wie neu" => "Wie neu",
            "gebraucht; sehr gut" => "Sehr gut",
            "gebraucht; gut" => "Gut",
            "gebraucht; akzeptabel" => "Akzeptabel",
            "gebraucht; ebook" => "Ebook",
        ];
    private $map_keys = [
        "Sachgebiet" => "categoryName",
        "Schlagwort" => "1",
        "Autor" => "author",
        "Titel" => "title",
        "Untertitel" => "2",
        "Verlag" => "supplier",
        "Format" => "format",
        "Bestellnummer" => "3",
        "Währung" => "euro",
        "Preis" => "price",
        "Auflage" => "empty",
        "Erscheinungsjahr" => "edition",
        "Beschreibung" => "description",
        "Stichworte" => "keywords",
        "Medium" => "4",
        "Sprache" => "language",
        "FSK" => "5",
        "Schutzumschlag" => "6",
        "Menge" => "stock",
        "Zustand" => "condition",
        "Signiert" => "7",
        "Erstausgabe" => "8",
        "Bibliophil" => "9",
        "ISBN" => "ordernumber",
        "Bildname" => "10",
        "MwSt" => "Var",
        "Gewicht" => "weight",
        "Bild1" => "image",
    ];
    private $dictionary =
        [
            "ar" => "Arabisch",
            "de" => "Deutsch",
            "en" => "Englisch",
            "es" => "Spanisch",
            "fr" => "Französisch",
            "it" => "Italienisch",
            "ja" => "Japanisch",
            "pl" => "Polnisch",
            "pt" => "Portugiesisch",
            "ru" => "Russisch",
            "sv" => "Schwedisch",
            "tr" => "Türkisch",
            "zh" => "Chinesisch",

        ];


    public static function getSubscribedEvents()
    {
        return [
            'Shopware_CronJob_MyCoolCron' => ['MyCoolCronRun'],
        ];
    }

    /**
     * @param \Shopware_Components_Cron_CronJob $job
     * @return bool
     */
    public
    function MyCoolCronRun(
        \Shopware_Components_Cron_CronJob $job
    ) {
        $apiClient = new ApiClient();
        $apiClient->callZip();

        $this->parseAllXlsFiles();
        return true;
    }

    public function parseAllXlsFiles()
    {
        set_time_limit(4800);
        $pathResult = $this->getPath();
        $arrayOfFiles = scandir($pathResult);
        if (count($arrayOfFiles)) {
            foreach ($arrayOfFiles as $file) {
                if ($file != "." && $file != "..") {
                    $fileParts = explode(".", $file);
                    $extension = array_pop($fileParts);
                    if ($extension == "xls" || $extension == "csv") {
                        $this->parseDocument($pathResult . '/' . $file);
                    }
                }
            }
        }
    }

    protected function parseDocument($documentName)
    {
        Shopware()->Db()->query("Update s_articles_details SET instock = 0");
        Shopware()->Db()->query("Update s_articles SET active = 0");
        $this->createCommonSupplier();

        $document = $documentName;

        $excelReader = new \PHPExcel_Reader_CSV();
        $excelReader->setDelimiter("~");
        $excelReader->setInputEncoding("windows-1252");
        $excelObj = $excelReader->load($document);
        $workSheet = $excelObj->getSheet(0);
        $excel_arr = $workSheet->toArray(null, false, false, true);
        $lastRow = $workSheet->getHighestRow();

        $startRow = 2;
        for ($row = $startRow;
             $row <= $lastRow; $row++) {
            $product = $this->mappedProduct($excel_arr, $row);
            if (empty($product['ordernumber'])) {
                continue;
            }
            try {
                $this->loadProductToDatabase($product);
            } catch (\Exception $ex) {
                $ex->getCode();
            }
            if ($row == $lastRow) {
                Shopware()->Db()->query("UPDATE s_article_configurator_sets SET type = 2");
                $file = $documentName;
                if (is_file($file)) {
                    unlink($file);
                }
                return;
            }
        }
    }

    public function generateManufacturer($productSupplier)
    {
        if (empty($productSupplier) || !isset($productSupplier)) {
            return Shopware()->Db()->fetchOne("SELECT id FROM s_articles_supplier WHERE `name` = 'unbekannt'");
        } else {
            $supplierAlreadyExist = Shopware()->Db()->fetchOne("SELECT id FROM s_articles_supplier WHERE `name` = '{$productSupplier}'");
            if (empty($supplierAlreadyExist) || !isset($supplierAlreadyExist)) {
                Shopware()->Db()->query("INSERT INTO s_articles_supplier(`name`,changed) VALUES('{$productSupplier}','2019-04-19 15:45:23')");
            }
            $supplierId = Shopware()->Db()->fetchOne("SELECT id FROM s_articles_supplier WHERE `name` = '{$productSupplier}'");
            return $supplierId;
        }
    }

    public function generateCategories($product)
    {
        $categoryName = $product['categoryName'];
        $isCategory = Shopware()->Db()->fetchOne("SELECT id FROM s_categories WHERE description = '{$categoryName}'");

        if (!$isCategory) {
            Shopware()->Db()->query("INSERT INTO s_categories(parent,`path`,`description`,`left`,`right`,`level`,added,changed,active,blog,hidefilter,hidetop,hide_sortings)
VALUES(3,'|3|','{$categoryName}',0,0,0,'2018-08-09 12:59:12','2018-08-09 12:59:12',1,0,0,0,0)");
        }
    }

    public function loadProductToDatabase($product)
    {

        $taxId = 4;
        if ($product["Var"] == 19) {
            $taxId = 1;
        }


        //$this->generateCategories($product);
        $categoryName = $product['categoryName'];
        $category = Shopware()->Db()->fetchOne("SELECT id FROM s_categories WHERE description = '{$categoryName}'");
        if (empty($category)) {
            $category = 3;
        }
        $supplierId = $this->generateManufacturer($product["supplier"]);


        $productData = [
            '_dc' => '',
            'module' => 'backend',
            'controller' => 'Article',
            'action' => 'save',
            'supplierId' => $supplierId,
            'supplierName' => "",
            'name' => $product['title'],
            'description' => '',
            'descriptionLong' => $product['description'],
            'metaTitle' => '',
            'keywords' => '',
            'active' => 1,
            'taxId' => $taxId,
            'highlight' => '',
            'pseudoSales' => 0,
            'priceGroupId' => '',
            'priceGroupActive' => '',
            'lastStock' => 1,
            'notification' => '',
            'template' => '',
            'autoNumber' => '',
            'avoidCustomerGroups' => array
            (),

            'isConfigurator' => 1,
            'categories' => array
            (
                '0' => array
                (
                    'id' => $category,
                )
            ),

            'seoCategories' => array
            (),

            'related' => array
            (),

            'similar' => array
            (),

            'images' => array
            (),

            'customerGroups' => array
            (),

            'mainPrices' => array
            (
                '0' => array
                (
                    'id' => 0,
                    'from' => 1,
                    'to' => 'Arbitrary',
                    'price' => $product['price'],
                    'pseudoPrice' => 0,
                    'percent' => 0,
                    'cloned' => '',
                    'customerGroupKey' => 'EK',
                    'customerGroup' => array
                    (),

                ),

            ),

            'mainDetail' => array
            (
                '0' => array
                (
                    'id' => '',
                    'articleId' => '',
                    'number' => $product['ordernumber'],
                    'additionalText' => '',
                    'supplierNumber' => '',
                    'active' => 1,
                    'inStock' => 0,
                    'stockMin' => 0,
                    'lastStock' => 1,
                    'ean' => '',
                    'weight' => (float)$product["weight"],
                    'width' => '',
                    'height' => '',
                    'len' => '',
                    'kind' => 1,
                    'position' => 0,
                    'releaseDate' => '',
                    'shippingTime' => '',
                    'shippingFree' => '',
                    'purchaseSteps' => '',
                    'minPurchase' => 1,
                    'maxPurchase' => '',
                    'unitId' => 2,
                    'purchaseUnit' => '',
                    'referenceUnit' => '',
                    'packUnit' => '',
                    'purchasePrice' => 0,
                    'price' => 0,
                    'standard' => '',
                    'prices' => array
                    (),
                    'configuratorOptions' => array
                    (),

                ),

            ),

            'links' => array
            (),

            'downloads' => array
            (),

            'configuratorSet' => array
            (),

            'configuratorTemplate' => array
            (),

            'dependencies' => array
            (),

            'streams' => array
            (),

        ];
        $refClass = new \ReflectionClass('ImportBooks\Connector');
        $instance = $refClass->newInstanceWithoutConstructor();
        $method = $refClass->getMethod('saveArticle');
        $method->setAccessible(true);
        $sqlChecker = "SELECT articleID FROM s_articles_details WHERE ordernumber = '{$productData['mainDetail'][0]['number']}'";

        $sqlCheckerResult = Shopware()->Db()->fetchOne($sqlChecker);
        if ((int)$sqlCheckerResult != null) {
            Shopware()->Db()->query("Update s_articles SET active = 1 WHERE id = '{$sqlCheckerResult}'");
            $this->updateProductData($product);
            return;
        } else {
            $article = new Article();
            $savedArticle = $method->invoke($instance, $productData, $article);

            $savedArticleId = $savedArticle[0]["mainDetailId"];
            $savedId = $savedArticle[0]["id"];

            $edition = $product["edition"];
            $keywords = $product["keywords"];
            $author = preg_replace("/[^ a-zA-Z]/", "", $product["author"]);
            $language = $product["language"];
            if (isset($this->dictionary[$language])) {
                $language = $this->dictionary[$language];
            }
            $isbn = $product['ordernumber'];
            $format = $product["format"];

            $formatArr = explode(" ", $format);
            $seitenZahl = "";
            if (in_array("Seiten", $formatArr)) {
                $seitenZahl = $formatArr[0] . " " . $formatArr[1];
                unset($formatArr[0]);
                unset($formatArr[1]);
                $format = implode(" ", $formatArr);
            }


            Shopware()->Db()->query("INSERT INTO s_articles_attributes(articleID,articledetailsID) VALUES('{$savedId}','{$savedArticleId}')");
        }

        $groupIdZustand = $this->getGroupId("Zustand");
        $setId = $this->getSetId($product['ordernumber']);
        if (!empty($product['condition'])) {
            $this->createRelationGroupSet($setId, $groupIdZustand);
            $this->generateOptions($groupIdZustand, "Neu");
            $this->generateOptions($groupIdZustand, "Wie neu");
            $this->generateOptions($groupIdZustand, "Sehr gut");
            $this->generateOptions($groupIdZustand, "Gut");
            $this->generateOptions($groupIdZustand, "Akzeptabel");
            $this->generateOptions($groupIdZustand, "Ebook");
        }

        $optionIdFirst = $this->getOptionId("Neu");
        $optionIdSecond = $this->getOptionId("Wie neu");
        $optionIdThird = $this->getOptionId("Sehr gut");
        $optionIdFourth = $this->getOptionId("Gut");
        $optionIdFifth = $this->getOptionId("Akzeptabel");
        $optionIdSixth = $this->getOptionId("Ebook");

        $this->organizeSetOptionRelation($setId, $optionIdFirst);
        $this->organizeSetOptionRelation($setId, $optionIdSecond);
        $this->organizeSetOptionRelation($setId, $optionIdThird);
        $this->organizeSetOptionRelation($setId, $optionIdFourth);
        $this->organizeSetOptionRelation($setId, $optionIdFifth);
        $this->organizeSetOptionRelation($setId, $optionIdSixth);
        $this->generateVariants($product['ordernumber']);

        $optionName = $this->optionDictionary[$product["condition"]];
        $optionId = $this->getOptionId($optionName);
        if (!empty($optionId)) {
            $index = array_search($product["condition"], array_keys($this->optionDictionary));
            if ($index != 0) {
                $test = $product["ordernumber"] . "." . $index;
                Shopware()->Db()->query("Update s_articles_details SET instock = '{$product["stock"]}' WHERE ordernumber = '{$test}'");
            } else {
                Shopware()->Db()->query("Update s_articles_details SET instock = '{$product["stock"]}' WHERE ordernumber = '{$product["ordernumber"]}'");
            }
        }

        $ids = Shopware()->Db()->fetchAll("SELECT id FROM s_articles_details WHERE articleID = '{$savedId}'");
        $formatedIds = [];
        foreach ($ids as $id) {
            array_push($formatedIds, $id["id"]);
        }

        $formatedIds = implode(",", $formatedIds);
        if (!empty($edition)) {
            Shopware()->Db()->query("UPDATE s_articles_attributes SET  exc_studibuch_appeared  = '{$edition}' WHERE articledetailsID IN($formatedIds)");
        }
        if (!empty($format)) {
            Shopware()->Db()->query("UPDATE s_articles_attributes SET  exc_studibuch_cover  = '{$format}' WHERE articledetailsID IN($formatedIds)");
        }
        if ($seitenZahl != "") {
            Shopware()->Db()->query("UPDATE s_articles_attributes SET  exc_studibuch_pages_count  = '{$seitenZahl}' WHERE articledetailsID IN($formatedIds)");
        }
        if (!empty($language)) {
            Shopware()->Db()->query("UPDATE s_articles_attributes SET  exc_studibuch_language  = '{$language}' WHERE articledetailsID IN($formatedIds)");
        }
        if (!empty($isbn)) {
            Shopware()->Db()->query("UPDATE s_articles_attributes SET  exc_studibuch_isbn  = '{$isbn}' WHERE articledetailsID IN($formatedIds)");
        }
        if (!empty($author)) {
            Shopware()->Db()->query("UPDATE s_articles_attributes SET  exc_studibuch_author_name  = '{$author}' WHERE articledetailsID IN($formatedIds)");
        }
        if (!empty($keywords)) {
            Shopware()->Db()->query("UPDATE s_articles_attributes SET  exc_studibuch_subjects  = '{$keywords}' WHERE articledetailsID IN($formatedIds)");
        }

        // SET THE IMAGE FOR THE PRODUCT
        $sqlChecker = "SELECT articleID FROM s_articles_details WHERE ordernumber = '{$productData['mainDetail'][0]['number']}'";
        $sqlCheckerResult = Shopware()->Db()->fetchOne($sqlChecker);
        $apiClient = new ApiClient();
        $apiClient->call($product["image"]);
        $this->setImage($product["image"], $sqlCheckerResult);
    }

    public function setImage($image, $articleId)
    {
        if (!empty($image)) {
            $mediaId = $this->uploadMedia($image);
            $this->setProductImageRelation($articleId, $image, $mediaId);
        }
    }

    public function setProductImageRelation($articleID, $img, $mediaId)
    {
        $position = Shopware()->Db()->fetchOne("SELECT position FROM s_articles_img WHERE articleID = '{$articleID}'ORDER BY id DESC LIMIT 1");
        if (empty($position)) {
            $position = 1;
        } else {
            $position += 1;
        }
        $imgParts = explode(".", $img);
        $imgName = $imgParts[0];
        $imgExtension = $imgParts[1];
        $imageAlreadyExist = Shopware()->Db()->fetchOne("SELECT id FROM s_articles_img WHERE img = '{$imgName}'");
        if (empty($imageAlreadyExist)) {
            Shopware()->Db()->query("INSERT INTO s_articles_img(articleID,img,main,position,width,height,extension,media_id) VALUES('{$articleID}','{$imgName}',
'{$position}','{$position}',0,0,'{$imgExtension}','{$mediaId}')");
        }
    }

    public function uploadMedia($image)
    {
        $basePath = str_replace("custom/plugins/ImportBooks", "", $this->getPath());
        $imagesPath = $basePath . $image;

        if (file_exists($imagesPath)) {
            $nameParts = explode(".", $image);
            $ext = array_pop($nameParts);
            $name = implode(".", $nameParts);
            list($width, $height, $type, $attr) = getimagesize($imagesPath);

            $imageNewPath = $basePath . "media/image/" . $this->prepareMd5Name("media/image/" . $image);
            $storagePath = $basePath . "media/image/" . $this->getMd5Path("media/image/" . $image);
            $this->prepareStorage($storagePath);

            if (copy($imagesPath, $imageNewPath)) {
                $sql = "INSERT INTO `s_media`(`albumID`, `name`, `description`, `path`, `type`, `extension`, `file_size`, `width`, `height`, `userID`, `created`)
					VALUES (-1, '" . $name . "', '', 'media/image/" . $image . "', 'IMAGE', '" . $ext . "', " . filesize($imagesPath) . ", " . $width . ", " . $height . ", 0, now())";
                Shopware()->Db()->query($sql);

                return Shopware()->Db()->lastInsertId();
            }
        }

        return false;
    }

    public function prepareStorage($path)
    {
        $pathParts = explode("/", $path);
        $currentPath = array_shift($pathParts);
        while (count($pathParts)) {
            $currentPath = $currentPath . "/" . array_shift($pathParts);
            if (!file_exists($currentPath)) {
                mkdir($currentPath);
            }
        }
    }

    public function prepareMd5Name($name)
    {
        $parts = explode("/", $name);
        $fileName = array_pop($parts);

        $md5Path = $this->getMd5Path($name);

        $return = $md5Path . "/" . $fileName;

        return $return;
    }

    public function getMd5Path($name)
    {
        $md5 = md5($name);
        $p1 = substr($md5, 0, 2);
        if ($p1 == "ad") {
            $p1 = "g0";
        }
        $p2 = substr($md5, 2, 2);
        if ($p2 == "ad") {
            $p2 = "g0";
        }
        $p3 = substr($md5, 4, 2);
        if ($p3 == "ad") {
            $p3 = "g0";
        }

        return $p1 . "/" . $p2 . "/" . $p3;
    }

    public function updateProductData($product)
    {
        error_log(print_r($product['ordernumber'],true));
        $optionName = $this->optionDictionary[$product["condition"]];
        $like = $product['ordernumber']."%";

        $optionId = $this->getOptionId($optionName);
        $productIds = Shopware()->Db()->fetchAll("SELECT id FROM s_articles_details WHERE ordernumber LIKE '{$like}'");

        $reformatProductIds = [];
        foreach ($productIds as $id) {
            array_push($reformatProductIds, $id["id"]);
        }

        $reformatProductIds = implode(",", $reformatProductIds);
        $particularItemId = Shopware()->Db()->fetchOne("SELECT article_id FROM s_article_configurator_option_relations WHERE option_id = '{$optionId}' AND article_id IN($reformatProductIds)");

        $totalStock = $product["stock"];
        Shopware()->Db()->query("UPDATE s_articles_details SET instock = '{$totalStock}' WHERE id = '{$particularItemId}'");
        $tax = $product["Var"];
        $price = $product["price"] - ($product["price"] / 100 * $tax);
        Shopware()->Db()->query("UPDATE s_articles_prices SET price = '{$price}' WHERE articledetailsID = '{$particularItemId}'");

    }

    /**
     * @param $arr
     * @return array
     */
    public
    function createTemplate(
        $arr
    ) {
        $this->increment += 1;
        $Zustand = "Zustand";

        $ZustandId = Shopware()->Db()->fetchOne("SELECT id FROM s_article_configurator_groups WHERE name = '{$Zustand}'");

        foreach ($arr as &$item) {
            $item["active"] = 1;
            $item["articleId"] = 0;

        }
        $array =
            [
                '0' => array
                (
                    'id' => $ZustandId,
                    'active' => 1,
                    'name' => $Zustand,
                    'description' => '',
                    'position' => $this->increment,
                    'options' => $arr
                ),
            ];
        return $array;
    }

    /**
     * @param $orderNumber
     */
    public function generateVariants($orderNumber)
    {

        $sql = Shopware()->Db()->fetchAll("SELECT * FROM s_article_configurator_options WHERE id IN (SELECT option_id FROM s_article_configurator_set_option_relations
WHERE set_id IN(SELECT id FROM s_article_configurator_sets WHERE s_article_configurator_sets.name='Set-{$orderNumber}'))");
        $groups = $this->createTemplate($sql);
        $refClass = new \ReflectionClass('ImportBooks\Connector');
        $instance = $refClass->newInstanceWithoutConstructor();
        $method = $refClass->getMethod('createConfiguratorVariants');
        $method->setAccessible(true);
        $sql = Shopware()->Db()->fetchOne("SELECT articleID FROM s_articles_details WHERE ordernumber='{$orderNumber}'");
        $method->invoke($instance, $sql, $groups, 0, 50, 1);
    }

    /**
     * @param $setId
     * @param $optionId
     * @return \Zend_Db_Statement_Pdo
     */
    public function organizeSetOptionRelation($setId, $optionId)
    {
        $sqlCheck = "SELECT COUNT(*) FROM s_article_configurator_set_option_relations WHERE set_id ='{$setId}' AND option_id = '{$optionId}'";
        $sqlCheckRes = Shopware()->Db()->fetchOne($sqlCheck);
        if ($sqlCheckRes < 1) {
            $sql = "INSERT INTO s_article_configurator_set_option_relations VALUES('{$setId}','{$optionId}')";
            return Shopware()->Db()->executeQuery($sql);
        }
    }

    /**
     * @param $optionName
     * @return string
     */
    public
    function getOptionId(
        $optionName
    ) {
        $sql = "SELECT id FROM s_article_configurator_options WHERE name='{$optionName}'";
        return Shopware()->Db()->fetchOne($sql);
    }

    /**
     * @param $setId
     * @param $groupId
     */
    public
    function createRelationGroupSet(
        $setId,
        $groupId
    ) {
        $sql = "INSERT INTO s_article_configurator_set_group_relations VALUES ('{$setId}','{$groupId}')";
        Shopware()->Db()->executeQuery($sql);
    }

    /**
     * @param $groupId
     * @param $name
     */
    private
    function generateOptions(
        $groupId,
        $name
    ) {
        $sql = "SELECT COUNT(*) FROM s_article_configurator_options WHERE group_id = '{$groupId}' AND name = '{$name}' ";
        $count = Shopware()->Db()->fetchOne($sql);
        if ($count < 1) {
            $this->increment += 1;
            $sqlSet = "INSERT INTO s_article_configurator_options(group_id, name,position) VALUES('{$groupId}','{$name}','{$this->increment}')";
            Shopware()->Db()->executeQuery($sqlSet);
        }
    }

    /**
     * @param $nameOfSet
     * @return string
     */
    public function getSetId($nameOfSet)
    {
        $sql = "SELECT id FROM s_article_configurator_sets WHERE name = 'Set-{$nameOfSet}'";
        return Shopware()->Db()->fetchOne($sql);
    }

    /**
     * @param $group
     * @return string
     */
    public function getGroupId($group)
    {
        $sql = "SELECT id FROM s_article_configurator_groups WHERE name = '{$group}'";
        return Shopware()->Db()->fetchOne($sql);
    }

    /**
     * @param $excel_arr
     * @param $row
     * @return array
     */
    public function mappedProduct($excel_arr, $row)
    {
        $product = [];
        foreach ($excel_arr[$row] as $key => $col) {
            if ($excel_arr[1][$key]) {
                $product[$this->map_keys[$excel_arr[1][$key]]] = $col;

            }
        }
        return $product;
    }

    public function createCommonSupplier()
    {
        $commonSupplier = 'unbekannt';
        $commonSupplierId = Shopware()->Db()->fetchOne("SELECT id FROM s_articles_supplier WHERE `name` = 'unbekannt'");
        if (empty($commonSupplierId) || !isset($commonSupplierId)) {
            Shopware()->Db()->query("INSERT INTO s_articles_supplier(`name`,changed) VALUES('unbekannt','2019-04-19 15:45:23')");
        }
    }

    public function install(InstallContext $context)
    {

        $this->addCron('MyCoolCron');
        parent::install($context); // TODO: Change the autogenerated stub
    }

    public function addCron($cronName)
    {
        $connection = $this->container->get('dbal_connection');
        $connection->insert(
            's_crontab',
            [
                'name' => $cronName,
                'action' => $cronName,
                'next' => new \DateTime(),
                'start' => null,
                '`interval`' => '100',
                'active' => 1,
                'end' => new \DateTime(),
                'pluginID' => null
            ],
            [
                'next' => 'datetime',
                'end' => 'datetime',
            ]

        );
    }

    /**
     * @param $cronName
     */
    public function removeCron($cronName)
    {
        try {
            $this->container->get('dbal_connection')->executeQuery('DELETE FROM s_crontab WHERE `name` = ?', [
                $cronName
            ]);
        } catch (DBALException $exception) {
            error_log(print_r($exception->getLine(), true));
        }

    }

    public function uninstall(UninstallContext $context)
    {
        $this->removeCron("MyCoolCron");
    }


}