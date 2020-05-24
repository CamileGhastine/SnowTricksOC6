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
use App\Repository\CategoryRepository;
use App\Repository\TrickRepository;
use App\Service\HandlerService;
use App\Service\PaginatorService;
use App\Service\UploaderService;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    private $maxResult = 10;

    /**
     * @Route("/", name="home")
     * @Route("/trick/category/{id<[0-9]+>}", name="trick_category")
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
     * @Route("/trick/ajax-loadMore", name="ajax_load_more")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxLoadMore(TrickRepository $repoTrick, Request $request)
    {
        // Load more trick by category
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
    public function show($id, TrickRepository $repoTrick, PaginatorService $paginator, HandlerService $handler)
    {
        $trick = $repoTrick->findTrickWithCategoriesImagesVideosComments($id);
        $paginatorResponse = $paginator->paginate($id, 1);

        if ($this->getUser()) {
            $comment = new Comment($trick, $this->getUser());
            $form = $this->createForm(CommentType::class, $comment);

            if ($handler->handle($form, $comment)) {
                return $this->redirect($this->generateUrl('trick_show', [
                        'id' => $trick->getId(), ]).'#comments'
                );
            }
        }

        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
            'comments' => $paginatorResponse['comments'],
            'render' => $paginatorResponse['render'],
            'form' => $this->getUser() ? $form->createView() : null,
        ]);
    }

    /**
     * Paginate comments.
     *
     * @Route("/trick/{id<[0-9]+>}/ajax-commentsPagination/{page<[0-9]+>}", name="ajax_comments_pagination")
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
     * @Route("/trick/new", name="trick_new")
     * @isGranted("ROLE_USER", message="Vous devez être connecté pour créer un trick ! ")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function create(HandlerService $handler)
    {
        $category = new Category();
        $formCategory = $this->createForm(CategoryType::class, $category);

        $trick = new Trick($this->getUser());

        $form = $this->createForm(AddTrickType::class, $trick);

        if ($handler->handleTrick($form, $trick)) {
            $this->addFlash('success', 'La figure a été ajoutée avec succès !');

            return $this->redirectToRoute('trick_show', [
                    'id' => $trick->getId(), ]
            );
        }

        return $this->render('trick/addForm.html.twig', [
            'form' => $form->createView(),
            'formCategory' => $formCategory->createView(),
        ]);
    }

    /**
     * Create new category in add trick form.
     *
     * @Route("/trick/ajax-addCategory", name="ajax_add_category")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAddCategory(HandlerService $handler)
    {
        $category = new Category();

        $formCategory = $this->createForm(CategoryType::class, $category);

        if ($handler->handle($formCategory, $category)) {
            return $this->render('trick/ajax/ajax_add_category.html.twig', [
                'category' => $category,
            ]);
        }

        return $this->render('trick/ajax/ajax_add_category.html.twig', [
            'errors' => $this->getErrorMessages($formCategory),
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

        if (0 == $form->count()) {
            return $errors;
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = str_replace('ERROR: ', '', (string) $form[$child->getName()]->getErrors());
            }
        }

        return $errors;
    }

    /**
     * Edit a trick.
     *
     * @Route("/trick/{id<[0-9]+>}/update", name="trick_edit")
     * @isGranted("ROLE_USER", message="Vous devez être connecté pour modifier un trick ! ")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(Trick $trick, UploaderService $uploader, HandlerService $handler)
    {
        $formTrick = $this->createForm(EditTrickType::class, $trick);

        $trick->setUpdatedAt(new DateTime());

        if ($handler->handle($formTrick, $trick)) {
            $this->addFlash('success', 'La figure a été modifiée avec succès !');

            return $this->redirectToRoute('trick_show', ['id' => $trick->getId()]);
        }

        // add a category, an image or a video
        $entities = ['Category' => 'catégorie', 'Image' => 'photo', 'Video' => 'video'];
        foreach ($entities as $entity => $flash) {
            $form = 'form'.$entity;
            $$form = $this->addEntity($entity, $trick, $handler);
            if (!$$form) {
                $this->addFlash('success', 'La '.$flash.' a été ajoutée avec succès !');

                return $this->redirect($this->generateUrl('trick_edit', ['id' => $trick->getId()]).'#alert');
            }
        }

        return $this->render('trick/editForm.html.twig', [
            'formTrick' => $formTrick->createView(),
            'formImage' => $formImage->createView(),
            'formVideo' => $formVideo->createView(),
            'formCategory' => $formCategory->createView(),
            'trick' => $trick,
        ]);
    }

    public function addEntity($entity, $trick, $handler)
    {
        $entityClass = 'App\Entity\\'.$entity;
        $typeClass = 'App\Form\\'.$entity.'Type';
        $object = new $entityClass();
        $handle = 'handle'.$entity;

        $form = $this->createForm($typeClass, $object);

        if ($handler->$handle($form, $object, $trick)) {
            return false;
        }

        return $form;
    }

    /**
     * Delete a trick.
     *
     * @Route("trick/{id<[0-9]+>}/delete", name="trick_delete")
     * @isGranted("ROLE_USER", message="Vous devez être connecté pour supprimer un trick ! ")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Trick $trick, HandlerService $handler, Request $request)
    {
        if (!$request->request->get('_token') || !$this->isCsrfTokenValid('delete'.$trick->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'La figure n\'a pas pu être supprimée');

            return $this->redirectToRoute('home');
        }

        $handler->handleDeleteTrick($trick);

        $this->addFlash('success', 'la figure a été supprimée avec succès !');

        return $this->redirectToRoute('home');
    }

    /**
     * Delete image in edit trick page.
     *
     * @Route("trick/image/{id<[0-9]+>}/delete", name="image_delete")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteImage(Image $image, HandlerService $handler, Request $request)
    {
        if (!$request->query->get('csrf_token') || !$this->isCsrfTokenValid('delete'.$image->getId(), $request->query->get('csrf_token'))) {
            $this->addFlash('danger', 'L\'image n\'a pas pu être supprimée');

            return $this->redirect($this->generateUrl('trick_edit', ['id' => $image->getTrick()->getId()]).'#alert');
        }

        $trick = $image->getTrick();

        $handler->handlerDeleteImage($image);

        $this->addFlash('success', 'La photo a été supprimée avec succès !');

        return $this->redirect($this->generateUrl('trick_edit', ['id' => $trick->getId()]).'#alert');
    }

    /**
     * Changer the poster in edit trick page.
     *
     * @Route("trick/image/{oldPoster}/poster/{newPoster}", name="image_poster_change")
     * @isGranted("ROLE_USER", message="Vous devez être connecté pour supprimer une image ! ")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changePoster(HandlerService $handler, Image $newPoster, Image $oldPoster)
    {
        $handler->handleChangePoster($newPoster, $oldPoster);

        return $this->redirectToRoute('trick_edit', ['id' => $newPoster->getTrick()->getId()]);
    }

    /**
     * Delete video in edit trick page.
     *
     * @Route("trick/video/{id<[0-9]+>}/delete", name="video_delete")
     * @isGranted("ROLE_USER", message="Vous devez être connecté pour supprimer une vidéo ! ")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteVideo(Video $video, HandlerService $handler, Request $request)
    {
        if (!$request->query->get('csrf_token') || !$this->isCsrfTokenValid('delete'.$video->getId(), $request->query->get('csrf_token'))) {
            $this->addFlash('danger', 'La vidéo n\'a pas pu être supprimée.');

            return $this->redirect($this->generateUrl('trick_edit', ['id' => $video->getTrick()->getId()]).'#alert');
        }

        $handler->flush($video, 'remove');

        $this->addFlash('success', 'La vidéo a été supprimée avec succès !');

        return $this->redirect($this->generateUrl('trick_edit', ['id' => $video->getTrick()->getId()]).'#alert');
    }
}
