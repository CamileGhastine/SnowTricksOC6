<?php


namespace App\Tests\Entity;


use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TrickTest extends KernelTestCase
{
    public function testValidityEntity()
    {
        $trick = new Trick();
    }
}