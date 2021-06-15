<?php
/**
 * ownCloud
 *
 * @author Hari Bhandari <haribhandari07@gmail.com>
 * @copyright Copyright (c) 2020 Hari Bhandari haribhandari07@gmail.com
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

use OC\OCS\Exception;
use OC\User\AccountMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IRequest;
use OC\OCS\Result;

class LastLoginDate {
	/**
	 * @var AccountMapper
	 */
	private $accountMapper;

	/**
	 * @var IRequest
	 */
	private $request;

	/**
	 *
	 * @param AccountMapper $accountMapper
	 * @param IRequest $request
	 *
	 * @return void
	 */
	public function __construct(
		AccountMapper $accountMapper,
		IRequest $request
	) {
		$this->accountMapper = $accountMapper;
		$this->request = $request;
	}
	
	/**
	 * Sets the last login date for a user
	 *
	 * @param array $param
	 *
	 * @return Result
	 */
	public function setLastLoginDate($param) {
		$user = \trim($param['user']);
		try {
			$account = $this->accountMapper->getByUid($user);
		} catch (DoesNotExistException $e) {
			return new Result(null, 404, 'user '. $user . ' not found');
		}
		$days = $this->request->getParam('days');
		if (!\is_numeric($days) || $days < 0) {
			return new Result(null, 400, 'number of days is expected to be a positive integer');
		}
		try {
			$account->setLastLogin(\time() - 60*60*24*$days);
			$this->accountMapper->update($account);
		} catch (Exception $exception) {
			return new Result(null, $exception->getCode(), $exception->getMessage());
		}
		return new Result(null, 201);
	}

	/**
	 * Gets the last login date for a user
	 *
	 * @param array $param
	 *
	 * @return Result
	 */
	public function getLastLoginDate($param) {
		$user = \trim($param['user']);
		try {
			$account = $this->accountMapper->getByUid($user);
		} catch (DoesNotExistException $e) {
			return new Result(null, 404, 'user '. $user . ' not found');
		}
		return new Result([$account->getLastLogin()], 200);
	}
}
