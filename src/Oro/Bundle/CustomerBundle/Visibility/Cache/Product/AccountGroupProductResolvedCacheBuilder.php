<?php

namespace Oro\Bundle\CustomerBundle\Visibility\Cache\Product;

use Doctrine\ORM\EntityManagerInterface;

use Oro\Bundle\CustomerBundle\Entity\Visibility\AccountGroupProductVisibility;
use Oro\Bundle\CustomerBundle\Entity\Visibility\VisibilityInterface;
use Oro\Bundle\CustomerBundle\Entity\VisibilityResolved\BaseProductVisibilityResolved;
use Oro\Bundle\CustomerBundle\Entity\VisibilityResolved\Repository\AccountGroupProductRepository;
use Oro\Bundle\CustomerBundle\Visibility\Cache\ProductCaseCacheBuilderInterface;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class AccountGroupProductResolvedCacheBuilder extends AbstractResolvedCacheBuilder implements
    ProductCaseCacheBuilderInterface
{
    /**
     * @param VisibilityInterface|AccountGroupProductVisibility $visibilitySettings
     */
    public function resolveVisibilitySettings(VisibilityInterface $visibilitySettings)
    {
        $product = $visibilitySettings->getProduct();
        $website = $visibilitySettings->getWebsite();
        $accountGroup = $visibilitySettings->getAccountGroup();

        $selectedVisibility = $visibilitySettings->getVisibility();
        $visibilitySettings = $this->refreshEntity($visibilitySettings);

        $insert = false;
        $delete = false;
        $update = [];
        $where = ['accountGroup' => $accountGroup, 'website' => $website, 'product' => $product];

        $em = $this->registry
            ->getManagerForClass('OroCustomerBundle:VisibilityResolved\AccountGroupProductVisibilityResolved');
        $er = $em->getRepository('OroCustomerBundle:VisibilityResolved\AccountGroupProductVisibilityResolved');
        $hasAccountGroupProductVisibilityResolved = $er->hasEntity($where);

        if (!$hasAccountGroupProductVisibilityResolved
            && $selectedVisibility !== AccountGroupProductVisibility::CURRENT_PRODUCT
        ) {
            $insert = true;
        }

        if ($selectedVisibility === AccountGroupProductVisibility::CATEGORY) {
            $category = $this->registry
                ->getManagerForClass('OroCatalogBundle:Category')
                ->getRepository('OroCatalogBundle:Category')
                ->findOneByProduct($product);
            if ($category) {
                $visibility = $this->registry
                    ->getManagerForClass(
                        'OroCustomerBundle:VisibilityResolved\AccountGroupCategoryVisibilityResolved'
                    )
                    ->getRepository('OroCustomerBundle:VisibilityResolved\AccountGroupCategoryVisibilityResolved')
                    ->getFallbackToGroupVisibility($category, $accountGroup);
                $update = [
                    'sourceProductVisibility' => $visibilitySettings,
                    'visibility' => $visibility,
                    'source' => BaseProductVisibilityResolved::SOURCE_CATEGORY,
                    'category' => $category
                ];
            } else {
                // default fallback
                if ($hasAccountGroupProductVisibilityResolved) {
                    $delete = true;
                }
            }
        } elseif ($selectedVisibility === AccountGroupProductVisibility::CURRENT_PRODUCT) {
            if ($hasAccountGroupProductVisibilityResolved) {
                $delete = true;
            }
        } else {
            $update = $this->resolveStaticValues($selectedVisibility, $visibilitySettings);
        }

        $this->executeDbQuery($er, $insert, $delete, $update, $where);
        $this->triggerProductReindexation($product, $website);
    }

    /**
     * {@inheritdoc}
     */
    public function isVisibilitySettingsSupported(VisibilityInterface $visibilitySettings)
    {
        return $visibilitySettings instanceof AccountGroupProductVisibility;
    }

    /**
     * {@inheritdoc}
     */
    public function productCategoryChanged(Product $product)
    {
        $category = $this->registry
            ->getManagerForClass('OroCatalogBundle:Category')
            ->getRepository('OroCatalogBundle:Category')
            ->findOneByProduct($product);
        if (!$category) {
            $this->registry
                ->getManagerForClass('OroCustomerBundle:Visibility\AccountGroupProductVisibility')
                ->getRepository('OroCustomerBundle:Visibility\AccountGroupProductVisibility')
                ->setToDefaultWithoutCategoryByProduct($product);
        }

        $this->getRepository()->deleteByProduct($product);
        $this->getRepository()->insertByProduct($product, $this->insertFromSelectQueryExecutor, $category);
    }

    /**
     * {@inheritdoc}
     */
    public function buildCache(Website $website = null)
    {
        $this->getManager()->beginTransaction();
        try {
            $repository = $this->getRepository();
            $repository->clearTable($website);
            $repository->insertStatic($this->insertFromSelectQueryExecutor, $website);
            $repository->insertByCategory($this->insertFromSelectQueryExecutor, $website);
            $this->getManager()->commit();
        } catch (\Exception $exception) {
            $this->getManager()->rollback();
            throw $exception;
        }
    }

    /**
     * @return AccountGroupProductRepository
     */
    protected function getRepository()
    {
        return $this
            ->getManager()
            ->getRepository('OroCustomerBundle:VisibilityResolved\AccountGroupProductVisibilityResolved');
    }

    /**
     * @return EntityManagerInterface|null
     */
    protected function getManager()
    {
        return $this->registry
            ->getManagerForClass('OroCustomerBundle:VisibilityResolved\AccountGroupProductVisibilityResolved');
    }
}

