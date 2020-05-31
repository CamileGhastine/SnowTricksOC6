<?php


namespace App\Service\HandlerService;


use App\Entity\User;
use App\Service\EmailerService;
use App\Service\UploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class HandlerUserService extends HandlerService
{
    private $passwordEncoder;
    private $mailer;
    private $generateToken;
    private $uploader;

    public function __construct(EntityManagerInterface $em, FlashBagInterface $flash,
                                UploaderService $uploader,
                                UserPasswordEncoderInterface $passwordEncoder,
                                EmailerService $mailer,
                                TokenGeneratorInterface $generateToken)
    {
        parent::__construct($em, $flash);
        $this->passwordEncoder = $passwordEncoder;
        $this->mailer = $mailer;
        $this->generateToken = $generateToken;
        $this->uploader = $uploader;
    }

    public function handleRegistrationAlraedyConnected(TokenStorageInterface $tokenStorage, $user)
    {
        if ($user) {
            $this->flash->add('danger', 'Vous avez été déconnecté pour enregistrer un nouvel utilisateur.');
            $tokenStorage->setToken();
        }
    }

    public function handleRegistration(Request $request, TokenGeneratorInterface $generateToken, Form $form, $user)
    {
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return false;
        }

        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()))
            ->setAvatar('images/users/nobody.jpg')
            ->setToken($generateToken->generateToken());

        if ($form->getData()->getFile()) {
            $url = $this->uploader->uploadAvatar($form->getData()->getFile());
            $user->setAvatar($url);
        }

        $this->create($user);

        $this->mailer->sendEmailRegistration($user);
        $this->flash->add('success', 'Votre inscription a été réalisée avec succès. Consultez votre boite mail pour valider votre inscription.');

        return true;
    }


    public function handleTokenNotValid(Request $request, $user)
    {
        if (!$user || $user->getToken() !== $request->query->get('token')) {
            $this->flash->add('danger', 'Votre lien n\'est pas valide. Merci d\'en générer un nouveau.');

            return true;
        }

        return false;
    }

    public function handleValidateRegistration(Request $request, User $user)
    {
        if ($request->query->get('validate')) {
            $user->setValidate(true);
            $this->em->flush();
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
        $this->mailer->sendEmailForgotten($user);

        return false;
    }

    public function handleResetPassword(Request $request, Form $form, User $user)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $form->getData()->getPassword()));

            $this->em->flush();

            return true;
        }

        return false;
    }
}