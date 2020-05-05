<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use App\Form\AddTrickType;
use App\Form\CategoryType;
use App\Form\CommentType;
use App\Form\EditTrickType;
use App\Form\ImageType;
use App\Form\VideoType;
use App\Kernel;
use App\Repository\CategoryRepository;
use App\Repository\TrickRepository;
use App\Service\PaginatorService;
use App\Service\UploaderService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    private $maxResult = 10;

    /**
     * @Route("/", name="home")
     * @Route("/trick/{id<[0-9]+>}/category", name="trick_category")
     *
     * @param null $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(TrickRepository $repoTrick, CategoryRepository $repoCategory, $id = null)
    {
        return $this->render('trick/index.html.twig', [
            'tricks' => $id ? $repoTrick->findByCategoryWithPoster($id, $this->maxResult) : $repoTrick->findAllWithPoster($this->maxResult),
            'categories' => $repoCategory->findAll(),
            'categoryId' => $id,
        ]);
    }

    /**
     * Load More trick button in homme page.
     *
     * @Route("/trick/ajax/loadMore", name="ajax_load_more")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxLoadMore(TrickRepository $repoTrick, Request $request)
    {
        $id = $request->request->get('id');
        $firstResult = $request->request->get('page') * $this->maxResult;

        return $this->render('trick/ajax/ajax_load_more.html.twig', [
            'tricks' => $id ? $repoTrick->findByCategoryWithPoster($id, $this->maxResult, $firstResult) : $repoTrick->findAllWithPoster($this->maxResult, $firstResult),
        ]);
    }

    /**
     * Show one trick.
     *
     * @Route("/trick/{id<[0-9]+>}", name="trick_show")
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function show($id, Request $request, TrickRepository $repoTrick, PaginatorService $paginator, EntityManagerInterface $em)
    {
        $trick = $repoTrick->findTrickWithCategoriesImagesVideosComments($id);
        $paginatorResponse = $paginator->paginate($id, 1);

        $user = $this->getUser();
        if ($user) {
            $comment = new Comment($trick, $this->getUser());
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
            'comments' => $paginatorResponse['comments'],
            'render' => $paginatorResponse['render'],
            'form' => $user ? $form->createView() : null,
        ]);
    }

    /**
     * Paginate comments.
     *
     * @Route("/trick/ajax/commentsPagination/{id<[0-9]+>}/{page<[0-9]+>}", name="ajax_comments_pagination")
     *
     * @param $id
     * @param $page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxCommentsPagination($id, $page, PaginatorService $paginator)
    {
        $paginatorResponse = $paginator->paginate($id, $page);

        return $this->render('trick/ajax/ajax_comments_pagination.html.twig', [
            'comments' => $paginatorResponse['comments'],
            'render' => $paginatorResponse['render'],
        ]);
    }

    /**
     * Create new trick.
     *
     * @Route("/trick/edit/new", name="trick_new")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request, EntityManagerInterface $em, UploaderService $uploader)
    {
        $category = new Category();
        $formCategory = $this->createForm(CategoryType::class, $category);

        $trick = new Trick($this->getUser());

        $form = $this->createForm(AddTrickType::class, $trick);
        $form->handleRequest($request);

        if (!$form->isSubmitted() or !$form->isValid()) {
            return $this->render('trick/addForm.html.twig', [
                'form' => $form->createView(),
                'formCategory' => $formCategory->createView(),
            ]);
        }

        $trick->setUpdatedAt(new DateTime());

        /** @var Video $video */
        foreach ($form->getData()->getVideos() as $video) {
            $video->refactorIframe();
        }

        /** @var Image $image */
        foreach ($form->getData()->getImages() as $key => $image) {
            $uploader->upload($image);
            0 === $key ? $image->setPoster(true) : null;
        }

        $em->persist($trick);
        $em->flush();

        $this->addFlash('success', 'La figure a été ajoutée avec succès !');

        return $this->redirectToRoute('trick_show', ['id' => $trick->getId()]);
    }

    /**
     * Create new category in add trick form.
     *
     * @Route("/trick/ajax/addCategory", name="ajax_add_category")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAddCategory(Request $request, EntityManagerInterface $em)
    {
        $category = new Category();

        $formCategory = $this->createForm(CategoryType::class, $category);
        $formCategory->handleRequest($request);

        if (!$formCategory->isSubmitted() or !$formCategory->isValid()) {
            return $this->render('trick/ajax/ajax_add_category.html.twig', [
                'errors' => $this->getErrorMessages($formCategory),
            ]);
        }

        $em->persist($category);
        $em->flush();

        return $this->render('trick/ajax/ajax_add_category.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * get Error Messages From Form.
     *
     * @return array
     */
    private function getErrorMessages($form)
    {
        $errors = [];
        if ($form->count() > 0) {
            foreach ($form->all() as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = str_replace('ERROR: ', '', (string) $form[$child->getName()]->getErrors());
                }
            }
        }

        return $errors;
    }

    /**
     * Edit a trick.
     *
     * @Route("/trick/edit/{id<[0-9]+>}/update", name="trick_edit")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, EntityManagerInterface $em, Trick $trick, UploaderService $uploader)
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

        $category = new Category();
        $formCategory = $this->createForm(CategoryType::class, $category);
        $formCategory->handleRequest($request);

        if ($formCategory->isSubmitted() && $formCategory->isValid()) {
            $em->persist($category);
            $em->flush();

            $this->addFlash('success', 'La catégorie a été ajoutée avec succès !');

            return $this->redirect($this->generateUrl('trick_edit', ['id' => $trick->getId()]).'#alert');
        }

        $image = new Image();
        $formImage = $this->createForm(ImageType::class, $image);
        $formImage->handleRequest($request);

        if ($formImage->isSubmitted() && $formImage->isValid()) {
            $uploader->upload($image);
            $image->setTrick($trick);

            if (0 === count($trick->getImages())) {
                $image->setPoster(1);
            }

            $em->persist($image);
            $em->flush();

            $this->addFlash('success', 'La photo a été ajoutée avec succès !');

            return $this->redirect($this->generateUrl('trick_edit', ['id' => $trick->getId()]).'#alert');
        }

        $video = new Video();
        $formVideo = $this->createForm(VideoType::class, $video);
        $formVideo->handleRequest($request);

        if ($formVideo->isSubmitted() && $formVideo->isValid()) {
            $video->setTrick($trick);
            $video->refactorIframe();

            $em->persist($video);
            $em->flush();

            $this->addFlash('success', 'La vidéo a été ajoutée avec succès !');

            return $this->redirect($this->generateUrl('trick_edit', ['id' => $trick->getId()]).'#alert');
        }

        return $this->render('trick/editForm.html.twig', [
            'formTrick' => $formTrick->createView(),
            'formImage' => $formImage->createView(),
            'formVideo' => $formVideo->createView(),
            'formCategory' => $formCategory->createView(),
            'trick' => $trick,
        ]);
    }

    /**
     * Delete a trick.
     *
     * @Route("trick/edit/{id<[0-9]+>}/delete", name="trick_delete")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Trick $trick, EntityManagerInterface $em, Request $request, TrickRepository $repoTrick)
    {
        if (!$request->request->get('_token') || !$this->isCsrfTokenValid('delete'.$trick->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'La figure n\'a pas pu être supprimée');

            return $this->redirectToRoute('home');
        }

        foreach ($trick->getImages() as $image) {
            unlink(Kernel::getProjectDir().'/public/'.$image->getUrl());
        }

        $em->remove($trick);
        $em->flush();

        $this->addFlash('success', 'la figure a été supprimée avec succès !');

        return $this->redirectToRoute('home');
    }

    /**
     * Delete image in edit trick page.
     *
     * @Route("trick/edit/image/{id<[0-9]+>}/delete", name="image_delete")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteImage(Image $image, EntityManagerInterface $em, Request $request)
    {
        if (!$request->query->get('csrf_token') || !$this->isCsrfTokenValid('delete'.$image->getId(), $request->query->get('csrf_token'))) {
            $this->addFlash('danger', 'L\'image n\'a pas pu être supprimée');

            return $this->redirect($this->generateUrl('trick_edit', ['id' => $image->getTrick()->getId()]).'#alert');
        }

        // New poster before delete old poster
        $trick = $image->getTrick();
        $trick->removeImage($image);
        $images = $trick->getImages();
        if ($image->getPoster() && count($images) > 0) {
            $images[array_key_first($images->toArray())]->setPoster(true);
        }

        $em->remove($image);
        $em->flush();

        unlink(Kernel::getProjectDir().'/public/'.$image->getUrl());

        $this->addFlash('success', 'La photo a été supprimée avec succès !');

        return $this->redirect($this->generateUrl('trick_edit', ['id' => $trick->getId()]).'#alert');
    }

    /**
     * Changer the poster in edit trick page.
     *
     * @Route("trick/edit/image/{newPoster}/poster/{oldPoster}", name="image_poster_change")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changePoster(EntityManagerInterface $em, Image $newPoster, Image $oldPoster)
    {
        $oldPoster->setPoster(false);
        $newPoster->setPoster(true);

        $em->flush();

        return $this->redirectToRoute('trick_edit', ['id' => $newPoster->getTrick()->getId()]);
    }

    /**
     * Delete video in edit trick page.
     *
     * @Route("trick/edit/video/{id<[0-9]+>}/delete", name="video_delete")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteVideo(Video $video, EntityManagerInterface $em, Request $request)
    {
        if (!$request->query->get('csrf_token') or !$this->isCsrfTokenValid('delete'.$video->getId(), $request->query->get('csrf_token'))) {
            $this->addFlash('danger', 'La vidéo n\'a pas pu être supprimée.');

            return $this->redirect($this->generateUrl('trick_edit', ['id' => $video->getTrick()->getId()]).'#alert');
        }

        $em->remove($video);
        $em->flush();

        $this->addFlash('success', 'La vidéo a été supprimée avec succès !');

        return $this->redirect($this->generateUrl('trick_edit', ['id' => $video->getTrick()->getId()]).'#alert');
    }
}
