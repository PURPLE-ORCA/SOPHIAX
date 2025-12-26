<?php

namespace App\Controller;

use App\Entity\SOP;
use App\Entity\SOPVersion;
use App\Repository\SOPRepository;
use App\Repository\CategoryRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/sops')]
final class SOPController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SOPRepository $sopRepository,
        private CategoryRepository $categoryRepository,
        private TagRepository $tagRepository,
        private UserRepository $userRepository
    ) {}

    #[Route('', name: 'sop_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $criteria = [];
        
        // Filter by status
        if ($status = $request->query->get('status')) {
            $criteria['status'] = $status;
        }
        
        // Filter by category
        if ($categoryId = $request->query->get('category')) {
            $category = $this->categoryRepository->find($categoryId);
            if ($category) {
                $criteria['category'] = $category;
            }
        }

        $sops = $this->sopRepository->findBy($criteria, ['createdAt' => 'DESC']);
        
        return $this->json(array_map(fn(SOP $sop) => $this->serializeSop($sop), $sops));
    }

    #[Route('/{id}', name: 'sop_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $sop = $this->sopRepository->find($id);

        if (!$sop) {
            return $this->json(['error' => 'SOP not found'], 404);
        }

        return $this->json($this->serializeSop($sop, true));
    }

    #[Route('', name: 'sop_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validation
        if (!isset($data['title']) || !isset($data['description']) || !isset($data['categoryId'])) {
            return $this->json(['error' => 'Title, description, and categoryId are required'], 400);
        }

        $category = $this->categoryRepository->find($data['categoryId']);
        if (!$category) {
            return $this->json(['error' => 'Category not found'], 404);
        }

        $sop = new SOP();
        $sop->setTitle($data['title']);
        $sop->setDescription($data['description']);
        $sop->setSummary($data['summary'] ?? null);
        $sop->setDifficulty($data['difficulty'] ?? null);
        $sop->setDepartment($data['department'] ?? null);
        $sop->setStatus($data['status'] ?? 'draft');
        $sop->setVersionNumber(1);
        $sop->setCategory($category);
        $sop->setCreatedAt(new \DateTimeImmutable());

        // Handle createdBy user
        if (isset($data['createdById'])) {
            $user = $this->userRepository->find($data['createdById']);
            if ($user) {
                $sop->setCreatedBy($user);
            }
        }

        // Handle tags
        if (isset($data['tagIds']) && is_array($data['tagIds'])) {
            foreach ($data['tagIds'] as $tagId) {
                $tag = $this->tagRepository->find($tagId);
                if ($tag) {
                    $sop->addTag($tag);
                }
            }
        }

        $this->entityManager->persist($sop);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'SOP created successfully',
            'id' => $sop->getId()
        ], 201);
    }

    #[Route('/{id}', name: 'sop_update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $sop = $this->sopRepository->find($id);

        if (!$sop) {
            return $this->json(['error' => 'SOP not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        // Create a version snapshot before updating
        $this->createVersionSnapshot($sop);

        if (isset($data['title'])) {
            $sop->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $sop->setDescription($data['description']);
        }
        if (array_key_exists('summary', $data)) {
            $sop->setSummary($data['summary']);
        }
        if (isset($data['difficulty'])) {
            $sop->setDifficulty($data['difficulty']);
        }
        if (isset($data['department'])) {
            $sop->setDepartment($data['department']);
        }
        if (isset($data['status'])) {
            $sop->setStatus($data['status']);
        }
        if (isset($data['categoryId'])) {
            $category = $this->categoryRepository->find($data['categoryId']);
            if ($category) {
                $sop->setCategory($category);
            }
        }

        // Handle tags update
        if (isset($data['tagIds']) && is_array($data['tagIds'])) {
            // Clear existing tags
            foreach ($sop->getTags() as $tag) {
                $sop->removeTag($tag);
            }
            // Add new tags
            foreach ($data['tagIds'] as $tagId) {
                $tag = $this->tagRepository->find($tagId);
                if ($tag) {
                    $sop->addTag($tag);
                }
            }
        }

        $sop->setVersionNumber($sop->getVersionNumber() + 1);
        $sop->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $this->json([
            'message' => 'SOP updated successfully',
            'id' => $sop->getId(),
            'versionNumber' => $sop->getVersionNumber()
        ]);
    }

    #[Route('/{id}', name: 'sop_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $sop = $this->sopRepository->find($id);

        if (!$sop) {
            return $this->json(['error' => 'SOP not found'], 404);
        }

        $this->entityManager->remove($sop);
        $this->entityManager->flush();

        return $this->json(['message' => 'SOP deleted successfully']);
    }

    #[Route('/{id}/publish', name: 'sop_publish', methods: ['POST'])]
    public function publish(int $id): JsonResponse
    {
        $sop = $this->sopRepository->find($id);

        if (!$sop) {
            return $this->json(['error' => 'SOP not found'], 404);
        }

        $sop->setStatus('published');
        $sop->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $this->json(['message' => 'SOP published successfully']);
    }

    #[Route('/{id}/unpublish', name: 'sop_unpublish', methods: ['POST'])]
    public function unpublish(int $id): JsonResponse
    {
        $sop = $this->sopRepository->find($id);

        if (!$sop) {
            return $this->json(['error' => 'SOP not found'], 404);
        }

        $sop->setStatus('draft');
        $sop->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $this->json(['message' => 'SOP unpublished successfully']);
    }

    private function createVersionSnapshot(SOP $sop): void
    {
        $version = new SOPVersion();
        $version->setSop($sop);
        $version->setVersionNumber($sop->getVersionNumber());
        $version->setCreatedAt(new \DateTimeImmutable());
        $version->setCreatedBy($sop->getCreatedBy());
        $version->setContent([
            'title' => $sop->getTitle(),
            'description' => $sop->getDescription(),
            'summary' => $sop->getSummary(),
            'difficulty' => $sop->getDifficulty(),
            'department' => $sop->getDepartment(),
            'status' => $sop->getStatus()
        ]);

        $this->entityManager->persist($version);
    }

    private function serializeSop(SOP $sop, bool $includeRelations = false): array
    {
        $data = [
            'id' => $sop->getId(),
            'title' => $sop->getTitle(),
            'description' => $sop->getDescription(),
            'summary' => $sop->getSummary(),
            'difficulty' => $sop->getDifficulty(),
            'department' => $sop->getDepartment(),
            'status' => $sop->getStatus(),
            'versionNumber' => $sop->getVersionNumber(),
            'createdAt' => $sop->getCreatedAt()?->format('c'),
            'updatedAt' => $sop->getUpdatedAt()?->format('c'),
            'category' => $sop->getCategory() ? [
                'id' => $sop->getCategory()->getId(),
                'name' => $sop->getCategory()->getName()
            ] : null,
            'createdBy' => $sop->getCreatedBy() ? [
                'id' => $sop->getCreatedBy()->getId(),
                'name' => $sop->getCreatedBy()->getName()
            ] : null,
            'tags' => array_map(fn($tag) => [
                'id' => $tag->getId(),
                'name' => $tag->getName()
            ], $sop->getTags()->toArray())
        ];

        if ($includeRelations) {
            $data['steps'] = array_map(fn($step) => [
                'id' => $step->getId(),
                'stepNumber' => $step->getStepNumber(),
                'content' => $step->getContent(),
                'attachment' => $step->getAttachment()
            ], $sop->getSteps()->toArray());

            $data['versionsCount'] = $sop->getVersions()->count();
        }

        return $data;
    }
}
