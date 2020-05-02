<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgottenPasswordType;
use App\Form\RegistrationType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use App\Service\EmailerService;
use App\Service\UploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('home');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/inscription", name="security_registration")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function registration(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder, UploaderService $uploader)
    {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if (!$form->isSubmitted() or !$form->isValid()) {
            return $this->render('security/registration.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()))
            ->setAvatar('images/users/nobody.jpg')
        ;

        if ($form->getData()->getFile()) {
            $url = $uploader->uploadAvatar($form->getData()->getFile());
            $user->setAvatar($url);
        }

        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Votre inscription a été réalisée avec succès. Connectez vous pour profiter de toutes les fonctionnalités de SnowTricks.');

        return $this->redirectToRoute('security_login');
    }

    /**
     * @Route("/forgotten_password", name="security_forgotten")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function forgotenPasword(Request $request, UserRepository $repo, EmailerService $emailer)
    {
        $form = $this->createForm(ForgottenPasswordType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() or !$form->isValid()) {
            return $this->render('/security/forgottenPassword.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        $user = $repo->findOneBy(['email' => $form->getData()->getEmail()]);

        if (!$user) {
            $this->addFlash('danger', 'Cette adresse n\'existe pas.');

            return $this->redirectToRoute('security_forgotten');
        }

        $this->addFlash('success', 'Un lien de reconnexion vient de vous être envoyé à votre adresse courriel.');
        $emailer->sendEmailForgotten($user);

        return $this->render('/security/forgottenPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/reset_password", name="reset_password")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function resetPassword(UserRepository $repo, Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em)
    {
        $user = $repo->findOneBy(['email' => $request->query->get('email')]);

        if (!$user) {
            $this->addFlash('danger', 'Votre lien n\'est pas valide. Merci d\'en générer un nouveau.');

            return $this->redirectToRoute('home');
        }

        if (!password_verify('forgotten_password'.$user->getId().$user->getEmail(), $request->query->get('token'))) {
            $this->addFlash('danger', 'Votre lien n\'est pas valide. Merci d\'en générer un nouveau.');

            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() or !$form->isValid()) {
            return $this->render('/security/reset_pasword.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        $user->setPassword($passwordEncoder->encodePassword($user, $form->getData()->getPassword()));
        $em->flush();

        $this->addFlash('success', 'Connectez-vous avec votre nouveau mot de passe.');

        return $this->redirectToRoute('security_login');
    }
}
