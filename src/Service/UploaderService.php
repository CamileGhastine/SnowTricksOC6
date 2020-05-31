<?php

namespace App\Service;

use App\Entity\Image;
use App\Kernel;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploaderService
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    /**
     * @param Image $image
     */
    public function upload(Image $image)
    {
        $OriginalName = pathinfo($image->getFile()->getClientOriginalName(), PATHINFO_FILENAME);
        $name = $this->slugger->slug($OriginalName).'-'.uniqid().'.'.pathinfo($image->getFile()->getClientOriginalName(), PATHINFO_EXTENSION);

        $image->getFile()->move(Kernel::getProjectDir().'/public/images/tricks', $name);

        $image->setUrl('images/tricks/'.$name);
        $image->setAlt($name);
        $image->setPoster(0);
    }

    /**
     * @param UploadedFile $file
     *
     * @return string
     */
    public function uploadAvatar(UploadedFile $file)
    {
        $OriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $name = $this->slugger->slug($OriginalName).'-'.uniqid().'.'.pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);

        $file->move(Kernel::getProjectDir().'/public/images/users', $name);

        return 'images/users/'.$name;
    }
}
