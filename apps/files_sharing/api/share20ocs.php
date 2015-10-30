<?php

namespace OCA\Files_Sharing\API;

class Share20OCS {

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

	public function __construct(\OC\Share20\Manager $shareManager,
	                            \OCP\IGroupManager $groupManager,
	                            \OCP\IUserManager $userManager,
	                            \OCP\IRequest $request,
								\OCP\Files\Folder $userFolder) {
		$this->shareManager = $shareManager;
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		$this->request = $request;
		$this->userFolder = $userFolder;
	}

	/**
	 * Delete a share
	 *
	 * @param int $id
	 * @return \OC_OCS_Result
	 */
	public function deleteShare($id) {
		try {
			$share = $this->shareManager->getShareById($id);
		} catch (\OC\Share20\Exception\ShareNotFound $e) {
			return new \OC_OCS_Result(null, 404, 'wrong share ID, share doesn\'t exist.');
		}

		try {
			$this->shareManager->deleteShare($share);
		} catch (\OC\Share20\Exception\BackendError $e) {
			return new \OC_OCS_Result(null, 404, 'could not delete share');
		}

		return new \OC_OCS_Result();
	}
}
