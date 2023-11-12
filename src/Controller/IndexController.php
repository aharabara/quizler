<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints\NotBlank;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Security $security): Response
    {
        /* fixme probably not required and can be replaced with security.yaml config */
        if ($security->getUser() !== null) {
            return $this->redirectToRoute('app_quiz');
        }
        return $this->render('public/login.html.twig');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(Security $security): Response
    {
        if ($security->getUser() !== null) {
            $security->logout(false);
        }

        return $this->redirectToRoute('app_home');
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils
    ): Response
    {
        $formBuilder = $this->createFormBuilder(options: [
            'action' => $this->generateUrl('app_login'),
            'csrf_token_id' => 'authenticate'
        ]);

        $formBuilder->add('username', TextType::class, [
            'constraints' => [
                new NotBlank(),
            ]
        ]);
        $formBuilder->add('password', PasswordType::class);
        $formBuilder->add('submit', SubmitType::class, [
            'label' => 'Login',
            'attr' => [
                'data-turbo-submits-with' => 'Wait a sec...'
            ]
        ]);

        $form = $formBuilder->getForm();

        $authenticationException = $authenticationUtils->getLastAuthenticationError();
        if ($authenticationException) {
            $form->addError(new FormError($authenticationException->getMessageKey()));
            $form->setData(['username' => $authenticationUtils->getLastUsername()]);
        }

        return $this->render('public/login/frames/sign-in.html.twig', [
            'form' => $form
        ]);
    }
}
