<?php
/**
 * @author Vincent Petry <pvince81@owncloud.com>
 *
 * @copyright Copyright (c) 2017, ownCloud GmbH
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

use OCP\AppFramework\App;
use OCA\Testing\AlternativeHomeUserBackend;

class Application extends App {
	public function __construct (array $urlParams = array()) {
		$appName = 'testing';
		parent::__construct($appName, $urlParams);

		$c = $this->getContainer();
		$config = $c->getServer()->getConfig();
		if ($config->getAppValue($appName, 'enable_alt_user_backend', 'no') === 'yes') {
			$userManager = $c->getServer()->getUserManager();

			// replace all user backends with this one
			$userManager->clearBackends();
			$userManager->registerBackend($c->query(AlternativeHomeUserBackend::class));
		}
	}
}
