<?php
/**
 * @author Björn Schießle <schiessle@owncloud.com>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
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


namespace OCA\Federation\Tests\lib;


use OCA\Federation\TrustedServers;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use OCP\IDBConnection;
use OCP\ILogger;
use Test\TestCase;

class TrustedServersTest extends TestCase {

	/** @var  TrustedServers */
	private $trustedServers;

	/** @var  IDBConnection */
	private $connection;

	/** @var \PHPUnit_Framework_MockObject_MockObject | IClientService */
	private $httpClientService;

	/** @var  \PHPUnit_Framework_MockObject_MockObject | IClient */
	private $httpClient;

	/** @var  \PHPUnit_Framework_MockObject_MockObject | IResponse */
	private $response;

	/** @var  \PHPUnit_Framework_MockObject_MockObject | ILogger */
	private $logger;

	/** @var string  */
	private $dbTable = 'trusted_servers';

	public function setUp() {
		parent::setUp();

		$this->connection = \OC::$server->getDatabaseConnection();
		$this->httpClientService = $this->getMock('OCP\Http\Client\IClientService');
		$this->httpClient = $this->getMock('OCP\Http\Client\IClient');
		$this->response = $this->getMock('OCP\Http\Client\IResponse');
		$this->logger = $this->getMock('OCP\ILogger');

		$this->trustedServers = new TrustedServers(
			$this->connection,
			$this->httpClientService,
			$this->logger
		);

		$query = $this->connection->getQueryBuilder()->select('*')->from($this->dbTable);
		$result = $query->execute()->fetchAll();
		$this->assertEmpty($result, 'we need to start with a empty trusted_servers table');
	}

	public function tearDown() {
		parent::tearDown();
		$query = $this->connection->getQueryBuilder()->delete($this->dbTable);
		$query->execute();
	}

	public function testAddServer() {
		$this->trustedServers->addServer('http://server1');

		$query = $this->connection->getQueryBuilder()->select('*')->from($this->dbTable);
		$result = $query->execute()->fetchAll();
		$this->assertSame(1, count($result));
		$this->assertSame('server1', $result[0]['url']);
	}

	public function testRemoveServer() {
		$this->trustedServers->addServer('http://server1');
		$this->trustedServers->addServer('server2');

		$query = $this->connection->getQueryBuilder()->select('*')->from($this->dbTable);
		$result = $query->execute()->fetchAll();
		$this->assertSame(2, count($result));
		$this->assertSame('server1', $result[0]['url']);
		$this->assertSame('server2', $result[1]['url']);

		$this->trustedServers->removeServer('http://server2');
		$query = $this->connection->getQueryBuilder()->select('*')->from($this->dbTable);
		$result = $query->execute()->fetchAll();
		$this->assertSame(1, count($result));
		$this->assertSame('server1', $result[0]['url']);
	}

	public function testGetServers() {
		$this->trustedServers->addServer('server1');
		$this->trustedServers->addServer('server2');

		$result = $this->trustedServers->getServers();
		$this->assertSame(2, count($result));
		$this->assertSame('server1', $result[0]['url']);
		$this->assertSame('server2', $result[1]['url']);
	}

	/**
	 * @dataProvider dataTestIsTrustedServer
	 *
	 * @param string $serverInTable
	 * @param string $checkForServer
	 * @param bool $expected
	 */
	public function testIsTrustedServer($serverInTable, $checkForServer, $expected) {
		$this->trustedServers->addServer($serverInTable);
		$this->assertSame($expected,
			$this->trustedServers->isTrustedServer($checkForServer)
		);
	}

	public function dataTestIsTrustedServer() {
		return [
			['server1', 'server1', true],
			['server1', 'server1', true],
			['http://server1', 'server1', true],
			['server1', 'server2', false]
		];
	}

	/**
	 * @dataProvider dataTestIsOwnCloudServer
	 *
	 * @param int $statusCode
	 * @param bool $isValidOwnCloudVersion
	 * @param bool $expected
	 */
	public function testIsOwnCloudServer($statusCode, $isValidOwnCloudVersion, $expected) {

		$server = 'server1';

		/** @var \PHPUnit_Framework_MockObject_MockObject | TrustedServers $trustedServer */
		$trustedServers = $this->getMockBuilder('OCA\Federation\TrustedServers')
			->setConstructorArgs(
				[
					$this->connection,
					$this->httpClientService,
					$this->logger
				]
			)
			->setMethods(['checkOwnCloudVersion'])
			->getMock();

		$this->httpClientService->expects($this->once())->method('newClient')
			->willReturn($this->httpClient);

		$this->httpClient->expects($this->once())->method('get')->with($server . '/status.php')
			->willReturn($this->response);

		$this->response->expects($this->once())->method('getStatusCode')
			->willReturn($statusCode);

		if ($statusCode === 200) {
			$trustedServers->expects($this->once())->method('checkOwnCloudVersion')
				->willReturn($isValidOwnCloudVersion);
		} else {
			$trustedServers->expects($this->never())->method('checkOwnCloudVersion');
		}

		$this->assertSame($expected,
			$trustedServers->isOwnCloudServer($server)
		);

	}

	public function dataTestIsOwnCloudServer() {
		return [
			[200, true, true],
			[200, false, false],
			[404, true, false],
		];
	}

	public function testIsOwnCloudServerFail() {
		$server = 'server1';

		$this->httpClientService->expects($this->once())->method('newClient')
			->willReturn($this->httpClient);

		$this->logger->expects($this->once())->method('error')
			->with('simulated exception', ['app' => 'federation']);

		$this->httpClient->expects($this->once())->method('get')->with($server . '/status.php')
			->willReturnCallback(function() {
				throw new \Exception('simulated exception');
			});

		$this->assertFalse($this->trustedServers->isOwnCloudServer($server));

	}

	/**
	 * @dataProvider dataTestCheckOwnCloudVersion
	 *
	 * @param $statusphp
	 * @param $expected
	 */
	public function testCheckOwnCloudVersion($statusphp, $expected) {
		$this->assertSame($expected,
			$this->invokePrivate($this->trustedServers, 'checkOwnCloudVersion', [$statusphp])
		);
	}

	public function dataTestCheckOwnCloudVersion() {
		return [
			['{"version":"8.4.0"}', false],
			['{"version":"9.0.0"}', true],
			['{"version":"9.1.0"}', true]
		];
	}

	/**
	 * @dataProvider dataTestNormalizeUrl
	 *
	 * @param string $url
	 * @param string $expected
	 */
	public function testNormalizeUrl($url, $expected) {
		$this->assertSame($expected,
			$this->invokePrivate($this->trustedServers, 'normalizeUrl', [$url])
		);
	}

	public function dataTestNormalizeUrl() {
		return [
			['owncloud.org', 'owncloud.org'],
			['http://owncloud.org', 'owncloud.org'],
			['https://owncloud.org', 'owncloud.org'],
			['https://owncloud.org//mycloud', 'owncloud.org/mycloud'],
			['https://owncloud.org/mycloud/', 'owncloud.org/mycloud'],
		];
	}
}
