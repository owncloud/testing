<?php

namespace OCA\Testing\Tests\Unit\Lib;

use OC\AppFramework\Http\Request;
use OCA\Testing\ApacheModules;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

/**
 * Class ApacheModulesTest
 *
 * @package OCA\Testing\Tests\Unit\Lib
 */
class ApacheModulesTest extends TestCase {
	/**
	 * @var MockObject
	 */
	private $apacheModule;

	public function setUp(): void {
		parent::setUp();

		$this->apacheModule = $this->getMockBuilder(ApacheModules::class)
			->setConstructorArgs([new Request()])
			->setMethods(['isApache', 'getModules'])
			->getMock();
	}

	public function testWhenNotBehindApache() {
		$this->apacheModule->expects($this->once())
			->method('isApache')
			->willReturn(false);

		$result = $this->apacheModule->getModule(["module" => "core"]);

		$this->assertEquals($result->getMeta(),
			[
				"message" => "the server does not seem to be running behind Apache.",
				"statuscode" => 998,
				'status' => 'failure'
			]
		);
		$this->assertEmpty($result->getData());
	}

	public function testWhenBehindApacheAndAvailableModule() {
		$this->apacheModule->expects($this->once())
			->method('isApache')
			->willReturn(true);
		$this->apacheModule->expects($this->once())
			->method('getModules')
			->willReturn(['core', 'core2']);

		$result = $this->apacheModule->getModule(["module" => "core"]);

		$this->assertEquals($result->getMeta(),
			[
				"message" => null,
				"statuscode" => 100,
				'status' => 'ok'
			]
		);
		$this->assertEquals($result->getData(), ["message" => "core is loaded in apache_modules."]);
	}

	public function testWhenBehindApacheAndNotAvailableModule() {
		$this->apacheModule->expects($this->once())
			->method('isApache')
			->willReturn(true);
		$this->apacheModule->expects($this->once())
			->method('getModules')
			->willReturn([]);

		$result = $this->apacheModule->getModule(["module" => "core"]);

		$this->assertEquals($result->getMeta(),
			[
				"message" => "core could not be found in apache_modules.",
				"statuscode" => 998,
				'status' => 'failure'
			]
		);
		$this->assertEquals($result->getData(), []);
	}
}
