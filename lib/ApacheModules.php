<?php
/**
 * ownCloud
 *
 * @author Saugat Pachhai <saugat@jankaritech.com>
 * @copyright Copyright (c) 2019 Saugat Pachhai saugat@jankaritech.com
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

use OC\OCS\Result;
use OCP\API;
use OCP\IRequest;

class ApacheModules {
	/**
	 *
	 * @var IRequest
	 */
	private $request;

	/**
	 *
	 * @param IRequest $request
	 */
	public function __construct(IRequest $request) {
		$this->request = $request;
	}

	protected function isApache() {
		return (\strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false);
	}

	protected function getModules() {
		if (\function_exists("apache_get_modules")) {
			return apache_get_modules();
		}
		return [];
	}

	/**
	 *
	 * @return Result
	 */
	public function getModule(array $parameters) {
		$module = $parameters["module"];

		if (!$this->isApache()) {
			return new Result(null, API::RESPOND_NOT_FOUND, 'the server does not seem to be running behind Apache.');
		}

		if (\in_array($module, $this->getModules(), true)) {
			return new Result(["message" => "$module is loaded in apache_modules."], 100, null);
		}

		return new Result(null, API::RESPOND_NOT_FOUND, "$module could not be found in apache_modules.");
	}
}
