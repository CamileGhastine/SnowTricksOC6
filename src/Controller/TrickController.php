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
use App\Repository\CategoryRepository;
use App\Repository\TrickRepository;
use App\Service\EditTrickService;
use App\Service\GetErrorsMessageService;
use App\Service\HandlerService\HandlerImageService;
use App\Service\HandlerService\HandlerService;
use App\Service\HandlerService\HandlerTrickService;
use App\Service\HandlerService\HandlerVideoService;
use App\Service\PaginatorService;
use Doctrine\ORM\NonUniqueResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @return Response
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
     * Load More trick button in home page.
     *
     * @Route("/trick/ajax-loadMore", name="ajax_load_more")
     *
     * @return Response
     */
    public function ajaxLoadMore(TrickRepository $repoTrick, Request $request)
    {
        $id = $request->request->get('id'); // Load more trick by category

        $firstResult = $request->request->get('page') * $this->maxResult;

        return $this->render('trick/ajax/ajax_load_more.html.twig', [
            'tricks' => $id ? $repoTrick->findByCategoryWithPoster($id, $this->maxResult, $firstResult) : $repoTrick->findAllWithPoster($this->maxResult, $firstResult),
        ]);
    }

    /**
     * Show one trick.
     *
     * @Route("/trick/{id<[0-9]+>}", name="trick_show")
     * @Entity("trick", expr="repository.findTrickWithCategoriesImagesVideosComments(id)")
     *
     * @param $id
     *
     * @return RedirectResponse|Response
     *
     * @throws NonUniqueResultException
     */
    public function show($id, Trick $trick, PaginatorService $paginator, Request $request, HandlerService $handler)
    {
        $paginatorResponse = $paginator->paginate($id, 1);

        if ($this->getUser()) {
            $comment = new Comment($trick, $this->getUser());

            /** @var Form $form */
            $form = $this->createForm(CommentType::class, $comment);

            if ($handler->handle($request, $form, $comment)) {
                return $this->redirect($this->generateUrl('trick_show', [
                        'id' => $id ]).'#comments');
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
     * @return Response
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
     * @return RedirectResponse|Response
     */
    public function create(Request $request, HandlerTrickService $handler)
    {
        $category = new Category();
        $formCategory = $this->createForm(CategoryType::class, $category);

        $trick = new Trick($this->getUser());

        /** @var Form $form */
        $form = $this->createForm(AddTrickType::class, $trick);

        if ($handler->handleAddTrick($request, $form, $trick)) {

            return $this->redirectToRoute('trick_show', [
                'id' => $trick->getId(), ]);
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
     * @isGranted("ROLE_USER", message="Vous devez être connecté pour créer un trick ! ")
     *
     * @return Response
     */
    public function ajaxAddCategory(Request $request, HandlerService $handler, GetErrorsMessageService $errorsMessage)
    {
        $category = new Category();

        /** @var Form $formCategory */
        $formCategory = $this->createForm(CategoryType::class, $category);

        if ($handler->handle($request, $formCategory, $category)) {
            return $this->render('trick/ajax/ajax_add_category.html.twig', [
                'category' => $category,
            ]);
        }

        return $this->render('trick/ajax/ajax_add_category.html.twig', [
            'errors' => $errorsMessage->getMessage($formCategory),
        ]);
    }

    /**
     * Edit a trick.
     *
     * @Route("/trick/{id<[0-9]+>}/update", name="trick_edit")@param Trick $trick
     * @isGranted("ROLE_USER", message="Vous devez être connecté pour modifier un trick ! ")
     *
     * @return RedirectResponse|Response
     */
    public function edit(Trick $trick, Request $request, EditTrickService $editTrick)
    {
        // Edit Trick
        $formTrick = $editTrick->formTrickCreate($request, $trick);
        if(!$formTrick) {
            return $this->redirect($this->generateUrl('trick_show', ['id' => $trick->getId()]));
        }

        // add Category, image or video
        foreach( ['Category', 'Image', 'Video'] as $entity) {
            $form = 'form'.$entity;
            $formCreate = 'form'.$entity.'Create';

            $$form = $editTrick->$formCreate($request, $trick);
            if(!$$form) {
                return $this->redirect($this->generateUrl('trick_edit', ['id' => $trick->getId()]).'#alert');
            }
        }

        return $this->render('trick/editForm.html.twig', [
            'trick' => $trick,
            'formTrick' =>$formTrick->createView(),
            'formCategory' => $formCategory->createView(),
            'formImage' => $formImage->createView(),
            'formVideo' => $formVideo->createView(),
        ]);
    }

    /**
     * Delete a trick.
     *
     * @Route("trick/{id<[0-9]+>}/delete", name="trick_delete")
     *
     * @isGranted("ROLE_USER", message="Vous devez être connecté pour supprimer un trick ! ")@param Trick $trick
     *
     * @return RedirectResponse
     */
    public function delete(Trick $trick, Request $request, HandlerTrickService $handler)
    {
        if ($handler->handleDeleteTrick($request, $trick)) {
            return $this->redirectToRoute('home');
        }

        $this->addFlash('danger', 'La figure n\'a pas pu être supprimée');

        return $this->redirectToRoute('home');
    }

    /**
     * Delete image in edit trick page.
     *
     * @Route("trick/image/{id<[0-9]+>}/delete", name="image_delete")@param Image $image
     * @isGranted("ROLE_USER", message="Vous devez être connecté pour supprimer une image ! ")
     *
     * @return RedirectResponse
     */
    public function deleteImage(Request $request, Image $image, HandlerImageService $handler)
    {
        $trick = $image->getTrick();

        if ($handler->handlerDeleteImage($request, $image)) {
            return $this->redirect($this->generateUrl('trick_edit', ['id' => $trick->getId()]).'#alert');
        }

        $this->addFlash('danger', 'L\'image n\'a pas pu être supprimée');

        return $this->redirect($this->generateUrl('trick_edit', ['id' => $trick->getId()]).'#alert');
    }

    /**
     * Changer the poster in edit trick page.
     *
     * @Route("trick/image/{oldPoster}/poster/{newPoster}", name="image_poster_change")
     * @isGranted("ROLE_USER", message="Vous devez être connecté pour modifier une image ! ")
     *
     * @return RedirectResponse
     */
    public function changePoster(HandlerImageService $handler, Image $newPoster, Image $oldPoster)
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
     * @return RedirectResponse
     */
    public function deleteVideo(Request $request, Video $video, HandlerVideoService $handler)
    {
        if ($handler->handleDeleteVideo($request, $video)) {
            return $this->redirect($this->generateUrl('trick_edit', ['id' => $video->getTrick()->getId()]).'#alert');
        }
        $this->addFlash('danger', 'La vidéo n\'a pas pu être supprimée.');

        return $this->redirect($this->generateUrl('trick_edit', ['id' => $video->getTrick()->getId()]).'#alert');
    }
}
