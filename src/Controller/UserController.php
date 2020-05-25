<?php

namespace App\Controller;

use App\Entity\AvatarProto;
use App\Form\AvatarType;
use App\Service\AvatarService;
use App\Service\HandlerService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user_account")
     * @isGranted("ROLE_USER", message="Vous devez être connecté pour accéder à votre compte ! ")
     */
    public function account(Request $request, AvatarService $avatar, HandlerService $handler)
    {
        $file = new AvatarProto();

        $formChangeAvatar = $this->createForm(AvatarType::class, $file);

        $formDeleteAvatar = $this->createFormBuilder()
            ->getForm();

        foreach ([$formChangeAvatar, $formDeleteAvatar] as $form) {
            if ($handler->handleAvatar($form, $this->getUser())) {
                return $this->redirectToRoute('user_account');
            }
        }

        return $this->render('user/account.html.twig', [
            'form' => $formChangeAvatar->createView(),
            'formDeleteAvatar' => $formDeleteAvatar->createView(),
        ]);
    }
}
