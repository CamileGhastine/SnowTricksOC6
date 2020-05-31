<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CommentFixtures extends Fixture implements dependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        $users = [];
        for ($i = 0; $i <= 3; ++$i) {
            $users[] = 'user'.$i;
        }

        for ($j = 0; $j < TrickFixtures::NB_TRICKS; ++$j) {
            for ($i = 1; $i < rand(-5, 20); ++$i) {
                /** @var User $user */
                $user = $this->getReference($users[array_rand($users)]);
                /** @var Trick $trick */
                $trick = $this->getReference('trick'.$j);

                $date = max($user->getRegisteredAt(), $trick->getCreatedAt());

                $comment = new Comment($trick, $user);

                $comment->setContent(implode("\n", $faker->sentences(4)))
                    ->setCreatedAt($faker->dateTimeBetween('-'.(new DateTime())->diff($date)->days.'days'));

                $manager->persist($comment);
            }
        }
        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies()
    {
        return [
            TrickFixtures::class,
        ];
    }
}
