<?php
namespace Ag\Login\Domain\Model;

class AccountDescriptor {

	/**
	 * @var string
	 */
	public $accountId;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $email;

	/**
	 * @var string
	 */
	public $imageId;

	/**
	 * @var bool
	 */
	public $enabled;

	/**
	 * @var array
	 */
	public $roles;

}

?>