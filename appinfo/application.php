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

namespace OCA\LockTools\AppInfo;

use Doctrine\DBAL\Schema\View;
use OC\Files\Filesystem;
use OCA\LockTools\Log\LockLog;
use OCA\LockTools\Monitor\Monitor;
use OCA\LockTools\Monitor\MonitorWrapper;
use OCP\AppFramework\App;
use OCP\Files\Storage;
use OCP\IContainer;

class Application extends App {
	public function __construct(array $urlParams = array()) {
		parent::__construct('locktools', $urlParams);
		$container = $this->getContainer();

		$container->registerService('\OCA\LockTools\Log\LockLog', function (IContainer $c) {
			/** @var \OC\Server $server */
			$server = $c->query('ServerContainer');
			return new LockLog(
				$server->getMemCacheFactory()->create('locklog'),
				$server->getConfig()->getAppValue('locktools', 'ttl', 600)
			);
		});
		$container->registerAlias('LockLog', '\OCA\LockTools\Log\LockLog');

		$container->registerService('\OCA\LockTools\Monitor\Monitor', function (IContainer $c) {
			return new Monitor(
				$c->query('\OCA\LockTools\Log\LockLog')
			);
		});
		$container->registerAlias('Monitor', '\OCA\LockTools\Monitor\Monitor');
	}

	public function setupWrapper() {
		$storageFactory = Filesystem::getLoader();
		$monitor = $this->getContainer()->query('\OCA\LockTools\Monitor\Monitor');
		$storageFactory->addStorageWrapper('locktools', function ($mountPoint, Storage $storage) use ($monitor) {
			return new MonitorWrapper(['storage' => $storage, 'monitor' => $monitor, 'mountpoint' => $mountPoint]);
		});
	}

	public function getViewForPath($path) {
		$path = Filesystem::normalizePath($path);
		list(, $user) = explode('/', $path, 3);
//		\OC_Util::tearDownFS();
		\OC_Util::setupFS($user);
		$view = Filesystem::getView();
		return [$view, $view->getRelativePath($path)];
	}
}
