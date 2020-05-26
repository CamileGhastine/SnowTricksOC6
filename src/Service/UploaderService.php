<?php

namespace App\Service;

use Symfony\Component\String\Slugger\SluggerInterface;

class UploaderService
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function upload($image)
    {
        $OriginalName = pathinfo($image->getFile()->getClientOriginalName(), PATHINFO_FILENAME);
        $name = $this->slugger->slug($OriginalName).'-'.uniqid().'.'.pathinfo($image->getFile()->getClientOriginalName(), PATHINFO_EXTENSION);

        $image->getFile()->move((new \App\Kernel)->getProjectDir().'/public/images/tricks', $name);

        $image->setUrl('images/tricks/'.$name);
        $image->setAlt($name);
        $image->setPoster(0);
    }

    public function uploadAvatar($file)
    {
        $OriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $name = $this->slugger->slug($OriginalName).'-'.uniqid().'.'.pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);

        $file->move((new \App\Kernel)->getProjectDir().'/public/images/users', $name);

        return 'images/users/'.$name;
    }
}
