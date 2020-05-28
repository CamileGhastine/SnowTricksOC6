<?php

namespace App\DataFixtures;

use App\Entity\Trick;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TrickFixtures extends Fixture implements DependentFixtureInterface
{
    const NB_TRICKS = 13;

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        $categories = $this->randomCategories();
        $images = $this->random('Image',1 , 5);
        $videos = $this->random('Video', 0, 3);

        for ($j = 0; $j < self::NB_TRICKS; ++$j) {
            $trick = new Trick($this->randomUser()[$j]);

            $date = $faker->dateTimebetween('-7 days');

            $trick->setTitle(substr($faker->sentence(3, true), 0, 29))
                ->setDescription(implode("\n", $faker->sentences(4)))
                ->setCreatedAt($date)
                ->setUpdatedAt($date)
            ;

            $this->add($trick, 'Category', $categories[$j]);
            $this->add($trick, 'Image', $images[$j]);
            $this->add($trick, 'Video', $videos[$j]);

            $this->addReference('trick'.$j, $trick);

            $manager->persist($trick);
        }
        $manager->flush();
    }

    /**
     * @return array
     */
    private function randomUser()
    {
        for ($i = 0; $i < self::NB_TRICKS; ++$i) {
            $userRef = 'user'.rand(0, UserFixtures::NB_USERS);
            $users[] = $this->getReference($userRef);
        }

        return $users;
    }

    private function randomCategories()
    {
        for ($k = 0; $k < self::NB_TRICKS; ++$k) {
            $listCategories = (new CategoryFixtures())->getListCategories();
            $trickCategories = [];

            for ($i = 1; $i <= rand(1, 3); ++$i) {
                $key = array_rand($listCategories);
                $trickCategories[] = $listCategories[$key];
                unset($listCategories[$key]);
            }

            $categories[$k] = $trickCategories;
        }

        return $categories;
    }

    /**
     * @return mixed
     */
    private function random($entity, $min, $max)
    {
        $class = 'App\DataFixtures\\'.$entity.'Fixtures';
        $getList = 'getList'.$entity.'s';

        $list = (new $class())->$getList();
        for ($k = 0; $k < self::NB_TRICKS; ++$k) {
            $trickEntity = [];
            for ($i = 1; $i < rand($min, $max); ++$i) {
                $key = array_rand($list);
                $trickEntity[] = $list[$key];
                unset($list[$key]);
            }

            $entities[] = $trickEntity;
        }

        return $entities;
    }

    private function add($trick, $name, $entities)
    {
        foreach ($entities as $key => $entity) {
            $add = 'add'.$name;
            $entity = $this->getReference($entity);
            if ($name === 'Image' && $key === 0) {
                $entity->setPoster(true);
            }
            $trick->$add($entity);
        }
    }


    /**
     * @return string[]
     */
    public function getDependencies()
    {
        return [
            CategoryFixtures::class,
            UserFixtures::class,
            ImageFixtures::class,
            VideoFixtures::class,
        ];
    }
}
