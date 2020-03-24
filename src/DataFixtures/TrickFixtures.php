<?php

namespace App\DataFixtures;

use App\Entity\Trick;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class TrickFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR');

        for($i=0; $i<50; $i++)
        {
            $trick = new Trick();
            $trick->setTitle($faker->sentence(3, true))
                ->setDescription( implode('<br/>', $faker->sentences(4)))
                ->setImage('images/noImage.jpg')
                ->setCreatedAt($faker->dateTimebetween('-7 days'));
            $manager->persist($trick);
            $manager->flush();
        }


    }
}
