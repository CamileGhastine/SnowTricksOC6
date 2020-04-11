<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CommentFixtures extends Fixture implements dependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        $users = [];
        for ($i=0; $i<=3; $i++) {
            $users[]='user'.$i;
        }

        for ($j=1; $j<=20; $j++) {
            for ($i=1; $i<rand(-5, 15); $i++) {
                $comment = new Comment();

                $user = $users[array_rand($users)];

                $comment->setContent(implode('<br/>', $faker->sentences(4)))
                    ->setCreatedAt(new DateTime())
                    ->setTrick($this->getReference('trick'.$j))
                    ->setUser($this->getReference($user))
                ;

                $manager->persist($comment);
            }
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return array (
            TrickFixtures::class,
        );
    }
}
