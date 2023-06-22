<?php

namespace App\Controller;

use App\Repository\QuizRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_quiz')]
    public function index(QuizRepository $repository): Response
    {
        return $this->render('main.html.twig');
    }
}
