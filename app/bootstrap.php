<?php

require __DIR__ . '/../vendor/autoload.php';

$oConfigurator = new Nette\Configurator;

//$oConfigurator->setDebugMode('23.75.345.200'); // enable for your remote IP
$oConfigurator->enableDebugger(__DIR__ . '/../log');

$oConfigurator->setTempDirectory(__DIR__ . '/../temp');

$oConfigurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

$oConfigurator->addConfig(__DIR__ . '/config/config.neon');
if ($oConfigurator->debugMode)
{
	$oConfigurator->addConfig(__DIR__ . '/config/config.local.neon');
}
$oContainer = $oConfigurator->createContainer();
//$oContainer->getByType('App\Model\UserManager')->add("admin", "admin", "bgthnev@gmail.com", 1);
//$oContainer->getByType('App\Model\UserManager')->add("demo", "demo", "bgthnev@gmail.com", 2);
return $oContainer;
