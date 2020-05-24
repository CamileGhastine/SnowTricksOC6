<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Image;
use App\Entity\Trick;
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

    public function handleCategory($form, Category $category, $trick)
    {
        return $this->handle($form, $category);
    }

    public function handleImage($form, Image $image, Trick $trick)
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->uploader->upload($image);

            $image->setTrick($trick);

            if (0 === count($trick->getImages())) {
                $image->setPoster(1);
            }
            $this->flush($image);

            return true;
        }

        return false;
    }

    public function handleVideo($form, Video $video, Trick $trick)
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $video->setTrick($trick);
            $video->refactorIframe();

            $this->flush($video);

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
