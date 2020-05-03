<?php

namespace App\Service;



use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;

class AvatarService
{
    private $uploader;
    private $em;

    public function __construct (UploaderService $uploader, EntityManagerInterface $em)
    {
        $this->uploader = $uploader;
        $this->em = $em;
    }

    public function manageAvatar($user, $action, $file)
    {
        $fileToDelete =$user->getAvatar();

        if ($file === null) $user->setAvatar('images/users/nobody.jpg');
        if ($file !== null) $user->setAvatar($this->uploader->uploadAvatar($file));

        $this->em->persist($user);
        $this->em->flush();

        if ($fileToDelete !== 'images/users/nobody.jpg') unlink(Kernel::getProjectDir().'/public/'.$fileToDelete);
    }
}