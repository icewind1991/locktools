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

namespace OCA\LockTools\Log;

use OCP\IMemcache;

class LockLog {
	/**
	 * @var IMemcache
	 */
	private $memCache;

	/**
	 * @var int
	 */
	private $ttl;

	/**
	 * LockLog constructor.
	 *
	 * @param IMemcache $memCache
	 * @param int $ttl (optional) defaults to 10 min
	 */
	public function __construct(IMemcache $memCache, $ttl = 600) {
		$this->memCache = $memCache;
		$this->ttl = $ttl;
		$this->memCache->add('key', 0);
	}

	public function log($time, $path, $event, $params = []) {
		$key = $this->memCache->inc('key');
		$this->memCache->set($key, [
			'key' => $key,
			'time' => $time,
			'path' => $path,
			'event' => $event,
			'params' => $params
		], $this->ttl);
	}

	public function getLog() {
		$key = $this->memCache->get('key');
		$exists = $this->memCache->hasKey($key);
		$entries = [];
		while ($exists) {
			$entries[] = $this->memCache->get($key);

			$key--;
			$exists = $this->memCache->hasKey($key);
		}
		return $entries;
	}
}
