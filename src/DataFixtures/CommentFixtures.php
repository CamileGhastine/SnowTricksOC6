<?php

namespace App\DataFixtures;

use App\Entity\Comment;
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
        for ($i = 0; $i <= 3; ++$i) {
            $users[] = 'user'.$i;
        }

        for ($j = 1; $j <= 10; ++$j) {
            for ($i = 1; $i < rand(-5, 15); ++$i) {
                $user = $users[array_rand($users)];

                $comment = new Comment($this->getReference('trick'.$j), $this->getReference($user));

                $comment->setContent(implode("\n", $faker->sentences(4)));

                $manager->persist($comment);
            }
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            TrickFixtures::class,
        ];
    }
}
