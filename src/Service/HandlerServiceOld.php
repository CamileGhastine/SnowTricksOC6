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
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class HandlerServiceOld
{
    private $em;
    private $request;
    private $uploader;
    private $avatar;
    private $passwordEncoder;
    private $emailer;
    private $token;
    private $generateToken;
    private $flash;

    public function __construct(
        EntityManagerInterface $em,
        RequestStack $request,
        UploaderService $uploader,
        AvatarService $avatar,
        UserPasswordEncoderInterface $passwordEncoder,
        EmailerService $emailer,
        CsrfTokenManagerInterface $token,
        TokenGeneratorInterface $generateToken,
        FlashBagInterface $flash
    ) {
        $this->em = $em;
        $this->request = $request->getCurrentRequest();
        $this->uploader = $uploader;
        $this->avatar = $avatar;
        $this->passwordEncoder = $passwordEncoder;
        $this->emailer = $emailer;
        $this->token = $token;
        $this->generateToken = $generateToken;
        $this->flash = $flash;
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
     * @return bool
     */
    public function handleVideo(Form $form, Video $video, Trick $trick)
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $video->setTrick($trick);
            $video->refactorIframe();

            $this->flush($video);

            $this->flash->add('success', 'La vidéo a été ajoutée avec succès !');

            return true;
        }

        return false;
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

    //SecurityController

    /**
     * @param $user
     *
     * @return bool
     *
     * @throws TransportExceptionInterface
     */
    public function handleRegistration(Form $form, $user)
    {
        $form->handleRequest($this->request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return false;
        }

        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()))
            ->setAvatar('images/users/nobody.jpg')
            ->setToken($this->generateToken->generateToken());

        if ($form->getData()->getFile()) {
            $url = $this->uploader->uploadAvatar($form->getData()->getFile());
            $user->setAvatar($url);
        }

        $this->flush($user);

        $this->emailer->sendEmailRegistration($user);
        $this->flash->add('success', 'Votre inscription a été réalisée avec succès. Consultez votre boite mail pour valider votre inscription.');

        return true;
    }

    /**
     * @param $user
     *
     * @return bool
     */
    public function handleValidateRegistration($user)
    {
        if ($this->request->query->get('validate')) {
            $user->setValidate(true);
            $this->flush($user);
            $this->flash->add('success', 'Votre inscription est validée. Cliquez sur l\'onglet connexion du menu pour vous connecter.');

            return true;
        }

        return false;
    }

    /**
     * @param $user
     *
     * @return bool
     */
    public function handleTokenNotValideInSecurity($user)
    {
        if (!$user || $user->getToken() !== $this->request->query->get('token')) {
            $this->flash->add('danger', 'Votre lien n\'est pas valide. Merci d\'en générer un nouveau.');

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
