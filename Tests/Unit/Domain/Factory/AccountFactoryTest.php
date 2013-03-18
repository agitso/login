<?php
namespace Ag\Login\Tests\Unit\Domain\Factory;
use \Mockery as m;
class AccountFactoryTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var \Ag\Login\Domain\Factory\AccountFactory
	 */
	protected $factory;

	public function setUp() {
		$this->factory = new \Ag\Login\Domain\Factory\AccountFactory();

		$loginFactory = m::mock('\TYPO3\Flow\Security\AccountFactory');
		$loginFactory->shouldReceive('createAccountWithPassword')->andReturnUsing(function($email, $password) {
			$login = new \TYPO3\Flow\Security\Account();
			$login->setAccountIdentifier($email);
			return $login;
		});

		\TYPO3\Flow\Reflection\ObjectAccess::setProperty($this->factory, 'accountFactory', $loginFactory, TRUE);
		\TYPO3\Flow\Reflection\ObjectAccess::setProperty($this->factory, 'emailAddressValidator', new \TYPO3\Flow\Validation\Validator\EmailAddressValidator(), TRUE);
	}

	/**
	 * @test
	 */
	public function canCreateSimpleAccount() {
		$account = $this->factory->create('info@hmrdesign.dk', 'Henrik', '1234', 'ABCD');

		$d = $account->getDescriptor();

		$this->assertEquals('Henrik', $d->name);
		$this->assertEquals('ABCD', $d->imageId);
		$this->assertEquals('info@hmrdesign.dk', $d->email);

	}

	/**
	 * @expectedException \InvalidArgumentException
	 * @test
	 */
	public function invalidEmailThrowsException(){
		$this->factory->create('info');
	}

	public function tearDown() {
		m::close();
	}
}
?>