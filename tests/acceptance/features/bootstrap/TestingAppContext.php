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
	 * @Then /^following config keys should (not|)\s?exist$/
	 *
	 * @param string $shouldOrNot
	 * @param TableNode $table
	 *
	 * @return void
	 */
	public function followingConfigKeysMustExist($shouldOrNot, TableNode $table) {
		$should = ($shouldOrNot !== "not");
		if ($should) {
			foreach ($table as $item) {
				PHPUnit_Framework_Assert::assertTrue($this->checkConfigKeyInApp($item['configkey'], $item['appid']));
			}
		} else {
			foreach ($table as $item) {
				PHPUnit_Framework_Assert::assertFalse($this->checkConfigKeyInApp($item['configkey'], $item['appid']));
			}
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
