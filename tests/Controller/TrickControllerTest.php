<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TrickControllerTest extends WebTestCase
{
    public function testHome()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();

        $this->assertPageTitleSame('SnowTricks : le site de tous les snowborders');

        // 2 Ways to do the same test
        $this->assertSelectorTextContains('header>div>h1', 'SnowTricks');
        $this->assertSelectorTextSame('header>div>h1', 'SnowTricks');

        // 2 Ways to do the same test
        $this->assertSame('container', $crawler->filter('body>div')->attr('class'));
        $this->assertSelectorExists('div[class="container"]');

        $this->assertSelectorExists('div[id="tricks"] a span');

        $this->assertsame(1, $crawler->filter('div[id="tricks"] a span[class="badge badge-pill badge-activate mx-3 "]')->count());
    }

}
