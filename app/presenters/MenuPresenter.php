<?php

namespace App\Presenters;

use Nette;
use App\Model;
use IPub\VisualPaginator\Components as VisualPaginator;

class MenuPresenter extends BasePresenter
{
	const
				MENU_ID = "ax12_menu_id",
				ORDER = "id DESC",
				SELECT = "id, title, lastedit",
				TABLE_ARTICLE = "ax12_article",
					COLUMN_MENU = "ax12_menu_id = ?";
	private $oModel;
	private $oArticle;
	private $id = 0;
	protected function startup()
	{
		parent::startup();
		$this->oModel = $this->context->getService("menu");
		$this->oArticle = $this->context->getService("article");
	}
	public function actionShow($id)
	{
		$qoArticles = $this->oArticle->getAll()->select(self::SELECT)->where(self::MENU_ID, $id)->order(self::ORDER);
		$oNow = $this['paginator'];
		$oPaginator = $oNow->getPaginator();
		$oPaginator->itemsPerPage = 2;
		$oPaginator->itemCount    = $qoArticles->count();
		$qoArticles->Limit($oPaginator->itemsPerPage, $oPaginator->offset);
		$this->template->articles = $qoArticles;
		$this->actionDelete($id);
	}
	public function actionDelete($id)
	{
		$oNow = $this->oModel->getOne($id);
		$this->template->menu = $oNow ? $oNow->toArray() : null;
		if($oNow)
		{
			$this->template->title = $this->template->menu['name'];
		}
		$this->id = $oNow ? $id : 0;
		$this->template->id = $oNow ? $id : 0;
	}
	protected function createComponentPaginator()
	{
		$oNow = new VisualPaginator\Control;
		$oNow->setTemplateFile(__DIR__ .'/templates/paginator.latte');
		$oNow->disableAjax();
		return $oNow;
	}
	protected function createComponentAddEditForm()
	{
		$oForm = new Nette\Application\UI\Form();
		$oForm->addText('name', "Název")
				->setRequired();
		$oForm->addText('title', "Titulek")
				->setRequired();
		$oForm->addCheckbox('active', "Aktivní menu")
					->setDefaultValue('checked');
		if($this->id != 0)
		{
			$oForm->addHidden('id');
			$oForm->setDefaults($this->oModel->getOne($this->id)->toArray());
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
		if (isset($qoValues['id']))
		{
			if ($this->user->isAllowed('menu', '_edit'))
			{
				$this->oModel->update($qoValues);
				$this->flashMessage("Položka menu úspěšně editována");
				$this->redirect("this");
			}
			else
			{
				$this->flashMessage("Nemáte dostatečná práva", "danger");
				$this->redirect("this");
			}
		}
		else if($this->user->isAllowed('menu', '_add'))
		{
			$iId = $this->oModel->insert($qoValues)->id;
			$this->oModel->update(array('sortid' => $iId, 'id' => $iId));
			$this->flashMessage("Položka menu úspěšně vložena");
			$this->redirect("Menu:add");
		}
		else
		{
			$this->flashMessage("Nemáte dostatečná práva", "danger");
			$this->redirect("this");
		}
	}

	public function handleDelete($id, $bConfirm = FALSE)
	{
		if ($this->user->isAllowed('menu', '_delete') && $bConfirm)
		{
			$this->oModel->database()->table(self::TABLE_ARTICLE)->where(self::COLUMN_MENU, $id)->delete();
			$this->oModel->remove($id);
			$this->flashMessage("Položka menu odstraněna", "success");
			$this->redirect("Homepage:default");
		}
		else
		{
			$this->flashMessage("Nemáte dostatečná práva", "danger");
			$this->redirect("this");
		}
	}
}
