<?php
/**
 * ownCloud
 *
 * @author Artur Neumann <artur@jankaritech.com>
 * @copyright Copyright (c) 2018 Artur Neumann artur@jankaritech.com
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

namespace OCA\Testing\Dav;

use OCP\ILogger;
use Sabre\DAV\Server;
use Sabre\DAV\ServerPlugin;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;

/**
 * Sabre plugin for the the file firewall:
 */
class SlowdownPlugin extends ServerPlugin {
	const NS_OWNCLOUD = 'http://owncloud.org/ns';

	/**
	 * @var Server $server
	 */
	private $server;

	/**
	 * @var ILogger
	 */
	private $logger;

	/**
	 * SlowdownPlugin plugin
	 *
	 * @param ILogger $logger
	 */
	public function __construct(ILogger $logger) {
		$this->logger = $logger;
	}

	/**
	 * registers an event for every method mentioned in 'dav.slowdown' setting
	 *
	 * @param Server $server
	 *
	 * @return void
	 */
	public function initialize(Server $server) {
		$this->server = $server;
		$slowDown = \OC::$server->getConfig()->getSystemValue('dav.slowdown', '{}');
		$this->slowDownSettings = \json_decode($slowDown, true);
		foreach ($this->slowDownSettings as $method => $seconds) {
			$this->server->on("method:$method", [$this, 'sleep'], 90);
		}
	}

	/**
	 *
	 * @param RequestInterface $request request object
	 * @param ResponseInterface $response response object
	 * @throws \Sabre\DAV\Exception\Forbidden
	 * @return boolean
	 */
	public function sleep(
		RequestInterface $request, ResponseInterface $response
	) {
		$timeToSleep = $this->slowDownSettings[\strtoupper($request->getMethod())];
		$this->logger->info("time to sleep $timeToSleep");
		for ($i = 0; $i <= $timeToSleep; $i++) {
			$this->logger->info("sleeping $i ...");
			\sleep(1);
		}
	}
}
