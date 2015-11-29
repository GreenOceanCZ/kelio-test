<?php
namespace App\Presenters;

use Nette;
use App\Model;
use IPub\VisualPaginator\Components as VisualPaginator;
use Nette\Security\Passwords;
use Nette\Application\UI\Form;

class UserPresenter extends BasePresenter
{
	const
				TABLE = "ax12_users",
					COLUMN_ID = "id",
					COLUMN_LOGIN = "password",
					COLUMN_EMAIL = "email",
					COLUMN_TYP = "ax12_typ_id",
					ORDER = "id ASC",
					SELECT = 'id',
					LIKE = 'login LIKE ?',
					QUERY = "SELECT ax12_users.id, login, email, typ.name FROM ax12_users LEFT JOIN ax12_typ typ ON typ.id=ax12_users.ax12_typ_id ORDER BY id ASC LIMIT ?, ?",
					QUERY_LIKE = "SELECT ax12_users.id, login, email, typ.name FROM ax12_users LEFT JOIN ax12_typ typ ON typ.id=ax12_users.ax12_typ_id where login LIKE ? ORDER BY id ASC LIMIT ?, ?",
				TABLE_TYP = "ax12_typ",
					COLUMN_NAME = "name",
					SELECT_TYP = "id, name",
					ORDER_TYP = "name ASC",
				TABLE_ARTICLE = "ax12_article",
					COLUMN_USER = "ax12_users_id";
	private $oModel;
	private $id = 0;
	private $oSession;
	private $sSearch = '';
	protected function startup()
	{
		parent::startup();
		$this->oModel = $this->context->getService("userss");
		$this->oSession = $this->getSession()->getSection('UsersServiceSection');
	}
	public function actionList()
	{
		$qoUsers = $this->oModel->getAll()->select(self::SELECT);
		$this->sSearch = $sSearch = isset($this->oSession->sFilter) ? $this->oSession->sFilter : '';
		if($sSearch !== '')
		{
			$qoUsers->where(self::LIKE,
											$sSearch = new \Nette\Database\SqlLiteral($this->oModel->database()->getConnection()->getSupplementalDriver()->formatLike($sSearch, 0)));
		}
		
		$oNow = $this['paginator'];
		$oPaginator = $oNow->getPaginator();
		$oPaginator->itemsPerPage = 2;
		$oPaginator->itemCount    = $qoUsers->count();
		if($sSearch !== '')
		{
			$qoUsers = $this->oModel->database()->queryArgs(self::QUERY_LIKE, [$sSearch, $oPaginator->offset, $oPaginator->itemsPerPage]);
		}
		else
		{
			$qoUsers = $this->oModel->database()->queryArgs(self::QUERY, [$oPaginator->offset, $oPaginator->itemsPerPage]);
		}
		//$qoUsers->Limit($oPaginator->itemsPerPage, $oPaginator->offset);
		$this->template->users = $qoUsers;
		$this->template->user_edit = $this->user->isAllowed('user', '_edit');
		$this->template->user_delete = $this->user->isAllowed('user', '_delete');
	}
	public function actionEdit($id)
	{
		$oNow = $this->oModel->getOne($id);
		$this->template->usernow = $oNow ? $oNow->toArray() : null;
		$this->id = $oNow ? $id : 0;
		$this->template->id = $oNow ? $id : 0;
	}
	public function actionDelete($id)
	{
		$this->actionEdit($id);
	}
	public function actionRight_Edit($id)
	{
		$oNow = $this->oModel->database()->table(self::TABLE_TYP)->get($id);
		$this->template->usernow = $oNow ? $oNow->toArray() : null;
		$this->id = $oNow ? $id : 0;
		$this->template->id = $oNow ? $id : 0;
	}
	public function actionRight_Delete($id)
	{
		$this->actionRight_Edit($id);
	}
	public function actionRight()
	{
		$qoRows = $this->oModel->database()->table(self::TABLE_TYP)->order(self::ORDER_TYP);
		$oPaginator = $this['paginator']->getPaginator();
		$oPaginator->itemsPerPage = 2;
		$oPaginator->itemCount    = $qoRows->count();
		$qoRows->Limit($oPaginator->itemsPerPage, $oPaginator->offset);
		$this->template->qoRights = $qoRows;
		$this->template->user_edit = $this->user->isAllowed('user', '_edit');
		$this->template->user_delete = $this->user->isAllowed('user', '_delete');
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
		$oForm = new Form();
		$oForm->addText('login', "Login")
					->addRule(Form::MAX_LENGTH, 'Maximální délka loginu je %d znaků', 50)
					->setRequired();
		$oForm->addText('email', "Email")
					->addRule(Nette\Application\UI\Form::EMAIL, "Špatný formát emailu.")
					->setRequired();
		$oNow = $oForm->addPassword("password", "Heslo:");
		if($this->id == 0)
		{
			$oNow->addRule(Form::FILLED, "Heslo musí být vyplněné !")->setRequired();
		}
		$oNow = $oForm->addPassword("confirm_password", "Heslo znovu:");
		if($this->id == 0)
		{
			$oNow = $oNow->addRule(Form::FILLED, "Potvrzovací heslo musí být vyplněné!")->addConditionOn($oForm["confirm_password"], Form::FILLED);
		}
		$oNow->addRule(Form::EQUAL, "Hesla se musí shodovat!", $oForm["password"]);
		$qoSelect = $this->oModel->database()->table(self::TABLE_TYP)->select(self::SELECT_TYP)->order(self::ORDER_TYP)->fetchPairs('id', 'name');
		$oForm->addSelect('ax12_typ_id', 'Typ uživatele:', $qoSelect);
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
		unset($qoValues['confirm_password']);
		try
		{
			if (isset($qoValues['id']))
			{
				if ($this->user->isAllowed('user', '_edit') && $qoValues['id'] > 2)
				{
					if(isset($qoValues['password']) && !($qoValues['password']))
					{
						unset($qoValues['password']);
					}
					else
					{
						$qoValues['password'] = Passwords::hash($qoValues['password']);
					}
					$this->oModel->update($qoValues);
					$this->flashMessage("Uživatel úspěšně editován.");
					$this->redirect("this");
				}
				else
				{
					$this->flashMessage("Nemáte dostatečná práva", "danger");
					$this->redirect("this");
				}
			}
			else if($this->user->isAllowed('user', '_add'))
			{
				$qoValues['password'] = Passwords::hash($qoValues['password']);
				$this->oModel->insert($qoValues);
				$this->flashMessage("Uživatel úspěšně přidán.");
				$this->redirect("this");
			}
			else
			{
				$this->flashMessage("Nemáte dostatečná práva", "danger");
				$this->redirect("this");
			}
		}
		catch(Nette\Database\UniqueConstraintViolationException $ex)
		{
			$this->flashMessage("Uživatele se nepovedlo přidat pravděpodně jste zvolili duplicitní login.", "danger");
			$this->redirect("this");
		}
	}
	protected function createComponentFilterForm()
	{
		$oForm = new Form();
		$oForm->addText('filtr', "")
					->addRule(Form::MAX_LENGTH, 'Maximální délka loginu je %d znaků', 50);
		if($this->sSearch !== '')
		{
			$oForm->setDefaults(array('filtr' => $this->sSearch));
		}
		$oForm->addSubmit('send', "Filtrovat");
		$oForm->onSuccess[] = $this->filterSubmit;
		return $oForm;
	}
	public function filterSubmit(\Nette\Application\UI\Form $oForm)
	{
		$qoValues = $oForm->getValues(TRUE);
		if(isset($qoValues['filtr']))
		{
			$this->oSession->sFilter = $qoValues['filtr'];
		}
		else
		{
			$this->oSession->sFilter = '';
		}
		$this->redirect("this");
	}
	protected function createComponentAddEditRightForm()
	{
		$oForm = new Form();
		$oForm->addGroup();
			$oForm->addText('name', "Název")
						->addRule(Form::MAX_LENGTH, 'Maximální délka loginu je %d znaků', 50)
						->setRequired();
		$oForm->addGroup("Přidávání (+)");
			$oForm->addCheckbox('user_add', 'Uživatele / práva');
			$oForm->addCheckbox('menu_add', 'Menu');
			$oForm->addCheckbox('article_add', 'Články');
		$oForm->addGroup("Editace (E)");
			$oForm->addCheckbox('user_edit', 'Uživatele / práva');
			$oForm->addCheckbox('menu_edit', 'Menu');
			$oForm->addCheckbox('article_edit', 'Články');
		$oForm->addGroup("Mazání (X)");
			$oForm->addCheckbox('user_delete', 'Uživatele / práva');
			$oForm->addCheckbox('menu_delete', 'Menu');
			$oForm->addCheckbox('article_delete', 'Články');
		$oForm->addGroup();
		if($this->id != 0)
		{
			$oForm->addHidden('id');
			$oForm->setDefaults($this->oModel->database()->table(self::TABLE_TYP)->get($this->id)->toArray());
			$oForm->addSubmit('send', "Editovat");
		}
		else
		{
			$oForm->addSubmit('send', "Přidat");
		}
		$oForm->onSuccess[] = $this->addEditRightSubmit;
		return $oForm;
	}
	public function addEditRightSubmit(\Nette\Application\UI\Form $oForm)
	{
		$qoValues = $oForm->getValues(TRUE);
		try
		{
			if (isset($qoValues['id']))
			{
				if ($this->user->isAllowed('user', '_edit') && $qoValues['id'] > 2)
				{
					$this->oModel->updateTable($qoValues, self::TABLE_TYP);
					$this->flashMessage("Uživatelská práva úspěšně editována.");
					$this->redirect("this");
				}
				else
				{
					$this->flashMessage("Nemáte dostatečná práva", "danger");
					$this->redirect("this");
				}
			}
			else if($this->user->isAllowed('user', '_add'))
			{
				$this->oModel->insertTable($qoValues, self::TABLE_TYP);
				$this->flashMessage("Uživatelská práva úspěšně přidána.");
				$this->redirect("this");
			}
			else
			{
				$this->flashMessage("Nemáte dostatečná práva", "danger");
				$this->redirect("this");
			}
		}
		catch(Nette\Database\UniqueConstraintViolationException $ex)
		{
			$this->flashMessage("Uživatele se nepovedlo přidat pravděpodně jste zvolili duplicitní login.", "danger");
			$this->redirect("this");
		}
	}
	public function handleDelete($id, $bConfirm = FALSE)
	{
		if ($this->user->isAllowed('user', '_delete') && $bConfirm && $id > 2)
		{
			$this->oModel->database()->table(self::TABLE_ARTICLE)->where(self::COLUMN_USER.' = ?', $id)->update(array(self::COLUMN_USER => 1));
			$this->oModel->remove($id);
			$this->flashMessage("Uživatel odstraněn", "success");
			$this->redirect("User:list");
		}
		else
		{
			$this->flashMessage("Nemáte dostatečná práva", "danger");
			$this->redirect("this");
		}
	}
	public function handleDeleteRight($id, $bConfirm = FALSE)
	{
		if ($this->user->isAllowed('user', '_delete') && $bConfirm && $id > 2)
		{
			$this->oModel->getAll()->where(self::COLUMN_TYP.' = ?', $id)->update(array(self::COLUMN_TYP => 2));
			$this->oModel->removeTable($id, self::TABLE_TYP);
			$this->flashMessage("Uživatelský typ odstraněn", "success");
			$this->redirect("User:right");
		}
		else
		{
			$this->flashMessage("Nemáte dostatečná práva", "danger");
			$this->redirect("this");
		}
	}
}