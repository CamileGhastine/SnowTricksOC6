<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgottenPasswordType;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use App\Service\Emailer\Emailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();

        $form= $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordEncoder ->encodePassword($user, $user->getPassword()))
                ->setAvatar('images/users/nobody.jpg')
            ;
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Votre inscription a été réalisée avec succès. Connectez vous pour profiter de toutes les fonctionnalités de SnowTricks.');

            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/login", name="security_login")
     */
    public function login(authenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig',[
            'error' => $error,
            'lastUsername' => $lastUsername,
        ]);
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logout()
    {
    }

    /**
     * @Route("/forgotten_password", name="security_forgotten")
     */
    public function forgotenPasword (Request $request, UserRepository $repo, Emailer $emailer)
    {
        $form = $this->createForm(ForgottenPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $adress = $form->getData()['email'];

            if (!$repo->findOneBy(['email' => $adress ])) {
                $this->addFlash('danger', 'Cette adresse n\'existe pas.');
                return $this->redirectToRoute('security_forgotten');
            }

            $this->addFlash('success', 'Un lien de reconnexion vient de vous être envoyé à votre adresse courriel.');
            $emailer->sendEmailForgotten($adress);
        }

        return $this->render('/security/forgottenPassword.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/reset_password", name="reset_password")
     */
    public function resetPassword ()
    {
        dd('test');
    }
}
