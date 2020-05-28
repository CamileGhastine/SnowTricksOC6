<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TrickControllerTest extends WebTestCase
{
    /**
     * @dataProvider provideUrls
     */
    public function testPageIsSuccessful($url)
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function provideUrls()
    {
        return [
            ['/'],
            ['/login'],
//            ['/inscription'],
        ];
    }

    public function testHome()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertPageTitleSame('SnowTricks : le site de tous les snowborders');

        $this->assertSelectorTextSame('header>div>h1', 'SnowTricks');

        // 2 Ways to do the same test
        $this->assertSame('container', $crawler->filter('body>div')->attr('class'));
        $this->assertSelectorExists('div[class="container"]');

        $this->assertsame(1, $crawler->filter('div[id="tricks"] a span[class="badge badge-pill badge-activate mx-3 "]')->count());

        $this->assertCount(10, $crawler->filter('article'));
    }

//    public function testAddTrickForm()
//    {
//        $client = static::createClient();
//
//        $crawler = $client->request('GET', '/trick/1');
//
//        $trick = [
//            'add_trick[title]' => "new title",
//            'add_trick[description]' => "new description",
//            'add_trick[categories]' => [ 1, 3]
//        ];
//        $form = $crawler->selectButton('submit')->form();
//        $crawler = $client->submitForm('Commenter', ['comment[content]' => 'commentaire test']);
//    }
}
