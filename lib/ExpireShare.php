<?php

/**
 * ownCloud
 *
 * @author Kiran Parajuli <kiran@jankaritech.com>
 * @copyright Copyright (c) 2021 Kiran Parajuli kiran@jankaritech.com
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

use DateTime;
use OC\OCS\Result;
use OCP\Share\Exceptions\GenericShareException;
use OCP\Share\Exceptions\ShareNotFound;
use OCP\Share\IManager;
use OCP\Share\IShare;

class ExpireShare {
	/** @var IManager */
	private $shareManager;

	/**
	 * @param IManager $shareManager
	 */
	public function __construct(
		IManager $shareManager
	) {
		$this->shareManager = $shareManager;
	}

	/**
	 * @param mixed $id
	 * @return IShare
	 * @throws ShareNotFound
	 */
	private function getShareById($id) {
		try {
			$share = $this->shareManager->getShareById('ocinternal:'.$id);
		} catch (ShareNotFound $e) {
			if (!$this->shareManager->outgoingServer2ServerSharesAllowed()) {
				throw new ShareNotFound();
			}
			$share = $this->shareManager->getShareById('ocFederatedSharing:' . $id);
		}
		return $share;
	}

	public function expireShare(array $param) {
		$id = \trim($param["share_id"]);
		if (!$this->shareManager->shareApiEnabled()) {
			return new Result(null, 404, 'Share API is disabled');
		}

		try {
			$share = $this->getShareById($id);
			$original_date = $share->getExpirationDate();
		} catch (ShareNotFound $e) {
			return new Result(null, 404, 'Wrong share ID, share doesn\'t exist');
		}
		// set share expiration to a past datetime
		$share->setExpirationDate(new \DateTime('yesterday'));
		$new_date = $share->getExpirationDate();
		try {
			$this->shareManager->updateShare($share, true);
		} catch (GenericShareException $e) {
			$exceptionMessage = $e->getMessage();
			return new Result(null, 400, "Share expire failed: $exceptionMessage");
		}
		$date = new DateTime();

		return new Result([
			"stime" => $date->getTimestamp(),
			"share_id" => $id,
			"original_date"=> $original_date->format('Y-m-d H:i:s'),
			"new_date" => $new_date->format('Y-m-d H:i:s')
		], 100, 'Share is now expired');
	}
}
