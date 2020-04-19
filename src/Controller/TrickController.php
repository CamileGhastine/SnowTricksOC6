<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\AddTrickType;
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
        return $this->render('trick/index.html.twig', [
            'tricks' => $id ? $repoTrick->findByCategoryWithPoster($id) : $repoTrick->findAllWithPoster(),
            'categories' => $repoCategory->findAll()
        ]);
    }

    /**
     * @Route("/trick/{id}", name="trick_show")
     */
    public function show($id, Request $request, TrickRepository $repoTrick, EntityManagerInterface $em)
    {
        $trick = $repoTrick->findTrickWithCommentsAndCategories($id);

        $user = $this->getUser();
        if($user)
        {
            $comment= new Comment($trick, $this->getUser());
            $form = $this->createForm(CommentType::class, $comment);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($comment);
                $em->flush();

                return $this->redirect($this->generateUrl('trick_show', ['id' => $trick->getId()]).'#comments');
            }
        }

        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
            'form' => $user ? $form->createView() : null
        ]);
    }

    /**
     * @Route("/trick/edit/new", name="trick_new")
     */
    public function create(Request $request, EntityManagerInterface $em, Trick $trick = null)
    {
        $trick = new Trick($this->getUser());

        $form = $this->createForm(AddTrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $trick->setUpdatedAt(new DateTime());

            foreach ($form->getData()->getImages() as $key => $image){
                $image->upload();
                $key == 0 ? $image->setPoster(1) : null;
            }

            $em->persist($trick);
            $em->flush();

            $this->addFlash('success', 'La figure a été ajoutée avec succès !');

            return $this->redirectToRoute('trick_show', ['id' => $trick->getId()]);
        }

        return $this->render('trick/addForm.html.twig', [
            'form' => $form->createView(),
            'trick' =>$trick,
        ]);
    }

    /**
     * @Route("/trick/edit/{id}/update", name="trick_edit")
     */
    public function form(Request $request, EntityManagerInterface $em, Trick $trick = null)
    {
        $action = 'modifiée';

        if (!$trick) {
            $trick = new Trick($this->getUser());
            $action = 'ajoutée';
        }

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $trick->setUpdatedAt(new DateTime());

            $em->persist($trick);
            $em->flush();

            $this->addFlash('success', 'La figure a été '.$action.' avec succès !');

            return $this->redirectToRoute('trick_show', ['id' => $trick->getId()]);
        }

        return $this->render('trick/form.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
            'edit' => $action === 'modifiée'
        ]);
    }


    /**
     * @Route("trick/edit/{id}/delete", name="trick_delete")
     */
    public function delete(Trick $trick, EntityManagerInterface $em, Request $request, TrickRepository $repoTrick)
    {
        if ($request->request->get('_token') && $this->isCsrfTokenValid('delete'.$trick->getId(), $request->request->get('_token'))) {
            $em->remove($trick);
            $em->flush();

            $this->addFlash('success', 'la figure a été supprimée avec succès !');

            return $this->redirectToRoute('home');
        }

        return $this->render('trick/delete.html.twig', [
                'trick' => $repoTrick->findWithPoster($trick->getId()),
            ]
        );
    }
}
