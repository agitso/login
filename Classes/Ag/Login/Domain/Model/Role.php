<?php
namespace Ag\Login\Domain\Model;

class Role {

	/**
	 * @var string
	 */
	protected $role;

	/**
	 * If this role is only applicable to a single client
	 * specify a client id here. Mainly used in Agitso's
	 * multi-client SaaS products.
	 *
	 * @var string
	 */
	protected $clientId;


	/**
	 * @param string $role
	 * @param string $clientId
	 */
	public function __construct($role, $clientId = '') {
		$this->role = trim($role);
		$this->clientId = trim($clientId);

		if(empty($this->role)) {
			$this->role = NULL;
		}

		if(empty($this->clientId)) {
			$this->clientId = NULL;
		}
	}

	/**
	 * @return string
	 */
	public function getClientId() {
		return $this->clientId;
	}

	/**
	 * @return string
	 */
	public function getRole() {
		return $this->role;
	}
}
?>