<?php
namespace Hautelook\AliceBundle\Console\Command\Doctrine;
use Doctrine\Common\Persistence\ManagerRegistry;
use Hautelook\AliceBundle\OdmLoaderInterface as AliceBundleLoaderInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application as FrameworkBundleConsoleApplication;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
/**
 * Command used to load the fixtures.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
final class DoctrineOdmLoadDataFixturesCommand extends Command
{
	/**
	 * @var ManagerRegistry
	 */
	private $doctrine;
	/**
	 * @var AliceBundleLoaderInterface
	 */
	private $loader;
	public function __construct(string $name, ManagerRegistry $managerRegistry, AliceBundleLoaderInterface $loader)
	{
		parent::__construct($name);
		$this->doctrine = $managerRegistry;
		$this->loader = $loader;
	}
	/**
	 * @inheritdoc
	 */
	protected function configure()
	{
		$this
			->setAliases(['hautelook:odm:fixtures:load'])
			->setDescription('Load ODM data fixtures to your database.')
			->addOption(
				'bundle',
				'b',
				InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
				'Bundles where fixtures should be loaded.'
			)
			->addOption(
				'manager',
				'dm',
				InputOption::VALUE_REQUIRED,
				'The document manager to use for this command. If not specified, use the default Doctrine fixtures document '
				.'manager.'
			)
			->addOption(
				'append',
				null,
				InputOption::VALUE_NONE,
				'Append the data fixtures instead of deleting all data from the database first.'
			)
			->addOption('purge',
				null,
				InputOption::VALUE_NONE,
				'Purge data before loading fixtures.'
			)
		;
	}
	/**
	 * @inheritdoc
	 */
	public function setApplication(ConsoleApplication $application = null)
	{
		if (null !== $application && false === $application instanceof FrameworkBundleConsoleApplication) {
			throw new \InvalidArgumentException(
				sprintf(
					'Expected application to be an instance of "%s".',
					FrameworkBundleConsoleApplication::class
				)
			);
		}
		parent::setApplication($application);
	}
	/**
	 * {@inheritdoc}
	 *
	 * \RuntimeException Unsupported Application type
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Warn the user that the database will be purged
		// Ask him to confirm his choice
		if ($input->isInteractive() && !$input->getOption('append')) {
			if (false === $this->askConfirmation(
					$input,
					$output,
					'<question>Careful, database will be purged. Do you want to continue y/N ?</question>',
					false
				)
			) {
				return 0;
			}
		}
		$manager = $this->doctrine->getManager($input->getOption('manager'));
		$environment = $input->getOption('env');
		$bundles = $input->getOption('bundle');
		$append = $input->getOption('append');
		$truncate = $input->getOption('purge');
		/** @var FrameworkBundleConsoleApplication $application */
		$application = $this->getApplication();
		$this->loader->load($application, $manager, $bundles, $environment, $append, $truncate);
		return 0;
	}
	/**
	 * Prompts to the user a message to ask him a confirmation.
	 *
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @param string          $question
	 * @param bool            $default
	 *
	 * @return bool User choice
	 */
	private function askConfirmation(InputInterface $input, OutputInterface $output, $question, $default)
	{
		/** @var QuestionHelper $questionHelper */
		$questionHelper = $this->getHelperSet()->get('question');
		$question = new ConfirmationQuestion($question, $default);
		return (bool) $questionHelper->ask($input, $output, $question);
	}
}