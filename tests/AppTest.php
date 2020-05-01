<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class AppTest extends TestCase
{
    public function testTests () {
        $this->assertEquals(2, 1+1);
    }

}