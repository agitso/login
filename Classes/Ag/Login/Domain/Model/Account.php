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
	 * @ORM\OneToOne(cascade={"all"})
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
	 * @var bool
	 */
	protected $enabled = TRUE;

	/**
	 * @param \TYPO3\Flow\Security\Account $login
	 * @param string $name
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
	 * @param string $role
	 * @param string $key
	 */
	public function addRole($role, $key = '') {
		$role = $this->getRoleWithKey($role, $key);

		$role = new \TYPO3\Flow\Security\Policy\Role($role);

		if ($this->login->hasRole($role)) {
			return;
		}

		$this->login->addRole($role);
		$this->edits++;

		// Publish event
	}


	/**
	 * @param string $role
	 * @param string $key
	 */
	public function removeRole($role, $key = '') {
		$role = $this->getRoleWithKey($role, $key);

		$role = new \TYPO3\Flow\Security\Policy\Role($role);

		if (!$this->login->hasRole($role)) {
			return;
		}

		$this->login->removeRole($role);
		$this->edits++;

		// Publish event
	}

	/**
	 * @return array
	 */
	public function getRoles() {
		$roles = array();

		foreach ($this->login->getRoles() as $role) {
			$role = $role->__toString();

			if(strpos($role, '|') === FALSE) {
				$roles['Default'] = $role;
			} else {
				$role = explode('|', $role);

				if(!array_key_exists($role[1], $roles)) {
					$roles[$role[1]] = array();
				}

				$roles[$role[1]][] = $role[0];
			}
		}

		return $roles;
	}

	/**
	 * @return AccountDescriptor
	 */
	public function getDescriptor() {
		$d = new AccountDescriptor();
		$d->accountId = $this->accountId;
		$d->name = $this->name;
		$d->email = $this->login->getAccountIdentifier();
		$d->imageId = $this->imageId;
		$d->enabled = $this->enabled;

		return $d;
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
	 * @return bool
	 */
	public function isEnabled() {
		return $this->enabled;
	}

	/**
	 * @return void
	 */
	public function disable() {
		if (!$this->isEnabled()) {
			return;
		}

		$this->enabled = FALSE;
		$this->edits++;
	}

	/**
	 * @return void
	 */
	public function enable() {
		if ($this->isEnabled()) {
			return;
		}

		$this->enabled = TRUE;
		$this->edits++;
	}

	/**
	 * @param string $role
	 * @param string $key
	 * @return string
	 */
	protected function getRoleWithKey($role, $key) {
		$key = trim($key);
		if (!empty($key)) {
			$role .= '|' . $key;
			return $role;
		}
		return $role;
	}
}

?>