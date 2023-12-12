<?php

namespace App\Controller\CRUD;

use App\Entity\Quiz;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class CRUDController extends AbstractController
{
    public function __construct(protected EntityManagerInterface $entityManager)
    {
    }

    protected function handleForm(FormInterface $form, Request $request): bool
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($form->getData());
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    public function currentRouteIs(Request $request, string $route): bool{
        return $request->attributes->get('_route') === $route;
    }
}
