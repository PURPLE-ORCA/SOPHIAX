<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/users', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'id' => "1",
            'name' => "PURPLE ORCA",
            'email' => "purpleorca@gmail.com",
            'roles' => "ADMIN",
            'password' => "123456",
        ]);
    }

    #[Route('/user/create', name: 'app_user_add', methods: ['POST'])]
    public function add(EntityManagerInterface $entityManager): JsonResponse
    {
        $user = new User();
        $user->setName('PURPLE ORCA');
        $user->setPassword('123456');
        $user->setEmail('purpleorca@gmail.com');
        $user->setRoles(['ROLE_ADMIN']);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json($user);
    }
}
