<?php
namespace Ag\Login\Domain\Factory;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class AccountFactory {

	/**
	 * @var \TYPO3\Flow\Security\AccountFactory
	 * @Flow\Inject
	 */
	protected $accountFactory;

	/**
	 * @var \TYPO3\Flow\Validation\Validator\EmailAddressValidator
	 * @Flow\Inject
	 */
	protected $emailAddressValidator;

	/**
	 * @param string $email
	 * @param string $name
	 * @param string $password
	 * @param string $imageId
	 * @param string $imageId
	 * @throws \InvalidArgumentException
	 * @return \Ag\Login\Domain\Model\Account
	 */
	public function create($email, $name='', $password='', $imageId='') {
		$validation = $this->emailAddressValidator->validate($email);

		if($validation->hasErrors()) {
			throw new \InvalidArgumentException('Email "'.$email.'" is not valid.');
		}

		$password = trim($password);

		if(empty($password)) {
			$password = \Ag\Utility\TokenGenerator::generateToken(32);
		}

		$login = $this->accountFactory->createAccountWithPassword($email, $password);

		return new \Ag\Login\Domain\Model\Account($login, $name, $imageId);
	}
}
?>