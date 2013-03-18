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
	 * @param null|string $clientId
	 */
	public function __construct($role, $clientId = NULL) {
		$this->role = $role;
		$this->clientId = $clientId;
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