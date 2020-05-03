<?php

namespace App\Controller;

use App\Entity\AvatarProto;
use App\Form\AvatarType;
use App\Service\AvatarService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user_account")
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
            $avatar->manageAvatar($this->getUser(), 'delete', null);

            return $this->redirectToRoute('user_account');
        }

        if($form->isSubmitted() && $form->isValid() ) {
            $action = $this->getUser()->getAvatar() === 'images/users/nobody.jpg' ? 'create' : 'edit';
            $file = $form->getData()->getFile();

            $avatar->manageAvatar($this->getUser(), $action , $file);

            return $this->redirectToRoute('user_account');
        }

        return $this->render('user/account.html.twig', [
            'form' => $form->createView(),
            'formDeleteAvatar' => $formDeleteAvatar->createView(),
        ]);
    }
}
