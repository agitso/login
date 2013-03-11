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
	 */
	public function addRole($role) {
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
	 */
	public function removeRole($role) {
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

		foreach($this->login->getRoles() as $role) {
			$roles[] = $role->__toString();
		}

		return $roles;
	}

	/**
	 * @return \stdClass
	 */
	public function getDescriptor() {
		$descriptor = new \stdClass();
		$descriptor->accountId = $this->accountId;
		$descriptor->name = $this->name;
		$descriptor->email = $this->login->getAccountIdentifier();
		$descriptor->imageId = $this->imageId;
		$descriptor->enabled = $this->enabled;

		return $descriptor;
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
		if($this->isEnabled()) {
			return;
		}

		$this->enabled = TRUE;
		$this->edits++;
	}
}

?>