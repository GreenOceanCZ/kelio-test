<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;


/**
 * Users management.
 */
class UserManager extends Nette\Object implements Nette\Security\IAuthenticator
{
	const
		TABLE_NAME = 'ax12_users',
			COLUMN_ID = 'id',
			COLUMN_NAME = 'login',
			COLUMN_PASSWORD_HASH = 'password',
			COLUMN_MAIL = 'email',
			COLUMN_TYP = 'ax12_typ_id',
		TABLE_ROLE_NAME = 'ax12_typ',
			COLL_NAME = 'name';
		protected $table = self::TABLE_NAME;

	/** @var Nette\Database\Context */
	private $oDatabase;


	public function __construct(Nette\Database\Context $database)
	{
		$this->oDatabase = $database;
	}

	/**
	 * Performs an authentication.
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $qqscredentials)
	{
		list($sUsername, $sPassword) = $qqscredentials;

		$oRow = $this->oDatabase->table(self::TABLE_NAME)->where(self::COLUMN_NAME, $sUsername)->fetch();

		if (!$oRow)
		{
			throw new Nette\Security\AuthenticationException('Neplatné heslo.', self::IDENTITY_NOT_FOUND);
		}
		elseif (!Passwords::verify($sPassword, $oRow[self::COLUMN_PASSWORD_HASH]))
		{
			throw new Nette\Security\AuthenticationException('Neplatné heslo.', self::INVALID_CREDENTIAL);
		}
		elseif (Passwords::needsRehash($oRow[self::COLUMN_PASSWORD_HASH]))
		{
			$oRow->update(array(
				self::COLUMN_PASSWORD_HASH => Passwords::hash($sPassword),
			));
		}

		$qoData = $oRow->toArray();
		unset($qoData[self::COLUMN_PASSWORD_HASH]);
		return new Nette\Security\Identity
		(
			$oRow[self::COLUMN_ID],
			$this->oDatabase->table(self::TABLE_ROLE_NAME)->select(self::COLL_NAME)->get($oRow[self::COLUMN_TYP])[self::COLL_NAME],
			$qoData
		);
	}


	/**
	 * Adds new user.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  int
	 * @return void
	 */
	public function add($sUserName, $sPassword, $sEmail, $iRole)
	{
		try
		{
			$this->oDatabase->table(self::TABLE_NAME)->insert(array(
				self::COLUMN_NAME						=>	$sUserName,
				self::COLUMN_PASSWORD_HASH	=>	Passwords::hash($sPassword),
				self::COLUMN_MAIL						=>	$sEmail,
				self::COLUMN_TYP						=>	$iRole
			));
		}
		catch (Nette\Database\UniqueConstraintViolationException $oException)
		{
			throw new DuplicateNameException;
		}
	}

}



class DuplicateNameException extends \Exception
{}
