<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR');

        for($i=1; $i<=5; $i++)
        {
            $category = new Category();

            $category->setTitle($faker->word)
                ->setDescription(implode('<br/>', $faker->sentences(4)))
                ;

            $manager->persist($category);
        }




        $manager->flush();
    }
}
