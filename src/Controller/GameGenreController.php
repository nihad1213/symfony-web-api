<?php
namespace App\Controller;

use App\Entity\GameGenre;
use App\Repository\GameGenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/game-genres')]
class GameGenreController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private GameGenreRepository $gameGenreRepository,
        private ValidatorInterface $validator
    ) {}

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $genres = $this->gameGenreRepository->findAll();
        $data = array_map(fn(GameGenre $g) => [
            'id' => $g->getId(),
            'name' => $g->getName(),
            'createdAt' => $g->getCreatedAt()->format('c'),
            'updatedAt' => $g->getUpdatedAt()->format('c'),
        ], $genres);

        return $this->json($data);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $genre = $this->gameGenreRepository->find($id);
        if (!$genre) {
            return $this->json(['error' => 'Not found'], 404);
        }
        return $this->json([
            'id' => $genre->getId(),
            'name' => $genre->getName(),
            'createdAt' => $genre->getCreatedAt()->format('c'),
            'updatedAt' => $genre->getUpdatedAt()->format('c'),
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? null;

        if (!$name) {
            return $this->json(['error' => 'Name is required'], 400);
        }

        $genre = new GameGenre($name);

        $errors = $this->validator->validate($genre);
        if (count($errors) > 0) {
            return $this->json(['error' => (string) $errors], 400);
        }

        $this->em->persist($genre);
        $this->em->flush();

        return $this->json([
            'id' => $genre->getId(),
            'name' => $genre->getName(),
            'createdAt' => $genre->getCreatedAt()->format('c'),
            'updatedAt' => $genre->getUpdatedAt()->format('c'),
        ], 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $genre = $this->gameGenreRepository->find($id);
        if (!$genre) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? null;

        if (!$name) {
            return $this->json(['error' => 'Name is required'], 400);
        }

        $genre->setName($name);

        $errors = $this->validator->validate($genre);
        if (count($errors) > 0) {
            return $this->json(['error' => (string) $errors], 400);
        }

        $this->em->flush();

        return $this->json([
            'id' => $genre->getId(),
            'name' => $genre->getName(),
            'createdAt' => $genre->getCreatedAt()->format('c'),
            'updatedAt' => $genre->getUpdatedAt()->format('c'),
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $genre = $this->gameGenreRepository->find($id);
        if (!$genre) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $this->em->remove($genre);
        $this->em->flush();

        return $this->json(null, 204);
    }
}
