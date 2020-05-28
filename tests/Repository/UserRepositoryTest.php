<?php


namespace App\Tests\Repository;


use App\Repository\CategoryRepository;
use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    public function testCount()
    {
        self::bootKernel();

        $users = self::$container->get(UserRepository::class)->count([]);
        $this->assertEquals(4, $users);

        $categories = self::$container->get(CategoryRepository::class)->count([]);
        $this->assertEquals(4, $categories);

        $images = self::$container->get(ImageRepository::class)->count([]);
        $this->assertEquals(30, $images);

        $tricks = self::$container->get(TrickRepository::class)->count([]);
        $this->assertEquals(13, $tricks);


    }
}