<?php

namespace App\Presenters;

use Nette;


class ErrorPresenter extends BasePresenter
{

	public function renderDefault($oException)
	{
		if($oException instanceof Nette\Application\BadRequestException)
		{
			$iCode = $oException->getCode();
			$this->setView(in_array($iCOde, array(403, 404, 405, 410, 500)) ? $iCode : '4xx');
		}
		else
		{
			$this->setView('500');
		}
	}
}
