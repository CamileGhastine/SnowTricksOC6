<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentType;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(TrickRepository $repo)
    {
        $tricks = $repo->findAll();
        return $this->render('trick/index.html.twig', [
            'tricks' => $tricks,
        ]);
    }

    /**
     * @Route("/trick/new", name="trick_new")
     * @Route("/trick/{id}/edit", name="trick_edit")
     */
    public function form(Request $request, EntityManagerInterface $em, Trick $trick = null)
    {
        if(!$trick)
        {
            $trick = new Trick();
        }


        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            if(!$trick->getId())
            {
                $trick->setImage('images/tricks/image.jpg');
                $trick->setCreatedAt(new \DateTime());
            }

            $em->persist($trick);
            $em->flush();

            if($request->getRequestUri() == '/trick/new')
            {
                $this->addFlash('success', 'La figure a été ajoutée avec succès !');
            }
            else
            {
                $this->addFlash('success', 'La figure a été modifiée avec succès !');
            }

            return $this->redirectToRoute('trick_show', ['id' => $trick->getId()]);
        }

        return $this->render('trick/form.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
        ]);
    }

    /**
     * @Route("/trick/{id}", name="trick_show")
     */
    public function show(Trick $trick, Request $request, TrickRepository $repoTrick, UserRepository $repoUser, EntityManagerInterface $em)
    {
        $comment= new Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $comment->setCreatedAt(new \DateTime())
                ->setTrick($repoTrick->find($trick->getId()))
                ->setUser($repoUser->find($request->request->get('userId')));

            $em->persist($comment);
            $em->flush();

            return $this->redirect($this->generateUrl('trick_show', ['id' => $trick->getId()]).'#comments');
        }

        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("trick/{id}/delete", name="trick_delete")
     */
    public function delete(Trick $trick, EntityManagerInterface $em, Request $request)
    {
        if($request->request->get('_token') && $this->isCsrfTokenValid('delete'.$trick->getId(), $request->request->get('_token')))
        {
            $em->remove($trick);
            $em->flush();

            $this->addFlash('success', 'la figure a été supprimée avec succès !');

            return $this->redirectToRoute('home');
        }

        return $this->render('trick/delete.html.twig', [
                'trick' => $trick,
            ]
        );
    }
}
