<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use App\Kernel;
use DateTime;
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

    public function handleAddTrick($form, $object)
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

    public function handleTrick($form, Trick $trick, $nothing)
    {
        $trick->setUpdatedAt(new DateTime());

        return $this->handle($form, $trick);
    }

    public function handleCategory($form, Category $category, $nothing)
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

    public function handleDeleteTrick(Trick $trick)
    {
        foreach ($trick->getImages() as $image) {
            unlink(Kernel::getProjectDir().'/public/'.$image->getUrl());
        }

        $this->flush($trick, 'remove');
    }

    public function handlerDeleteImage(Image $image)
    {
        // New poster before delete old poster
        $trick = $image->getTrick();
        $trick->removeImage($image);
        $images = $trick->getImages();
        if ($image->getPoster() && count($images) > 0) {
            $images[array_key_first($images->toArray())]->setPoster(true);
        }

        $this->flush($image, 'remove');

        unlink(Kernel::getProjectDir().'/public/'.$image->getUrl());
    }

    public function handleChangePoster(Image $newPoster, Image $oldPoster)
    {
        $trick = $oldPoster->getTrick();
        $trick->setUpdatedAt(new DateTime());

        $oldPoster->setPoster(false);
        $newPoster->setPoster(true);

        $this->flush($trick);
    }

    public function flush($object, $action = 'persist')
    {
        $this->em->$action($object);
        $this->em->flush();
    }
}
