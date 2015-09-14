<?php

$c = \OC::$server;

$appId = 'locktools';
$appName = 'Lock Tools';

$user = \OC::$server->getUserSession()->getUser();
if ($user and \OC::$server->getGroupManager()->isAdmin($user->getUID())) {
	\OC::$server->getNavigationManager()->add(function () use ($appId, $appName) {
		return [
			'id' => $appId,
			'order' => 22,
			'name' => $appName,
			'href' => \OC::$server->getURLGenerator()->linkToRoute($appId . '.page.index'),
			'icon' => \OC::$server->getURLGenerator()->imagePath($appId, 'app.svg')
		];
	});
}

$app = new \OCA\LockTools\AppInfo\Application();
OCP\Util::connectHook('OC_Filesystem', 'preSetup', $app, 'setupWrapper');
