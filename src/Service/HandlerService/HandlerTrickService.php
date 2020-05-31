<?php


namespace App\Service\HandlerService;


use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use App\Kernel;
use App\Service\UploaderService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class HandlerTrickService extends HandlerService
{
    private $token;
    private $uploader;

    public function __construct(EntityManagerInterface $em, FlashBagInterface $flash, CsrfTokenManagerInterface $token, UploaderService $uploader)
    {
        parent::__construct($em, $flash);
        $this->token = $token;
        $this->uploader = $uploader;
    }

    public function handleAddTrick(Request $request, Form $form,Trick $trick)
    {
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return false;
        }

        /** @var Video $video */
        foreach ($form->getData()->getVideos() as $video) {
            $video->refactorIframe();
        }

        /** @var Image $image */
        foreach ($form->getData()->getImages() as $key => $image) {
            $this->uploader->upload($image);
            0 === $key ? $image->setPoster(true) : null;
        }
        $this->create($trick);
        $this->flash->add('success', 'La figure a été ajoutée avec succès !');

        return true;
    }

    public function handleEditTrick(Request $request, Form $form, Trick $trick)
    {
        $trick->setUpdatedAt(new DateTime());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->flash->add('success', 'Le trick a été modifiée avec succès !');

            return true;
        }

        return false;

    }
    /**
     * @return bool
     */
    public function handleDeleteTrick(Request $request, Trick $trick)
    {
        if (!$request->request->get('_token') || $this->token->getToken('delete'.$trick->getId())->getValue() !== $request->request->get('_token')) {
            return false;
        }

        $this->remove($trick);
        $this->flash->add('success', 'la figure a été supprimée avec succès !');

        foreach ($trick->getImages() as $image) {
            unlink(Kernel::getProjectDir().'/public/'.$image->getUrl());
        }

        return true;
    }

}