<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;


class RouterFactory
{

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$qoRouter = new RouteList;
		//$qoRouter[] = $qoUserRouter = new RouteList();
		//$qoUserRouter[] = new Route('prihlasit-se', 'Sign:in');
		$qoRouter[] = new Route('prihlasit-se', 'Sign:in');
		$qoRouter[] = new Route('ohlasit-se', 'Sign:out');
		$qoRouter[] = new Route('Menu-add', 'Menu:add');
		$qoRouter[] = new Route('Menu-delete-<id [0-9]+>', 'Menu:delete');
		$qoRouter[] = new Route('Menu-<id [0-9]+>[/[<do>]-<paginator-page [0-9]+>]',
			array
			(
				'presenter' => 'Menu',
				'action' => 'show',
				'paginator-page' => 1
			));
		$qoRouter[] = new Route('Clanek-add-<id [0-9]+>', 'Article:add');
		$qoRouter[] = new Route('Clanek-edit-<id [0-9]+>', 'Article:edit');
		$qoRouter[] = new Route('Clanek-<id [0-9]+>', 'Article:show');
		$qoRouter[] = new Route('Clanek-delete-<id [0-9]+>', 'Article:delete');
		$qoRouter[] = new Route('Uzivatele[/[<do>]-<paginator-page [0-9]+>]',
			array
			(
				'presenter' => 'User',
				'action' => 'list',
				'paginator-page' => 1
			));
		$qoRouter[] = new Route('Uzivatel-edit-<id [0-9]+>', 'User:edit');
		$qoRouter[] = new Route('Uzivatel-delete-<id [0-9]+>', 'User:delete');
		$qoRouter[] = new Route('Uzivatel-prava[/[<do>]-<paginator-page [0-9]+>]',
			array
			(
				'presenter' => 'User',
				'action' => 'right',
				'paginator-page' => 1
			));
		$qoRouter[] = new Route('Uzivatel-prava-edit-<id [0-9]+>', 'User:right_edit');
		$qoRouter[] = new Route('Uzivatel-prava-delete-<id [0-9]+>', 'User:right_delete');
		$qoRouter[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $qoRouter;
	}

}
