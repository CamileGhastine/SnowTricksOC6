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

        for($i=0; $i<35; $i++)
        {
            $trick = new Trick();
            $date = $faker->dateTimebetween('-7 days');
            $trick->setTitle($faker->sentence(3, true))
                ->setDescription( implode('<br/>', $faker->sentences(4)))
                ->setImage('images/tricks/noImage.jpg')
                ->setUser_id($user)
                ->setCreatedAt($date)
                ->setModifiedAt($date);
            $manager->persist($trick);
            $manager->flush();
        }


    }
}
