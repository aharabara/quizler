<?php

namespace App\Controller;

use App\Repository\QuizRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/app")]
class HomeController extends AbstractController
{
    #[Route('/', name: 'app_quiz')]
    public function index(QuizRepository $repository): Response
    {
        return $this->render('main.html.twig');
    }

    #[Route('/interview', name: 'app_interview')]
    public function interview(QuizRepository $repository): Response
    {
        return $this->render('interview.html.twig');
    }
    #[Route('/data.json', name: 'app_interviewwdas')]
    public function data(QuizRepository $repository): Response
    {
        return $this->json(
            json_decode('[
    {"id": "symfony_validator", "name": "Symfony validator", "tags": ["validation", "Symfony", "forms"], "parents": []},
    {"id": "validation_constraints", "name": "Validation constraints", "tags": ["constraints", "validation rules"], "parents": ["symfony_validator"]},
    {"id": "custom_constraints", "name": "Custom constraints", "tags": ["custom validation", "constraint classes"], "parents": ["symfony_validator"]},
    {"id": "validation_groups", "name": "Validation groups", "tags": ["grouped validation", "validation scenarios"], "parents": ["symfony_validator"]},
    {"id": "validation_messages", "name": "Validation messages", "tags": ["error messages", "validation feedback"], "parents": ["symfony_validator"]},
    {"id": "validation_events", "name": "Validation events", "tags": ["event listeners", "pre/post validation"], "parents": ["symfony_validator"]}
]')
//            [
//            [
//                "id" => 1,
//                "name" => "AI"
//            ],
//            [
//                "id" => 2,
//                "name" => "Machine Learning",
//                "parents" => [1]
//            ],
//            [
//                "id" => 3,
//                "name" => "Supervised Learning",
//                "tags" => ["regression", "classification", "labelled data", "training set"],
//                "parents" => [2]
//            ],
//            [
//                "id" => 4,
//                "name" => "Unsupervised Learning",
//                "tags" => ["clustering", "dimensionality reduction", "unlabelled data"],
//                "parents" => [2]
//            ],
//            [
//                "id" => 5,
//                "name" => "Reinforcement Learning",
//                "tags" => ["agent", "environment", "reward", "policy"],
//                "parents" => [2]
//            ],
//        ]
        );
    }
}
