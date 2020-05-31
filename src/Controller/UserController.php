<?php

namespace App\Controller;

use App\Entity\AvatarProto;
use App\Form\AvatarType;
use App\Service\HandlerService\HandlerImageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user_account")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function account(Request $request, HandlerImageService $handler)
    {
        $file = new AvatarProto();

        $formChangeAvatar = $this->createForm(AvatarType::class, $file);

        $formDeleteAvatar = $this->createFormBuilder()
            ->getForm();

        foreach ([$formChangeAvatar, $formDeleteAvatar] as $form) {
            if ($handler->handleAvatar($request, $form, $this->getUser())) {
                return $this->redirectToRoute('user_account');
            }
        }

        return $this->render('user/account.html.twig', [
            'form' => $formChangeAvatar->createView(),
            'formDeleteAvatar' => $formDeleteAvatar->createView(),
        ]);
    }
}
