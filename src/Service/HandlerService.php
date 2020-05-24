<?php

namespace App\Service;

use App\Entity\Image;
use App\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class HandlerService
{
    private $em;
    private $request;
    private $uploader;

    public function __construct(EntityManagerInterface $em, RequestStack $request, UploaderService $uploader)
    {
        $this->em = $em;
        $this->request = $request->getCurrentRequest();
        $this->uploader = $uploader;
    }

    public function handle($form, $object)
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->flush($object);

            return true;
        }

        return false;
    }

    public function handleTrick($form, $object)
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Video $video */
            foreach ($form->getData()->getVideos() as $video) {
                $video->refactorIframe();
            }

            /** @var Image $image */
            foreach ($form->getData()->getImages() as $key => $image) {
                $this->uploader->upload($image);
                0 === $key ? $image->setPoster(true) : null;
            }
            $this->flush($object);

            return true;
        }

        return false;
    }

    public function flush($object)
    {
        $this->em->persist($object);
        $this->em->flush();
    }
}
