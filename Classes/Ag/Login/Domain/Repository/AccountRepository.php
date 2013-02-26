<?php
namespace Ag\Login\Domain\Repository;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class AccountRepository extends \TYPO3\Flow\Persistence\Repository {

	/**
	 * @param string $accountId
	 * @return \Ag\Login\Domain\Model\Account
	 */
	public function findByIdentifier($accountId) {
		return parent::findByIdentifier($accountId);
	}

	/**
	 * @param string $email
	 * @return \Ag\Login\Domain\Model\Account
	 */
	public function findOneByEmail($email) {
		$query = $this->createQuery();

		return $query
				->setLimit(1)
				->matching($query->equals('login.accountIdentifier', $email))
				->execute()->getFirst();
	}

}

?>