<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
