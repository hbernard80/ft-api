<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MainControllerTest extends WebTestCase
{
    public function testHomePageIsAvailable(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('FtStats index');
    }
}
