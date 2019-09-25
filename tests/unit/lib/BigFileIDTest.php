<?php

namespace OCA\Testing\Tests\Unit\Lib;

use OCA\Testing\BigFileID;
use OCP\ILogger;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

/**
 * Class BigFileIDTest
 *
 * @package OCA\Testing\Tests\Unit\Lib
 * @group DB
 */
class BigFileIDTest extends TestCase {
	public function testBigId() {
		/** @var ILogger | MockObject $logger */
		$logger = $this->createMock(ILogger::class);
		$logger->expects(self::once())
			->method('warning')
			->with('Inserting dummy entry with fileid bigger than max int of 32 bits for testing');
		$bigID = new BigFileID(
			\OC::$server->getDatabaseConnection(),
			$logger
		);

		$bigID->increaseFileIDsBeyondMax32bits();

		$qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
		$maxId = (int) $qb->select($qb->createFunction('MAX(`fileid`)'))
			->from('filecache')
			->execute()->fetchColumn();
		self::assertEquals(2147483647, $maxId);
	}
}
