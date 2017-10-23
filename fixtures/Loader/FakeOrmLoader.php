<?php

/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hautelook\AliceBundle\Loader;

use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\OrmLoaderInterface;
use Hautelook\AliceBundle\NotCallableTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class FakeOrmLoader implements OrmLoaderInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function load(
        Application $application,
        EntityManagerInterface $manager,
        array $bundles,
        string $environment,
        bool $append,
        bool $purgeWithTruncate,
        string $shard = null
    ) {
        $this->__call(__METHOD__, func_get_args());
    }
}
