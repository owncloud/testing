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
	 * Returns a list of config keys for the given app
	 *
	 * @param string $appID
	 *
	 * @return array
	 */
	public function getConfigKeyList($appID) {
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$this->featureContext->getAdminUsername(),
			$this->featureContext->getAdminPassword(),
			'GET',
			$this->getBaseUrl("/app/{$appID}"),
			[],
			$this->featureContext->getOcsApiVersion()
		);
		$configkeyValues = \json_decode(\json_encode($this->featureContext->getResponseXml($response)->data), 1)['element'];
		return $configkeyValues;
	}

	/**
	 * Check if given config key is present for given app
	 *
	 * @param string $key
	 * @param string $appID
	 *
	 * @return bool
	 */
	public function checkConfigKeyInApp($key, $appID) {
		$configkeyList = $this->getConfigKeyList($appID);
		foreach ($configkeyList as $config) {
			if ($config['configkey'] === $key) {
				return  true;
			}
		}
		return false;
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
	 * @Then the config key :key of app :appID must have value :value
	 *
	 * @param string $key
	 * @param string $value
	 * @param string $appID
	 *
	 * @return void
	 */
	public function theConfigKeyOfAppMustHaveValue($key, $appID, $value) {
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$this->featureContext->getAdminUsername(),
			$this->featureContext->getAdminPassword(),
			'GET',
			$this->getBaseUrl("/app/{$appID}/{$key}"),
			[],
			$this->featureContext->getOcsApiVersion()
		);
		$configkeyValue = \json_decode(\json_encode($this->featureContext->getResponseXml($response)->data[0]->element->value), 1)[0];
		PHPUnit_Framework_Assert::assertEquals($value, $configkeyValue);
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
	 * @Then /^the app ((?:'[^']*')|(?:"[^"]*")) should (not|)\s?have config key ((?:'[^']*')|(?:"[^"]*"))$/
	 *
	 * @param string $appID
	 * @param string $shouldOrNot
	 * @param string $key
	 *
	 * @return void
	 */
	public function theAppShouldHaveConfigKey($appID, $shouldOrNot, $key) {
		$appID = \trim($appID, $appID[0]);
		$key = \trim($key, $key[0]);

		$should = ($shouldOrNot !== "not");

		if ($should) {
			PHPUnit_Framework_Assert::assertTrue($this->checkConfigKeyInApp($key, $appID));
		} else {
			PHPUnit_Framework_Assert::assertFalse($this->checkConfigKeyInApp($key, $appID));
		}
	}

	/**
	 * @Then the response should contain the installed version of the app
	 *
	 * @return void
	 */
	public function theResponseShouldContainTheInstalledVersionOfTheApp() {
		$responseXml = $this->featureContext->getResponseXml();
		\var_dump($responseXml);
		$data = \json_decode(\json_encode($responseXml->data[0]), 1)['element'];

		foreach ($data as $element) {
			if ($element['configkey'] == 'installed_version') {
				$version = $element['value'];
				$appName = $element['appid'];
				break;
			}
		}

		if (isset($appName, $version)) {
			$this->featureContext->invokingTheCommand(
				"config:list $appName"
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
		$responseXml = $this->featureContext->getResponseXml();
		$data = \json_decode(\json_encode($responseXml->data[0]), 1)['element'];

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
		$responseXml = $this->featureContext->getResponseXml();
		$data = \json_decode(\json_encode($responseXml->data[0]), 1)['element'];

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
