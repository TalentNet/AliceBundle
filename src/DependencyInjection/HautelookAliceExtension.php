<?php
/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hautelook\AliceBundle\DependencyInjection;
use Hautelook\AliceBundle\HautelookAliceBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
/**
 * @private
 *
 * @author Baldur Rensch <brensch@gmail.com>
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
final class HautelookAliceExtension extends Extension
{
	const SERVICES_DIR = __DIR__.'/../../resources/config';
	private $doctrineOrmEnabled = false;
	private $doctrineOdmEnabled = false;
	/**
	 * @inheritdoc
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$bundles = array_flip($container->getParameter('kernel.bundles'));
		if (false === array_key_exists('Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle', $bundles)) {
			throw new \LogicException(
				sprintf(
					'Cannot register "%s" without "Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle".',
					HautelookAliceBundle::class
				)
			);
		}
		$this->doctrineOrmEnabled = array_key_exists('Doctrine\Bundle\DoctrineBundle\DoctrineBundle', $bundles);
		$this->doctrineOdmEnabled = array_key_exists('Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle', $bundles);
		if ($this->doctrineOdmEnabled === false && $this->doctrineOrmEnabled === false) {
			throw new \LogicException(
				sprintf(
					'Cannot register "%s" without either "Doctrine\Bundle\DoctrineBundle\DoctrineBundle"'
					. ' or "Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle".',
					HautelookAliceBundle::class
				)
			);
		}
		$this->loadConfig($configs, $container);
		$this->loadServices($container);
	}
	/**
	 * Loads alice configuration and add the configuration values to the application parameters.
	 *
	 * @param array            $configs
	 * @param ContainerBuilder $container
	 *
	 * @throws \InvalidArgumentException
	 */
	private function loadConfig(array $configs, ContainerBuilder $container)
	{
		$configuration = new Configuration();
		$processedConfiguration = $this->processConfiguration($configuration, $configs);
		foreach ($processedConfiguration as $key => $value) {
			$container->setParameter(
				$this->getAlias().'.'.$key,
				$value
			);
		}
	}
	/**
	 * Loads all the services declarations.
	 *
	 * @param ContainerBuilder $container
	 */
	private function loadServices(ContainerBuilder $container)
	{
		$loader = new XmlFileLoader($container, new FileLocator(self::SERVICES_DIR));
		$finder = new Finder();
		if (!$this->doctrineOrmEnabled) {
			$finder->files()->in(self::SERVICES_DIR)->notName('*_orm.xml');
		} elseif (!$this->doctrineOdmEnabled) {
			$finder->files()->in(self::SERVICES_DIR)->notName('*_odm.xml');
		} else {
			$finder->files()->in(self::SERVICES_DIR);
		}
		foreach ($finder as $file) {
			$loader->load(
				$file->getRelativePathname()
			);
		}
		if ($container->hasParameter('kernel.project_dir')) {
			$rootDir = $container->getParameter('kernel.project_dir');
		} else {
			$rootDir = $container->getParameter('kernel.root_dir');
		}
		$locatorDefinition = $container->getDefinition('hautelook_alice.locator.env_directory');
		$locatorDefinition->addArgument($rootDir);
	}
}