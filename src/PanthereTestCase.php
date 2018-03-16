<?php

/*
 * This file is part of the Panthère project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Panthere;

use Goutte\Client as GoutteClient;
use Panthere\Client as PanthereClient;
use Panthere\ProcessManager\WebServer;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

if (\class_exists(WebTestCase::class)) {
    /**
     * @internal
     */
    abstract class InternalTestCase extends WebTestCase
    {
    }
} else {
    /**
     * @internal
     */
    abstract class InternalTestCase extends TestCase
    {
    }
}

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
abstract class PanthereTestCase extends InternalTestCase
{
    /**
     * @var string|null
     */
    protected static $webServerDir;

    /**
     * @var WebServer|null
     */
    protected static $webServer;

    /**
     * @var string|null
     */
    protected static $baseUri;

    /**
     * @var GoutteClient|null
     */
    protected static $goutteClient;

    /**
     * @var PanthereClient|null
     */
    protected static $panthereClient;

    public static function tearDownAfterClass()
    {
        if (null !== self::$webServer) {
            self::$webServer->stop();
            self::$webServer = null;
        }

        if (null !== self::$panthereClient) {
            self::$panthereClient->stop();
            self::$panthereClient = null;
        }

        if (null !== self::$goutteClient) {
            self::$goutteClient = null;
        }
    }

    protected static function startWebServer(?string $webServerDir = null): void
    {
        if (null !== static::$webServer) {
            return;
        }

        if (null === $webServerDir) {
            // Try the local $webServerDir property, or the PANTHERE_WEB_SERVER_DIR env var or default to the Flex directory structure
            $webServerDir = static::$webServerDir ?? $_ENV['PANTHERE_WEB_SERVER_DIR'] ?? __DIR__.'/../../../../public';
        }

        self::$webServer = new WebServer($webServerDir, '127.0.0.1', 9000);
        self::$webServer->run();

        self::$baseUri = 'http://127.0.0.1:9000';
    }

    protected static function createPanthereClient(): PanthereClient
    {
        self::startWebServer();
        if (null === self::$panthereClient) {
            self::$panthereClient = new PanthereClient();
        }

        return self::$panthereClient;
    }

    protected static function createGoutteClient(): GoutteClient
    {
        if (!\class_exists(GoutteClient::class)) {
            throw new \RuntimeException('Goutte is not installed. Run "composer req fabpot/goutte".');
        }

        self::startWebServer();
        if (null === self::$goutteClient) {
            self::$goutteClient = new GoutteClient();
        }

        return self::$goutteClient;
    }
}
