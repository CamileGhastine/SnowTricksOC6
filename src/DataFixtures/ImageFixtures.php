<?php

namespace App\DataFixtures;

use App\Entity\Image;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ImageFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for ($i=0; $i<=29; $i++) {

            $name = 'image'.$i;

            $image = new Image();

            $image->setUrl('images/tricks/'.$name.'.jpg')
                ->setAlt($name)
                ->setPoster(0)
            ;

            $this->addReference($name, $image);

            $manager->persist($image);
        }
        $manager->flush();
    }
}
