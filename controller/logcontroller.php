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

namespace OCA\LockTools\Controller;

use OCA\LockTools\Log\LockLog;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IRequest;

/**
 * Class LogController
 *
 * @package OCA\React_OC_Boilerplate\Controller
 */
class LogController extends Controller {
	/**
	 * @var LockLog
	 */
	private $lockLog;

	/**
	 * @var IConfig
	 */
	private $config;

	/**
	 * LogController constructor.
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param LockLog $lockLog
	 * @param IConfig $config
	 */
	public function __construct(
		$appName,
		IRequest $request,
		LockLog $lockLog,
		IConfig $config
	) {
		parent::__construct($appName, $request);
		$this->lockLog = $lockLog;
		$this->config = $config;
	}

	public function get() {
		return $this->lockLog->getLog();
	}

	public function timeout() {
		return $this->config->getAppValue('locktools', 'ttl', 600);
	}

	public function setTimeout($timeout) {
		$this->config->setAppValue('locktools', 'ttl', (int)$timeout);
	}
}
