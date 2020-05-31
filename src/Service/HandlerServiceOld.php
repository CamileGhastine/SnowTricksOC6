<?php

namespace App\Service;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class HandlerServiceOld
{
    private $em;
    private $request;
    private $flash;
    private $uploader;
    private $avatar;
    private $passwordEncoder;
    private $emailer;
    private $generateToken;

    public function __construct(
        EntityManagerInterface $em,
        RequestStack $request,
        UploaderService $uploader,
        AvatarService $avatar,
        UserPasswordEncoderInterface $passwordEncoder,
        EmailerService $emailer,
        TokenGeneratorInterface $generateToken,
        FlashBagInterface $flash
    ) {
        $this->em = $em;
        $this->request = $request->getCurrentRequest();
        $this->flash = $flash;
        $this->uploader = $uploader;
        $this->avatar = $avatar;
        $this->passwordEncoder = $passwordEncoder;
        $this->emailer = $emailer;
        $this->generateToken = $generateToken;
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

    public function handleTokenNotValid($user)
    {
        if (!$user || $user->getToken() !== $this->request->query->get('token')) {
            $this->flash->add('danger', 'Votre lien n\'est pas valide. Merci d\'en générer un nouveau.');

            return true;
        }

        return false;
    }

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

    public function handleForgottenPasswordUnsubmitted(Request $request, Form $form)
    {
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return true;
        }
        return false;
    }

    Public function handleForgottenPasswordUserNotExists(User $user = null)
    {
        if (!$user) {
            $this->flash->add('danger', 'Cette adresse n\'existe pas.');

            return true;
        }

        $this->flash->add('success', 'Un lien de reconnexion vient de vous être envoyé à votre adresse courriel.');
        $this->emailer->sendEmailForgotten($user);

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
