<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Felix Moeller <mail@felixmoeller.de>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Vincent Petry <pvince81@owncloud.com>
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @copyright Olivier Paroz 2015
 */

namespace OCA\LockTools\Queue;

/**
 *
 * @todo Replace with a library
 */
class EventSource implements \OCP\IEventSource {

	/**
	 * @var bool
	 */
	private $started = false;

	protected function init() {
		if ($this->started) {
			return;
		}
		$this->started = true;

		// prevent php output buffering, caching and nginx buffering
		while (ob_get_level()) {
			ob_end_clean();
		}
		header('Cache-Control: no-cache');
		header('X-Accel-Buffering: no');
		header("Content-Type: text/event-stream");
		flush();
	}

	/**
	 * Sends a message to the client
	 *
	 * If only one parameter is given, a typeless message will be sent with that parameter as data
	 *
	 * @param string $type
	 * @param mixed $data
	 *
	 * @throws \BadMethodCallException
	 */
	public function send($type, $data = null) {
		if ($data && !preg_match('/^[A-Za-z0-9_]+$/', $type)) {
			throw new \BadMethodCallException('Type needs to be alphanumeric (' . $type . ')');
		}
		$this->init();
		if (is_null($data)) {
			$data = $type;
			$type = null;
		}

		if (!empty($type)) {
			echo 'event: ' . $type . PHP_EOL;
		}
		echo 'data: ' . json_encode($data) . PHP_EOL;

		echo PHP_EOL;
		flush();
	}

	/**
	 * Closes the connection of the event source
	 *
	 * It's best to let the client close the stream
	 */
	public function close() {
		$this->send(
			'__internal__', 'close'
		);
	}
}
