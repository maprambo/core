<?php

namespace OCA\Files_Sharing\Tests\API;

use OCA\Files_Sharing\API\Share20OCS;

class Share20OCSTest extends \Test\TestCase {

	/** @var OC\Share20\Manager */
	private $shareManager;

	/** @var OCP\IGroupManager */
	private $groupManager;

	/** @var OCP\IUserManager */
	private $userManager;

	/** @var OCP\IRequest */
	private $request;

	/** @var OCP\Files\Folder */
	private $userFolder;

	/** @var OCS */
	private $ocs;

	protected function setUp() {
		$this->shareManager = $this->getMockBuilder('OC\Share20\Manager')
			->disableOriginalConstructor()
			->getMock();
		$this->groupManager = $this->getMockBuilder('OCP\IGroupManager')
			->disableOriginalConstructor()
			->getMock();
		$this->userManager = $this->getMockBuilder('OCP\IUserManager')
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getMockBuilder('OCP\IRequest')
			->disableOriginalConstructor()
			->getMock();
		$this->userFolder = $this->getMockBuilder('OCP\Files\Folder')
			->disableOriginalConstructor()
			->getMock();

		$this->ocs = new Share20OCS($this->shareManager,
									$this->groupManager,
									$this->userManager,
									$this->request,
									$this->userFolder);
	}

	public function testDeleteShareShareNotFound() {
		$this->shareManager
			->expects($this->once())
			->method('getShareById')
			->with(42)
			->will($this->throwException(new \OC\Share20\Exception\ShareNotFound()));

		$expected = new \OC_OCS_Result(null, 404, 'wrong share ID, share doesn\'t exist.');
		$this->assertEquals($expected, $this->ocs->deleteShare(42));
	}

	public function testDeleteShareCouldNotDelete() {
		$share = $this->getMock('OC\Share20\IShare');
		$this->shareManager
			->expects($this->once())
			->method('getShareById')
			->with(42)
			->willReturn($share);
		$this->shareManager
			->expects($this->once())
			->method('deleteShare')
			->with($share)
			->will($this->throwException(new \OC\Share20\Exception\BackendError()));


		$expected = new \OC_OCS_Result(null, 404, 'could not delete share');
		$this->assertEquals($expected, $this->ocs->deleteShare(42));
	}

	public function testDeleteShare() {
		$share = $this->getMock('OC\Share20\IShare');
		$this->shareManager
			->expects($this->once())
			->method('getShareById')
			->with(42)
			->willReturn($share);
		$this->shareManager
			->expects($this->once())
			->method('deleteShare')
			->with($share);

		$expected = new \OC_OCS_Result();
		$this->assertEquals($expected, $this->ocs->deleteShare(42));
	}
}
