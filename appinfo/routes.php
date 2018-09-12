<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
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

namespace OCA\Testing\AppInfo;

use OCA\Testing\BigFileID;
use OCA\Testing\Config;
use OCA\Testing\Locking\Provisioning;
use OCA\Testing\Occ;
use OCA\Testing\Notifications;
use OCA\Testing\ServerFiles;
use OCA\Testing\SysInfo;
use OCP\API;
use OCA\Testing\Opcache;
use OCA\Testing\Logfile;
use OCA\Testing\DavSlowdown;

$config = new Config(
	\OC::$server->getConfig(),
	\OC::$server->getRequest()
);

API::register(
	'post',
	'/apps/testing/api/v1/app/{appid}/{configkey}',
	[$config, 'setAppValue'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'delete',
	'/apps/testing/api/v1/app/{appid}/{configkey}',
	[$config, 'deleteAppValue'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'post',
	'/apps/testing/api/v1/apps',
	[$config, 'setAppValues'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'delete',
	'/apps/testing/api/v1/apps',
	[$config, 'deleteAppValues'],
	'testing',
	API::ADMIN_AUTH
);

$locking = new Provisioning(
	\OC::$server->getLockingProvider(),
	\OC::$server->getDatabaseConnection(),
	\OC::$server->getConfig(),
	\OC::$server->getRequest()
);
API::register('get', '/apps/testing/api/v1/lockprovisioning', [$locking, 'isLockingEnabled'], 'files_lockprovisioning', API::ADMIN_AUTH);
API::register('get', '/apps/testing/api/v1/lockprovisioning/{type}/{user}', [$locking, 'isLocked'], 'files_lockprovisioning', API::ADMIN_AUTH);
API::register('post', '/apps/testing/api/v1/lockprovisioning/{type}/{user}', [$locking, 'acquireLock'], 'files_lockprovisioning', API::ADMIN_AUTH);
API::register('put', '/apps/testing/api/v1/lockprovisioning/{type}/{user}', [$locking, 'changeLock'], 'files_lockprovisioning', API::ADMIN_AUTH);
API::register('delete', '/apps/testing/api/v1/lockprovisioning/{type}/{user}', [$locking, 'releaseLock'], 'files_lockprovisioning', API::ADMIN_AUTH);

//release all locks of the given type that were set by the testing app
API::register(
	'delete',
	'/apps/testing/api/v1/lockprovisioning/{type}',
	[$locking, 'releaseAll'],
	'files_lockprovisioning',
	API::ADMIN_AUTH
);
//relase all locks that were set by the testing app
//if global=true in the requests also locks that were not set by the testing app get cleared
API::register(
	'delete',
	'/apps/testing/api/v1/lockprovisioning',
	[$locking, 'releaseAll'],
	'files_lockprovisioning',
	API::ADMIN_AUTH
);

$bigFileID = new BigFileID(
	\OC::$server->getDatabaseConnection(),
	\OC::$server->getLogger()
);

API::register(
	'post',
	'/apps/testing/api/v1/increasefileid',
	[$bigFileID, 'increaseFileIDsBeyondMax32bits'],
	'testing',
	API::ADMIN_AUTH
);

$occ = new Occ(\OC::$server->getRequest());

API::register(
	'post',
	'/apps/testing/api/v1/occ',
	[$occ, 'execute'],
	'testing',
	API::ADMIN_AUTH
);

$opcache = new Opcache();

API::register(
	'delete',
	'/apps/testing/api/v1/opcache',
	[$opcache, 'execute'],
	'testing',
	API::ADMIN_AUTH
);

$notifications = new Notifications(
	'notificationsacceptancetesting',
	\OC::$server->getRequest(),
	\OC::$server->getNotificationManager()
);
\OCP\API::register(
	'delete',
	'/apps/testing/api/v1/notifications',
	[$notifications, 'deleteNotifications'],
	'notifications'
);
\OCP\API::register(
	'post',
	'/apps/testing/api/v1/notifications',
	[$notifications, 'addNotification'],
	'notifications'
);

$logFile = new Logfile();

API::register(
	'get',
	'/apps/testing/api/v1/logfile',
	[$logFile, 'read'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'get',
	'/apps/testing/api/v1/logfile/{lines}',
	[$logFile, 'read'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'delete',
	'/apps/testing/api/v1/logfile',
	[$logFile, 'clear'],
	'testing',
	API::ADMIN_AUTH
);

$sysInfo = new SysInfo();

API::register(
	'get',
	'/apps/testing/api/v1/sysinfo',
	[$sysInfo, 'read'],
	'testing',
	API::ADMIN_AUTH
);

$serverFiles = new ServerFiles(
	\OC::$server->getRequest()
);

API::register(
	'post',
	'/apps/testing/api/v1/dir',
	[$serverFiles, 'mkDir'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'post',
	'/apps/testing/api/v1/file',
	[$serverFiles, 'createFile'],
	'testing',
	API::ADMIN_AUTH
);

$davSlowDown = new DavSlowdown();

API::register(
	'put',
	'/apps/testing/api/v1/davslowdown/{method}/{seconds}',
	[$davSlowDown, 'setSlowdown'],
	'testing',
	API::ADMIN_AUTH
);
