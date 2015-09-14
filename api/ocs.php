<?php
/**
 * @author Robin Appelman <icewind@owncloud.com>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\LockTools\Api;

use OC\Files\View;
use OC\Lock\AbstractLockingProvider;
use OCP\AppFramework\Http;
use OCA\LockTools\AppInfo\Application;
use OCA\LockTools\Log\LockLog;
use OCP\Lock\ILockingProvider;
use OCP\Lock\LockedException;

class OCS {
	static public function getLog() {
		$app = new Application();
		/** @var LockLog $lockLog */
		$lockLog = $app->getContainer()->query('LockLog');

		$entries = $lockLog->getLog();

		return new \OC_OCS_Result($entries);
	}

	static private function doCall(array $params, callable $cb) {
		$app = new Application();
		$type = (int)$params['_put']['type'];
		$path = $params['_put']['path'];
		if ($type !== ILockingProvider::LOCK_SHARED and $type !== ILockingProvider::LOCK_EXCLUSIVE) {
			return new \OC_OCS_Result('Invalid type', 400, 'Invalid type ' . $type);
		}
		/** @var \OC\Files\View $view */
		list($view, $path) = $app->getViewForPath($path);
		try {
			/** @var AbstractLockingProvider $lockingProvider */
			$lockingProvider = \OC::$server->getLockingProvider();
			$lockingProvider->releaseAll();
			$result = $cb($view, $path, $type);
			$lockingProvider->clearMarkedLocks();
			return new \OC_OCS_Result($result);
		} catch (LockedException $e) {
			return new \OC_OCS_Result('Locked', Http::STATUS_LOCKED, $path . ' is locked for type ' . $type . '(' . $e->getMessage() . ')');
		}
	}

	static public function newLock($params) {
		return self::doCall($params, function (View $view, $path, $type) {
			$view->lockFile($path, $type);
			return true;
		});
	}

	static public function changeLock($params) {
		return self::doCall($params, function (View $view, $path, $type) {
			$view->changeLock($path, $type);
			return true;
		});
	}

	static public function unLock($params) {
		return self::doCall($params, function (View $view, $path, $type) {
			$view->unlockFile($path, $type);
			return true;
		});
	}
}
