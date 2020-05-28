<?php

namespace App\Tests\Controller;

use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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

    public function testRedirectToLogin()
    {
        $client = static::createClient();

        $client->request('GET', '/trick/new');
        $this->assertResponseRedirects('/login');

        $client->request('GET', '/trick/ajax-addCategory');
        $this->assertResponseRedirects('/login');

        $client->request('GET', '/trick/1/update');
        $this->assertResponseRedirects('/login');

        $client->request('GET', '/trick/1/delete');
        $this->assertResponseRedirects('/login');
    }


//    public function testAddTrickForm()
//    {
//        $client = static::createClient();
//
////        Connected user
//        self::bootKernel();
//        $user = self::$container->get(UserRepository::class)->findOneBy(['id' => 1]);
//        $session = self::$container->get('session');
//        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
//        $session->set('_securtiy_main', serialize($token));
//        $session->save();
//        $cookie = new Cookie($session->getName(), $session->getId());
//        $client ->getCookieJar()->set($cookie);
//
//        $crawler = $client->request('GET', '/trick/new');
//
//        $category = self::$container->get(CategoryRepository::class)->findOneBy(['id' => 1]);
//        $form = $crawler->selectButton('Ajouter un trick')->form([
//            'title' => 'New title',
//            'description' => 'New description',
//            'categories' =>[$category],
//        ]);
//
//        $client->submit($form);
//
//        $this->assertResponseRedirects('/trick/new');
//    }
}
