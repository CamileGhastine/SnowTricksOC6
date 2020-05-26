<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\User;
use App\Entity\Video;
use App\Kernel;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

//use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class HandlerService
{
    private $em;
    private $request;
    private $uploader;
    private $avatar;
    private $passwordEncoder;
    private $emailer;
    private $token;

    public function __construct(EntityManagerInterface $em,
                                RequestStack $request,
                                UploaderService $uploader,
                                AvatarService $avatar,
                                UserPasswordEncoderInterface $passwordEncoder,
                                EmailerService $emailer,
                                CsrfTokenManagerInterface $token)
    {
        $this->em = $em;
        $this->request = $request->getCurrentRequest();
        $this->uploader = $uploader;
        $this->avatar = $avatar;
        $this->passwordEncoder = $passwordEncoder;
        $this->emailer = $emailer;
        $this->token = $token;
    }

    /**
     * @param $object
     *
     * @return bool
     */
    public function handle(Form $form, $object)
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->flush($object);

            return true;
        }

        return false;
    }

    /**
     * @param $object
     *
     * @return bool
     */
    public function handleAddTrick(Form $form, $object)
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

    /**
     * @return bool
     */
    public function handleTrick(Form $form, Trick $trick, $nothing)
    {
        $trick->setUpdatedAt(new DateTime());

        return $this->handle($form, $trick);
    }

    /**
     * @param $nothing
     *
     * @return bool
     */
    public function handleCategory(Form $form, Category $category, $nothing)
    {
        return $this->handle($form, $category);
    }

    /**
     * @return bool
     */
    public function handleImage(Form $form, Image $image, Trick $trick)
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

    /**
     * @return bool
     */
    public function handleVideo(Form $form, Video $video, Trick $trick)
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

    /**
     * @return bool
     */
    public function handleDeleteTrick(Trick $trick)
    {
        if ($this->request->request->get('_token') && $this->token->getToken('delete'.$trick->getId())->getValue() === $this->request->request->get('_token')) {
            foreach ($trick->getImages() as $image) {
                unlink(Kernel::getProjectDir().'/public/'.$image->getUrl());
            }

            $this->flush($trick, 'remove');

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function handlerDeleteImage(Image $image)
    {
        if ($this->request->query->get('csrf_token') && $this->token->getToken('delete'.$image->getId())->getValue() === $this->request->query->get('csrf_token')) {
            // New poster before delete old poster
            $trick = $image->getTrick();
            $trick->removeImage($image);
            $images = $trick->getImages();

            if ($image->getPoster() && count($images) > 0) {
                $images[array_key_first($images->toArray())]->setPoster(true);
            }

            $this->flush($image, 'remove');

            unlink(Kernel::getProjectDir().'/public/'.$image->getUrl());

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function handleDeleteVideo(Video $video)
    {
        if ($this->request->query->get('csrf_token') && $this->token->getToken('delete'.$video->getId())->getValue() === $this->request->query->get('csrf_token')) {
            $this->flush($video, 'remove');

            return true;
        }

        return false;
    }

    public function handleChangePoster(Image $newPoster, Image $oldPoster)
    {
        $trick = $oldPoster->getTrick();
        $trick->setUpdatedAt(new DateTime());

        $oldPoster->setPoster(false);
        $newPoster->setPoster(true);

        $this->flush($trick);
    }

    /**
     * @param $object
     * @param string $action
     */
    public function flush($object, $action = 'persist')
    {
        $this->em->$action($object);
        $this->em->flush();
    }

    // UserController

    /**
     * @return bool
     */
    public function handleAvatar(Form $form, User $user)
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->getData() ? $form->getData()->getFile() : null;

            $this->avatar->manageAvatar($user, $file);

            return true;
        }

        return false;
    }

    //SecurityController

    /**
     * @param $user
     *
     * @return bool
     *
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function handleRegistration(Form $form, $user)
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()))
                ->setAvatar('images/users/nobody.jpg')
                ->setToken($this->token->generateToken());

            if ($form->getData()->getFile()) {
                $url = $this->uploader->uploadAvatar($form->getData()->getFile());
                $user->setAvatar($url);
            }

            $this->flush($user);

            $this->emailer->sendEmailRegistration($user);

            return true;
        }

        return false;
    }

    public function handleValidateRegistration($user)
    {
        if ($this->request->query->get('validate')) {
            $user->setValidate(true);
            $this->flush($user);

            return true;
        }

        return false;
    }

    public function handleTokenNotValidInSecurity($user)
    {
        if (!$user || $user->getToken() !== $request->query->get('token')) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function handleResetPassword(Form $form, User $user)
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $form->getData()->getPassword()));

            $this->flush($user);

            return true;
        }

        return false;
    }
}
