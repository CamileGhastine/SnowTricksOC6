<?php

namespace App\DataFixtures;

use App\Entity\Trick;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class TrickFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        $users = [];
        for ($i=0; $i<=3; $i++) {
            $users[]='user'.$i;
        }
        $user = $users[array_rand($users)];

        for ($j=1; $j<=20; $j++) {
            $categories=[];
            for ($i=1; $i<=5; $i++) {
                $categories[]='category'.$i;
            }

            $trick = new Trick($this->getReference($user));

            $date = $faker->dateTimebetween('-7 days');

            $trick->setTitle($faker->sentence(3, true))
                ->setDescription( implode('<br/>', $faker->sentences(4)) )
                ->setImage('images/tricks/noImage.jpg')
                ->setCreatedAt($date)
                ->setUpdatedAt($date)
            ;

            for ($i=1; $i<=rand(1,3); $i++) {
                $key = array_rand($categories);
                $category = $categories[$key];
                $trick->addCategory($this->getReference($category));
                unset($categories[$key]);
            }

            $this->addReference('trick'.$j, $trick);

            $manager->persist($trick);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            CategoryFixtures::class,
            UserFixtures::class,
        );
    }
}
