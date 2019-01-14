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
	 * Returns base url for the testing app
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function getBaseUrl($path) {
		return "/apps/testing/api/v{$this->testingAppVersion}" . $path;
	}

	/**
	* @When the administrator requests the system-info using the testing API
	 *
	 * @return void
	*/
	public function theAdministratorRequestsTheSystemInfoUsingTheTestingApi() {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'GET',
			$this->getBaseUrl("/sysinfo"),
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
	public function theAdministratorRequestsTheLogfileWithLinesUsingTheTestingApi($number) {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'GET',
			$this->getBaseUrl("/logfile/{$number}"),
			[],
			$this->featureContext->getOcsApiVersion()
			);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When the administrator requests the details about the app :appId
	 *
	 * @param int $number
	 *
	 * @return void
	 */
	public function theAdministratorRequestsTheDetailsAboutTheApp($appId) {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'GET',
			$this->getBaseUrl("/app/{$appId}"),
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
	public function theAdministratorAddsAConfigKeyWithValueInAppUsingTheTestingApi($key, $value, $appID) {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'POST',
			$this->getBaseUrl("/app/{$appID}/{$key}"),
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
	public function theAdministratorDeletesTheConfigKeyInAppUsingTheTestingApi($key, $appID) {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'DELETE',
			$this->getBaseUrl("/app/{$appID}/{$key}"),
			[],
			$this->featureContext->getOcsApiVersion()
			);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @Then the response should contain the server root
	 *
	 * @return void
	 */
	public function theResponseShouldContainTheServerRoot() {
		$responseXml = $this->featureContext->getResponseXml();
		$data = \json_decode(\json_encode($responseXml->data[0]), 1);

		PHPUnit_Framework_Assert::assertInternalType('string', $data['server_root']);
		PHPUnit_Framework_Assert::assertRegExp('/[^\0]+/', $data['server_root']);
	}

	/**
	 * @When the administrator adds these config keys using the testing API
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 */
	public function theAdministratorAddsTheseConfigKeysUsingTheTestingApi(TableNode $table) {
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
	public function theAdministratorDeletesTheseConfigKeysUsingTheTestingApi(TableNode $table) {
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
	public function theAdministratorHasAddedTheseConfigKeys(TableNode $table) {
		$this->theAdministratorAddsTheseConfigKeysUsingTheTestingApi($table);
		PHPUnit_Framework_Assert::assertSame(200, $this->featureContext->getResponse()->getStatusCode());
	}

	/**
	 * @Then the response should contain the installed version of the app
	 *
	 * @return void
	 */
	public function theResponseShouldContainTheInstalledVersionOfTheApp() {
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

			PHPUnit_Framework_Assert::assertSame($app_version, $version);
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
	public function theResponseShouldHaveTheNameOfTheApp($appID) {
		$data = $this->featureContext->parseConfigListFromResponseXml($this->featureContext->getResponseXml());
		foreach ($data as $element) {
			$responseAppName = $element['appid'];
			PHPUnit_Framework_Assert::assertSame($appID, $responseAppName);
		}
	}

	/**
	 * @Then the response should have the app enabled status of app
	 *
	 * @return void
	 */
	public function theResponseShouldHaveTheAppEnabledStatusOfApp() {
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
			PHPUnit_Framework_Assert::assertSame($appEnabled, $actualAppEnabledStatus);
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
	public function theAdministratorCreatesFileUsingTheTestingApi($path, $content) {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'POST',
			$this->getBaseUrl("/file"),
			['file'=>$path, 'content'=>$content],
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
	public function theAdministratorHasCreatedFileWithContent($path, $content) {
		$this->theAdministratorCreatesFileUsingTheTestingApi($path, $content);
		PHPUnit_Framework_Assert::assertSame(
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
	public function theAdministratorDeletesFileUsingTheTestingApi($path) {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'DELETE',
			$this->getBaseUrl("/file"),
			['file'=>$path],
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
	public function theAdministratorCreatesDirectoryInServerRootUsingTheTestingApi($dir) {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'POST',
			$this->getBaseUrl("/dir"),
			['dir'=>$dir],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
		if ($response->getStatusCode() === 200) {
			\array_push($this->createdDirectoryPaths, $dir);
		}
	}

	/**
	 * @When the administrator deletes directory :dir using the testing API
	 *
	 * @param string $dir
	 *
	 * @return void
	 */
	public function theAdministratorDeletesDirectoryUsingTheTestingApi($dir) {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'DELETE',
			$this->getBaseUrl("/dir"),
			['dir'=>$dir],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	public function runOccCommandUsingTestingAPI($command) {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'POST',
			$this->getBaseUrl("/occ"),
			['command'=>$command],
			$this->featureContext->getOcsApiVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When the administrator runs these occ commands using the testing API
	 */
	public function theAdministratorRunsTheseOccCommandsUsingTheTestingApi(TableNode $table) {
		foreach ($table as $item) {
			$this->runOccCommandUsingTestingAPI($item['command']);
		}
	}

	/**
	 * @When the administrator creates a notification with the following details using the testing API
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 */
	public function theAdministratorCreatesANotification(TableNode $table) {
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
	public function userDeletesAllNotificationsUsingTheTestingApi($user) {
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getPasswordForUser($user),
			'DELETE',
			$this->getBaseUrl("/notifications"),
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
	public function theAdministratorHasCreatedANotification(TableNode $table) {
		$this->theAdministratorCreatesANotification($table);
		PHPUnit_Framework_Assert::assertSame(200, $this->featureContext->getResponse()->getStatusCode());
	}

	/**
	 * @When the administrator increases the max file id size beyond 32 bits using the testing API
	 *
	 * @return void
	 */
	public function theAdministratorIncreasesTheMaxFileIdSizeBeyond32BitsUsingTheTestingApi() {
		$user = $this->featureContext->getAdminUsername();
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$user,
			$this->featureContext->getAdminPassword(),
			'POST',
			$this->getBaseUrl("/increasefileid"),
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
	public function theAdministratorGetsAllTheExtensionsOfMimeTypeUsingTheTestingApi($mimetype) {
		$extensions = [];
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$this->featureContext->getAdminUsername(),
			$this->featureContext->getAdminPassword(),
			'GET',
			"/apps/testing/api/v1/getextension/$mimetype",
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
	 * @param $string $user
	 *
	 * @return void
	 */
	public function fileShouldHaveFileIdGreaterThanBitsForUser($path, $max, $user) {
		$max_value = \bindec(\str_repeat("1", $max - 1));
		$currentFileID = $this->featureContext->getFileIdForPath($user, $path);
		PHPUnit_Framework_Assert::assertGreaterThan($max_value, (int)$currentFileID);
	}

	/**
	 * get log file using the testing API
	 *
	 * @return void
	 */
	public function getLogfile() {
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$this->featureContext->getAdminUsername(),
			$this->featureContext->getAdminPassword(),
			'GET',
			$this->getBaseUrl("/logfile"),
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
	public function clearLogFile() {
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$this->featureContext->getAdminUsername(),
			$this->featureContext->getAdminPassword(),
			'DELETE',
			$this->getBaseUrl("/logfile"),
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
	public function logEntriesShouldDecreaseWhenTheAdministratorClearsTheLogfile() {
		$this->getLogfile();
		$initialCount = \count($this->featureContext->getResponseXml()->data[0]);
		$this->clearLogFile();
		$this->getLogfile();
		$finalCount = \count($this->featureContext->getResponseXml()->data[0]);
		PHPUnit_Framework_Assert::assertLessThan($initialCount, $finalCount);
	}

	/**
	 * @Then the extensions returned should be :extensions
	 *
	 * @param string $extensions seperated by comma
	 *
	 * @return void
	 */
	public function theExtensionsReturnedShouldBe($extensions) {
		$responseXml = HttpRequestHelper::getResponseXml(
			$this->featureContext->getResponse()
		);
		$actualExtensions = \json_decode(\json_encode(
			$responseXml->data[0]), true
		);
		$expectedExtensions = \explode(', ', $extensions);
		PHPUnit_Framework_Assert::assertEquals(
			$expectedExtensions, $actualExtensions['element']
		);
	}

	/**
	 * After Scenario. delete files created while testing
	 *
	 * @AfterScenario
	 *
	 * @return void
	 */
	public function deleteAllCreatedFiles() {
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
	public function deleteAllCreatedDirectories() {
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
	public function setUpScenario(BeforeScenarioScope $scope) {
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
	}
}
