<?php


namespace App\Tests\Repository;


use App\Repository\CategoryRepository;
use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TrickRepositoryTest extends KernelTestCase
{
    public function testCount()
    {
        self::bootKernel();

        $tricks = self::$container->get(TrickRepository::class)->count([]);
        $this->assertEquals(13, $tricks);
    }
}