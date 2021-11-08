<?php
/**
 * ownCloud
 *
 * @author Saugat Pachhai <saugat@jankaritech.com>
 * @author Dipak Acharya <dipak@jankaritech.com>
 *
 * @copyright Copyright (c) 2018, JankariTech
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use TestHelpers\HttpRequestHelper;
use TestHelpers\OcsApiHelper;
use TestHelpers\SetupHelper;

require_once 'bootstrap.php';

/**
 * Context for testing app
 */
class TestingAppContext implements Context {

	/**
	 * @var FeatureContext
	 */
	private $featureContext;

	/**
	 * Version of the testing app used
	 *
	 * @var int
	 */
	private $testingAppVersion = 1;

	/**
	 * List of files created by the testing app
	 *
	 * @var array
	 */
	private $createdFilePaths = [];

	/**
	 * List of directories created by the testing app
	 *
	 * @var array
	 */
	private $createdDirectoryPaths = [];

	/**
	 * @var string
	 */
	private $initialLockValue;

	/**
	 * Returns base url for the testing app
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function getBaseUrl(string $path):string {
		return "/apps/testing/api/v{$this->testingAppVersion}" . $path;
	}

	/**
	 * @When the administrator requests the system-info using the testing API
	 *
	 * @return void
	 */
	public function theAdministratorRequestsTheSystemInfoUsingTheTestingApi():void {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'GET',
			$this->getBaseUrl("/sysinfo"),
			$this->featureContext->getStepLineRef(),
			[],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When the administrator requests the logfile with :number lines using the testing API
	 *
	 * @param int $number
	 *
	 * @return void
	 */
	public function theAdministratorRequestsTheLogfileWithLinesUsingTheTestingApi(int $number):void {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'GET',
			$this->getBaseUrl("/logfile/{$number}"),
			$this->featureContext->getStepLineRef(),
			[],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When the administrator requests the details about the app :appId
	 *
	 * @param string $appId
	 *
	 * @return void
	 */
	public function theAdministratorRequestsTheDetailsAboutTheApp(string $appId):void {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'GET',
			$this->getBaseUrl("/app/{$appId}"),
			$this->featureContext->getStepLineRef(),
			[],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When the administrator adds a config key :key with value :value in app :appID using the testing API
	 *
	 * @param string $key
	 * @param string $value
	 * @param string $appID
	 *
	 * @return void
	 */
	public function theAdministratorAddsAConfigKeyWithValueInAppUsingTheTestingApi(
		string $key,
		string $value,
		string $appID
	):void {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'POST',
			$this->getBaseUrl("/app/{$appID}/{$key}"),
			$this->featureContext->getStepLineRef(),
			["value" => $value],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When the administrator deletes the config key :key in app :appID using the testing API
	 *
	 * @param string $key
	 * @param string $appID
	 *
	 * @return void
	 */
	public function theAdministratorDeletesTheConfigKeyInAppUsingTheTestingApi(
		string $key,
		string $appID
	):void {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'DELETE',
			$this->getBaseUrl("/app/{$appID}/{$key}"),
			$this->featureContext->getStepLineRef(),
			[],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @Then the last login date in the response should be :day days ago
	 *
	 * @param int $day
	 *
	 * @return void
	 */
	public function theLastLoginDateForUserShouldBe(int $day):void {
		$responseXml = HttpRequestHelper::getResponseXml(
			$this->featureContext->getResponse()
		);
		$lastLoginTimeStamp = (string)$responseXml->data[0]->element;
		$currentTimeStamp = \time();
		$daysAgo = ($currentTimeStamp - $lastLoginTimeStamp) / (24 * 60 * 60);
		Assert::assertEquals($day, \floor($daysAgo), 'Expected ' . $day . ' days but got ' . $daysAgo . ' days');
	}

	/**
	 * @Then the response should contain the server root
	 *
	 * @return void
	 */
	public function theResponseShouldContainTheServerRoot():void {
		$responseXml = $this->featureContext->getResponseXml();
		$data = \json_decode(\json_encode($responseXml->data[0]), 1);

		Assert::assertIsString($data['server_root']);
		Assert::assertMatchesRegularExpression('/[^\0]+/', $data['server_root']);
	}

	/**
	 * @When the administrator adds these config keys using the testing API
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 */
	public function theAdministratorAddsTheseConfigKeysUsingTheTestingApi(TableNode $table):void {
		$requestBody = [];
		foreach ($table as $item) {
			\array_push($requestBody, $item);
		}
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'POST',
			$this->getBaseUrl("/apps"),
			$this->featureContext->getStepLineRef(),
			['values' => $requestBody],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When the administrator deletes these config keys using the testing API
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 */
	public function theAdministratorDeletesTheseConfigKeysUsingTheTestingApi(TableNode $table):void {
		$requestBody = [];
		foreach ($table as $item) {
			\array_push($requestBody, $item);
		}
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'DELETE',
			$this->getBaseUrl("/apps"),
			$this->featureContext->getStepLineRef(),
			['values' => $requestBody],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @Given the administrator has added these config keys
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 */
	public function theAdministratorHasAddedTheseConfigKeys(TableNode $table):void {
		$this->theAdministratorAddsTheseConfigKeysUsingTheTestingApi($table);
		Assert::assertSame(200, $this->featureContext->getResponse()->getStatusCode());
	}

	/**
	 * @Then the response should contain the installed version of the app
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theResponseShouldContainTheInstalledVersionOfTheApp():void {
		$data = $this->featureContext->parseConfigListFromResponseXml($this->featureContext->getResponseXml());

		foreach ($data as $element) {
			if ($element['configkey'] == 'installed_version') {
				$version = $element['value'];
				$appName = $element['appid'];
				break;
			}
		}

		if (isset($appName, $version)) {
			$this->featureContext->runOcc(
				["config:list $appName"]
			);
			$lastOutput = $this->featureContext->getStdOutOfOccCommand();
			$lastOutputArray = \json_decode($lastOutput, true);
			$app_version = $lastOutputArray['apps'][$appName]['installed_version'];

			Assert::assertSame($app_version, $version);
		} else {
			throw new \Exception("Version info could not be found in the response.");
		}
	}

	/**
	 * @Then the response should have the name of the app :appID
	 *
	 * @param string $appID
	 *
	 * @return void
	 */
	public function theResponseShouldHaveTheNameOfTheApp(string $appID):void {
		$data = $this->featureContext->parseConfigListFromResponseXml($this->featureContext->getResponseXml());
		foreach ($data as $element) {
			$responseAppName = $element['appid'];
			Assert::assertSame($appID, $responseAppName);
		}
	}

	/**
	 * @Then the response should have the app enabled status of app
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theResponseShouldHaveTheAppEnabledStatusOfApp():void {
		$data = $this->featureContext->parseConfigListFromResponseXml($this->featureContext->getResponseXml());
		foreach ($data as $element) {
			if ($element['configkey'] == 'enabled') {
				$appEnabled = $element['value'];
				$appName = $element['appid'];
				break;
			}
		}
		if (isset($appName, $appEnabled)) {
			$lastOutput = $this->featureContext->getStdOutOfOccCommand();
			$lastOutputArray = \json_decode($lastOutput, true);
			$actualAppEnabledStatus = $lastOutputArray['apps'][$appName]['enabled'];
			Assert::assertSame($appEnabled, $actualAppEnabledStatus);
		} else {
			throw new \Exception("App enabled status could not be found in the response.");
		}
	}

	/**
	 * @When the administrator creates file :path with content :content using the testing API
	 *
	 * @param string $path
	 * @param string $content
	 *
	 * @return void
	 */
	public function theAdministratorCreatesFileUsingTheTestingApi(
		string $path,
		string $content
	):void {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'POST',
			$this->getBaseUrl("/file"),
			$this->featureContext->getStepLineRef(),
			['file' => $path, 'content' => $content],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
		if ($response->getStatusCode() === 200) {
			\array_push($this->createdFilePaths, $path);
		}
	}

	/**
	 * @Given the administrator has created file :path with content :content
	 *
	 * @param string $path
	 * @param string $content
	 *
	 * @return void
	 */
	public function theAdministratorHasCreatedFileWithContent(
		string $path,
		string $content
	):void {
		$this->theAdministratorCreatesFileUsingTheTestingApi($path, $content);
		Assert::assertSame(
			200,
			$this->featureContext->getResponse()->getStatusCode()
		);
	}

	/**
	 * @When the administrator deletes file :path using the testing API
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	public function theAdministratorDeletesFileUsingTheTestingApi(string $path):void {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'DELETE',
			$this->getBaseUrl("/file"),
			$this->featureContext->getStepLineRef(),
			['file' => $path],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When the administrator creates directory :dir in server root using the testing API
	 *
	 * @param string $dir
	 *
	 * @return void
	 */
	public function theAdministratorCreatesDirectoryInServerRootUsingTheTestingApi(string $dir):void {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'POST',
			$this->getBaseUrl("/dir"),
			$this->featureContext->getStepLineRef(),
			['dir' => $dir],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
		if ($response->getStatusCode() === 200) {
			\array_push($this->createdDirectoryPaths, $dir);
		}
	}

	/**
	 * @When the administrator moves directory/file :source to :target using the testing API
	 *
	 * @param string $source
	 * @param string $target
	 *
	 * @return void
	 */
	public function theAdministratorMovesDirectoryToUsingTheTestingApi($source, $target) {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'MOVE',
			$this->getBaseUrl("/file"),
			$this->featureContext->getStepLineRef(),
			['source' => $source, 'target' => $target],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
		if ($response->getStatusCode() === 200) {
			\array_push($this->createdDirectoryPaths, $target);
		}
	}

	/**
	 * @When the administrator deletes directory :dir using the testing API
	 *
	 * @param string $dir
	 *
	 * @return void
	 */
	public function theAdministratorDeletesDirectoryUsingTheTestingApi(string $dir):void {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'DELETE',
			$this->getBaseUrl("/dir"),
			$this->featureContext->getStepLineRef(),
			['dir' => $dir],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @param string $command
	 *
	 * @return void
	 */
	public function runOccCommandUsingTestingAPI(string $command):void {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'POST',
			$this->getBaseUrl("/occ"),
			$this->featureContext->getStepLineRef(),
			['command' => $command],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When the administrator runs these occ commands using the testing API
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 */
	public function theAdministratorRunsTheseOccCommandsUsingTheTestingApi(
		TableNode $table
	):void {
		foreach ($table as $item) {
			$this->runOccCommandUsingTestingAPI($item['command']);
		}
	}

	/**
	 * @param array $commands
	 *
	 * @return void
	 */
	public function runBulkOccCommandUsingTestingAPI(array $commands):void {
		$user = $this->featureContext->getAdminUsername();
		$commandsBody = [];
		foreach ($commands as $command) {
			$commandsBody[] = ['command' => $command];
		}
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'POST',
			$this->getBaseUrl("/occ/bulk"),
			$this->featureContext->getStepLineRef(),
			\json_encode($commandsBody),
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When the administrator runs these occ commands in bulk using the testing API
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 */
	public function theAdministratorRunsTheseOccCommandsInBulkUsingTheTestingApi(
		TableNode $table
	):void {
		$commands = [];
		foreach ($table as $item) {
			$commands[] = $item['command'];
		}
		$this->runBulkOccCommandUsingTestingAPI($commands);
	}

	/**
	 * @When the administrator creates a notification with the following details using the testing API
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 */
	public function theAdministratorCreatesANotification(TableNode $table):void {
		$body_array = [];
		foreach ($table as $item) {
			$body_array[$item['key']] = $item['value'];
		}
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'POST',
			$this->getBaseUrl("/notifications"),
			$this->featureContext->getStepLineRef(),
			$body_array,
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When user :user deletes all notifications using the testing API
	 *
	 * @param string $user
	 *
	 * @return void
	 */
	public function userDeletesAllNotificationsUsingTheTestingApi(string $user):void {
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getPasswordForUser($user),
			'DELETE',
			$this->getBaseUrl("/notifications"),
			$this->featureContext->getStepLineRef(),
			[],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @Given the administrator has created a notification with the following details using the testing API
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 */
	public function theAdministratorHasCreatedANotification(TableNode $table):void {
		$this->theAdministratorCreatesANotification($table);
		Assert::assertSame(200, $this->featureContext->getResponse()->getStatusCode());
	}

	/**
	 * @When the administrator increases the max file id size beyond 32 bits using the testing API
	 *
	 * @return void
	 */
	public function theAdministratorIncreasesTheMaxFileIdSizeBeyond32BitsUsingTheTestingApi():void {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'POST',
			$this->getBaseUrl("/increasefileid"),
			$this->featureContext->getStepLineRef(),
			[],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When the administrator gets all the extensions of mime-type :mimetype using the testing API
	 *
	 * @param string $mimetype
	 *
	 * @return void
	 */
	public function theAdministratorGetsAllTheExtensionsOfMimeTypeUsingTheTestingApi(string $mimetype):void {
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$this->featureContext->getAdminUsername(),
			$this->featureContext->getAdminPassword(),
			'GET',
			"/apps/testing/api/v1/getextension/$mimetype",
			$this->featureContext->getStepLineRef(),
			null,
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @Then file :path should have file id greater than :max bits for user :user
	 *
	 * @param string $path
	 * @param int $max
	 * @param string $user
	 *
	 * @return void
	 */
	public function fileShouldHaveFileIdGreaterThanBitsForUser(
		string $path,
		int $max,
		string $user
	):void {
		$max_value = \bindec(\str_repeat("1", $max - 1));
		$currentFileID = $this->featureContext->getFileIdForPath($user, $path);
		Assert::assertGreaterThan($max_value, (int)$currentFileID);
	}

	/**
	 * get log file using the testing API
	 *
	 * @return void
	 */
	public function getLogfile():void {
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$this->featureContext->getAdminUsername(),
			$this->featureContext->getAdminPassword(),
			'GET',
			$this->getBaseUrl("/logfile"),
			$this->featureContext->getStepLineRef(),
			[],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * clear the contents of the logfile using the testing API
	 *
	 * @return void
	 */
	public function clearLogFile():void {
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$this->featureContext->getAdminUsername(),
			$this->featureContext->getAdminPassword(),
			'DELETE',
			$this->getBaseUrl("/logfile"),
			$this->featureContext->getStepLineRef(),
			[],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @Then log entries should decrease when the administrator clears the logfile
	 *
	 * @return void
	 */
	public function logEntriesShouldDecreaseWhenTheAdministratorClearsTheLogfile():void {
		$this->getLogfile();
		$initialCount = \count($this->featureContext->getResponseXml()->data[0]);
		$this->clearLogFile();
		$this->getLogfile();
		$finalCount = \count($this->featureContext->getResponseXml()->data[0]);
		Assert::assertLessThan($initialCount, $finalCount);
	}

	/**
	 * @Then the extensions returned should be :extensions
	 *
	 * @param string $extensions separated by comma
	 *
	 * @return void
	 */
	public function theExtensionsReturnedShouldBe(string $extensions):void {
		$responseXml = HttpRequestHelper::getResponseXml(
			$this->featureContext->getResponse()
		);
		$actualExtensions = \json_decode(
			\json_encode(
				$responseXml->data[0]
			),
			true
		);
		$expectedExtensions = \explode(', ', $extensions);
		Assert::assertEquals(
			$expectedExtensions,
			$actualExtensions['element']
		);
	}

	/**
	 * @Given locking has been enabled
	 *
	 * @return void
	 * @throws Exception
	 */
	public function lockingIsEnabled():void {
		$lockStatus = \trim(
			SetupHelper::getSystemConfig(
				'filelocking.enabled',
				$this->featureContext->getStepLineRef()
			)["stdOut"]
		);
		if ($lockStatus !== 'true') {
			$res = SetupHelper::setSystemConfig(
				'filelocking.enabled',
				'true',
				$this->featureContext->getStepLineRef(),
				'boolean'
			);
			Assert::assertSame(
				"System config value filelocking.enabled set to boolean true",
				\trim($res['stdOut'])
			);
		}
	}

	/**
	 * @Given locking has been disabled
	 *
	 * @return void
	 * @throws Exception
	 */
	public function lockingIsDisabled():void {
		$lockStatus = \trim(
			SetupHelper::getSystemConfig(
				'filelocking.enabled',
				$this->featureContext->getStepLineRef()
			)["stdOut"]
		);
		if ($lockStatus !== 'true') {
			$res = SetupHelper::setSystemConfig(
				'filelocking.enabled',
				'false',
				$this->featureContext->getStepLineRef(),
				'boolean'
			);
			Assert::assertSame(
				"System config value filelocking.enabled set to boolean false",
				\trim($res['stdOut'])
			);
		}
	}

	/**
	 * @When the administrator checks the lock provisioning status using the testing API
	 *
	 * @return void
	 */
	public function theAdministratorChecksLockProvisioningStatusUsingTheTestingApi():void {
		$adminUser = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$adminUser,
			$this->featureContext->getAdminPassword(),
			'GET',
			$this->getBaseUrl("/lockprovisioning"),
			$this->featureContext->getStepLineRef(),
			[],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When the administrator creates a lock for the file :path with the type :type for user :user
	 *
	 * @param string $path
	 * @param string $type
	 * @param string $user
	 *
	 * @return void
	 */
	public function theAdministratorCreatesLockForFileWithTypeForUser(
		string $path,
		string $type,
		string $user
	):void {
		$adminUser = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$adminUser,
			$this->featureContext->getAdminPassword(),
			'POST',
			$this->getBaseUrl("/lockprovisioning/{$type}/{$user}"),
			$this->featureContext->getStepLineRef(),
			['path' => $path],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When the administrator gets the last login date for user :user using the testing API
	 *
	 * @param string $user
	 *
	 * @return void
	 */
	public function theAdministratorGetsTheLastLoginDateOfUserUsingTheTestingApi(string $user):void {
		$adminUser = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$adminUser,
			$this->featureContext->getAdminPassword(),
			'GET',
			$this->getBaseUrl("/lastlogindate/{$user}"),
			$this->featureContext->getStepLineRef(),
			[],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @Given the administrator has created a lock for the file :path with the type :type for user :user
	 *
	 * @param string $path
	 * @param string $type
	 * @param string $user
	 *
	 * @return void
	 */
	public function theAdministratorHasCreatedLockForFileWithTypeForUser(
		string $path,
		string $type,
		string $user
	):void {
		$this->theAdministratorCreatesLockForFileWithTypeForUser($path, $type, $user);
		Assert::assertSame(
			200,
			$this->featureContext->getResponse()->getStatusCode()
		);
	}

	/**
	 * @When the administrator checks the lock for the file :path with the type :type for user :user
	 *
	 * @param string $path
	 * @param string $type
	 * @param string $user
	 *
	 * @return void
	 */
	public function theAdministratorChecksLockForFileWithTypeForUser(
		string $path,
		string $type,
		string $user
	):void {
		$adminUser = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$adminUser,
			$this->featureContext->getAdminPassword(),
			'GET',
			$this->getBaseUrl("/lockprovisioning/{$type}/{$user}?path={$path}"),
			$this->featureContext->getStepLineRef(),
			[],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When the administrator deletes the lock of the file :path with the type :type for user :user
	 *
	 * @param string $path
	 * @param string $type
	 * @param string $user
	 *
	 * @return void
	 */
	public function theAdministratorDeletesLockFromFileWithTypeForUser(
		string $path,
		string $type,
		string $user
	):void {
		$adminUser = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$adminUser,
			$this->featureContext->getAdminPassword(),
			'DELETE',
			$this->getBaseUrl("/lockprovisioning/{$type}/{$user}"),
			$this->featureContext->getStepLineRef(),
			['path' => $path],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When the administrator releases all locks of type :type
	 *
	 * @param string $type
	 *
	 * @return void
	 */
	public function theAdministratorReleasesAllLocksOfType(string $type):void {
		$adminUser = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$adminUser,
			$this->featureContext->getAdminPassword(),
			'DELETE',
			$this->getBaseUrl("/lockprovisioning/{$type}"),
			$this->featureContext->getStepLineRef(),
			[],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When the administrator releases all locks
	 *
	 * @return void
	 */
	public function theAdministratorReleasesAllLocks():void {
		$adminUser = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$adminUser,
			$this->featureContext->getAdminPassword(),
			'DELETE',
			$this->getBaseUrl("/lockprovisioning"),
			$this->featureContext->getStepLineRef(),
			[],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When the administrator checks if :module apache module is installed using the testing API
	 *
	 * @param string $module
	 *
	 * @return void
	 */
	public function theAdministratorChecksIfApacheModIsInstalled(string $module):void {
		$adminUser = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$adminUser,
			$this->featureContext->getAdminPassword(),
			'GET',
			$this->getBaseUrl("/apache_modules/{$module}"),
			$this->featureContext->getStepLineRef(),
			[],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @Given the administrator has created following locks
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 */
	public function theAdministratorHasCreatedFollowingLocks(TableNode $table):void {
		foreach ($table as $item) {
			$this->theAdministratorHasCreatedLockForFileWithTypeForUser($item['path'], $item['type'], $item['user']);
		}
	}

	/**
	 * @Then the fields of the last response should include
	 *
	 * @param TableNode $body
	 *
	 * @throws Exception
	 * @return void
	 */
	public function theFieldsOfTheLastResponseShouldInclude(TableNode $body):void {
		$this->featureContext->verifyTableNodeColumnsCount($body, 2);
		$bodyRows = $body->getRowsHash();
		foreach ($bodyRows as $field => $value) {
			Assert::assertTrue(
				$this->featureContext->isFieldInResponse($field, $value),
				"$field doesn't have value '$value'"
			);
		}
	}

	/**
	 * After Scenario. restore lock status
	 *
	 * @AfterScenario
	 *
	 * @return void
	 * @throws Exception
	 */
	public function restoreLockStatus():void {
		if ($this->initialLockValue === 'true') {
			SetupHelper::setSystemConfig(
				'filelocking.enabled',
				'true',
				$this->featureContext->getStepLineRef(),
				'boolean'
			);
		} elseif ($this->initialLockValue === 'false') {
			SetupHelper::setSystemConfig(
				'filelocking.enabled',
				'false',
				$this->featureContext->getStepLineRef(),
				'boolean'
			);
		} else {
			return;
		}
	}

	/**
	 * After Scenario. delete files created while testing
	 *
	 * @AfterScenario
	 *
	 * @return void
	 */
	public function deleteAllCreatedFiles():void {
		foreach ($this->createdFilePaths as $path) {
			$this->theAdministratorDeletesFileUsingTheTestingApi($path);
		}
	}

	/**
	 * After Scenario. delete directories created while testing
	 *
	 * @AfterScenario
	 *
	 * @return void
	 */
	public function deleteAllCreatedDirectories():void {
		foreach ($this->createdDirectoryPaths as $dir) {
			$this->theAdministratorDeletesDirectoryUsingTheTestingApi($dir);
		}
	}

	/**
	 * @BeforeScenario
	 *
	 * @param BeforeScenarioScope $scope
	 *
	 * @return void
	 * @throws Exception
	 */
	public function setUpScenario(BeforeScenarioScope $scope):void {
		// Get the environment
		$environment = $scope->getEnvironment();
		// Get all the contexts you need in this context
		$this->featureContext = $environment->getContext('FeatureContext');
		SetupHelper::init(
			$this->featureContext->getAdminUsername(),
			$this->featureContext->getAdminPassword(),
			$this->featureContext->getBaseUrl(),
			$this->featureContext->getOcPath()
		);
		$this->initialLockValue = \trim(
			SetupHelper::getSystemConfig(
				'filelocking.enabled',
				$this->featureContext->getStepLineRef()
			)["stdOut"]
		);
	}
}
