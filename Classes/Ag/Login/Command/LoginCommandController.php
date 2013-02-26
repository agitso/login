<?php
namespace Ag\Login\Command;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class LoginCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @var \Ag\Login\Service\AccountService
	 * @Flow\Inject
	 */
	protected $accountService;

	/**
	 * @param string $email
	 * @param string $name
	 */
	public function createAccountCommand($email, $name) {
		$account = $this->accountService->createAccount($email, $name);
		$this->outputLine('Created account "' . $account->name . ' <' . $account->email . '>"');
	}

	/**
	 * @param string $email
	 * @param string $role
	 * @return void
	 */
	public function addRoleCommand($email, $role) {
		$this->accountService->addRoleToAccount($email, $role);

		$this->outputLine('Role "' . $role . '" add to `' . $email . '`');
	}

	/**
	 * @param string $email
	 * @param string $role
	 * @return void
	 */
	public function removeRoleCommand($email, $role) {
		$this->accountService->removeRoleFromAccount($email, $role);

		$this->outputLine('Role "' . $role . '" removed from `' . $email . '`');
	}

	/**
	 * @param string $email
	 * @param string $password
	 */
	public function changePasswordCommand($email, $password) {
		$this->accountService->changePassword($email, $password);

		$this->outputLine('Changed password for `' . $email . '`');
	}

	/**
	 * @param string $email
	 */
	public function disableAccountCommand($email) {
		$this->accountService->disableAccount($email);

		$this->outputLine('Disabled account.');
	}

	/**
	 * @param string $email
	 */
	public function enableAccountCommand($email) {
		$this->accountService->enableAccount($email);

		$this->outputLine('Enabled account.');
	}
}

?>