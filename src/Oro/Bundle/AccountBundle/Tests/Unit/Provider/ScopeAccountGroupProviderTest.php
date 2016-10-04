<?php

namespace Oro\Bundle\AccountBundle\Tests\Unit\Provider;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AccountBundle\Entity\AccountAwareInterface;
use Oro\Bundle\AccountBundle\Entity\AccountGroup;
use Oro\Bundle\AccountBundle\Entity\AccountUser;
use Oro\Bundle\AccountBundle\Provider\ScopeAccountGroupCriteriaProvider;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class ScopeAccountGroupProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ScopeAccountGroupCriteriaProvider
     */
    private $provider;

    /**
     * @var SecurityFacade|\PHPUnit_Framework_MockObject_MockObject
     */
    private $securityFacade;

    protected function setUp()
    {
        $this->securityFacade = $this->getMockBuilder(SecurityFacade::class)->disableOriginalConstructor()->getMock();
        $this->provider = new ScopeAccountGroupCriteriaProvider($this->securityFacade);
    }

    public function testGetCriteriaForCurrentScope()
    {
        $accGroup = new AccountGroup();
        $accUser = new AccountUser();
        $account = new Account();
        $accUser->setAccount($account);
        $account->setGroup($accGroup);
        $this->securityFacade
            ->expects($this->once())
            ->method('getLoggedUser')
            ->willReturn($accUser);
        $actual = $this->provider->getCriteriaForCurrentScope();
        $this->assertEquals(['accountGroup' => $accGroup], $actual);
    }

    /**
     * @dataProvider contextDataProvider
     *
     * @param mixed $context
     * @param array $criteria
     */
    public function testGetCriteria($context, array $criteria)
    {
        $actual = $this->provider->getCriteriaByContext($context);
        $this->assertEquals($criteria, $actual);
    }

    /**
     * @return array
     */
    public function contextDataProvider()
    {
        $account = new Account();
        $accountGroup = new AccountGroup();
        $account->setGroup($accountGroup);
        $accountAware = $this->getMock(AccountAwareInterface::class);
        $accountAware->method('getAccount')->willReturn($account);
        $accountGroupAware = new \stdClass();
        $accountGroupAware->accountGroup = $accountGroup;

        return [
            'array_context_with_account_group_key' => [
                'context' => ['accountGroup' => $accountGroup],
                'criteria' => ['accountGroup' => $accountGroup],
            ],
            'array_context_with_account_group_key_invalid_value' => [
                'context' => ['accountGroup' => 123],
                'criteria' => [],
            ],
            'array_context_without_account_group_key' => [
                'context' => [],
                'criteria' => [],
            ],
            'object_context_account_group_aware' => [
                'context' => $accountGroupAware,
                'criteria' => ['accountGroup' => $accountGroup],
            ],
            'object_context_account_aware' => [
                'context' => $accountAware,
                'criteria' => ['accountGroup' => $accountGroup],
            ],
            'object_context_not_account_or_account_group_aware' => [
                'context' => new \stdClass(),
                'criteria' => [],
            ],
        ];
    }
}
