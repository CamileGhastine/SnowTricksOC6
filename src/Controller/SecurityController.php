<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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

        if($form->isSubmitted() && $form->isValid())
        {
            $user->setPassword($passwordEncoder ->encodePassword($user, $user->getPassword()))
                ->setAvatar('images/users/nobody.jpg')
                ->setCreatedAt(new \DateTime());

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
     * @Route("/connection", name="security_login")
     */
    public function login()
    {
        return $this->render('security/login.html.twig');
    }


}
