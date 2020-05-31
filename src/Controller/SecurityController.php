<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgottenPasswordType;
use App\Form\RegistrationType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use App\Security\FormAuthenticator;
use App\Service\EmailerService;
use App\Service\HandlerServiceOld;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
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

    public function __construct(
        UserRepository $repo,
        FormAuthenticator $authenticator,
        GuardAuthenticatorHandler $guardHandler
    ) {
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
    public function registration(HandlerServiceOld $handler)
    {
        $user = new User();

        /** @var Form $form */
        $form = $this->createForm(RegistrationType::class, $user);

        if ($handler->handleRegistration($form, $user)) {
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
    public function validateRegistration(Request $request, HandlerServiceOld $handler)
    {
        $user = $this->repo->findOneBy(['email' => $request->query->get('email')]);

        if ($handler->handleTokenNotValid($user)) {
            return $this->render('Security/validateRegistration.html.twig');
        }

        if ($handler->handleValidateRegistration($user)) {
            return $this->redirectToRoute('home');
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
    public function forgotenPasword(Request $request, HandlerServiceOld $handler, EmailerService $emailer)
    {
        /** @var Form $form */
        $form = $this->createForm(ForgottenPasswordType::class);

        if ($handler->handleForgottenPasswordUnsubmitted($request, $form))
        {
            return $this->render('/security/forgottenPassword.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        $user = $this->repo->findOneBy(['email' => $form->getData()->getEmail()]);

        if ($handler ->handleForgottenPasswordUserNotExists($user))
        {
            return $this->redirectToRoute('security_forgotten');
        }

        return $this->render('/security/forgottenPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/reset_password", name="reset_password")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response|null
     */
    public function resetPassword(Request $request, HandlerServiceOld $handler)
    {
        $user = $this->repo->findOneBy(['email' => $request->query->get('email')]);

        /** @var Form $form */
        $form = $this->createForm(ResetPasswordType::class);

        if ($handler->handleTokenNotValid($user)) {
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
