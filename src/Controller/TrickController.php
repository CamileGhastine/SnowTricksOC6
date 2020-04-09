<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentType;
use App\Form\TrickType;
use App\Repository\CategoryRepository;
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
     * @Route("/trick/{id}/category", name="trick_category")
     */
    public function index($id = null, TrickRepository $repoTrick, CategoryRepository $repoCategory, Request $request)
    {
        $categories = $repoCategory->findAll();

        if(!$id)
        {
            $tricks = $repoTrick->findBy([], [ 'updatedAt' => 'DESC']);
        }
        else
        {
            $tricks = $repoTrick->findByCategory($id);
        }
dump($this->getUser());
        return $this->render('trick/index.html.twig', [
            'tricks' => $tricks,
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/trick/{id}", name="trick_show")
     */
    public function show($id, Request $request, TrickRepository $repoTrick, UserRepository $repoUser, EntityManagerInterface $em)
    {
        $trick = $repoTrick->findTrickWithCommentsAndCategories($id);

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
     * @Route("/trick/edit/new", name="trick_new")
     * @Route("/trick/edit/{id}/update", name="trick_edit")
     */
    public function form(Request $request, EntityManagerInterface $em, Trick $trick = null, UserRepository $repoUser, CategoryRepository $repoCategory)
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
                $trick->setImage('images/tricks/image.jpg')
                    ->setCreatedAt(new \DateTime())
                    ->setUser($repoUser->find($request->request->get('userId')));
            }

            foreach($request->request->get('trick')['categories'] as $categoryId)
            {
                $trick->removeCategory($repoCategory->find($categoryId));
                $trick->addCategory($repoCategory->find($categoryId));
            }
            $em->persist($trick);
            $em->flush();

            if($request->getRequestUri() == $this->generateUrl('trick_new'))
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
     * @Route("trick/edit/{id}/delete", name="trick_delete")
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
