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
 * logfile access class
 */
class Logfile {

	/**
	 * reads and returns the raw content of the log file, one line per data element
	 *
	 * @param array $parameters
	 *
	 * @return Result
	 */
	public function read($parameters) {
		$noOfLinesToRead = (int)$parameters['lines'];
		$logType = \OC::$server->getConfig()->getSystemValue('log_type', 'owncloud');
		if ($logType !== 'owncloud') {
			return new Result(null, 400, "log_type is not set to 'owncloud'");
		}
		$defaultLogFile = $this->getLogFilePath();
		if ($noOfLinesToRead === 0) {
			$data = \file($defaultLogFile);
		} else {
			$data = $this->tailFile($defaultLogFile, $noOfLinesToRead);
		}
		return new Result($data, 100);
	}

	/**
	 *
	 * @return \OC\OCS\Result
	 * @throws \Exception
	 */
	public function clear() {
		$fp = \fopen($this->getLogFilePath(), 'w');
		if ($fp === false) {
			throw new \Exception("could not clear the log file");
		}
		\fclose($fp);
		return new Result();
	}

	/**
	 *
	 * @return string
	 */
	private function getLogFilePath() {
		$dataDir = \OC::$server->getConfig()->getSystemValue(
			'datadirectory', \OC::$SERVERROOT . '/data'
		);
		return \rtrim($dataDir, '/') . '/owncloud.log';
	}
	/**
	 * reads x last lines from a file
	 * Slightly modified version of
	 * http://www.geekality.net/2011/05/28/php-tail-tackling-large-files/
	 *
	 * @param string $filepath file to read
	 * @param int $noOfLinesToRead no of lines to read
	 * @param bool $adaptive make the file buffer adaptive
	 *
	 * @return array lines of the file to read
	 * @throws \Exception
	 * @author Torleif Berger, Lorenzo Stanco
	 * @link http://stackoverflow.com/a/15025877/995958
	 * @license http://creativecommons.org/licenses/by/3.0/
	 */
	private function tailFile(
		$filepath, $noOfLinesToRead = 1, $adaptive = true
	) {
		$lines = $noOfLinesToRead; //set a counter
		// Open file
		$f = @\fopen($filepath, "rb");
		if ($f === false) {
			throw new \Exception("could not read file '$filepath'");
		}
		
		// Sets buffer size, according to the number of lines to retrieve.
		// This gives a performance boost when reading a few lines from the file.
		if (!$adaptive) {
			$buffer = 4096;
		} else {
			$buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));
		}
		
		// Jump to last character
		\fseek($f, -1, SEEK_END);
		
		// Read it and adjust line number if necessary
		// Otherwise the result would be wrong if file doesn't end with a blank line
		if (\fread($f, 1) !== "\n") {
			$lines -= 1;
		}

		// Start reading
		$output = '';
		
		// While we would like more
		while (\ftell($f) > 0 && $lines >= 0) {
			
			// Figure out how far back we should jump
			$seek = \min(\ftell($f), $buffer);
			
			// Do the jump (backwards, relative to where we are)
			\fseek($f, -$seek, SEEK_CUR);
			
			// Read a chunk and prepend it to our output
			$output = ($chunk = \fread($f, $seek)) . $output;
			
			// Jump back to where we started reading
			\fseek($f, -\mb_strlen($chunk, '8bit'), SEEK_CUR);
			
			// Decrease our line counter
			$lines -= \substr_count($chunk, "\n");
		}
		
		// While we have too many lines
		// (Because of buffer size we might have read too many)
		while ($lines++ < 0) {
			// Find first newline and remove all text before that
			$output = \substr($output, \strpos($output, "\n") + 1);
		}
		
		// Close file and return
		\fclose($f);
		$output = \explode("\n", $output);
		if ($output[\count($output) - 1] === "") {
			\array_pop($output);
		}
		if (\count($output) > $noOfLinesToRead) {
			throw new \Exception("size of output array is bigger than expected");
		}
		return $output;
	}
}
