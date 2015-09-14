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

use OCA\LockTools\Log\LockLog;

class Monitor {
	/**
	 * @var LockLog
	 */
	private $log;

	/**
	 * Monitor constructor.
	 *
	 * @param LockLog $log
	 */
	public function __construct(LockLog $log) {
		$this->log = $log;
	}

	public function acquireLock($path, $type) {
		$this->log->log(microtime(true), $path, 'acquire', ['type' => $type]);
	}

	public function changeLock($path, $type) {
		$this->log->log(microtime(true), $path, 'change', ['type' => $type]);
	}

	public function releaseLock($path, $type) {
		$this->log->log(microtime(true), $path, 'release', ['type' => $type]);
	}

	public function lockedException($path, $type, $operation) {
		$this->log->log(microtime(true), $path, 'error', ['type' => $type, 'operation' => $operation]);
	}
}
