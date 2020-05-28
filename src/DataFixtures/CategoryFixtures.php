<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CategoryFixtures extends Fixture
{
    private $categories = ['grabs', 'rotations', 'slides', 'flips'];

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < count($this->categories); ++$i) {
            $category = new Category();

            $category->setTitle($this->categories[$i])
            ;

            $this->addReference('category'.$i, $category);

            $manager->persist($category);
        }
        $manager->flush();
    }

    public function getListCategories()
    {
        for ($j = 0; $j < count($this->categories); ++$j) {
            $listCategories[] = 'category'.$j;
        }

        return $listCategories;
    }
}
