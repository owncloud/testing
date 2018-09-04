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
use OCP\IConfig;
use OCP\IRequest;

/**
 *
 * @author Phil Davis <phil@jankaritech.com>
 *
 * manipulate files and folders on the server
 */
class ServerFiles {
	/**
	 * @var IRequest
	 */
	private $request;

	/**
	 * @param IConfig $config
	 * @param IRequest $request
	 */
	public function __construct(IRequest $request) {
		$this->request = $request;
	}

	/**
	 * Create the specified directory under the server root
	 *
	 * 'dir' is the directory to create, which may be inside other directories
	 * e.g. 'apps2/myapp/appinfo'
	 *
	 * @return Result
	 */
	public function mkDir() {
		$dir = \trim($this->request->getParam('dir'), '/');
		$targetDir = \OC::$SERVERROOT . "/$dir";
		if (!\file_exists($targetDir)) {
			// Ask for the full mode, it will be masked by the current umask anyway
			// Create recursively so that multiple levels of directory can be
			// created at once.
			\mkdir($targetDir, 0777, true);
		}

		return new Result();
	}

	/**
	 * Create the specified file under the server root
	 *
	 * 'file' is the file to create, including path from the server root
	 * e.g. 'apps2/myapp/appinfo/info.xml'
	 * 'content' is the data to write into the file
	 *
	 * @return Result
	 */
	public function createFile() {
		$filePath = \trim($this->request->getParam('file'), '/');
		$content = $this->request->getParam('content');
		$targetFile = \OC::$SERVERROOT . "/$filePath";
		\file_put_contents($targetFile, $content);

		return new Result();
	}
}
