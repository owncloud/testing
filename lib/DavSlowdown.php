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

namespace OCA\Testing;

use OC\OCS\Result;

/**
 *
 * @author Artur Neumann <artur@jankaritech.com>
 *
 */
class DavSlowdown {
	/**
	 * save the settings in a system setting
	 *
	 * @param array $parameters
	 *
	 * @return Result
	 */
	public function setSlowdown($parameters) {
		$method = \strtoupper($parameters['method']);
		$seconds = (int)$parameters['seconds'];
		$slowDown = \OC::$server->getConfig()->getSystemValue('dav.slowdown', '{}');
		$slowDown = \json_decode($slowDown, true);
		$slowDown[$method] = $seconds;
		\OC::$server->getConfig()->setSystemValue('dav.slowdown', \json_encode($slowDown));
		
		return new Result();
	}
}
