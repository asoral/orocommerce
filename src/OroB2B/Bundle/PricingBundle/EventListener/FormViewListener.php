<?php

namespace OroB2B\Bundle\PricingBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\UIBundle\View\ScrollData;

use OroB2B\Bundle\CustomerBundle\Entity\Customer;
use OroB2B\Bundle\CustomerBundle\Entity\CustomerGroup;
use OroB2B\Bundle\PricingBundle\Entity\Repository\PriceListRepository;

class FormViewListener
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param TranslatorInterface $translator
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(TranslatorInterface $translator, DoctrineHelper $doctrineHelper)
    {
        $this->translator = $translator;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param BeforeListRenderEvent $event
     */
    public function onCustomerView(BeforeListRenderEvent $event)
    {
        if (!$this->request) {
            return;
        }

        $customerId = $this->request->get('id');
        /** @var Customer $customer */
        $customer = $this->doctrineHelper->getEntityReference('OroB2BCustomerBundle:Customer', $customerId);
        $priceList = $this->getPriceListRepository()->getPriceListByCustomer($customer);

        if ($priceList) {
            $template = $event->getEnvironment()->render(
                'OroB2BPricingBundle:Customer:price_list_view.html.twig',
                ['priceList' => $priceList]
            );
            $event->getScrollData()->addSubBlockData(0, 0, $template);
        }
    }

    /**
     * @param BeforeListRenderEvent $event
     */
    public function onCustomerGroupView(BeforeListRenderEvent $event)
    {
        if (!$this->request) {
            return;
        }

        $groupId = $this->request->get('id');
        /** @var CustomerGroup $group */
        $group = $this->doctrineHelper->getEntityReference('OroB2BCustomerBundle:CustomerGroup', $groupId);
        $priceList = $this->getPriceListRepository()->getPriceListByCustomerGroup($group);

        if ($priceList) {
            $template = $event->getEnvironment()->render(
                'OroB2BPricingBundle:Customer:price_list_view.html.twig',
                ['priceList' => $priceList]
            );
            $event->getScrollData()->addSubBlockData(0, 0, $template);
        }
    }

    /**
     * @param BeforeListRenderEvent $event
     */
    public function onEntityEdit(BeforeListRenderEvent $event)
    {
        $template = $event->getEnvironment()->render(
            'OroB2BPricingBundle:Customer:price_list_update.html.twig',
            ['form' => $event->getFormView()]
        );
        $event->getScrollData()->addSubBlockData(0, 0, $template);
    }

    /**
     * @param BeforeListRenderEvent $event
     */
    public function onProductView(BeforeListRenderEvent $event)
    {
        if (!$this->request) {
            return;
        }

        $productId = $this->request->get('id');
        /** @var Customer $customer */
        $product = $this->doctrineHelper->getEntityReference('OroB2BProductBundle:Product', $productId);

        $template = $event->getEnvironment()->render(
            'OroB2BPricingBundle:Product:prices_view.html.twig',
            ['entity' => $product]
        );
        $this->addProductPricesBlock($event->getScrollData(), $template);
    }

    /**
     * @param BeforeListRenderEvent $event
     */
    public function onProductEdit(BeforeListRenderEvent $event)
    {
        $template = $event->getEnvironment()->render(
            'OroB2BPricingBundle:Product:prices_update.html.twig',
            ['form' => $event->getFormView()]
        );
        $this->addProductPricesBlock($event->getScrollData(), $template);
    }

    /**
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return PriceListRepository
     */
    protected function getPriceListRepository()
    {
        return $this->doctrineHelper->getEntityRepository('OroB2BPricingBundle:PriceList');
    }

    /**
     * @param ScrollData $scrollData
     * @param string $html
     */
    protected function addProductPricesBlock(ScrollData $scrollData, $html)
    {
        $blockLabel = $this->translator->trans('orob2b.pricing.productprice.entity_plural_label');
        $blockId = $scrollData->addBlock($blockLabel);
        $subBlockId = $scrollData->addSubBlock($blockId);
        $scrollData->addSubBlockData($blockId, $subBlockId, $html);
    }
}
