<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/new-app")]
class NewHomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        $this->addFlash('success', 'Somebody entered our new home!');
        return $this->render('/new/home/home.html.twig', []);
    }

    #[Route('/notifications', 'notifications')]
    public function notifications(): Response
    {
        return $this->render('layouts/frames/_flashes.html.twig');
    }
}
