<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    private $categories = ['grabs', 'rotations', 'slides', 'flips'];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < count($this->categories); ++$i) {
            $category = new Category();

            $category->setTitle($this->categories[$i])
            ;

            $this->addReference('category'.$i, $category);

            $manager->persist($category);
        }
        $manager->flush();
    }

    /**
     * @return mixed
     */
    public function getListCategories()
    {
        for ($j = 0; $j < count($this->categories); ++$j) {
            $listCategories[] = 'category'.$j;
        }

        return $listCategories;
    }
}
