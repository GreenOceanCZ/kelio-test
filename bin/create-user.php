<?php

if (!isset($_SERVER['argv'][4])) {
	echo '
Add new user to database.

Usage: create-user.php <name> <password> <email> <role_id>
';
	exit(1);
}

list(, $sName, $sPassword, $sEmail, $iRole) = $_SERVER['argv'];

$container = require __DIR__ . '/../app/bootstrap.php';
$manager = $container->getByType('App\Model\UserManager');

try {
	$manager->add($sName, $sPassword, $sEmail, $iRole);
	echo "User $name was added.\n";

} catch (App\Model\DuplicateNameException $e) {
	echo "Error: duplicate name.\n";
	exit(1);
}
