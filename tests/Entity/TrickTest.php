<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Trick;
use App\Entity\User;
use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TrickTest extends KernelTestCase
{
    public function getTrick()
    {
        $category = (new Category())
            ->setTitle('grabs');
        $user = new User();

        return $trick = (new Trick($user))
            ->setTitle('New title')
            ->setDescription('New description')
            ->addCategory($category);
    }

    public function assertHasErrors(Trick $trick, int $number = 0)
    {
        self::bootKernel();
        $error = self::$container->get('validator')->validate($trick);

        $this->assertCount($number, $error);
    }

    public function testValideTrickTitle()
    {
        $trick = $this->getTrick();

        $this->assertHasErrors($trick);
    }

    public function testInvalideTrickTitle()
    {
        $trick = $this->getTrick();
        $trick->setTitle('');
        $this->assertHasErrors($trick, 2);

        $trick->setTitle('N');
        $this->assertHasErrors($trick, 1);
    }

    public function testUniqueTrickTitle()
    {
        self::bootKernel();
        self::$container->get(TrickRepository::class)->findAll();
        $this->assertHasErrors($this->getTrick()->setTitle('Nulla quasi dolor ut sed.'),1);
    }
}
