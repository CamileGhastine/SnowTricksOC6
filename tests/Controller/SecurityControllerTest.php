<?php


namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{

    public function testLoginFormSuccess()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form([
            'username' => 'camile',
            'password' => 'camile',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/');
    }

    public function testLoginFormFailure()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form([
            'username' => 'camile',
            'password' => 'wrong password',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/login');
    }
}