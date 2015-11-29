<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;

/**
 * Base presenter for all application presenters.
 */
class ArticlePresenter extends BasePresenter
{
	const
				TABLE_USER = "ax12_users",
				USER_ID = "ax12_users_id",
				MENU_ID = "ax12_menu_id",
				LAST_EDIT = "lastedit",
				TEXT = 'text';
	private $oModel;
	private $oMenu;
	private $oUser;
	private $id = 0;
	private $menu = 0;
	protected function startup()
	{
		parent::startup();
		$this->oModel = $this->context->getService("article");
		$this->oMenu = $this->context->getService("menu");
		$this->oUser = $this->context->getService("userss");
	}
	public function actionShow($id)
	{
		$oRow = $this->oModel->getOne($id);
		$this->template->article = $oRow ? $oRow->toArray() : null;
		$this->template->id = $this->id = $oRow ? $id : 0;
		$this->menu = $this->template->menu_id = $oRow ? $this->template->article[self::MENU_ID] : 0;
		$this->template->user_name = 
			//$this->oMenu->database()->table(self::TABLE_USER)->select('id, login')->get($this->template->article[self::USER_ID])['login'];
			$this->oUser->getSelect('id, login', $this->template->article[self::USER_ID])['login'];
	}
	public function actionAdd($id)
	{
		$oRow = $this->oMenu->getOne($id);
		$this->menu = $oRow ? $id : 0;
		$this->template->id = $oRow ? $id : 0;
	}
	public function actionEdit($id)
	{
		$this->actionShow($id);
		if($this->template->article)
		{
			$this->template->article[self::TEXT] =str_replace("<br />", "\r\n", $this->template->article[self::TEXT]);
		}
	}
	
	public function actionDelete($id)
	{
		$this->actionShow($id);
	}
	protected function createComponentAddEditForm()
	{
		$oForm = new Nette\Application\UI\Form();
		$oForm->addText('title', "Název")
				->setRequired();
		$oForm->addTextArea('text', "Text")
				->addRule(Nette\Application\UI\Form::MAX_LENGTH, 'Text je příliš dlouhý', 1000);
		if($this->id != 0)
		{
			$oForm->addHidden('id');
			$oForm->setDefaults($this->template->article);
			$oForm->addSubmit('send', "Editovat");
		}
		else
		{
			$oForm->addSubmit('send', "Přidat");
		}
		$oForm->onSuccess[] = $this->addEditSubmit;
		return $oForm;
	}
	public function addEditSubmit(\Nette\Application\UI\Form $oForm)
	{
		$qoValues = $oForm->getValues(TRUE);
		$qoValues[self::USER_ID] = $this->user->id;
		$qoValues[self::LAST_EDIT] =  new \DateTime;
		$qoValues[self::TEXT] =str_replace("\n", "<br />", $qoValues[self::TEXT]);
		if (isset($qoValues['id']))
		{
			if ($this->user->isAllowed('article', '_edit'))
			{
				$this->oModel->update($qoValues);
				$this->flashMessage("Článek úspěšně editován.");
				$this->redirect("this");
			}
			else
			{
				$this->flashMessage("Nemáte dostatečná práva", "danger");
				$this->redirect("this");
			}
		}
		else if($this->user->isAllowed('article', '_add'))
		{
			$qoValues[self::MENU_ID] = $this->menu;
			$iId = $this->oModel->insert($qoValues)->id;
			$this->flashMessage("Článek úspěšně vložen");
			$this->redirect("Menu:show", array("id" => $this->menu));
		}
		else
		{
			$this->flashMessage("Nemáte dostatečná práva", "danger");
			$this->redirect("this");
		}
	}

	public function handleDelete($id, $bConfirm = FALSE)
	{
		if ($this->user->isAllowed('article', '_delete') && $bConfirm)
		{
			$this->oModel->remove($id);
			$this->flashMessage("Článek odstraněn.", "success");
			$this->redirect("Menu:show", array("id" => $this->menu));
		}
		else
		{
			$this->flashMessage("Nemáte dostatečná práva", "danger");
			$this->redirect("this");
		}
	}
}
