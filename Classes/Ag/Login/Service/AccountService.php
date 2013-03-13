<?php
namespace Ag\Login\Service;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class AccountService {

	/**
	 * @var \Ag\Login\Domain\Repository\AccountRepository
	 * @Flow\Inject
	 */
	protected $accountRepository;

	/**
	 * @var \Ag\Login\Domain\Factory\AccountFactory
	 * @Flow\Inject
	 */
	protected $accountFactory;

	/**
	 * @var \TYPO3\Flow\Security\Cryptography\HashService
	 * @Flow\Inject
	 */
	protected $hashService;

	/**
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 * @Flow\Inject
	 */
	protected $persistenceManager;

	/**
	 * @param string $email
	 * @param string $name
	 * @param string $password
	 * @param string $imageId
	 * @return \Ag\Login\Domain\Model\AccountDescriptor
	 * @throws \InvalidArgumentException
	 */
	public function createAccount($email, $name, $password = '', $imageId = '') {
		$account = $this->accountRepository->findOneByEmail($email);

		if (!empty($account)) {
			throw new \InvalidArgumentException('Account "' . $email . '" already exists');
		}

		return $this->createAccountHelper($email, $name, $password, $imageId);
	}

	/**
	 * @param string $email
	 * @param string $name
	 * @param string $imageId
	 * @return \Ag\Login\Domain\Model\AccountDescriptor
	 * @throws \InvalidArgumentException
	 */
	public function updateAccountInfo($email, $name = NULL, $imageId = NULL) {
		$account = $this->getAccountByEmailThrowExceptionIfNotExistsing($email);

		return $this->updateAccountHelper($account, $name, $imageId);
	}


	/**
	 * Create account or update account depending on if it exists or not
	 *
	 * @param string $email
	 * @param string $name
	 * @param string $password
	 * @param string $imageId
	 * @return \Ag\Login\Domain\Model\AccountDescriptor
	 */
	public function upsertAccount($email, $name, $password = NULL, $imageId = NULL) {
		$account = $this->accountRepository->findOneByEmail($email);
		if (empty($account)) {
			return $this->createAccountHelper($email, $name, $password, $imageId);
		} else {
			return $this->updateAccountHelper($account, $name, $imageId);
		}
	}

	/**
	 * @param string $email
	 * @param string $name
	 * @param string $password
	 * @param string $imageId
	 * @return \Ag\Login\Domain\Model\AccountDescriptor
	 */
	protected function createAccountHelper($email, $name, $password, $imageId) {
		$account = $this->accountFactory->create($email, $name, $password, $imageId);

		$this->accountRepository->add($account);

		$this->persistenceManager->persistAll();

		return $account->getDescriptor();
	}

	/**
	 * @param \Ag\Login\Domain\Model\Account $account
	 * @param string $name
	 * @param string $imageId
	 * @return \Ag\Login\Domain\Model\AccountDescriptor
	 */
	protected function updateAccountHelper($account, $name, $imageId) {
		if ($name !== NULL) {
			$account->changeName($name);
		}

		if ($imageId !== NULL) {
			$account->changeImage($imageId);
		}

		$this->accountRepository->update($account);
		$this->persistenceManager->persistAll();

		return $account->getDescriptor();
	}


	/**
	 * @param string $email
	 * @param string $newPassword
	 * @throws \InvalidArgumentException
	 */
	public function changePassword($email, $newPassword) {
		$account = $this->getAccountByEmailThrowExceptionIfNotExistsing($email);

		$account->changePassword($newPassword, $this->hashService);

		$this->accountRepository->update($account);
		$this->persistenceManager->persistAll();
	}

	/**
	 * @param string $email
	 * @return \Ag\Login\Domain\Model\AccountDescriptor
	 */
	public function getAccountByEmail($email) {
		$account = $this->accountRepository->findOneByEmail($email);

		if (empty($account)) {
			return NULL;
		}

		return $account->getDescriptor();
	}

	/**
	 * @param string $accountId
	 * @return \Ag\Login\Domain\Model\AccountDescriptor
	 */
	public function getAccountByAccountId($accountId) {
		$account = $this->accountRepository->findByIdentifier($accountId);

		if (empty($account)) {
			return NULL;
		}

		return $account->getDescriptor();
	}

	/**
	 * @param string $email
	 * @param string $role
	 * @param string $key Optional key. This could fx be a client id
	 * @return \Ag\Login\Domain\Model\AccountDescriptor
	 */
	public function addRoleToAccount($email, $role, $key = '') {
		$account = $this->getAccountByEmailThrowExceptionIfNotExistsing($email);

		$account->addRole($role, $key);

		$this->accountRepository->update($account);
		$this->persistenceManager->persistAll();

		return $account->getDescriptor();
	}

	/**
	 * @param string $email
	 * @param string $role
	 * @param string $key Optional key. This could fx be a client id
	 * @return \Ag\Login\Domain\Model\AccountDescriptor
	 */
	public function removeRoleFromAccount($email, $role, $key = '') {
		$account = $this->getAccountByEmailThrowExceptionIfNotExistsing($email);

		$account->removeRole($role, $key);

		$this->accountRepository->update($account);
		$this->persistenceManager->persistAll();

		return $account->getDescriptor();
	}

	/**
	 * @param string $email
	 * @return array
	 */
	public function getRolesForAccount($email) {
		$account = $this->accountRepository->findOneByEmail($email);

		if (empty($account)) {
			return array();
		}

		return $account->getRoles();
	}

	/**
	 * @param string $email
	 * @throws \InvalidArgumentException
	 * @return \Ag\Login\Domain\Model\AccountDescriptor
	 */
	public function disableAccount($email) {
		$account = $this->getAccountByEmailThrowExceptionIfNotExistsing($email);

		$account->disable();

		$this->accountRepository->update($account);
		$this->persistenceManager->persistAll();

		return $account->getDescriptor();
	}

	/**
	 * @param string $email
	 * @throws \InvalidArgumentException
	 * @return \Ag\Login\Domain\Model\AccountDescriptor
	 */
	public function enableAccount($email) {
		$account = $this->getAccountByEmailThrowExceptionIfNotExistsing($email);

		$account->enable();

		$this->accountRepository->update($account);
		$this->persistenceManager->persistAll();

		return $account->getDescriptor();
	}

	/**
	 * @param $email
	 * @return \Ag\Login\Domain\Model\Account
	 * @throws \InvalidArgumentException
	 */
	protected function getAccountByEmailThrowExceptionIfNotExistsing($email) {
		$account = $this->accountRepository->findOneByEmail($email);

		if (empty($account)) {
			throw new \InvalidArgumentException('No account found with email ' . $email);
		}
		return $account;
	}
}

?>