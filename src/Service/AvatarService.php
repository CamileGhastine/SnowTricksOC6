<?php

namespace App\Service;

use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;

class AvatarService
{
    private $uploader;
    private $em;

    public function __construct(UploaderService $uploader, EntityManagerInterface $em)
    {
        $this->uploader = $uploader;
        $this->em = $em;
    }

    public function manageAvatar($user, $file)
    {
        $fileToDelete = $user->getAvatar();

        if (null === $file) {
            $user->setAvatar('images/users/nobody.jpg');
        }
        if (null !== $file) {
            $user->setAvatar($this->uploader->uploadAvatar($file));
        }

        $this->em->persist($user);
        $this->em->flush();

        if ('images/users/nobody.jpg' !== $fileToDelete) {
            unlink(Kernel::getProjectDir().'/public/'.$fileToDelete);
        }
    }
}
