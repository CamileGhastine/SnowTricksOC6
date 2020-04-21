<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\Trick;
use App\Form\AddTrickType;
use App\Form\CommentType;
use App\Form\EditTrickType;
use App\Form\ImageType;
use App\Kernel;
use App\Repository\CategoryRepository;
use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    public function create(Request $request, EntityManagerInterface $em, Trick $trick = null, SluggerInterface $slugger)
    {
        $trick = new Trick($this->getUser());

        $form = $this->createForm(AddTrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $trick->setUpdatedAt(new DateTime());

            foreach ($form->getData()->getImages() as $key => $image){
                $image->upload($slugger);
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
    public function edit(Request $request, EntityManagerInterface $em, Trick $trick, SluggerInterface $slugger)
    {
        $formTrick = $this->createForm(EditTrickType::class, $trick);
        $formTrick->handleRequest($request);

        if ($formTrick->isSubmitted() && $formTrick->isValid()) {

            $trick->setUpdatedAt(new DateTime());

            $em->persist($trick);
            $em->flush();

            $this->addFlash('success', 'La figure a été modifiée avec succès !');

            return $this->redirectToRoute('trick_show', ['id' => $trick->getId()]);
        }

        $formImage = $this->createForm(ImageType::class);
        $formImage->handleRequest($request);

        if ($formImage->isSubmitted() && $formImage->isValid()) {

            $image = $formImage->getData();

            $image->upload($slugger);

            $image->setTrick($trick);
            if (count($trick->getImages()) === 0) {
                $image->setPoster(1);
            }

            $em->persist($image);
            $em->flush();

            $this->addFlash('success', 'La photo a été ajoutée avec succès !');

            return $this->redirect($this->generateUrl('trick_edit', ['id' => $trick->getId()]).'#alert');
        }

        return $this->render('trick/editForm.html.twig', [
            'formTrick' => $formTrick->createView(),
            'formImage' => $formImage->createView(),
            'trick' => $trick,
            'edit' => true
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

    /**
     * @Route("trick/edit/image/{id}/delete", name="image_delete")
     */
    public function deleteImage(Image $image, EntityManagerInterface $em, Request $request, TrickRepository $repo)
    {
        if ($request->query->get('csrf_token') && $this->isCsrfTokenValid('delete'.$image->getId(), $request->query->get('csrf_token'))) {

            //delete poster => new poster before delete
            if($image->getPoster()==1) {
                $trick = $repo->findTrickWithCommentsAndCategories($image->getTrick()->getId());
                $images = $trick->getImages();
                if(count($images) >1) {
                    if($images[0] != $image) {
                        $images[0]->setPoster(1);
                        $em->persist($images[0]);
                    }
                    else {
                        $images[1]->setPoster(1);
                        $em->persist($images[1]);
                    }
                }
            }

            $em->remove($image);
            $em->flush();

            unlink(Kernel::getProjectDir().'/public/'.$image->getUrl());

            $this->addFlash('success', 'La photo a été supprimée avec succès !');

            return $this->redirect($this->generateUrl('trick_edit', ['id' => $image->getTrick()->getId()]) . '#alert');
        }
    }

    /**
     * @Route("trick/edit/image/{id}/poster/{posterId}", name="image_poster_change")
     */
    public function changePoster(ImageRepository $repo, EntityManagerInterface $em, $id, $posterId)
    {
        $oldPoster = $repo->find($posterId);
        $oldPoster->setPoster(0);
        $em->persist($oldPoster);

        $newPoster = $repo->find($id);
        $newPoster->setPoster(1);
        $em->persist($newPoster);

        $em->flush();

        return $this->redirectToRoute('trick_edit', ['id' => $newPoster->getTrick()->getId()]);
    }
}
