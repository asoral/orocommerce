<?php

namespace Oro\Bundle\InventoryBundle\Tests\Unit\Validator;

use Oro\Bundle\CheckoutBundle\Entity\CheckoutLineItem;
use Oro\Bundle\InventoryBundle\Provider\ProductUpcomingProvider;
use Oro\Bundle\InventoryBundle\Validator\UpcomingLabelCheckoutLineItemValidator;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter;
use Oro\Bundle\ProductBundle\Entity\Product;
use Symfony\Component\Translation\TranslatorInterface;

class UpcomingLabelCheckoutLineItemValidatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var  UpcomingLabelCheckoutLineItemValidator */
    private $validator;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var ProductUpcomingProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $provider;

    /** @var DateTimeFormatter|\PHPUnit\Framework\MockObject\MockObject */
    private $dateFormatter;

    protected function setUp()
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->provider = $this->createMock(ProductUpcomingProvider::class);
        $this->dateFormatter = $this->createMock(DateTimeFormatter::class);

        $this->validator = new UpcomingLabelCheckoutLineItemValidator(
            $this->provider,
            $this->translator,
            $this->dateFormatter
        );
    }

    public function testValidateLineItemWithUpcomingProductWithoutDate()
    {
        $product = $this->createMock(Product::class);

        $lineItem = $this->createMock(CheckoutLineItem::class);
        $lineItem->expects($this->once())->method('getProduct')->willReturn($product);

        $this->provider->expects($this->once())->method('isUpcoming')->with($product)->willReturn(true);
        $this->provider->expects($this->once())->method('getAvailabilityDate')->with($product)
            ->willReturn(null);

        $message = 'This product will be available later';
        $this->translator->expects($this->once())
            ->method('trans')
            ->with('oro.inventory.is_upcoming.notification')
            ->willReturn($message);

        $this->assertSame($message, $this->validator->getMessageIfLineItemUpcoming($lineItem));
    }

    public function testValidateLineItemWithUpcomingProductWithDate()
    {
        $product = $this->createMock(Product::class);

        $lineItem = $this->createMock(CheckoutLineItem::class);
        $lineItem->expects($this->once())->method('getProduct')->willReturn($product);

        $today = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->provider->expects($this->once())->method('isUpcoming')->with($product)->willReturn(true);
        $this->provider->expects($this->once())->method('getAvailabilityDate')->with($product)
            ->willReturn($today);

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('oro.inventory.is_upcoming.notification_with_date')
            ->willReturn('This product will be available on 1/1/19');

        $this->dateFormatter->expects($this->once())->method('formatDate')->with($today)->willReturn('1/1/19');

        $this->assertSame(
            'This product will be available on 1/1/19',
            $this->validator->getMessageIfLineItemUpcoming($lineItem)
        );
    }

    public function testValidateLineItemWithoutUpcomingProduct()
    {
        $product = $this->createMock(Product::class);

        $lineItem = $this->createMock(CheckoutLineItem::class);
        $lineItem->expects($this->once())->method('getProduct')->willReturn($product);

        $this->provider->expects($this->once())->method('isUpcoming')->with($product)->willReturn(false);

        $this->assertNull($this->validator->getMessageIfLineItemUpcoming($lineItem));
    }
}
