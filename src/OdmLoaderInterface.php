<?php
/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hautelook\AliceBundle;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;


/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
interface OdmLoaderInterface
{
	/**
	 * Loads the specified fixtures of an application.
	 *
	 * @param Application $application Application the fixtures are loaded from
	 * @param DocumentManager $manager Document Manager used for the loading
	 * @param string[] $bundles Bundle names in which the fixtures can be found
	 * @param string $environment If set filter the fixtures by the environment given
	 * @param bool $append If true, then the database is not purged before loading the objects	 *
	 * @param bool $purge
	 * @return object[] Loaded objects
	 */
	public function load(
		Application $application,
		DocumentManager $manager,
		array $bundles,
		string $environment,
		bool $append,
		bool $purge
	);
}
