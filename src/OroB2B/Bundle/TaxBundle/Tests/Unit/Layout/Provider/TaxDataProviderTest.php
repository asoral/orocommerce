<?php

namespace OroB2B\Bundle\TaxBundle\Tests\Unit\Layout\Provider;

use OroB2B\Bundle\OrderBundle\Entity\Order;
use OroB2B\Bundle\TaxBundle\Layout\Provider\TaxDataProvider;
use OroB2B\Bundle\TaxBundle\Manager\TaxManager;
use OroB2B\Bundle\TaxBundle\Model\Result;

class TaxDataProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var TaxDataProvider */
    protected $provider;

    /** @var \PHPUnit_Framework_MockObject_MockObject|TaxManager */
    protected $taxManager;

    protected function setUp()
    {
        $this->taxManager = $this->getMockBuilder('OroB2B\Bundle\TaxBundle\Manager\TaxManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new TaxDataProvider($this->taxManager);
    }

    public function testGetTax()
    {
        $value = new Order();
        $result = new Result();
        $this->taxManager->expects($this->once())->method('loadTax')->with($value)->willReturn($result);

        $actual = $this->provider->getTax($value);

        $this->assertSame($result, $actual);
    }
}
