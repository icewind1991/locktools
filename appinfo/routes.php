<?php
/**
 * Copyright (c) 2015 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

/** @var $this OC\Route\Router */


use OCP\API;

API::register(
	'get',
	'/apps/locktools/log',
	['OCA\LockTools\Api\OCS', 'getLog'],
	'locktools',
	API::ADMIN_AUTH
);

API::register(
	'put',
	'/apps/locktools/lock',
	['OCA\LockTools\Api\OCS', 'newLock'],
	'locktools',
	API::ADMIN_AUTH
);

API::register(
	'put',
	'/apps/locktools/changelock',
	['OCA\LockTools\Api\OCS', 'changeLock'],
	'locktools',
	API::ADMIN_AUTH
);

API::register(
	'put',
	'/apps/locktools/unlock',
	['OCA\LockTools\Api\OCS', 'unLock'],
	'locktools',
	API::ADMIN_AUTH
);

return ['routes' => [
	// page
	['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
	['name' => 'log#get', 'url' => '/log', 'verb' => 'GET'],
	['name' => 'log#timeout', 'url' => '/timeout', 'verb' => 'GET'],
	['name' => 'log#setTimeout', 'url' => '/timeout', 'verb' => 'PUT'],
	['name' => 'log#listen', 'url' => '/listen', 'verb' => 'GET']
]];
