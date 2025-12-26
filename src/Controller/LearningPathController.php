<?php

namespace App\Controller;

use App\Entity\LearningPath;
use App\Entity\LearningPathItem;
use App\Repository\LearningPathRepository;
use App\Repository\LearningPathItemRepository;
use App\Repository\SOPRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/learning-paths')]
final class LearningPathController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LearningPathRepository $learningPathRepository,
        private LearningPathItemRepository $itemRepository,
        private SOPRepository $sopRepository,
        private UserRepository $userRepository
    ) {}

    #[Route('', name: 'learning_path_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $paths = $this->learningPathRepository->findBy([], ['createdAt' => 'DESC']);
        
        return $this->json(array_map(fn(LearningPath $path) => [
            'id' => $path->getId(),
            'title' => $path->getTitle(),
            'description' => $path->getDescription(),
            'sopCount' => $path->getItems()->count(),
            'createdAt' => $path->getCreatedAt()?->format('c'),
            'createdBy' => $path->getCreatedBy() ? [
                'id' => $path->getCreatedBy()->getId(),
                'name' => $path->getCreatedBy()->getName()
            ] : null
        ], $paths));
    }

    #[Route('/{id}', name: 'learning_path_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $path = $this->learningPathRepository->find($id);

        if (!$path) {
            return $this->json(['error' => 'Learning path not found'], 404);
        }

        $items = $this->itemRepository->findBy(
            ['learningPath' => $path],
            ['position' => 'ASC']
        );

        return $this->json([
            'id' => $path->getId(),
            'title' => $path->getTitle(),
            'description' => $path->getDescription(),
            'createdAt' => $path->getCreatedAt()?->format('c'),
            'createdBy' => $path->getCreatedBy() ? [
                'id' => $path->getCreatedBy()->getId(),
                'name' => $path->getCreatedBy()->getName()
            ] : null,
            'items' => array_map(fn(LearningPathItem $item) => [
                'id' => $item->getId(),
                'position' => $item->getPosition(),
                'sop' => $item->getSop() ? [
                    'id' => $item->getSop()->getId(),
                    'title' => $item->getSop()->getTitle(),
                    'description' => $item->getSop()->getDescription(),
                    'difficulty' => $item->getSop()->getDifficulty(),
                    'status' => $item->getSop()->getStatus()
                ] : null
            ], $items)
        ]);
    }

    #[Route('', name: 'learning_path_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title'])) {
            return $this->json(['error' => 'Title is required'], 400);
        }

        $path = new LearningPath();
        $path->setTitle($data['title']);
        $path->setDescription($data['description'] ?? null);
        $path->setCreatedAt(new \DateTimeImmutable());

        if (isset($data['createdById'])) {
            $user = $this->userRepository->find($data['createdById']);
            if ($user) {
                $path->setCreatedBy($user);
            }
        }

        $this->entityManager->persist($path);

        // Add SOPs if provided
        if (isset($data['sopIds']) && is_array($data['sopIds'])) {
            $position = 1;
            foreach ($data['sopIds'] as $sopId) {
                $sop = $this->sopRepository->find($sopId);
                if ($sop) {
                    $item = new LearningPathItem();
                    $item->setLearningPath($path);
                    $item->setSop($sop);
                    $item->setPosition($position);
                    $this->entityManager->persist($item);
                    $position++;
                }
            }
        }

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Learning path created successfully',
            'id' => $path->getId()
        ], 201);
    }

    #[Route('/{id}', name: 'learning_path_update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $path = $this->learningPathRepository->find($id);

        if (!$path) {
            return $this->json(['error' => 'Learning path not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $path->setTitle($data['title']);
        }
        if (array_key_exists('description', $data)) {
            $path->setDescription($data['description']);
        }

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Learning path updated successfully',
            'id' => $path->getId()
        ]);
    }

    #[Route('/{id}', name: 'learning_path_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $path = $this->learningPathRepository->find($id);

        if (!$path) {
            return $this->json(['error' => 'Learning path not found'], 404);
        }

        // Remove all items first
        foreach ($path->getItems() as $item) {
            $this->entityManager->remove($item);
        }

        $this->entityManager->remove($path);
        $this->entityManager->flush();

        return $this->json(['message' => 'Learning path deleted successfully']);
    }

    #[Route('/{id}/items', name: 'learning_path_add_item', methods: ['POST'])]
    public function addItem(int $id, Request $request): JsonResponse
    {
        $path = $this->learningPathRepository->find($id);

        if (!$path) {
            return $this->json(['error' => 'Learning path not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['sopId'])) {
            return $this->json(['error' => 'sopId is required'], 400);
        }

        $sop = $this->sopRepository->find($data['sopId']);
        if (!$sop) {
            return $this->json(['error' => 'SOP not found'], 404);
        }

        // Check if SOP is already in path
        foreach ($path->getItems() as $existingItem) {
            if ($existingItem->getSop()?->getId() === $sop->getId()) {
                return $this->json(['error' => 'SOP is already in this learning path'], 400);
            }
        }

        $position = $data['position'] ?? ($path->getItems()->count() + 1);

        $item = new LearningPathItem();
        $item->setLearningPath($path);
        $item->setSop($sop);
        $item->setPosition($position);

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'SOP added to learning path',
            'itemId' => $item->getId()
        ], 201);
    }

    #[Route('/{id}/items/{itemId}', name: 'learning_path_remove_item', methods: ['DELETE'])]
    public function removeItem(int $id, int $itemId): JsonResponse
    {
        $item = $this->itemRepository->find($itemId);

        if (!$item || $item->getLearningPath()?->getId() !== $id) {
            return $this->json(['error' => 'Item not found'], 404);
        }

        $path = $item->getLearningPath();
        $deletedPosition = $item->getPosition();

        $this->entityManager->remove($item);

        // Reorder remaining items
        if ($path) {
            foreach ($path->getItems() as $remaining) {
                if ($remaining->getPosition() > $deletedPosition) {
                    $remaining->setPosition($remaining->getPosition() - 1);
                }
            }
        }

        $this->entityManager->flush();

        return $this->json(['message' => 'Item removed from learning path']);
    }

    #[Route('/{id}/reorder', name: 'learning_path_reorder', methods: ['POST'])]
    public function reorder(int $id, Request $request): JsonResponse
    {
        $path = $this->learningPathRepository->find($id);

        if (!$path) {
            return $this->json(['error' => 'Learning path not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['order']) || !is_array($data['order'])) {
            return $this->json(['error' => 'Order array is required'], 400);
        }

        $position = 1;
        foreach ($data['order'] as $itemId) {
            $item = $this->itemRepository->find($itemId);
            if ($item && $item->getLearningPath()?->getId() === $id) {
                $item->setPosition($position);
                $position++;
            }
        }

        $this->entityManager->flush();

        return $this->json(['message' => 'Learning path items reordered successfully']);
    }
}
