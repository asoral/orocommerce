<?php

namespace OroB2B\Bundle\CatalogBundle\EventListener;

use OroB2B\Bundle\ProductBundle\ImportExport\Event\ProductNormalizerEvent;

class ProductNormalizerEventListener extends AbstractProductImportEventListener
{
    /**
     * @param ProductNormalizerEvent $event
     */
    public function onNormalize(ProductNormalizerEvent $event)
    {
        $context = $event->getContext();
        if (isset($context['fieldName']) && isset($context['mode'])) {
            // It's a related Product entity (like variantLinks)
            return;
        }

        $category = $this->getCategoryByProduct($event->getProduct(), true);
        if (!$category) {
            return;
        }

        $data = $event->getPlainData();
        $data[self::CATEGORY_KEY] = $category->getDefaultTitle();
        $event->setPlainData($data);
    }
}
