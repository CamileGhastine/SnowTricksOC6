<?php

namespace App\Service\HandlerService;

use App\Entity\Trick;
use App\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class HandlerVideoService extends HandlerService
{
    private $token;

    public function __construct(EntityManagerInterface $em, FlashBagInterface $flash, CsrfTokenManagerInterface $token)
    {
        parent::__construct($em, $flash);
        $this->token = $token;
    }

    public function handleAddVideo(Request $request, Form $form, Video $video, Trick $trick)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $video->setTrick($trick);
            $video->refactorIframe();

            $this->create($video);

            $this->flash->add('success', 'La vidéo a été ajoutée avec succès !');

            return true;
        }

        return false;
    }

    public function handleDeleteVideo(Request $request, Video $video)
    {
        if ($request->query->get('csrf_token') && $this->token->getToken('delete'.$video->getId())->getValue() === $request->query->get('csrf_token')) {
            $this->remove($video);
            $this->flash->add('success', 'La vidéo a été supprimée avec succès !');

            return true;
        }

        return false;
    }
}
