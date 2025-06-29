<?php
namespace App\Controller;

use App\Entity\Game;
use App\Repository\GameRepository;
use App\Repository\GameGenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/games')]
class GameController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private GameRepository $gameRepository,
        private GameGenreRepository $gameGenreRepository,
        private ValidatorInterface $validator
    ) {}

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $games = $this->gameRepository->findAll();
        $data = array_map(fn(Game $g) => [
            'id' => $g->getId(),
            'name' => $g->getName(),
            'genre' => [
                'id' => $g->getGenre()->getId(),
                'name' => $g->getGenre()->getName(),
            ],
            'createdAt' => $g->getCreatedAt()->format('c'),
            'updatedAt' => $g->getUpdatedAt()->format('c'),
        ], $games);

        return $this->json($data);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $game = $this->gameRepository->find($id);
        if (!$game) {
            return $this->json(['error' => 'Not found'], 404);
        }
        return $this->json([
            'id' => $game->getId(),
            'name' => $game->getName(),
            'genre' => [
                'id' => $game->getGenre()->getId(),
                'name' => $game->getGenre()->getName(),
            ],
            'createdAt' => $game->getCreatedAt()->format('c'),
            'updatedAt' => $game->getUpdatedAt()->format('c'),
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $name = $data['name'] ?? null;
        $genreId = $data['genre_id'] ?? null;

        if (!$name || !$genreId) {
            return $this->json(['error' => 'Name and genre_id are required'], 400);
        }

        $genre = $this->gameGenreRepository->find($genreId);
        if (!$genre) {
            return $this->json(['error' => 'Genre not found'], 404);
        }

        $game = new Game();
        $game->setName($name);
        $game->setGenre($genre);

        $errors = $this->validator->validate($game);
        if (count($errors) > 0) {
            return $this->json(['error' => (string)$errors], 400);
        }

        $this->em->persist($game);
        $this->em->flush();

        return $this->json([
            'id' => $game->getId(),
            'name' => $game->getName(),
            'genre' => [
                'id' => $genre->getId(),
                'name' => $genre->getName(),
            ],
            'createdAt' => $game->getCreatedAt()->format('c'),
            'updatedAt' => $game->getUpdatedAt()->format('c'),
        ], 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $game = $this->gameRepository->find($id);
        if (!$game) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $name = $data['name'] ?? null;
        $genreId = $data['genre_id'] ?? null;

        if (!$name || !$genreId) {
            return $this->json(['error' => 'Name and genre_id are required'], 400);
        }

        $genre = $this->gameGenreRepository->find($genreId);
        if (!$genre) {
            return $this->json(['error' => 'Genre not found'], 404);
        }

        $game->setName($name);
        $game->setGenre($genre);

        $errors = $this->validator->validate($game);
        if (count($errors) > 0) {
            return $this->json(['error' => (string)$errors], 400);
        }

        $this->em->flush();

        return $this->json([
            'id' => $game->getId(),
            'name' => $game->getName(),
            'genre' => [
                'id' => $genre->getId(),
                'name' => $genre->getName(),
            ],
            'createdAt' => $game->getCreatedAt()->format('c'),
            'updatedAt' => $game->getUpdatedAt()->format('c'),
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $game = $this->gameRepository->find($id);
        if (!$game) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $this->em->remove($game);
        $this->em->flush();

        return $this->json(null, 204);
    }
}
