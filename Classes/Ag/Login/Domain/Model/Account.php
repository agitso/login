<?php
namespace Ag\Login\Domain\Model;

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class Account {

	/**
	 * @var string
	 * @ORM\Id
	 */
	protected $accountId;

	/**
	 * @var \DateTime
	 */
	protected $creationDate;

	/**
	 * @var int
	 * @ORM\Version
	 */
	protected $version = 0;

	/**
	 * Used to ensure that the version is increased even
	 * though only childs elements (fx. the login object)
	 * are edited.
	 *
	 * @var int
	 */
	protected $edits = 0;

	/**
	 * @var \TYPO3\Flow\Security\Account
	 * @ORM\OneToOne(cascade={"all"}, fetch="EAGER")
	 */
	protected $login;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $imageId;

	/**
	 * @param \TYPO3\Flow\Security\Account $login
	 * @param string $name
	 * @param string $imageId
	 * @param string $imageId
	 */
	public function __construct($login, $name, $imageId) {
		$this->accountId = \TYPO3\Flow\Utility\Algorithms::generateUUID();
		$this->creationDate = new \DateTime();
		$this->login = $login;
		$this->name = trim($name);
		$this->imageId = trim($imageId);
	}

	/**
	 * @param string $name
	 */
	public function changeName($name) {
		$name = trim($name);

		if ($name !== $this->name) {
			$this->name = $name;
			$this->edits++;

			// Publish event
		}
	}

	/**
	 * @param string $imageId
	 */
	public function changeImage($imageId) {
		$imageId = trim($imageId);

		if ($imageId !== $this->imageId) {
			$this->imageId = $imageId;
			$this->edits++;

			// Publish event
		}
	}

	/**
	 * @param string $newPassword
	 * @param \TYPO3\Flow\Security\Cryptography\HashService $hashService
	 * @throws \InvalidArgumentException
	 */
	public function changePassword($newPassword, $hashService) {
		$newPassword = trim($newPassword);

		if (empty($newPassword)) {
			throw new \InvalidArgumentException('Password must be set.');
		}

		$this->edits++;
		$this->login->setCredentialsSource($hashService->hashPassword($newPassword, 'default'));
	}

	/**
	 * @param \Ag\Login\Domain\Model\Role $role
	 */
	public function addRole($role) {
		$this->login->addRole($this->roleToFlowRole($role));
	}

	/**
	 * @param \Ag\Login\Domain\Model\Role $role
	 */
	public function removeRole($role) {
		$this->login->removeRole($this->roleToFlowRole($role));
	}

	/**
	 * @param \Ag\Login\Domain\Model\Role $role
	 * @return bool
	 */
	public function hasRole($role) {
		return $this->login->hasRole($this->roleToFlowRole($role));
	}

	/**
	 * @param \Ag\Login\Domain\Model\Role $role
	 * @return \TYPO3\Flow\Security\Policy\Role
	 */
	protected function roleToFlowRole($role) {
		return new \TYPO3\Flow\Security\Policy\Role($role->getRole().'|'.$role->getClientId());
	}

	/**
	 * @param \TYPO3\Flow\Security\Policy\Role $role
	 * @throws \InvalidArgumentException
	 * @return \Ag\Login\Domain\Model\Role $role
	 */
	protected function flowRoleToRole($role) {
		if(strpos($role->__toString(), '|') === FALSE) {
			return new Role($role->__toString());
		} else {
			$role = explode('|', $role->__toString());
			if(count($role)===2) {
				return new Role($role[0],$role[1]);
			} else {
				throw new \InvalidArgumentException('Could not succesfully parse the Flow role for account ' . $this->login->getAccountIdentifier());
			}
		}
	}

	/**
	 * @return \Ag\Login\Domain\Model\AccountDescriptor
	 */
	public function getDescriptor() {
		$d = new AccountDescriptor();
		$d->accountId = $this->accountId;
		$d->email = $this->login->getAccountIdentifier();
		$d->name = $this->name;
		$d->imageId = $this->imageId;

		$d->roles = $this->login->getRoles();
		foreach($d->roles as $key=>$role) {
			$d->roles[$key] = $this->flowRoleToRole($role);
		}

		return $d;
	}
}

?>