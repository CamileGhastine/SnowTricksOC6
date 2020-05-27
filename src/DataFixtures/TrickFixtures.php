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
        $images = $this->randomImages();
        $videos = $this->randomVideos();

        for ($j = 0; $j < self::NB_TRICKS; ++$j) {
            $trick = new Trick($this->randomUser()[$j]);

            $date = $faker->dateTimebetween('-7 days');

            $trick->setTitle(substr($faker->sentence(3, true), 0, 29))
                ->setDescription(implode("\n", $faker->sentences(4)))
                ->setCreatedAt($date)
                ->setUpdatedAt($date)
            ;

            foreach ($categories[$j] as $category) {
                $trick->addCategory($this->getReference($category));
            }

            foreach ($images[$j] as $key => $image) {
                $image = $this->getReference($image);
                if (0 === $key) {
                    $image->setPoster(true);
                }
                $trick->addImage($image);
            }

            foreach ($videos[$j] as $video) {
                $video = $this->getReference($video);
                $trick->addVideo($video);
            }

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

    /**
     * @return mixed
     */
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
    private function randomImages()
    {
        $listImages = (new ImageFixtures())->getListImages();
        for ($k = 0; $k < self::NB_TRICKS; ++$k) {
            $trickImages = [];
            for ($i = 1; $i < rand(1, 5); ++$i) {
                $key = array_rand($listImages);
                $trickImages[] = $listImages[$key];
                unset($listImages[$key]);
            }

            $images[] = $trickImages;
        }

        return $images;
    }

    /**
     * @return mixed
     */
    private function randomVideos()
    {
        $listVideos = (new VideoFixtures())->getListVideos();
        for ($k = 0; $k < self::NB_TRICKS; ++$k) {
            $trickVideos = [];
            for ($i = 0; $i < rand(0, 2); ++$i) {
                $key = array_rand($listVideos);
                $trickVideos[] = $listVideos[$key];
                unset($listVideos[$key]);
            }

            $videos[] = $trickVideos;
        }

        return $videos;
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
