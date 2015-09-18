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
use OCP\IMemcache;

/**
 * Memcache based message queue using a circular buffer
 */
class Queue implements Emitter {
	use EmitterTrait;

	const RESULT_PUSHED = 2;
	const RESULT_PROCESSED = 1;
	const RESULT_NO_MESSAGE = 0;
	const ERROR_ALREADY_PROCESSED = -1;
	const ERROR_ID_ALREADY_USED = -2;

	const BUFFER_SIZE = 1024;
	const MAX_TRIES = 10;
	const TRY_PAUSE = 10; // time to sleep after a failed try in ms

	/**
	 * @var IMemcache
	 */
	private $memCache;

	/**
	 * Queue constructor.
	 *
	 * @param IMemcache $memCache
	 */
	public function __construct(IMemcache $memCache) {
		$this->memCache = $memCache;
	}

	/**
	 * Pull a message from the queue and emit it
	 *
	 * Will not retry on race conditions
	 *
	 * @return int self::RESULT_PROCESSED, self::ERROR_ALREADY_PROCESSED or self::RESULT_NO_MESSAGE
	 */
	private function pullMessage() {
		$lastMessage = $this->memCache->get('lastMessage');
		$lastProcessed = $this->memCache->get('lastProcessed');

		if ($lastMessage !== $lastProcessed) {
			$messageId = ($lastProcessed + 1) % self::BUFFER_SIZE;
			if ($this->memCache->cas('lastProcessed', $lastProcessed, $messageId)) {
				$message = $this->memCache->get($messageId);
				$this->emit('Queue', 'message', [$message]);
				$this->memCache->remove($messageId);
				return self::RESULT_PROCESSED;
			} else {
				// another process has already processed this message
				return self::ERROR_ALREADY_PROCESSED;
			}
		} else {
			return self::RESULT_NO_MESSAGE;
		}
	}

	/**
	 * Push a message to the queue
	 *
	 * Will not retry on race conditions
	 *
	 * @param mixed $message
	 * @return int self::RESULT_PUSHED or self::ERROR_ID_ALREADY_USED
	 * @throws \Exception
	 */
	private function pushMessage($message) {
		$lastMessage = $this->memCache->get('lastMessage');
		$messageId = ($lastMessage + 1) % self::BUFFER_SIZE;
		if ($this->memCache->add($messageId, $message, 100)) {
			if ($this->memCache->cas('lastMessage', $lastMessage, $messageId)) {
				return self::RESULT_PUSHED;
			} else {
				// this shouldn't happen due to the locking `add` gives us, so we fail hard
				throw new \Exception('unexpected value for lastMessage');
			}
		} else {
			\OC::$server->getLogger()->warning('id conflict');
			// another processes pushed a message before us
			return self::ERROR_ID_ALREADY_USED;
		}
	}

	/**
	 * Push a message to the queue
	 *
	 * Will retry on race conditions
	 *
	 * @param mixed $message
	 * @throws \Exception
	 */
	public function push($message) {
		$tries = 0;
		while ($tries < self::MAX_TRIES) {
			if ($this->pushMessage($message) === self::RESULT_PUSHED) {
				return;
			}
			$tries++;
			usleep(self::TRY_PAUSE * 1000);
		}
	}

	/**
	 * Pull a message from the queue and emit it
	 *
	 * Will retry on race conditions
	 */
	public function poll() {
		$tries = 0;
		while ($tries < self::MAX_TRIES) {
			$result = $this->pullMessage();
			$i = 0;
			while ($result === self::RESULT_PROCESSED && $i < 10) {
				$result = $this->pullMessage();
				$i++;
			}
			if ($result !== self::ERROR_ALREADY_PROCESSED) {
				return;
			}
			$tries++;
			usleep(self::TRY_PAUSE * 1000);
		}
	}

	public function clear() {
		$this->memCache->clear();
	}
}
