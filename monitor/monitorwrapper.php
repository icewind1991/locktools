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

namespace OCA\LockTools\Monitor;

use OC\Files\Filesystem;
use OC\Files\Storage\Wrapper\Wrapper;
use OCP\Lock\ILockingProvider;
use OCP\Lock\LockedException;

class MonitorWrapper extends Wrapper {
	/**
	 * @var Monitor
	 */
	private $monitor;

	/**
	 * @var string
	 */
	private $mountPoint;

	/**
	 * @param array $parameters
	 */
	public function __construct($parameters) {
		parent::__construct($parameters);
		$this->monitor = $parameters['monitor'];
		$this->mountPoint = Filesystem::normalizePath($parameters['mountpoint']);
	}

	/**
	 * @param string $path
	 * @return string
	 */
	private function getFullPath($path) {
		if ($path !== '') {
			$path = '/' . $path;
		}
		return $this->mountPoint . $path;
	}

	/**
	 * {@inheritdoc}
	 */
	public function acquireLock($path, $type, ILockingProvider $provider) {
		try {
			parent::acquireLock($path, $type, $provider);
		} catch (LockedException $e) {
			$this->monitor->lockedException($this->getFullPath($path), $type, 'acquire');
			throw $e;
		}
		$this->monitor->acquireLock($this->getFullPath($path), $type);
	}

	/**
	 * {@inheritdoc}
	 */
	public function releaseLock($path, $type, ILockingProvider $provider) {
		try {
			parent::releaseLock($path, $type, $provider);
		} catch (LockedException $e) {
			$this->monitor->lockedException($this->getFullPath($path), $type, 'release');
			throw $e;
		}
		$this->monitor->releaseLock($this->getFullPath($path), $type);
	}

	/**
	 * {@inheritdoc}
	 */
	public function changeLock($path, $type, ILockingProvider $provider) {
		try {
			parent::changeLock($path, $type, $provider);
		} catch (LockedException $e) {
			$this->monitor->lockedException($this->getFullPath($path), $type, 'change');
			throw $e;
		}
		$this->monitor->changeLock($this->getFullPath($path), $type);
	}
}
