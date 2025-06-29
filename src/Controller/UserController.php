<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/users')]
class UserController extends AbstractController
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    #[Route('/register', name: 'api_user_register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            // Test 1: Can we get the request data?
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                return $this->json(['error' => 'Invalid JSON'], 400);
            }
        
            // Test 2: Basic validation
            if (empty($data['email']) || empty($data['password']) || empty($data['username'])) {
                return $this->json(['error' => 'Email, username and password are required'], 400);
            }
        
            // Test 3: Can we check for existing user?
            $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            if ($existingUser) {
                return $this->json(['error' => 'Email already taken'], 400);
            }
        
            // Test 4: Can we create a User object?
            $user = new User();
            $user->setEmail($data['email']);
            $user->setUsername($data['username']);
            $user->setRoles(['ROLE_USER']);
        
            // Test 5: Can we hash the password?
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        
            // Test 6: Can we persist to database?
            $em->persist($user);
            $em->flush();
        
            return $this->json(['message' => 'User registered successfully']);
        
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Registration failed',
                'message' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine()
            ], 500);
        }
    }
}
