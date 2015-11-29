<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;


class SignFormFactory extends Nette\Object
{
	/** @var User */
	private $oUser;


	public function __construct(User $oUser)
	{
		$this->oUser = $oUser;
	}


	/**
	 * @return Form
	 */
	public function create()
	{
		$oForm = new Form;
		$oForm->addText('username', 'Login:')
					->setRequired('Prosím zadejte uživatelské jméno(login).');

		$oForm->addPassword('password', 'Heslo:')
					->setRequired('Prosím zadejte uživatelské heslo.');

		$oForm->addCheckbox('remember', 'Ponechat mě přihlášeného');

		$oForm->addSubmit('send', 'Přihlásit');

		$oForm->onSuccess[] = array($this, 'formSucceeded');
		return $oForm;
	}


	public function formSucceeded(Form $oForm, $oValues)
	{
		if ($oValues->remember)
		{
			$this->oUser->setExpiration('14 days', FALSE);
		}
		else
		{
			$this->oUser->setExpiration('20 minutes', TRUE);
		}

		try
		{
			$this->oUser->login($oValues->username, $oValues->password);
		}
		catch (Nette\Security\AuthenticationException $e)
		{
			$oForm->addError("Špatný login nebo heslo.");
		}
	}

}
