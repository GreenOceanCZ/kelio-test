<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model;

/**
 * Description of BaseModel
 *
 * @author Pepa
 */
abstract class DatabaseModel {
	
	/** @var \Nette\Database\Context */
	protected $database;
	
	/** @var \Nette\DI\Container */
	protected $context;
	
	/** @var string */
	protected $table;
	
	/** @var \Nette\Security\IIdentity */
	private $user;
	
	/** @var \Nette\Http\Session */
	private $session;
	
	public function __construct(\Nette\Database\Context $database, \Nette\Security\User $user,\Nette\Http\Session $session, \Nette\DI\Container $context) {
		$this->database = $database;
		$this->user = $user;
		$this->session = $session;
		$this->context = $context;
	}
	public function count()
	{
		return $this->database->table($this->table)->count("*");
	}
	public function getDB()
	{
		return $this->database;
	}
	public function getSelect($sSelection, $iId = false)
	{
		return $iId  === false ? $this->database->table($this->table)->select($sSelection) : $this->database->table($this->table)->select($sSelection)->get($iId);
	}
	public function getAll()
	{
		return $this->database->table($this->table);
	}	
	
	public function getOne($iId)
	{
		return $this->getAll()->get($iId);
	}
	
	public function insert(array $qoData)
	{
		return $this->database->table($this->table)->insert($qoData);
	}
	public function insertTable(array $qoData, $sTable)
	{
		return $this->database->table($sTable)->insert($qoData);
	}
	public function update(array $qoData)
	{
		$iId = $qoData['id'];
		unset($qoData['id']);
		return $this->database->table($this->table)->get($iId)->update($qoData);
	}
	public function updateTable(array $qoData, $sTable)
	{
		$iId = $qoData['id'];
		unset($qoData['id']);
		return $this->database->table($sTable)->get($iId)->update($qoData);
	}
	public function remove($iId)
	{
		$this->getOne($iId)->delete();
	}
	public function removeTable($iId, $sTable)
	{
		$this->database->table($sTable)->get($iId)->delete();
	}
	public function database()
	{
		return $this->database;
	}
}
