<?php
/**
 * @author Phil Davis <phil@jankaritech.com>
 *
 * @copyright Copyright (c) 2021, ownCloud GmbH
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

use OCP\IConfig;
use OCP\IRequest;
use OC\OCS\Result;

/*
 * This allows tests to set, change and delete system config settings that are
 * stored in config/config.php without having to use the occ config:system commands
 */
class SysConfig {

	/** @var IConfig */
	private $config;

	/** @var IRequest */
	private $request;

	/**
	 * @param IConfig $config
	 * @param IRequest $request
	 */
	public function __construct(IConfig $config, IRequest $request) {
		$this->config = $config;
		$this->request = $request;
	}

	/**
	 * @param array $parameters
	 * @return Result
	 */
	public function setSysConfigValue(array $parameters): Result {
		$configKey = $parameters['configkey'];

		$textValue = $this->request->getParam('value');
		$dataType = $this->request->getParam('type');
		$value = $this->castValue($textValue, $dataType);
		$this->config->setSystemValue($configKey, $value);

		return new Result();
	}

	/**
	 * @param array $parameters
	 * @return Result
	 */
	public function deleteSysConfigValue(array $parameters): Result {
		$configKey = $parameters['configkey'];

		$this->config->deleteSystemValue($configKey);

		return new Result();
	}

	/**
	 * @param array $parameters
	 * @return Result
	 */
	public function getSysConfigValue(array $parameters): Result {
		$configKey = $parameters['configkey'];
		
		$value = $this->config->getSystemValue($configKey);
		$result[] = [
			'configkey' => $configKey,
			'value' => $value,
			'type' => \gettype($value)
		];
		return new Result($result);
	}

	/**
	 * @param string $value
	 * @param string $type
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	protected function castValue(string $value, string $type) {
		switch ($type) {
			case 'integer':
			case 'int':
				if (!\is_numeric($value)) {
					throw new \InvalidArgumentException('Non-numeric value specified');
				}
				return (int) $value;

			case 'double':
			case 'float':
				if (!\is_numeric($value)) {
					throw new \InvalidArgumentException('Non-numeric value specified');
				}
				return (double) $value;

			case 'boolean':
			case 'bool':
				$value = \strtolower($value);
				switch ($value) {
					case 'true':
						return true;

					case 'false':
						return false;

					default:
						throw new \InvalidArgumentException('Unable to parse value as boolean');
				}

			// no break
			case 'null':
				return null;

			case 'string':
				return (string) $value;

			case 'json':
				$decodedJson = \json_decode($value, true);
				if ($decodedJson === null) {
					throw new \InvalidArgumentException('Unable to parse value as json');
				}
				return $decodedJson;

			default:
				throw new \InvalidArgumentException('Invalid type');
		}
	}
}
