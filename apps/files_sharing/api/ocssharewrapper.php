<?php

namespace OCA\Files_Sharing\API;

class OCSShareWrapper {

	/**
	 * @return Share20OCS
	 */
	private function getShare20OCS() {
		return new share20OCS(new \OC\Share20\Manager(
		                   \OC::$server->getUserSession()->getUser(),
		                   \OC::$server->getUserManager(),
		                   \OC::$server->getGroupManager(),
		                   \OC::$server->getLogger(),
		                   \OC::$server->getAppConfig(),
		                   \OC::$server->getUserFolder(),
		                    new \OC\Share20\DefaultShareProvider(
		                       \OC::$server->getDatabaseConnection(),
							   \OC::$server->getUserManager(),
							   \OC::$server->getGroupManager(),
							   \OC::$server->getUserFolder()
		                   )
		               ),
		               \OC::$server->getGroupManager(),
		               \OC::$server->getUserManager(),
		               \OC::$server->getRequest(),
		               \OC::$server->getUserFolder());
	}

	public function getAllShares($params) {
		return \OCA\Files_Sharing\API\Local::getAllShares($params);
	}

	public function createShare($params) {
		return \OCA\Files_Sharing\API\Local::createShare($params);
	}

	public function getShare($params) {
		return \OCA\Files_Sharing\API\Local::getShare($params);
	}

	public function updateShare($params) {
		return \OCA\Files_Sharing\API\Local::updateShare($params);
	}

	public function deleteShare($params) {
		$id = (int)$params['id'];
		return $this->getShare20OCS()->deleteShare($id);
	}
}
