<?php

namespace App\Service\HandlerService;

use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\User;
use App\Kernel;
use App\Service\AvatarService;
use App\Service\UploaderService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class HandlerImageService extends HandlerService
{
    private $token;
    private $avatar;
    private $uploader;

    public function __construct(EntityManagerInterface $em, FlashBagInterface $flash, UploaderService $uploader, CsrfTokenManagerInterface $token, AvatarService $avatar)
    {
        parent::__construct($em, $flash);
        $this->token = $token;
        $this->avatar = $avatar;
        $this->uploader = $uploader;
    }

    /**
     * @param Request $request
     * @param Form    $form
     * @param Image   $image
     * @param Trick   $trick
     *
     * @return bool
     */
    public function handleAddImage(Request $request, Form $form, Image $image, Trick $trick)
    {
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return false;
        }

        $this->uploader->upload($image);

        $image->setTrick($trick);

        if (0 === count($trick->getImages())) {
            $image->setPoster(1);
        }
        $this->create($image);

        $this->flash->add('success', 'La photo a été ajoutée avec succès !');

        return true;
    }

    /**
     * @param Request $request
     * @param Image   $image
     *
     * @return bool
     */
    public function handlerDeleteImage(Request $request, Image $image)
    {
        if (!$request->query->get('csrf_token') || !$this->token->getToken('delete'.$image->getId())->getValue() === $request->query->get('csrf_token')) {
            return false;
        }

        // New poster before delete old poster
        $trick = $image->getTrick();
        $trick->removeImage($image);
        $images = $trick->getImages();

        if ($image->getPoster() && count($images) > 0) {
            $images[array_key_first($images->toArray())]->setPoster(true);
        }

        $this->remove($image);
        $this->flash->add('success', 'La photo a été supprimée avec succès !');

        unlink(Kernel::getProjectDir().'/public/'.$image->getUrl());

        return true;
    }

    /**
     * @param Image $newPoster
     * @param Image $oldPoster
     */
    public function handleChangePoster(Image $newPoster, Image $oldPoster)
    {
        $trick = $oldPoster->getTrick();
        $trick->setUpdatedAt(new DateTime());

        $oldPoster->setPoster(false);
        $newPoster->setPoster(true);

        $this->em->flush();
    }

    /**
     * @param Request $request
     * @param Form    $form
     * @param User    $user
     *
     * @return bool
     */
    public function handleAvatar(Request $request, Form $form, User $user)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->getData() ? $form->getData()->getFile() : null;

            $this->avatar->manageAvatar($user, $file);

            return true;
        }

        return false;
    }
}
