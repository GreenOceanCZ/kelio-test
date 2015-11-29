<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model;

/**
 * Description of PermissionManager
 *
 * @author Pepa
 */
class PermissionManager extends \Nette\Security\Permission {

	/** @var \Nette\Database\Context */
	private $database;

	/** @var \Nette\Caching\Cache */
	private $cache;

	const TABLE_ROLE_NAME = 'ax12_typ',
					COLL_NAME = 'name',
					MODUL_USER = 'user',
					MODUL_MENU = 'menu',
					MODUL_ARTICLE = 'article',
					ACTION_ADD = '_add',
					ACTION_EDIT = '_edit',
					ACTION_DELETE = '_delete',
					COLS = 'id, name, user_add, user_edit, user_delete, menu_add, menu_edit, menu_delete, article_add, article_edit, article_delete';

	public function __construct(\Nette\Database\Context $database, \Nette\DI\Container $context) {
		$this->database = $database;



		$this->cache = new \Nette\Caching\Cache($context->getService('cacheStorage'), "system");

		$roles = $this->cache->load("PermissionsRoles");
		$resources = $this->cache->load("PermissionsResources");
		
		$this->init($roles, $resources);
		
		if ( $roles === NULL || $resources === NULL) {
			
			$this->cache->save("PermissionsRoles",$this->getRoles());
			$this->cache->save("PermissionsResources",$this->getResources());
			
		} 
		
	}

	private function init($qoRoles = NULL,$qoResources = NULL)
	{
		$qoRoles = $this->database->table(self::TABLE_ROLE_NAME)->fetchPairs('id', 'name');
		if ($qoResources === NULL)
		{
			$qoResources = array(self::MODUL_USER, self::MODUL_MENU, self::MODUL_ARTICLE);
		}
		foreach ($qoRoles as $oRole)
		{
			$this->addRole($oRole);
		}
		foreach ($qoResources as $oResource)
		{
			$this->addResource($oResource);
		}
		$qoAccess = $this->database->table(self::TABLE_ROLE_NAME)->select(self::COLS);
		foreach ($qoAccess as $oNow)
		{
			//print_r($oNow);
			foreach ($qoResources as $oResource)
			{
				$this->setAllowDeny($oNow, $oResource, self::ACTION_ADD);
				$this->setAllowDeny($oNow, $oResource, self::ACTION_EDIT);
				try
				{
					$this->setAllowDeny($oNow, $oResource, self::ACTION_DELETE);
				}catch(\Exception $ex){print_r($oNow);}
			}
		}
	}
	private function setAllowDeny($oNow, $oResource, $sAction)
	{
		if($oNow[$oResource.$sAction])
		{
			$this->allow($oNow[self::COLL_NAME], $oResource, $sAction);
		}
		else
		{
			$this->deny($oNow[self::COLL_NAME], $oResource, $sAction);
		}
	}
	
	public function setAllow($iRole, $sResource, $sPrivilege)
	{
		$this->database->table(self::TABLE_ROLE_NAME)->get($iRole)->update(array($sResource.$sPrivilege, 1));
	}

	public function setDeny($iRole, $sResource, $sPrivilege)
	{
		$this->database->table(self::TABLE_ROLE_NAME)->get($iRole)->update(array($sResource.$sPrivilege, 1));
	}




}
