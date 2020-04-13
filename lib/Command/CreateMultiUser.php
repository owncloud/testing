<?php
/**
 * @author Tom Needham <tom@owncloud.com>
 * @author Piotr Mrowczynski <piotr@owncloud.com>
 *
 * @copyright Copyright (c) 2019, ownCloud GmbH
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
 */

namespace OCA\Testing\Command;

use OC\Core\Command\Base;
use OCA\DAV\CardDAV\CardDavBackend;
use OCA\DAV\Connector\Sabre\Principal;
use OCA\DAV\DAV\GroupPrincipalBackend;
use OCP\ILogger;
use OCP\IUserManager;
use OCP\IUserSession;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sabre\VObject\Component\VCard;

/**
 * Creates, provisions and logs in/out database users.
 */
class CreateMultiUser extends Base {

	/**
	 * @var IUserManager
	 */
	protected $userManager;

	/**
	 * @var CardDavBackend
	 */
	protected $cardDavBackend;

	/**
	 * @var IUserSession
	 */
	protected $session;

	/**
	 * @var ILogger
	 */
	protected $logger;

	/**
	 *
	 * @param IUserManager $userManager
	 * @param IUserSession $session
	 * @param ILogger $logger
	 *
	 * @return void
	 */
	public function __construct(IUserManager $userManager, IUserSession $session, ILogger $logger) {
		$this->userManager = $userManager;
		$this->session = $session;
		$this->logger = $logger;
		$userPrincipalBackend = new Principal(
			\OC::$server->getUserManager(),
			\OC::$server->getGroupManager()
		);
		$groupPrincipalBackend = new GroupPrincipalBackend(
			\OC::$server->getGroupManager()
		);
		$db = \OC::$server->getDatabaseConnection();
		$dispatcher = \OC::$server->getEventDispatcher();
		$this->cardDavBackend = new CardDavBackend($db, $userPrincipalBackend, $groupPrincipalBackend, $dispatcher);
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return void
	 * @see \OC\Core\Command\Base::configure()
	 *
	 */
	protected function configure() {
		parent::configure();

		$defaultUidPrefix = 'zombie-' . \time() . '-';
		$defaultContactsNumber = 0;

		$this
			->setName('testing:createusers')
			->setDescription('Creates and provisions multiple users for testing, optionaly generating contacts')
			->addOption('prefix', 'p', InputOption::VALUE_REQUIRED, 'User userid prefix for created users', $defaultUidPrefix)
			->addOption('generateContacts', 'c', InputOption::VALUE_REQUIRED, 'Number of CardDav contacts to add for each user', $defaultContactsNumber)
			->addArgument('numUsers', InputArgument::REQUIRED, 'Number of users to create');
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 *
	 * @return int|null null or 0 if everything went fine, or an error code
	 * @see \Symfony\Component\Console\Command\Command::execute()
	 *
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$num = $input->getArgument('numUsers');
		$cont = $input->getOption('generateContacts');
		$prefix = $input->getOption('prefix');
		$progress = new ProgressBar($output, $num);
		$progress->setFormatDefinition('custom', ' %current%/%max% [%bar%] %percent:3s%% Elapsed:%elapsed:6s% Estimated:%estimated:-6s% Remaining:%remaining% %message%');
		$progress->setFormat('custom');
		$usersCreated = 0;
		$start = \round(\microtime(true) * 1000);
		for ($i = 0; $i < $num; $i++) {
			// Create a user and log them in
			$uid = $this->getUid($prefix, (string)$i);
			$msElapsed = \round(\microtime(true) * 1000) - $start;
			$rate = \round($usersCreated / ($msElapsed / 1000), 1);
			$progress->setMessage("Creating [$uid] Rate: $rate users/second");
			try {
				$user = $this->userManager->createUser($uid, $uid);
				$this->fakeLoginAndLogout($uid);

				// Create 5 addressbook entries for each user
				$username = $user->getUID();
				$addBookId = (string)$this->cardDavBackend->getAddressBooksForUser("principals/users/$username")[0]['id'];
				for ($n = 0; $n < $cont; $n++) {
					$uri = $uid . $n . '.vcf';
					$vCard = new VCard();
					$vCard->UID = $uri;
					$vCard->FN = $uid . $n . ' test contact';
					$this->cardDavBackend->createCard($addBookId, $uri, $vCard->serialize());
				}

				$usersCreated++;
			} catch (\Exception $e) {
				$error = $e->getMessage();
				$this->logger->logException($e);
				$output->writeln("<error>Failed to create user with error: $error</error>");
			}
			$progress->advance();
		}
		$progress->finish();
		return null;
	}

	/**
	 *
	 * @param string $prefix
	 * @param string $i
	 *
	 * @return string
	 */
	protected function getUid($prefix, $i) {
		return $prefix . $i;
	}
	
	/**
	 *
	 * @param string $uid
	 *
	 * @return void
	 */
	private function fakeLoginAndLogout($uid) {
		$this->session->login($uid, $uid);
		$this->session->logout();
	}
}
