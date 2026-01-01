<?php

namespace App\Controller;

use App\Entity\LearningPath;
use App\Entity\LearningPathItem;
use App\Repository\LearningPathRepository;
use App\Repository\SOPRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/learning-paths')]
final class LearningPathViewController extends AbstractController
{
    public function __construct(
        private LearningPathRepository $learningPathRepository,
        private SOPRepository $sopRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_learning_paths_index', methods: ['GET'])]
    public function index(): Response
    {
        $learningPaths = $this->learningPathRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('learning_path/index.html.twig', [
            'learning_paths' => $learningPaths,
        ]);
    }

    #[Route('/new', name: 'app_learning_paths_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $learningPath = new LearningPath();
            $learningPath->setTitle($request->request->get('title'));
            $learningPath->setDescription($request->request->get('description'));
            $learningPath->setCreatedAt(new \DateTimeImmutable());
            $learningPath->setCreatedBy($this->getUser());

            $this->entityManager->persist($learningPath);
            $this->entityManager->flush();

            $this->addFlash('success', 'Learning Path created successfully!');
            return $this->redirectToRoute('app_learning_paths_show', ['id' => $learningPath->getId()]);
        }

        return $this->render('learning_path/form.html.twig', [
            'learning_path' => null,
        ]);
    }

    #[Route('/{id}', name: 'app_learning_paths_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $learningPath = $this->learningPathRepository->find($id);

        if (!$learningPath) {
            throw $this->createNotFoundException('Learning Path not found');
        }

        // Get items sorted by position
        $items = $learningPath->getItems()->toArray();
        usort($items, fn($a, $b) => $a->getPosition() <=> $b->getPosition());

        // Get available SOPs for adding
        $availableSops = $this->sopRepository->findBy(['status' => 'published']);

        return $this->render('learning_path/show.html.twig', [
            'learning_path' => $learningPath,
            'items' => $items,
            'available_sops' => $availableSops,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_learning_paths_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $learningPath = $this->learningPathRepository->find($id);

        if (!$learningPath) {
            throw $this->createNotFoundException('Learning Path not found');
        }

        if ($request->isMethod('POST')) {
            $learningPath->setTitle($request->request->get('title'));
            $learningPath->setDescription($request->request->get('description'));

            $this->entityManager->flush();

            $this->addFlash('success', 'Learning Path updated successfully!');
            return $this->redirectToRoute('app_learning_paths_show', ['id' => $learningPath->getId()]);
        }

        return $this->render('learning_path/form.html.twig', [
            'learning_path' => $learningPath,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_learning_paths_delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        $learningPath = $this->learningPathRepository->find($id);

        if (!$learningPath) {
            throw $this->createNotFoundException('Learning Path not found');
        }

        $this->entityManager->remove($learningPath);
        $this->entityManager->flush();

        $this->addFlash('success', 'Learning Path deleted successfully!');
        return $this->redirectToRoute('app_learning_paths_index');
    }

    #[Route('/{id}/add-sop', name: 'app_learning_paths_add_sop', methods: ['POST'])]
    public function addSop(int $id, Request $request): Response
    {
        $learningPath = $this->learningPathRepository->find($id);

        if (!$learningPath) {
            throw $this->createNotFoundException('Learning Path not found');
        }

        $sopId = $request->request->get('sop_id');
        $sop = $this->sopRepository->find($sopId);

        if (!$sop) {
            $this->addFlash('error', 'SOP not found');
            return $this->redirectToRoute('app_learning_paths_show', ['id' => $id]);
        }

        // Check if SOP already in path
        foreach ($learningPath->getItems() as $item) {
            if ($item->getSop()->getId() === $sop->getId()) {
                $this->addFlash('error', 'This SOP is already in the learning path');
                return $this->redirectToRoute('app_learning_paths_show', ['id' => $id]);
            }
        }

        // Get next position
        $maxPosition = 0;
        foreach ($learningPath->getItems() as $item) {
            if ($item->getPosition() > $maxPosition) {
                $maxPosition = $item->getPosition();
            }
        }

        $newItem = new LearningPathItem();
        $newItem->setLearningPath($learningPath);
        $newItem->setSop($sop);
        $newItem->setPosition($maxPosition + 1);

        $this->entityManager->persist($newItem);
        $this->entityManager->flush();

        $this->addFlash('success', 'SOP added to learning path!');
        return $this->redirectToRoute('app_learning_paths_show', ['id' => $id]);
    }

    #[Route('/{id}/remove-item/{itemId}', name: 'app_learning_paths_remove_item', methods: ['POST'])]
    public function removeItem(int $id, int $itemId): Response
    {
        $learningPath = $this->learningPathRepository->find($id);

        if (!$learningPath) {
            throw $this->createNotFoundException('Learning Path not found');
        }

        foreach ($learningPath->getItems() as $item) {
            if ($item->getId() === $itemId) {
                $this->entityManager->remove($item);
                $this->entityManager->flush();
                $this->addFlash('success', 'SOP removed from learning path!');
                break;
            }
        }

        return $this->redirectToRoute('app_learning_paths_show', ['id' => $id]);
    }
}
