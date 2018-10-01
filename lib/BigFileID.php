<?php
/**
 * @author Sergio Bertolin <sbertolin@owncloud.com>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH
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

use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\Schema\Sequence;
use OC\OCS\Result;
use OCP\IDBConnection;
use OCP\ILogger;

/**
 * Class for increasing file ids over 32 bits max int
 */
class BigFileID {

	/** @var IDBConnection */
	private $connection;
	/** @var ILogger */
	private $logger;

	public function __construct(IDBConnection $connection, ILogger $logger) {
		$this->connection = $connection;
		$this->logger = $logger;
	}

	/**
	 * Put a dummy entry to make the autoincrement go beyond the 32 bits limit
	 *
	 * @return Result
	 * @throws \Doctrine\DBAL\DBALException
	 */
	public function increaseFileIDsBeyondMax32bits() {
		$this->logger->warning('Inserting dummy entry with fileid bigger than max int of 32 bits for testing');

		if ($this->connection->getDatabasePlatform() instanceof OraclePlatform) {
			$seq = new Sequence('"*PREFIX*filecache_SEQ"', 1, 2147483647);
			$dropSql = $this->connection->getDatabasePlatform()->getDropSequenceSQL($seq);
			$createSql = $this->connection->getDatabasePlatform()->getCreateSequenceSQL($seq);

			$this->connection->executeQuery($dropSql);
			$this->connection->executeQuery($createSql);
		}

		$this->connection->insertIfNotExist('*PREFIX*filecache',
			[
				'fileid' => 2147483647, 'storage' => 10000, 'path' => 'dummy',
				'path_hash' => '59f91d3e7ebd97ade2e147d2066cc4eb', 'parent' => '5831',
				'name' => '', 'mimetype' => 4, 'mimepart' => 3, 'size' => 163,
				'mtime' => 1499256550, 'storage_mtime' => 1499256550, 'encrypted' => 0,
				'unencrypted_size' => 0, 'etag' => '595cd6e63f375', 'permissions' => 27, 'checksum' => null],
			['fileid']);

		return new Result();
	}
}
