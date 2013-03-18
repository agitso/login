<?php
namespace Ag\Login\Tests\Unit\Domain\Model;
use \Mockery as m;
class AccountTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function canCreateSimpleAccount() {
		$accountDescriptor = $this->getSimpleAccount()->getDescriptor();

		$this->assertEquals('Henrik', $accountDescriptor->name);
		$this->assertEquals('info@hmrdesign.dk', $accountDescriptor->email);
		$this->assertCount(0, $accountDescriptor->roles);
	}

	/**
	 * @test
	 */
	public function canChangeName() {
		$account = $this->getSimpleAccount();
		$account->changeName('Anders');
		$this->assertEquals('Anders', $account->getDescriptor()->name);
	}

	/**
	 * @test
	 */
	public function canChangeImageId() {
		$account = $this->getSimpleAccount();
		$account->changeImage('789');
		$this->assertEquals('789', $account->getDescriptor()->imageId);
	}

	/**
	 * @test
	 */
	public function canAddSimpleRoles() {
		$account = $this->getSimpleAccount();

		$account->addRole(new \Ag\Login\Domain\Model\Role('Administrator'));
		$account->addRole(new \Ag\Login\Domain\Model\Role('Editor'));

		$d = $account->getDescriptor();
		$roles = $d->roles;

		$this->assertTrue($account->hasRole(new \Ag\Login\Domain\Model\Role('Administrator')));
		$this->assertTrue($account->hasRole(new \Ag\Login\Domain\Model\Role('Editor')));

		$this->assertFalse($account->hasRole(new \Ag\Login\Domain\Model\Role('Visitor')));

		$this->assertCount(2, $roles);

		$this->assertEquals($roles[0]->getRole(), 'Administrator');
		$this->assertNull($roles[0]->getClientId());
		$this->assertEquals($roles[1]->getRole(), 'Editor');
		$this->assertNull($roles[1]->getClientId());
	}

	/**
	 * @test
	 */
	public function canAddAdvancedRoles() {
		$account = $this->getSimpleAccount();

		$account->addRole(new \Ag\Login\Domain\Model\Role('Administrator', '1234'));
		$account->addRole(new \Ag\Login\Domain\Model\Role('Editor', '4321'));

		$d = $account->getDescriptor();
		$roles = $d->roles;

		$this->assertTrue($account->hasRole(new \Ag\Login\Domain\Model\Role('Administrator', '1234')));
		$this->assertTrue($account->hasRole(new \Ag\Login\Domain\Model\Role('Editor', '4321')));

		$this->assertFalse($account->hasRole(new \Ag\Login\Domain\Model\Role('Administrator', '4321')));

		$this->assertCount(2, $roles);

		$this->assertEquals($roles[0]->getRole(), 'Administrator');
		$this->assertEquals($roles[0]->getClientId(), '1234');
		$this->assertEquals($roles[1]->getRole(), 'Editor');
		$this->assertEquals($roles[1]->getClientId(), '4321');
	}

	/**
	 * @return \Ag\Login\Domain\Model\Account
	 */
	protected function getSimpleAccount() {
		$login = new \TYPO3\Flow\Security\Account();
		$login->setAccountIdentifier('info@hmrdesign.dk');

		$account = new \Ag\Login\Domain\Model\Account($login, 'Henrik', '1234');

		return $account;
	}

	public function tearDown() {
		m::close();
	}
}
?>