<?php


namespace ImportBooks;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Shopware\Components\DependencyInjection\Bridge\Models;
use Shopware\Models\Category\Category;
use Shopware\Models\Category\Repository;
use Shopware_Controllers_Backend_Category;

/**
 * Class CategoryConnector
 * @package ImportBooks
 */
class CategoryConnector extends \Shopware_Controllers_Backend_Category
{

    /**
     * Saves a single category. If no category id is passed,
     * the save function will create a new category model and persist
     * it.
     *
     * To successful saving a category a parent category id must supplied.
     */
    /** @param $category */
    public function saveDetail($category)
    {
        $params = [
            '_dc' => '',
            'node' => 3,
            'module' => "backend",
            'controller' => "category",
            'action' => "createDetail",
            'parent' => 0,
            'name' => $category,
            'active' => 1,
            'childrenCount' => 0,
            'text' => "",
            'cls' => '',
            'leaf' => '',
            'allowDrag' => '',
            'parentId' => 3
        ];
        $categoryId = $params['id'];

        if (empty($categoryId)) {
            $categoryModel = new Category();
            Shopware()->Models()->persist($categoryModel);

            // Find parent for newly created category
            $params['parentId'] = is_numeric($params['parentId']) ? (int)$params['parentId'] : 1;
            $parentCategory = Shopware()->Models()->getRepository('Shopware\Models\Category\Category')->find($params['parentId']);
            $categoryModel->setParent($parentCategory);

            // If Leaf-Category gets childcategory move all assignments to new childcategory
            if ($parentCategory->getChildren()->count() === 0 && $parentCategory->getArticles()->count() > 0) {
                /** @var $article \Shopware\Models\Article\Article * */
                foreach ($parentCategory->getArticles() as $article) {
                    $article->removeCategory($parentCategory);
                    $article->addCategory($categoryModel);
                }
            }
        } else {
            $categoryModel = Shopware()->Models()->getRepository('Shopware\Models\Category\Category')->find($categoryId);
        }

        $categoryModel->setStream(null);
        if ($params['streamId']) {
            $params['stream'] = Shopware()->Models()->find('Shopware\Models\ProductStream\ProductStream',
                $params['streamId']);
        }

        //$params = $this->prepareCustomerGroupsAssociatedData($params);
        //$params = $this->prepareMediaAssociatedData($params);

        unset($params['articles']);
        unset($params['emotion']);
        unset($params['imagePath']);
        unset($params['parentId']);
        unset($params['parent']);

        if (!array_key_exists('template', $params)) {
            $params['template'] = null;
        }

        $params['changed'] = new \DateTime();
        $categoryModel->fromArray($params);
        Shopware()->Models()->flush();

        $categoryId = $categoryModel->getId();
        /** @var  Repository $test */
        $test = Shopware()->Models()->getRepository(Repository::class);
        $query = $test->getBackendDetailQuery($categoryId)->getQuery();
        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        /** @var Paginator $paginator */
        $paginator = Shopware()->Container()->get('Models')->getModelManager()->createPaginator($query);
        $data = $paginator->getIterator()->getArrayCopy();
        $data = $data[0];
        $data['imagePath'] = $data['media']['path'];
        return true;
        //$this->View()->assign(['success' => true, 'data' => $data, 'total' => count($data)]);
    }


}
