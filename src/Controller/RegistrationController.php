<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');

            $errors = [];

            if (empty($name)) {
                $errors[] = 'Name is required';
            }
            if (empty($email)) {
                $errors[] = 'Email is required';
            }
            if (empty($password)) {
                $errors[] = 'Password is required';
            }
            if ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }

            if (empty($errors)) {
                $user = new User();
                $user->setName($name);
                $user->setEmail($email);
                $user->setCreatedAt(new \DateTimeImmutable());
                $user->setPassword($passwordHasher->hashPassword($user, $password));
                $user->setRoles(['ROLE_USER']);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Account created successfully! Please log in.');
                return $this->redirectToRoute('app_login');
            }

            return $this->render('security/register.html.twig', [
                'errors' => $errors,
                'last_name' => $name,
                'last_email' => $email,
            ]);
        }

        return $this->render('security/register.html.twig', [
            'errors' => [],
            'last_name' => '',
            'last_email' => '',
        ]);
    }
}
