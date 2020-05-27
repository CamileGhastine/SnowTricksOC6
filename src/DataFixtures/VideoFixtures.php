<?php

namespace App\DataFixtures;

use App\Entity\Video;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class VideoFixtures extends Fixture
{
    private $videos = ['src="https://www.youtube.com/embed/V9xuy-rVj9w"',
        'src="https://www.youtube.com/embed/0uGETVnkujA"',
        'src="https://www.youtube.com/embed/Q5691RGDUJ4"',
       'src="https://www.youtube.com/embed/MasvoDXQe3U"',
        'src="https://www.youtube.com/embed/R2Cp1RumorU"',
        'src="https://www.youtube.com/embed/-ClNrBe-fr4"',
        'src="https://www.youtube.com/embed/0uGETVnkujA"',
        'src="https://www.youtube.com/embed/Q5691RGDUJ4"',
        'src="https://www.youtube.com/embed/MasvoDXQe3U"',
        'src="https://www.youtube.com/embed/R2Cp1RumorU"',
        'src="https://www.youtube.com/embed/-ClNrBe-fr4"',
        'src="https://www.youtube.com/embed/V9xuy-rVj9w"',
        'src="https://www.youtube.com/embed/R2Cp1RumorU"',
        'src="https://www.youtube.com/embed/-ClNrBe-fr4"',
        'src="https://www.youtube.com/embed/V9xuy-rVj9w"',
        ];

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < count($this->videos); ++$i) {
            $video = new video();

            $video->setIframe('<iframe '.$this->videos[$i].'></iframe>');

            $this->addReference('video'.$i, $video);

            $manager->persist($video);
        }
        $manager->flush();
    }

    /**
     * @return mixed
     */
    public function getListVideos()
    {
        for ($j = 0; $j < count($this->videos); ++$j) {
            $listVideos[] = 'video'.$j;
        }

        return $listVideos;
    }
}
