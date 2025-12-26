<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tags')]
final class TagController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TagRepository $tagRepository
    ) {}

    #[Route('', name: 'tag_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $tags = $this->tagRepository->findAll();
        
        return $this->json(array_map(fn(Tag $tag) => [
            'id' => $tag->getId(),
            'name' => $tag->getName(),
            'sopsCount' => $tag->getSops()->count()
        ], $tags));
    }

    #[Route('/{id}', name: 'tag_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $tag = $this->tagRepository->find($id);

        if (!$tag) {
            return $this->json(['error' => 'Tag not found'], 404);
        }

        return $this->json([
            'id' => $tag->getId(),
            'name' => $tag->getName(),
            'sopsCount' => $tag->getSops()->count()
        ]);
    }

    #[Route('', name: 'tag_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'])) {
            return $this->json(['error' => 'Name is required'], 400);
        }

        // Check if tag already exists
        $existing = $this->tagRepository->findOneBy(['name' => $data['name']]);
        if ($existing) {
            return $this->json(['error' => 'Tag with this name already exists'], 400);
        }

        $tag = new Tag();
        $tag->setName($data['name']);

        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Tag created successfully',
            'id' => $tag->getId()
        ], 201);
    }

    #[Route('/{id}', name: 'tag_update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $tag = $this->tagRepository->find($id);

        if (!$tag) {
            return $this->json(['error' => 'Tag not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            // Check if new name already exists
            $existing = $this->tagRepository->findOneBy(['name' => $data['name']]);
            if ($existing && $existing->getId() !== $tag->getId()) {
                return $this->json(['error' => 'Tag with this name already exists'], 400);
            }
            $tag->setName($data['name']);
        }

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Tag updated successfully',
            'id' => $tag->getId()
        ]);
    }

    #[Route('/{id}', name: 'tag_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $tag = $this->tagRepository->find($id);

        if (!$tag) {
            return $this->json(['error' => 'Tag not found'], 404);
        }

        // Remove tag from all SOPs first
        foreach ($tag->getSops() as $sop) {
            $sop->removeTag($tag);
        }

        $this->entityManager->remove($tag);
        $this->entityManager->flush();

        return $this->json(['message' => 'Tag deleted successfully']);
    }
}
