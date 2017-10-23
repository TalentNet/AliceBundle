<?php
namespace Hautelook\AliceBundle\Loader;
use Doctrine\ODM\MongoDB\DocumentManager;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Persister\ObjectManagerPersister;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Purger\Purger;
use Fidry\AliceDataFixtures\Loader\FileResolverLoader;
use Fidry\AliceDataFixtures\Loader\PurgerLoader;
use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PersisterAwareInterface;
use Hautelook\AliceBundle\BundleResolverInterface;
use Hautelook\AliceBundle\FixtureLocatorInterface;
use Hautelook\AliceBundle\OdmLoaderInterface as AliceBundleLoaderInterface;
use Hautelook\AliceBundle\LoggerAwareInterface;
use Hautelook\AliceBundle\Resolver\File\KernelFileResolver;
use Nelmio\Alice\IsAServiceTrait;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;

final class DoctrineOdmLoader implements AliceBundleLoaderInterface, LoggerAwareInterface
{
	use IsAServiceTrait;
	/**
	 * @var BundleResolverInterface
	 */
	private $bundleResolver;
	/**
	 * @var FixtureLocatorInterface
	 */
	private $fixtureLocator;
	/**
	 * @var LoaderInterface|PersisterAwareInterface
	 */
	private $loader;
	/**
	 * @var LoggerInterface
	 */
	private $logger;
	public function __construct(
		BundleResolverInterface $bundleResolver,
		FixtureLocatorInterface $fixtureLocator,
		LoaderInterface $loader,
		LoggerInterface $logger
	) {
		$this->bundleResolver = $bundleResolver;
		$this->fixtureLocator = $fixtureLocator;
		if (false === $loader instanceof PersisterAwareInterface) {
			throw new \InvalidArgumentException(
				sprintf(
					'Expected loader to be an instance of "%s".',
					PersisterAwareInterface::class
				)
			);
		}
		$this->loader = $loader;
		$this->logger = $logger;
	}
	/**
	 * @inheritdoc
	 */
	public function withLogger(LoggerInterface $logger): self
	{
		return new self($this->bundleResolver, $this->fixtureLocator, $this->loader, $logger);
	}
	/**
	 * @inheritdoc
	 */
	public function load(
		Application $application,
		DocumentManager $manager,
		array $bundles,
		string $environment,
		bool $append,
		bool $purge
	) {
		$bundles = $this->bundleResolver->resolveBundles($application, $bundles);
		$fixtureFiles = $this->fixtureLocator->locateFiles($bundles, $environment);
		$this->logger->info('fixtures found', ['files' => $fixtureFiles]);
		$fixtures = $this->loadFixtures(
			$this->loader,
			$application->getKernel(),
			$manager,
			$fixtureFiles,
			$application->getKernel()->getContainer()->getParameterBag()->all(),
			$append,
			$purge
		);
		$this->logger->info('fixtures loaded');
		return $fixtures;
	}
	/**
	 * @param LoaderInterface|PersisterAwareInterface $loader
	 * @param KernelInterface                         $kernel
	 * @param DocumentManager                         $manager
	 * @param string[]                                $files
	 * @param array                                   $parameters
	 * @param bool                                    $append
	 * @param bool|null                               $purge
	 *
	 * @return \object[]
	 */
	private function loadFixtures(
		LoaderInterface $loader,
		KernelInterface $kernel,
		DocumentManager $manager,
		array $files,
		array $parameters,
		bool $append,
		bool $purge = false
	) {
		if ($append === true && $purge === true) {
			throw new \LogicException(
				'Cannot append loaded fixtures and at the same time purge the database. Choose one.'
			);
		}
		$loader = $loader->withPersister(new ObjectManagerPersister($manager));
		if (true === $append) {
			return $loader->load($files, $parameters);
		}
		$purger = new Purger($manager);
		$loader = new PurgerLoader($loader, $purger);
		$loader = new FileResolverLoader($loader, new KernelFileResolver($kernel));
		return $loader->load($files, $parameters);
	}
}