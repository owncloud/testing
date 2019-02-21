<?php
/**
 * ownCloud
 *
 * @author Artur Neumann <artur@jankaritech.com>
 * @copyright Copyright (c) 2019 Artur Neumann artur@jankaritech.com
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

use OCP\IRequest;
use OC\Files\Filesystem;
use OC\OCS\Result;

/**
 *
 * @author Artur Neumann <artur@jankaritech.com>
 *
 */
class TestingSkeletonDirectory {
	/**
	 * @var IRequest
	 */
	private $request;
	
	/**
	 * @param IRequest $request
	 */
	public function __construct(IRequest $request) {
		$this->request = $request;
	}

	/**
	 * returns the root directory of the skeleton directories for various tests
	 *
	 * @return Result
	 */
	public function get() {
		return new Result(['rootdirectory' => \realpath(__DIR__ . "/../data/")]);
	}

	/**
	 * set a folder below the data folder as skeleton directory
	 *
	 * @return Result
	 */
	public function set() {
		$folder = \trim($this->request->getParam('directory'), '/');
		$folder = Filesystem::normalizePath($folder, true);
		if (Filesystem::isValidPath($folder) === false) {
			return new Result(null, 400, "invalid folder name");
		}
		$fullPath = \realpath(__DIR__ . "/../data/$folder");
		if ($fullPath === false || !\file_exists($fullPath)) {
			return new Result(null, 404, "skeleton directory not found");
		}
		\OC::$server->getConfig()->deleteSystemValue('skeletondirectory');
		\OC::$server->getConfig()->setSystemValue('skeletondirectory', $fullPath);
		return new Result();
	}
}
