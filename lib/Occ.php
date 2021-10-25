<?php
/**
 * ownCloud
 *
 * @author Artur Neumann <artur@jankaritech.com>
 * @copyright Copyright (c) 2017 Artur Neumann artur@jankaritech.com
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
use OCP\IRequest;

/**
 * run the occ command from an API call
 *
 * @author Artur Neumann <artur@jankaritech.com>
 *
 */
class Occ {

	// Variables defined in CI environments to pass automatically on
	// to subshells when using remoteocc
	public const TESTING_ENV_VARS = [
		'CI',
		'PATH',
	];

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

	/**
	 * @param string $command
	 * @param Array $reqEnvVars
	 * @param string $userInput
	 *
	 * @return Array
	 */
	public function runCommand($command, $reqEnvVars, $userInput) {
		// Match the pieces of the string that are like:
		//   Strings with single-quoted parts and there could be space(s) in the
		//   single-quoted parts:
		//     --display-name='User One'
		//     --email='user1@example.org'
		//     'An already quoted literal value with spaces in it'
		//     ' '
		//     ''
		//   Simple strings like:
		//     user:add
		//     user1
		//     --password-from-env

		$envVars = \array_merge($this->getDefaultEnv(), $reqEnvVars);
		\preg_match_all("/\S*?'[^']*?'|\S+/", $command, $matches);
		$args = $matches[0];
		$args = \array_map(
			function ($arg) {
				// if the arg is already surrounded by single-quotes or the arg looks like:
				//   something='abc'
				// then do not use escapeshellarg on it.
				if (((\substr($arg, 0, 1) === "'") || \strpos($arg, "='", 1) !== false)
					&& (\substr($arg, -1) === "'")) {
					return $arg;
				}
				return \escapeshellarg($arg);
			},
			$args
		);

		$args = \implode(' ', $args);

		$input = '';
		if($userInput !== '' && $userInput !== null) {
			$input = "echo '$userInput' | ";
		}

		$descriptor = [
			0 => ['pipe', 'r'],
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];

		$process = \proc_open(
			$input . 'php console.php ' . $args,
			$descriptor,
			$pipes,
			\realpath("../"),
			$envVars
		);
		$lastStdOut = \stream_get_contents($pipes[1]);
		$lastStdErr = \stream_get_contents($pipes[2]);
		$lastCode = \proc_close($process);
		$result = [
			"code" => $lastCode,
			"stdOut" => $lastStdOut,
			"stdErr" => $lastStdErr
		];

		return $result;
	}

	/**
	 *
	 * @return Result
	 */
	public function bulkOccExecute() {
		$data = \json_decode(\file_get_contents('php://input'), true);
		$results = [];
		$highCode = 100;
		foreach ($data as $item) {
			if (!\array_key_exists("command", $item)) {
				return new Result(null, 405, "Invalid format for the data, please check!");
			}
			if (\array_key_exists("env_variables", $item)) {
				if (\is_array($item['env_variables'])) {
					$envVariables = $item['env_variables'];
				} else {
					return new Result(null, 405, "Invalid format for the data, please check!");
				}
			} else {
				$envVariables = [];
			}

			$result = $this->runCommand($item["command"], $envVariables);
			\array_push($results, $result);
			if ($result['code'] + 100 > $highCode) {
				$highCode = $result["code"] + 100;
			}
		}
		return new Result($results, $highCode);
	}

	/**
	 *
	 * @return Result
	 */
	public function execute() {
		$command = $this->request->getParam("command", "");
		$reqEnvVars = $this->request->getParam("env_variables", []);
		$userInput = $this->request->getParam("user_input", "");

		$result = $this->runCommand($command, $reqEnvVars, $userInput);
		$resultCode = $result['code'] + 100;

		return new Result($result, $resultCode);
	}

	// Taken from https://github.com/symfony/process/blob/master/Process.php
	private function getDefaultEnv() {
		$env = [];
		foreach ($_SERVER as $k => $v) {
			if (\is_string($v) && false !== $v = \getenv($k)) {
				$env[$k] = $v;
			}
		}
		foreach ($_ENV as $k => $v) {
			if (\is_string($v)) {
				$env[$k] = $v;
			}
		}
		foreach (self::TESTING_ENV_VARS as $k) {
			if (\getenv($k)) {
				$env[$k] = \getenv($k);
			}
		}
		return $env;
	}
}
