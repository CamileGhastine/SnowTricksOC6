<?php

namespace App\DataFixtures;

use App\Entity\Image;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ImageFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i <= 29; ++$i) {
            $name = 'image'.$i;

            $image = new Image();

            $image->setUrl('images/tricks/'.$name.'.jpg')
                ->setAlt($name)
                ->setPoster(false)
            ;

            $this->addReference($name, $image);

            $manager->persist($image);
        }
        $manager->flush();
    }

    /**
     * @return mixed
     */
    public function getListImages()
    {
        for ($j = 0; $j < 29; ++$j) {
            $listImages[] = 'image'.$j;
        }

        return $listImages;
    }
}
