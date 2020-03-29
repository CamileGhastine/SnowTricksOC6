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
     * @Route("/trick/new", name="trick_new")
     * @Route("/trick/{id}/edit", name="trick_edit")
     */
    public function form(Request $request, EntityManagerInterface $em, Trick $trick = null)
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
                $trick->setImage('images/tricks/image.jpg');
                $trick->setCreatedAt(new \DateTime());
            }

            $em->persist($trick);
            $em->flush();

            if($request->getRequestUri() == '/trick/new')
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
     * @Route("/trick/{id}", name="trick_show")
     */
    public function show(Trick $trick)
    {
        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
        ]);
    }

}
