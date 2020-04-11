<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentType;
use App\Form\TrickType;
use App\Repository\CategoryRepository;
use App\Repository\TrickRepository;
use DateTime;
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
    public function index(TrickRepository $repoTrick, CategoryRepository $repoCategory, $id = null)
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

        return $this->render('trick/index.html.twig', [
            'tricks' => $tricks,
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/trick/{id}", name="trick_show")
     */
    public function show($id, Request $request, TrickRepository $repoTrick, EntityManagerInterface $em)
    {
        $trick = $repoTrick->findTrickWithCommentsAndCategories($id);

        $comment= new Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $comment->setCreatedAt(new DateTime())
                ->setTrick($repoTrick->find($trick->getId()))
                ->setUser($this->getUser());

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
    public function form(Request $request, EntityManagerInterface $em, CategoryRepository $repoCategory, Trick $trick = null)
    {
        $edit = true;

        if(!$trick)
        {
            $trick = new Trick();
            $edit = false;
        }

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            if(!$trick->getId())
            {
                $trick->setImage('images/tricks/image.jpg')
                    ->setCreatedAt(new DateTime())
                    ->setUser($this->getUser());
            }

            foreach($request->request->get('trick')['categories'] as $categoryId)
            {
                $trick->removeCategory($repoCategory->find($categoryId));
                $trick->addCategory($repoCategory->find($categoryId));
            }
            $em->persist($trick);
            $em->flush();

            $action = $edit ? "ajoutée" : "modifiée";
            $this->addFlash('success', 'La figure a été '.$action.' avec succès !');

            return $this->redirectToRoute('trick_show', ['id' => $trick->getId()]);
        }

        return $this->render('trick/form.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
            'edit' => $edit,
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
