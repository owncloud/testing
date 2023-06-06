<?php
/**
 * ownCloud
 *
 * @author Phil Davis <phil@jankaritech.com>
 * @copyright Copyright (c) 2018 Phil Davis phil@jankaritech.com
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
 * @author Phil Davis <phil@jankaritech.com>
 *
 * access system information
 */
class SysInfo {
	/**
	 * gathers and returns internal system information
	 *
	 * @return Result
	 */
	public function read() {
		$sysInfo = [];
		$sysInfo['server_root'] = $this->getServerRoot();
		return new Result($sysInfo, 100);
	}

	/**
	 *
	 * @return string
	 */
	private function getServerRoot() {
		return \OC::$SERVERROOT;
	}
}
