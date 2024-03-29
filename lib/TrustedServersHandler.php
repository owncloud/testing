<?php
/**
 * @author Dipak Acharya<dipak@jankaritech.com>
 *
 * @copyright Copyright (c) 2019, ownCloud, Inc.
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

namespace OCA\Testing;

use OCP\API;
use OCP\IRequest;
use OC\OCS\Result;

/**
 * controller for TrustedServer testing
 *
 */
class TrustedServersHandler {
	/**
	 * @var IRequest
	 */
	private $request;

	/**
	 * @var \OCA\Federation\TrustedServers|null
	 */
	private $trustedServers;

	/**
	 * @param IRequest $request
	 */
	public function __construct(IRequest $request) {
		$this->request = $request;
		$DbHandler = '\OCA\Federation\DbHandler';
		$TrustedServer = '\OCA\Federation\TrustedServers';
		if (\class_exists($DbHandler) && \class_exists($TrustedServer)) {
			$dbHandler = new $DbHandler(
				\OC::$server->getDatabaseConnection(),
				\OC::$server->getL10N('federation')
			);
			$this->trustedServers = new $TrustedServer(
				$dbHandler,
				\OC::$server->getHTTPClientService(),
				\OC::$server->getLogger(),
				\OC::$server->getJobList(),
				\OC::$server->getSecureRandom(),
				\OC::$server->getConfig(),
				\OC::$server->getEventDispatcher()
			);
		}
	}

	/**
	 * The classes are not present if Federation is disabled.
	 * Send some useful info if it is currently disabled.
	 * Otherwise, return the response from the handler
	 *
	 * @return \OC\OCS\Result
	 */
	public function defaultHandler($param) {
		if ($this->trustedServers === null) {
			return new Result(
				null,
				API::RESPOND_SERVER_ERROR,
				'Federation app is disabled. Please enable it to use this api.'
			);
		}
		$handler = $param[0];
		return $this->{$handler}();
	}

	/**
	 * Get the list of trusted owncloud servers
	 *
	 * @return \OC\OCS\Result
	 */
	public function getTrustedServers() {
		$servers = $this->trustedServers->getServers();

		return new Result($servers, 100, null);
	}

	/**
	 * Remove a server from the trusted servers
	 *
	 * @return \OC\OCS\Result
	 */
	public function removeTrustedServer() {
		$url = $this->request->getParam('url');

		$servers = $this->trustedServers->getServers();
		foreach ($servers as $server) {
			if ($server["url"] === $url) {
				$this->trustedServers->removeServer($server["id"]);
				return new Result(null, 204);
			}
		}
		return new Result(null, 404, "Could not find $url in trusted servers");
	}

	/**
	 * Remove all server from the trusted servers
	 *
	 * @return \OC\OCS\Result
	 */
	public function removeAllTrustedServers() {
		$servers = $this->trustedServers->getServers();
		foreach ($servers as $server) {
			$this->trustedServers->removeServer($server['id']);
		}
		return new Result(null, 204, null);
	}

	/**
	 * Add given server to the list of trusted servers
	 *
	 * @return \OC\OCS\Result
	 */
	public function addTrustedServer() {
		$servers = $this->trustedServers->getServers();
		$url = $this->request->getParam('url');
		foreach ($servers as $server) {
			if ($server['url'] == $url) {
				return new Result(null, 200);
			}
		}
		$this->trustedServers->addServer($url);
		return new Result(null, 201);
	}
}
