<?php

namespace Oro\Bundle\SaleBundle\Tests\Unit\Provider;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\PricingBundle\Model\ProductPriceCriteria;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteriaFactoryInterface;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteriaInterface;
use Oro\Bundle\PricingBundle\Provider\ProductPriceProviderInterface;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\ProductBundle\Tests\Unit\Entity\Stub\Product;
use Oro\Bundle\SaleBundle\Entity\Quote;
use Oro\Bundle\SaleBundle\Entity\QuoteProduct;
use Oro\Bundle\SaleBundle\Entity\QuoteProductOffer;
use Oro\Bundle\SaleBundle\Provider\QuoteProductPriceProvider;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Component\Testing\Unit\EntityTrait;

class QuoteProductPriceProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var QuoteProductPriceProvider */
    protected $quoteProductPriceProvider;

    /** @var ProductPriceScopeCriteriaFactoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $priceScopeCriteriaFactory;

    /** @var ProductPriceProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $productPriceProvider;

    protected function setUp()
    {
        $this->productPriceProvider = $this->createMock(ProductPriceProviderInterface::class);
        $this->priceScopeCriteriaFactory = $this->createMock(ProductPriceScopeCriteriaFactoryInterface::class);

        $this->quoteProductPriceProvider = new QuoteProductPriceProvider(
            $this->productPriceProvider,
            $this->priceScopeCriteriaFactory
        );
    }

    protected function tearDown()
    {
        unset($this->quoteProductPriceProvider, $this->productPriceProvider, $this->priceScopeCriteriaFactory);
    }

    /**
     * @dataProvider getTierPricesDataProvider
     * @param QuoteProduct[] $quoteProducts
     * @param array|null $products
     * @param int $tierPricesCount
     */
    public function testGetTierPrices($quoteProducts, $products, $tierPricesCount)
    {
        $quote = new Quote();
        $website = new Website();
        $customer = new Customer();
        $quote->setWebsite($website)->setCustomer($customer);

        foreach ($quoteProducts as $quoteProduct) {
            $quote->addQuoteProduct($quoteProduct);
        }

        if ($products) {
            $productScopeCriteria = $this->createMock(ProductPriceScopeCriteriaInterface::class);
            $this->priceScopeCriteriaFactory->expects($this->once())
                ->method('createByContext')
                ->with($quote)
                ->willReturn($productScopeCriteria);
            $this->productPriceProvider
                ->expects($this->once())
                ->method('getPricesByScopeCriteriaAndProducts')
                ->with($productScopeCriteria, $products)
                ->willReturn(range(0, $tierPricesCount - 1));
        } else {
            $this->productPriceProvider
                ->expects($this->never())
                ->method('getPricesByScopeCriteriaAndProducts');
        }

        $result = $this->quoteProductPriceProvider->getTierPrices($quote);

        $this->assertInternalType('array', $result);
        $this->assertCount($tierPricesCount, $result);
    }

    /**
     * @dataProvider getTierPricesDataProvider
     * @param QuoteProduct[] $quoteProducts
     * @param array|null $products
     * @param int $tierPricesCount
     */
    public function testGetTierPricesForProducts($quoteProducts, $products, $tierPricesCount)
    {
        $website = new Website();
        $customer = new Customer();

        $quote = new Quote();
        $quote->setWebsite($website)->setCustomer($customer);

        if ($products) {
            $productScopeCriteria = $this->createMock(ProductPriceScopeCriteriaInterface::class);
            $this->priceScopeCriteriaFactory->expects($this->once())
                ->method('createByContext')
                ->with($quote)
                ->willReturn($productScopeCriteria);
            $this->productPriceProvider
                ->expects($this->once())
                ->method('getPricesByScopeCriteriaAndProducts')
                ->with($productScopeCriteria, $products)
                ->willReturn(range(0, $tierPricesCount - 1));
        } else {
            $this->productPriceProvider
                ->expects($this->never())
                ->method('getPricesByScopeCriteriaAndProducts');
        }

        $result = $this->quoteProductPriceProvider->getTierPricesForProducts(
            $quote,
            array_filter(
                array_map(
                    function (QuoteProduct $quoteProduct) {
                        return $quoteProduct->getProduct() ? $quoteProduct->getProduct() : null;
                    },
                    $quoteProducts
                )
            )
        );

        $this->assertInternalType('array', $result);
        $this->assertCount($tierPricesCount, $result);
    }

    /**
     * @return array
     */
    public function getTierPricesDataProvider()
    {
        $quoteProduct = $this->getQuoteProduct();
        $emptyQuoteProduct = $this->getQuoteProduct('empty');

        $product = $quoteProduct->getProduct();

        return [
            'quote product with product' => [
                'quoteProducts' => [$quoteProduct],
                'products' => [$product],
                'tierPricesCount' => 1,
            ],
            'quote product without product' => [
                'quoteProducts' => [$emptyQuoteProduct],
                'products' => null,
                'tierPricesCount' => 0,
            ],
            'empty quote products' => [
                'quoteProducts' => [],
                'products' => null,
                'tierPricesCount' => 0,
            ],
        ];
    }

    /**
     * @dataProvider getMatchedPricesDataProvider
     * @param QuoteProduct[] $quoteProducts
     * @param array|null $productPriceCriteria
     * @param array $prices
     * @param array $expectedResult
     */
    public function testGetMatchedPrices($quoteProducts, $productPriceCriteria, $prices, $expectedResult)
    {
        $quote = new Quote();
        $website = new Website();
        $customer = new Customer();
        $quote->setWebsite($website)->setCustomer($customer);

        foreach ($quoteProducts as $quoteProduct) {
            $quote->addQuoteProduct($quoteProduct);
        }

        if ($productPriceCriteria) {
            $productScopeCriteria = $this->createMock(ProductPriceScopeCriteriaInterface::class);
            $this->priceScopeCriteriaFactory->expects($this->once())
                ->method('createByContext')
                ->with($quote)
                ->willReturn($productScopeCriteria);
            $this->productPriceProvider
                ->expects($this->once())
                ->method('getMatchedPrices')
                ->with($productPriceCriteria, $productScopeCriteria)
                ->willReturn($prices);
        } else {
            $this->productPriceProvider
                ->expects($this->never())
                ->method('getMatchedPrices');
        }

        $result = $this->quoteProductPriceProvider->getMatchedPrices($quote);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getMatchedPricesDataProvider()
    {
        $quoteProduct = $this->getQuoteProduct();
        $emptyQuoteProduct = $this->getQuoteProduct('empty');

        $product1 = $quoteProduct->getProduct();

        $quoteProductOffer1 = $quoteProduct->getQuoteProductOffers()->get(0);
        $quoteProductOffer2 = $quoteProduct->getQuoteProductOffers()->get(1);

        $productsPriceCriteria = [
            new ProductPriceCriteria(
                $product1,
                $quoteProductOffer1->getProductUnit(),
                $quoteProductOffer1->getQuantity(),
                $quoteProductOffer1->getPrice()->getCurrency()
            ),
        ];
        $productsPriceCriteria[] = new ProductPriceCriteria(
            $product1,
            $quoteProductOffer2->getProductUnit(),
            $quoteProductOffer2->getQuantity(),
            $quoteProductOffer2->getPrice()->getCurrency()
        );

        return [
            'quote product with product' => [
                'quoteProducts' => [$quoteProduct],
                'productPriceCriteria' => $productsPriceCriteria,
                'prices' => [
                    1 => Price::create(10, 'USD')
                ],
                'expectedResult' => [
                    1 => [
                        'value' => 10,
                        'currency' => 'USD'
                    ]
                ]
            ],
            'quote product with product and empty matched price' => [
                'quoteProducts' => [$quoteProduct],
                'productPriceCriteria' => $productsPriceCriteria,
                'prices' => [
                    1 => null
                ],
                'expectedResult' => [
                    1 => null
                ]
            ],
            'quote product without product' => [
                'quoteProducts' => [$emptyQuoteProduct],
                'productPriceCriteria' => null,
                'prices' => [],
                'expectedResult' => []
            ],
            'empty quote products' => [
                'quoteProducts' => [],
                'productPriceCriteria' => null,
                'prices' => [],
                'expectedResult' => []
            ],
        ];
    }

    /**
     * @param string $type
     * @return QuoteProduct
     */
    protected function getQuoteProduct($type = '')
    {
        $productUnit = new ProductUnit();
        $productUnit->setCode('kg');

        $price = new Price();
        $price->setCurrency('USD');

        $quoteProductOffer = new QuoteProductOffer();
        $quoteProductOffer->setProductUnit($productUnit);
        $quoteProductOffer->setQuantity(1);
        $quoteProductOffer->setPrice($price);

        $quoteProductOffer2 = new QuoteProductOffer();
        $quoteProductOffer2->setQuantity(2);

        $quoteProductOffer3 = new QuoteProductOffer();
        $quoteProductOffer3->setProductUnit($productUnit);

        /** @var Product $product1 */
        $product1 = $this->getEntity(Product::class, ['id' => 1]);

        switch ($type) {
            case 'empty':
                $quoteProduct = new QuoteProduct();
                break;
            default:
                $quoteProduct = new QuoteProduct();
                $quoteProduct->setProduct($product1);
                $quoteProduct->addQuoteProductOffer($quoteProductOffer);
                $quoteProduct->addQuoteProductOffer(clone($quoteProductOffer));
                $quoteProduct->addQuoteProductOffer($quoteProductOffer2);
                $quoteProduct->addQuoteProductOffer($quoteProductOffer3);
                break;
        }

        return $quoteProduct;
    }

    public function testHasEmptyPriceTrue()
    {
        $quote = new Quote();
        $quoteProduct = $this->getQuoteProduct();

        $quote->addQuoteProduct($quoteProduct);
        $this->assertTrue($this->quoteProductPriceProvider->hasEmptyPrice($quote));
    }

    public function testHasEmptyPriceFalse()
    {
        $quote = new Quote();
        $quoteProduct = new QuoteProduct();

        $productUnit = new ProductUnit();
        $productUnit->setCode('kg');

        $price = new Price();
        $price->setCurrency('USD');
        $price->setValue(12.345);

        $quoteProductOffer = new QuoteProductOffer();
        $quoteProductOffer->setProductUnit($productUnit);
        $quoteProductOffer->setQuantity(1);
        $quoteProductOffer->setPrice($price);
        $quoteProduct->addQuoteProductOffer($quoteProductOffer);

        $quote->addQuoteProduct($quoteProduct);
        $this->assertFalse($this->quoteProductPriceProvider->hasEmptyPrice($quote));
    }
}
