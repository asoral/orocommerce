<?php

namespace Oro\Bundle\SEOBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Temporary table for storing products ids to limit sitemap generator to.
 *
 * @ORM\Table(name="oro_web_catalog_product_limit")
 * @ORM\Entity()
 */
class WebCatalogProductLimitation
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", name="product_id")
     */
    protected $productId;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", name="version")
     */
    protected $version;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param int $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }
}
