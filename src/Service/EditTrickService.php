<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use App\Form\CategoryType;
use App\Form\EditTrickType;
use App\Form\ImageType;
use App\Form\VideoType;
use App\Service\HandlerService\HandlerImageService;
use App\Service\HandlerService\HandlerTrickService;
use App\Service\HandlerService\HandlerVideoService;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class EditTrickService
{
    private $formFactory;
    private $handlerTrick;
    private $handlerImage;
    private $handlerVideo;

    public function __construct(FormFactoryInterface $formFactory,
                                HandlerTrickService $handlerTrick,
                                HandlerImageService $handlerImage,
                                HandlerVideoService $handlerVideo)
    {
        $this->formFactory = $formFactory;
        $this->handlerTrick = $handlerTrick;
        $this->handlerImage = $handlerImage;
        $this->handlerVideo = $handlerVideo;
    }

    /**
     * @param Request $request
     * @param Trick   $trick
     *
     * @return bool|Form
     */
    public function formTrickCreate(Request $request, Trick $trick)
    {
        /** @var Form $formTrick */
        $formTrick = $this->formFactory->create(EditTrickType::class, $trick);

        if ($this->handlerTrick->handleEditTrick($request, $formTrick, $trick)) {
            return false;
        }

        return $formTrick;
    }

    /**
     * @param Request $request
     *
     * @return bool|Form
     */
    public function formCategoryCreate(Request $request)
    {
        $category = new Category();

        /** @var Form $formCategory */
        $formCategory = $this->formFactory->create(CategoryType::class, $category);

        if ($this->handlerTrick->handle($request, $formCategory, $category, 'La catégorie a été ajoutée avec succès !')) {
            return false;
        }

        return $formCategory;
    }

    /**
     * @param Request $request
     * @param Trick   $trick
     *
     * @return bool|Form
     */
    public function formImageCreate(Request $request, Trick $trick)
    {
        $image = new Image();

        /** @var Form $formImage */
        $formImage = $this->formFactory->create(ImageType::class, $image);

        if ($this->handlerImage->handleAddImage($request, $formImage, $image, $trick)) {
            return false;
        }

        return $formImage;
    }

    /**
     * @param Request $request
     * @param Trick   $trick
     *
     * @return bool|Form
     */
    public function formVideoCreate(Request $request, Trick $trick)
    {
        $video = new Video();

        /** @var Form $formVideo */
        $formVideo = $this->formFactory->create(VideoType::class, $video);

        if ($this->handlerVideo->handleAddVideo($request, $formVideo, $video, $trick)) {
            return false;
        }

        return $formVideo;
    }
}
