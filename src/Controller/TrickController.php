<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\TrickType;
use App\Repository\TrickRepository;
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
     * @Route("/trick/new", name="trick_form")
     */
    public function form(Request $request, EntityManagerInterface $em)
    {
        $trick = new Trick();

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $trick->setImage('images/tricks/image.jpg');
            $trick->setCreatedAt(new \DateTime());

            $em->persist($trick);
            $em->flush();

            return $this->redirectToRoute('trick_show', ['id' => $trick->getId()]);
        }

        return $this->render('trick/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/trick/{id}", name="trick_show")
     */
    public function show(Trick $trick)
    {
        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
        ]);
    }

}
