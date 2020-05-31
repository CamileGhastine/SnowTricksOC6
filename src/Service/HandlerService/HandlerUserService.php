<?php


namespace App\Service\HandlerService;


use App\Entity\User;
use App\Service\EmailerService;
use App\Service\UploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class HandlerUserService extends HandlerService
{
    private $passwordEncoder;
    private $mailer;
    private $token;
    private $generateToken;
    private $uploader;

    public function __construct(EntityManagerInterface $em, FlashBagInterface $flash,
                                UploaderService $uploader,
                                UserPasswordEncoderInterface $passwordEncoder,
                                EmailerService $mailer,
                                CsrfTokenManagerInterface $token,
                                TokenGeneratorInterface $generateToken)
    {
        parent::__construct($em, $flash);
        $this->passwordEncoder = $passwordEncoder;
        $this->mailer = $mailer;
        $this->token = $token;
        $this->generateToken = $generateToken;
        $this->uploader = $uploader;
    }

    public function handleRegistration(Request $request, Form $form, $user)
    {
        $form->handleRequest($request);

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

        $this->create($user);

        $this->mailer->sendEmailRegistration($user);
        $this->flash->add('success', 'Votre inscription a été réalisée avec succès. Consultez votre boite mail pour valider votre inscription.');

        return true;
    }


    public function handleTokenNotValideInSecurity(Request $request, User $user = null)
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