<?php


namespace App\Service\HandlerService;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class HandlerService
{
    protected $em;
    protected $flash;

    public function __construct(EntityManagerInterface $em, FlashBagInterface $flash)
    {
        $this->em = $em;
        $this->flash = $flash;
    }

    /**
     * @param $entity
     *
     * @return bool
     */
    public function handle(Request $request, Form $form, $entity)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->create($entity);

            return true;
        }

        return false;
    }

    /**
     * @param $entity
     */
    protected function create($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
    }

    /**
     * @param $entity
     */
    protected function remove($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }



}