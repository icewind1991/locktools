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

namespace OCA\LockTools\Queue;

use OC\Hooks\Emitter;
use OC\Hooks\EmitterTrait;

class LockEmitter implements Emitter {
	use EmitterTrait;

	/**
	 * @var Queue
	 */
	private $queue;

	/**
	 * @var int
	 */
	private $lastKnown;

	/**
	 * LockEmitter constructor.
	 *
	 * @param Queue $queue
	 * @param int $lastKnownLockKey
	 */
	public function __construct(Queue $queue, $lastKnownLockKey) {
		$this->queue = $queue;
		$this->lastKnown = $lastKnownLockKey;

		$this->queue->listen('Queue', 'message', function ($entry) {
			if ($entry['key'] > $this->lastKnown) {
				$this->lastKnown = $entry['key'];
				$this->emit('LockEmitter', 'lock', [$entry]);
			}
		});
	}

	public function poll() {
		$this->queue->poll();
	}
}
