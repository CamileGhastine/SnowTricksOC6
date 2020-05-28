<?php

namespace App\Service;

use App\Entity\User;
use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AvatarService
{
    private $uploader;
    private $em;

    public function __construct(UploaderService $uploader, EntityManagerInterface $em)
    {
        $this->uploader = $uploader;
        $this->em = $em;
    }

    public function manageAvatar(User $user, UploadedFile $file)
    {
        $fileToDelete = $user->getAvatar();

        $user->setAvatar('images/users/nobody.jpg');
        if (null !== $file) {
            $user->setAvatar($this->uploader->uploadAvatar($file));
        }

//        if (null === $file) {
//            $user->setAvatar('images/users/nobody.jpg');
//        }
//        if (null !== $file) {
//            $user->setAvatar($this->uploader->uploadAvatar($file));
//        }
//
//        if (null === $file) {
//            $user->setAvatar('images/users/nobody.jpg');
//        } else {
//            $user->setAvatar($this->uploader->uploadAvatar($file));
//        }

        $this->em->persist($user);
        $this->em->flush();

        if ('images/users/nobody.jpg' !== $fileToDelete) {
            unlink(Kernel::getProjectDir().'/public/'.$fileToDelete);
        }
    }
}
