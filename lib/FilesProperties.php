<?php
/**
 * @author Artur Neumann <artur@jankaritech.com>
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

use OCP\IDBConnection;
use OCP\IRequest;
use OC\OCS\Result;
use OCP\AppFramework\Http;

/**
 * controller for FilesProperties testing
 *
 */
class FilesProperties {

	/** @var IDBConnection */
	private $connection;

	/**
	 * @var IRequest
	 */
	private $request;

	/**
	 * @param IRequest $request
	 */
	public function __construct(IDBConnection $connection, IRequest $request) {
		$this->connection = $connection;
		$this->request = $request;
	}

	/**
	 * Updates the given property for the given file
	 * if there is no property with that name for the file a new one will be created
	 *
	 * @return Result
	 * @throws \Doctrine\DBAL\DBALException
	 */
	public function upsertProperty() {
		$parameters = [];
		foreach (['user', 'propertyname', 'propertyvalue'] as $parameterName) {
			$parameters[$parameterName] = $this->request->getParam($parameterName);
			if ($parameters[$parameterName] === null) {
				return new Result(
					null, Http::STATUS_BAD_REQUEST, "parameter '$parameterName' is missing"
				);
			}
		}

		$parameters['path'] = $this->request->getParam('path');
		if ($parameters['path'] === null) {
			$parameters['id'] = $this->request->getParam('id');
			if ($parameters['id'] === null) {
				return new Result(
					null, Http::STATUS_BAD_REQUEST, "parameter id or path must be given"
				);
			}
			$id = (int)$parameters['id'];
		} else {
			try {
				$id = \OC::$server->getUserFolder($parameters['user'])->get($parameters['path'])->getId();
			} catch (\Exception $e) {
				return new Result(
					null,
					Http::STATUS_INTERNAL_SERVER_ERROR,
					"Could not get file id: '" . $e->getMessage() . "'"
				);
			}
		}
		$this->connection
			->upsert(
				'*PREFIX*properties',
				[
					'propertyname' => $parameters['propertyname'],
					'propertyvalue' => $parameters['propertyvalue'],
					'fileid' => $id,
				],
				[
					'fileid', 'propertyname'
				]
			);
		return new Result(['fileId' => $id]);
	}
}
