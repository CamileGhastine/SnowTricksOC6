<?php

namespace App\Controller;

use App\Entity\AvatarProto;
use App\Form\AvatarType;
use App\Service\AvatarService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user_account")
     * @isGranted("ROLE_USER", message="Vous devez être connecté pour accéder à votre compte ! ")
     *
     */
    public function account(Request $request, AvatarService $avatar)
    {
        $file = new AvatarProto();

        $form = $this->createForm(AvatarType::class, $file);
        $form->handleRequest($request);

        $formDeleteAvatar = $this->createFormBuilder($file)
                                ->getForm();
        $formDeleteAvatar->handleRequest($request);

        if ($formDeleteAvatar->isSubmitted()) {
            $avatar->manageAvatar($this->getUser(), null);

            return $this->redirectToRoute('user_account');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $action = 'images/users/nobody.jpg' === $this->getUser()->getAvatar() ? 'create' : 'edit';
            $file = $form->getData()->getFile();

            $avatar->manageAvatar($this->getUser(), $file);

            return $this->redirectToRoute('user_account');
        }

        return $this->render('user/account.html.twig', [
            'form' => $form->createView(),
            'formDeleteAvatar' => $formDeleteAvatar->createView(),
        ]);
    }
}
