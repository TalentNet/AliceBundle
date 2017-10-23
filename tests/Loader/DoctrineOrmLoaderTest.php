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

use Hautelook\AliceBundle\DataFixtures\Loader\FakeLoader;
use Hautelook\AliceBundle\DataFixtures\Loader\FakeLoaderPersisterAware;
use Hautelook\AliceBundle\FixtureLocator\FakeFixtureLocator;
use Hautelook\AliceBundle\OrmLoaderInterface;
use Hautelook\AliceBundle\Logger\FakeLogger;
use Hautelook\AliceBundle\LoggerAwareInterface;
use Hautelook\AliceBundle\Resolver\FakeBundleResolver;

/**
 * @covers \Hautelook\AliceBundle\Loader\DoctrineOrmLoader
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class DoctrineOrmLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testIsALoader()
    {
        $this->assertTrue(is_a(DoctrineOrmLoader::class, OrmLoaderInterface::class, true));
    }

    public function testIsLoggerAware()
    {
        $this->assertTrue(is_a(DoctrineOrmLoader::class, LoggerAwareInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new DoctrineOrmLoader(new FakeBundleResolver(), new FakeFixtureLocator(), new FakeLoaderPersisterAware(), new FakeLogger());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected loader to be an instance of "Fidry\AliceDataFixtures\Persistence\PersisterAwareInterface".
     */
    public function testDataFixtureLoaderMustBePersisterAware()
    {
        new DoctrineOrmLoader(new FakeBundleResolver(), new FakeFixtureLocator(), new FakeLoader(), new FakeLogger());
    }
}
