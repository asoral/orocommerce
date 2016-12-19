<?php

namespace Oro\Bundle\ShippingBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRuleDestinationPostalCode;
use Symfony\Component\Form\DataTransformerInterface;

class DestinationPostalCodeTransformer implements DataTransformerInterface
{
    /**
     * @param ArrayCollection|ShippingMethodsConfigsRuleDestinationPostalCode[] $postalCodes
     * @return string
     */
    public function transform($postalCodes)
    {
        $postalCodesString = '';
        foreach ($postalCodes as $postalCode) {
            $postalCodesString .= $postalCode->getName() . ', ';
        }
        $postalCodesString = rtrim($postalCodesString, ', ');

        return $postalCodesString;
    }

    /**
     * @param string $postalCodesString
     * @return ArrayCollection|ShippingMethodsConfigsRuleDestinationPostalCode[]
     */
    public function reverseTransform($postalCodesString)
    {
        $postalCodes = new ArrayCollection();

        $postalCodeNames = explode(',', $postalCodesString);
        foreach ($postalCodeNames as $postalCodeName) {
            $postalCode = new ShippingMethodsConfigsRuleDestinationPostalCode();

            $postalCode->setName(trim($postalCodeName));
            $postalCodes->add($postalCode);
        }

        return $postalCodes;
    }
}
