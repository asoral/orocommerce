<?php

namespace Oro\Bundle\CheckountBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Migrations\Data\ORM\LoadCustomerUserRoles;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractUpdatePermissions;

/**
 * Updates permissions for Checkout entity for ROLE_FRONTEND_ADMINISTRATOR storefront role.
 */
class UpdateFrontendAdministratorPermissionsForCheckout extends AbstractUpdatePermissions implements
    DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadCustomerUserRoles::class];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $aclManager = $this->getAclManager();
        if (!$aclManager->isAclEnabled()) {
            return;
        }

        $this->setEntityPermissions(
            $aclManager,
            $this->getRole($manager, 'ROLE_FRONTEND_ADMINISTRATOR', CustomerUserRole::class),
            Checkout::class,
            ['VIEW_DEEP', 'CREATE_DEEP', 'EDIT_DEEP', 'DELETE_DEEP', 'ASSIGN_DEEP']
        );
        $aclManager->flush();
    }
}
