<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgottenPasswordType;
use App\Form\RegistrationType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use App\Security\FormAuthenticator;
use App\Service\EmailerService;
use App\Service\HandlerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private $repo;
    private $authenticator;
    private $guardHandler;

    public function __construct(UserRepository $repo,
                                FormAuthenticator $authenticator,
                                GuardAuthenticatorHandler $guardHandler)
    {
        $this->repo = $repo;
        $this->authenticator = $authenticator;
        $this->guardHandler = $guardHandler;
    }

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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function registration(HandlerService $handler)
    {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);

        if ($handler->handleRegistration($form, $user)) {
            $this->addFlash('success', 'Votre inscription a été réalisée avec succès. Consultez votre boite mail pour valider votre inscription.');

            return $this->redirectToRoute('security_registration');
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/validate_registration", name="validate_registration")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function validateRegistration(Request $request)
    {
        $user = $this->repo->findOneBy(['email' => $request->query->get('email')]);

        if ($request->query->get('validate')) {
            $user->setValidate(true);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Votre inscription est validée. Cliquez sur l\'onglet connexion du menu pour vous connecter.');

            return $this->redirectToRoute('home');
        }

        if (!$user || $user->getToken() !== $request->query->get('token')) {
            $this->addFlash('danger', 'Votre lien n\'est pas valide. Merci d\'en générer un nouveau.');

            return $this->render('Security/validateRegistration.html.twig');
        }

        return $this->render('Security/validateRegistration.html.twig', ['user' => $user]);
    }

    /**
     * @Route("/forgotten_password", name="security_forgotten")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function forgotenPasword(Request $request, EmailerService $emailer)
    {
        $form = $this->createForm(ForgottenPasswordType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('/security/forgottenPassword.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        $user = $this->repo->findOneBy(['email' => $form->getData()->getEmail()]);

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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response|null
     */
    public function resetPassword(Request $request, HandlerService $handler)
    {
        $user = $this->repo->findOneBy(['email' => $request->query->get('email')]);

        $form = $this->createForm(ResetPasswordType::class);

        if (!$user || $user->getToken() !== $request->query->get('token')) {
            $this->addFlash('danger', 'Votre lien n\'est pas valide. Merci d\'en générer un nouveau.');

            return $this->render('/security/reset_pasword.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        if ($handler->handleResetPassword($form, $user)) {
            return $this->resetPasswordRedirectToRoute($user, $request);
        }

        return $this->render('/security/reset_pasword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param $user
     * @param $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response|null
     */
    private function resetPasswordRedirectToRoute($user, $request)
    {
        if ($request->query->get('account')) {
            $this->addFlash('success', 'Votre mot de passe a été modifié avec susccès.');

            return $this->redirectToRoute('user_account');
        }

        $this->addFlash('success', 'Vous êtes connecté avec votre nouveau mot de passe.');

        return $this->guardHandler->authenticateUserAndHandleSuccess(
            $user,          // the User object you just created
            $request,
            $this->authenticator, // authenticator whose onAuthenticationSuccess you want to use
            'main'          // the name of your firewall in security.yaml
        );
    }
}
