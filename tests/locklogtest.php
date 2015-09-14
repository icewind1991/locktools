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

namespace OCA\LockTools\Test;

use OC\Memcache\ArrayCache;
use OCA\LockTools\Log\LockLog;
use OCP\Lock\ILockingProvider;
use Test\TestCase;

class LockLogTest extends TestCase {
	private function getInstance() {
		$cache = new ArrayCache();
		return new LockLog($cache);
	}

	public function testLogSingle() {
		$instance = $this->getInstance();
		$time = microtime(true);
		$instance->log($time, '/foo', 'acquire', ['type' => ILockingProvider::LOCK_SHARED]);

		$entries = $instance->getLog();
		$this->assertCount(1, $entries);
		$this->assertEquals([
			'key' => $entries[0]['key'],
			'time' => $time,
			'path' => '/foo',
			'event' => 'acquire',
			'params' => ['type' => ILockingProvider::LOCK_SHARED]
		], $entries[0]);
	}

	public function testLogMultiple() {
		$instance = $this->getInstance();
		$time1 = microtime(true);
		$instance->log($time1, '/foo', 'acquire', ['type' => ILockingProvider::LOCK_SHARED]);
		$time2 = microtime(true);
		$instance->log($time2, '/foo', 'change', ['type' => ILockingProvider::LOCK_EXCLUSIVE]);
		$time3 = microtime(true);
		$instance->log($time3, '/foo', 'release', ['type' => ILockingProvider::LOCK_EXCLUSIVE]);

		$entries = $instance->getLog();
		$this->assertCount(3, $entries);
		$this->assertEquals([
			'key' => $entries[2]['key'],
			'time' => $time1,
			'path' => '/foo',
			'event' => 'acquire',
			'params' => ['type' => ILockingProvider::LOCK_SHARED]
		], $entries[2]); // sorted newest to oldest
		$this->assertEquals([
			'key' => $entries[1]['key'],
			'time' => $time2,
			'path' => '/foo',
			'event' => 'change',
			'params' => ['type' => ILockingProvider::LOCK_EXCLUSIVE]
		], $entries[1]);
		$this->assertEquals([
			'key' => $entries[0]['key'],
			'time' => $time3,
			'path' => '/foo',
			'event' => 'release',
			'params' => ['type' => ILockingProvider::LOCK_EXCLUSIVE]
		], $entries[0]);
	}
}
