<?php

declare(strict_types=1);

namespace Pumukit\WebTVBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class IndexControllerTest.
 *
 * @internal
 * @coversNothing
 */
class IndexControllerTest extends WebTestCase
{
    public function testIndex()
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertTrue($crawler->filter('html')->count() > 0);
        static::assertTrue($crawler->filter('title')->count() > 0);
        static::assertTrue($crawler->filter('body')->count() > 0);
    }
}
